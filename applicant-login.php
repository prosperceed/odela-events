<?php
session_start();
require './config/db.php';

// If already logged in as applicant, redirect to dashboard
if (isset($_SESSION['applicant_id'])) {
    header("Location: applicant-dashboard.php");
    exit();
}

// If admin is logged in, redirect to admin dashboard
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format";
    } else {
        // Check applicant credentials
        $stmt = $conn->prepare("SELECT id, fullname, email, password FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $applicant = $result->fetch_assoc();
            if (password_verify($password, $applicant['password'])) {
                $_SESSION['applicant_id'] = $applicant['id'];
                $_SESSION['applicant_name'] = $applicant['fullname'];
                $_SESSION['applicant_email'] = $applicant['email'];
                header("Location: applicant-dashboard.php");
                exit();
            } else {
                $error = "Invalid email or password";
            }
        } else {
            $error = "Invalid email or password";
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Login - Odela Events</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; color: #e2e8f0; }
        .app-bg {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(99,102,241,0.16), transparent 20%),
                        radial-gradient(circle at bottom right, rgba(236,72,153,0.16), transparent 18%),
                        linear-gradient(180deg, rgba(7,11,26,0.96), rgba(10,15,35,0.98));
            position: relative;
            overflow: hidden;
        }
        .app-bg::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,0.32), rgba(15,23,42,0.76));
            pointer-events: none;
        }
        .form-card {
            background: rgba(15, 23, 42, 0.72);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }
        .glass-input {
            width: 100%;
            padding: 14px 14px 14px 42px;
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 16px;
            background: rgba(255,255,255,0.08);
            color: #eef2ff;
            transition: all 0.25s;
            outline: none;
            backdrop-filter: blur(14px);
        }
        .glass-input:focus {
            border-color: rgba(59,130,246,0.9);
            background: rgba(255,255,255,0.14);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }
        .glass-input::placeholder { color: rgba(203,213,225,0.8); }
        .alert-danger {
            background: rgba(244,63,94,0.16);
            border: 1px solid rgba(248,113,113,0.24);
            color: #fecdd3;
            border-radius: 16px;
            padding: 14px 16px;
            margin-bottom: 20px;
        }
        .glass-button {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 16px;
            background: linear-gradient(135deg, #3b82f6, #ec4899);
            color: #ffffff;
            font-weight: 700;
            box-shadow: 0 16px 36px rgba(236,72,153,0.24);
            transition: opacity 0.2s, transform 0.2s;
        }
        .glass-button:hover { opacity: 0.95; transform: translateY(-1px); }
        .text-soft { color: #cbd5e1; }
        .link-soft { color: #bfdbfe; text-decoration: none; }
        .link-soft:hover { color: #ffffff; }
        .form-icon { color: #94a3b8; }
        .form-title { color: #eef2ff; }
    </style>
</head>
<body class="app-bg flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="form-card p-8">
            <div class="text-center mb-8">
                <p class="text-soft uppercase tracking-[0.24em] text-sm mb-3">Welcome back</p>
                <!-- <h1 class="text-3xl font-bold form-title">User Login</h1> -->
                <p class="text-soft mt-2">Odela Events</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-soft font-semibold mb-2">Email Address</label>
                    <div class="relative">
                        <i class="fas fa-envelope absolute left-3 top-3 text-soft"></i>
                        <input type="email" id="email" name="email" class="glass-input" placeholder="your@email.com" required>
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-soft font-semibold mb-2">Password</label>
                    <div class="relative">
                        <i class="fas fa-lock absolute left-3 top-3 text-soft"></i>
                        <input type="password" id="password" name="password" class="glass-input" placeholder="Enter your password" required>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="glass-button">
                    <i class="fas fa-sign-in-alt mr-2"></i>Login
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-soft">Don't have an account? <a href="signup.php" class="link-soft font-semibold">Sign up here</a></p>
            </div>

           
            <div class="mt-6 text-center">
                <a href="index.php" class="link-soft">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Home
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.tailwindcss.com"></script>

</body>
</html>
<?php $conn->close(); ?>

<?php
session_start();
include '../config/db.php';

$error = $success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname   = trim($_POST['fullname'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (!$fullname || !$email || !$password || !$confirm) {
        $error = 'All fields are required.';
    } elseif (strlen($fullname) < 3) {
        $error = 'Full name must be at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/', $password)) {
        $error = 'Password must be 8+ characters with an uppercase letter, a number, and a special character.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = 'An admin with this email already exists.';
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $ins = $conn->prepare("INSERT INTO admins(fullname, email, password, created_at) VALUES (?, ?, ?, NOW())");
            $ins->bind_param("sss", $fullname, $email, $hashed);
            if ($ins->execute()) {
                $success = 'Admin account created successfully.';
            } else {
                $error = 'Registration failed. Please try again.';
            }
            $ins->close();
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
    <title>Admin Registration  Odela Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --blue:      #1D4ED8;
            --blue-dark: #1E3A8A;
            --blue-mid:  #2563EB;
            --blue-light:#DBEAFE;
            --pink:      #EC4899;
            --pink-dark: #BE185D;
            --pink-light:#FCE7F3;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(145deg, #07102b 0%, #0f1f5c 40%, #1D4ED8 75%, #be185d 100%);
            display: flex; align-items: center; justify-content: center;
            padding: 40px 16px;
            position: relative; overflow-x: hidden;
        }

        /* Subtle dot grid */
        body::before {
            content: '';
            position: fixed; inset: 0; pointer-events: none;
            background-image: radial-gradient(rgba(255,255,255,0.045) 1px, transparent 1px);
            background-size: 32px 32px;
        }

        /* Soft glow blobs */
        .blob {
            position: fixed; border-radius: 50%; pointer-events: none; filter: blur(80px);
        }
        .blob-1 {
            width: 420px; height: 420px;
            background: rgba(236,72,153,0.14);
            top: -100px; right: -80px;
        }
        .blob-2 {
            width: 340px; height: 340px;
            background: rgba(29,78,216,0.18);
            bottom: -80px; left: -60px;
        }

        /* ── Card ── */
        .card {
            width: 100%; max-width: 460px;
            background: rgba(255,255,255,0.97);
            border-radius: 24px;
            box-shadow:
                0 40px 80px rgba(0,0,0,0.35),
                0 0 0 1px rgba(255,255,255,0.12);
            overflow: hidden;
            position: relative; z-index: 10;
        }

        /* ── Card top accent bar ── */
        .card-top {
            height: 5px;
            background: linear-gradient(90deg, var(--blue-dark), var(--blue), var(--pink));
        }

        /* ── Card header ── */
        .card-header {
            padding: 36px 40px 24px;
            border-bottom: 1px solid #f3f4f6;
        }

        .brand {
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 28px;
            text-decoration: none;
        }
        .brand-name {
            font-family: 'Georgia', serif;
            font-size: 1.1rem; font-weight: 700;
            color: var(--blue-dark);
        }
        .brand-dot {
            width: 7px; height: 7px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--pink));
            display: inline-block;
        }

        .card-header h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.75rem; font-weight: 900;
            color: #0f172a; line-height: 1.2; margin-bottom: 6px;
        }
        .card-header p {
            font-size: 13.5px; color: #6b7280; line-height: 1.5;
        }

        /* ── Card body ── */
        .card-body { padding: 28px 40px 36px; }

        /* ── Alerts ── */
        .alert {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 13px 16px; border-radius: 12px;
            font-size: 13.5px; line-height: 1.5; margin-bottom: 22px;
        }
        .alert i { margin-top: 1px; flex-shrink: 0; }
        .alert-err {
            background: #fff1f2;
            border: 1px solid #fda4af;
            border-left: 4px solid #f43f5e;
            color: #9f1239;
        }
        .alert-ok {
            background: #f0fdf4;
            border: 1px solid #86efac;
            border-left: 4px solid #22c55e;
            color: #14532d;
        }

        /* ── Form fields ── */
        .field { margin-bottom: 18px; }

        .field-label {
            display: block;
            font-size: 10.5px; font-weight: 700;
            letter-spacing: 1.6px; text-transform: uppercase;
            color: #374151; margin-bottom: 6px;
        }
        .field-label span { color: #f43f5e; margin-left: 2px; }

        .input-wrap { position: relative; }

        .field-input {
            width: 100%; padding: 12px 16px;
            border: 1.5px solid #e5e7eb; border-radius: 12px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            color: #111827; background: #fafafa;
            outline: none; transition: all 0.22s;
        }
        .field-input:focus {
            border-color: var(--blue);
            background: #fff;
            box-shadow: 0 0 0 4px rgba(29,78,216,0.09);
        }
        .field-input.has-err {
            border-color: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244,63,94,0.08);
        }
        .field-input::placeholder { color: #b0b7c3; }

        /* eye toggle */
        .eye-btn {
            position: absolute; right: 14px; top: 50%;
            transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: 14px;
            transition: color 0.2s; padding: 0;
        }
        .eye-btn:hover { color: var(--blue); }

        .field-hint {
            font-size: 11.5px; color: #9ca3af;
            margin-top: 5px; display: block;
        }
        .field-err {
            font-size: 11.5px; color: #f43f5e;
            margin-top: 5px; display: none;
        }
        .field-err.show { display: block; }

        /* Password strength */
        .strength-track {
            height: 3px; background: #e5e7eb;
            border-radius: 3px; margin-top: 8px; overflow: hidden;
        }
        .strength-fill {
            height: 100%; width: 0%; border-radius: 3px;
            transition: width 0.35s, background 0.35s;
        }
        .strength-label {
            font-size: 11px; color: #9ca3af; margin-top: 4px; display: block;
            transition: color 0.3s;
        }


        /* ── Divider ── */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 6px 0 20px; color: #d1d5db; font-size: 11px;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #f0f0f0;
        }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%; padding: 14px;
            border: none; border-radius: 13px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 14.5px; font-weight: 700; letter-spacing: 0.3px;
            color: #fff;
            background: linear-gradient(135deg, var(--blue-dark), var(--blue), var(--pink));
            background-size: 200% auto;
            transition: background-position 0.4s ease, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 6px 22px rgba(29,78,216,0.32);
        }
        .btn-submit:hover {
            background-position: right center;
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(29,78,216,0.4);
        }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.65; cursor: not-allowed; transform: none; }

        /* ── Footer note ── */
        .card-footer {
            padding: 16px 40px 28px;
            text-align: center;
        }
        .card-footer a {
            font-size: 13px; color: var(--blue);
            font-weight: 600; text-decoration: none;
            transition: color 0.2s;
        }
        .card-footer a:hover { color: var(--pink); }
    </style>
</head>
<body>

<div class="blob blob-1"></div>
<div class="blob blob-2"></div>

<div class="card">

    <!-- Gradient top bar -->
    <div class="card-top"></div>

    <!-- Header -->
    <div class="card-header">
        <a href="../index.php" class="brand">
            <span class="brand-name">Odela Events</span>
            <span class="brand-dot"></span>
        </a>
        <h1>Admin Registration</h1>
        <p>Create a secure admin account to manage bookings, clients, and messages.</p>
    </div>

    <!-- Body -->
    <div class="card-body">

        <?php if ($error): ?>
        <div class="alert alert-err">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($error); ?></span>
        </div>
        <?php endif; ?>

        <?php if ($success): ?>
        <div class="alert alert-ok">
            <i class="fas fa-check-circle"></i>
            <span><?php echo $success; ?> <a href="login.php" style="font-weight:700; color:#15803d;">Sign in now →</a></span>
        </div>
        <?php endif; ?>

        <form method="POST" id="regForm" action="register.php" novalidate>

            <!-- Full Name -->
            <div class="field">
                <label class="field-label" for="fullname">Full Name <span>*</span></label>
                <input
                    type="text" id="fullname" name="fullname"
                    class="field-input" placeholder="Caleb Osanga"
                    value="<?php echo htmlspecialchars($_POST['fullname'] ?? ''); ?>"
                    autocomplete="name">
                <span class="field-err" id="err-fullname">Please enter your full name (min. 3 characters).</span>
            </div>

            <!-- Email -->
            <div class="field">
                <label class="field-label" for="email">Email Address <span>*</span></label>
                <input
                    type="email" id="email" name="email"
                    class="field-input" placeholder="admin@odelaevents.com"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    autocomplete="email">
                <span class="field-err" id="err-email">Enter a valid email address.</span>
            </div>

            <!-- Password -->
            <div class="field">
                <label class="field-label" for="password">Password <span>*</span></label>
                <div class="input-wrap">
                    <input
                        type="password" id="password" name="password"
                        class="field-input" placeholder="Create a strong password"
                        style="padding-right: 44px;"
                        oninput="checkStrength(this.value)"
                        autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="togglePass('password', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="strength-track">
                    <div class="strength-fill" id="strengthFill"></div>
                </div>
                <span class="strength-label" id="strengthLabel">Min. 8 chars · 1 uppercase · 1 number · 1 special character</span>
                <span class="field-err" id="err-password">Password must be 8+ characters with uppercase, number, and special character.</span>
            </div>

            <!-- Confirm Password -->
            <div class="field" style="margin-bottom: 24px;">
                <label class="field-label" for="confirm_password">Confirm Password <span>*</span></label>
                <div class="input-wrap">
                    <input
                        type="password" id="confirm_password" name="confirm_password"
                        class="field-input" placeholder="Repeat your password"
                        style="padding-right: 44px;"
                        autocomplete="new-password">
                    <button type="button" class="eye-btn" onclick="togglePass('confirm_password', this)" tabindex="-1">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <span class="field-err" id="err-confirm">Passwords do not match.</span>
            </div>

            <!-- Submit -->
            <button type="submit" class="btn-submit" id="submitBtn">
                Create Admin Account
            </button>

        </form>
    </div>

    <!-- Footer -->
    <div class="card-footer">
        <p style="font-size: 13px; color: #9ca3af;">
            Already have an account? <a href="login.php">Sign in →</a>
        </p>
      
    </div>

</div><!-- /card -->

<script>
/* ── Password visibility toggle ── */
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

/* ── Password strength meter ── */
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score   = 0;

    if (val.length >= 8)            score++;
    if (/[A-Z]/.test(val))          score++;
    if (/[0-9]/.test(val))          score++;
    if (/[\W_]/.test(val))          score++;

    const levels = [
        { w: '0%',   bg: '#e5e7eb', txt: 'Min. 8 chars · 1 uppercase · 1 number · 1 special character' },
        { w: '25%',  bg: '#f43f5e', txt: 'Weak — add uppercase, numbers, special characters' },
        { w: '50%',  bg: '#f97316', txt: 'Fair — add a special character' },
        { w: '75%',  bg: '#eab308', txt: 'Good — almost there!' },
        { w: '100%', bg: '#22c55e', txt: 'Strong password ✓' },
    ];

    const l = levels[score];
    fill.style.width      = l.w;
    fill.style.background = l.bg;
    label.textContent     = l.txt;
    label.style.color     = score === 0 ? '#9ca3af' : l.bg;
}

/* ── Client-side validation ── */
document.getElementById('regForm').addEventListener('submit', function (e) {
    let ok = true;

    // Helpers
    function fail(id, inputId) {
        document.getElementById(id).classList.add('show');
        document.getElementById(inputId).classList.add('has-err');
        ok = false;
    }
    function clear(id, inputId) {
        document.getElementById(id).classList.remove('show');
        document.getElementById(inputId).classList.remove('has-err');
    }

    // Reset
    [['err-fullname','fullname'],['err-email','email'],
     ['err-password','password'],['err-confirm','confirm_password']].forEach(([e,i]) => clear(e,i));

    const fn  = document.getElementById('fullname').value.trim();
    const em  = document.getElementById('email').value.trim();
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirm_password').value;

    if (fn.length < 3)  fail('err-fullname', 'fullname');
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)) fail('err-email', 'email');
    if (!/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/.test(pw)) fail('err-password', 'password');
    if (pw !== cpw)     fail('err-confirm', 'confirm_password');

    if (!ok) {
        e.preventDefault();
        // Scroll to first error
        document.querySelector('.has-err')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});
</script>
</body>
</html>
<?php if (isset($conn)) $conn->close(); ?>
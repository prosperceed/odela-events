<?php
session_start();
include("./includes/header.php");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up — Odela Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap');

        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; }
        .font-display { font-family: 'Playfair Display', serif; }

        /* ── Page background ── */
        .page-bg {
            min-height: 100vh;
            background: radial-gradient(circle at top left, rgba(59,130,246,0.18), transparent 24%),
                        radial-gradient(circle at bottom right, rgba(236,72,153,0.18), transparent 20%),
                        linear-gradient(180deg, rgba(7,11,26,0.96), rgba(15,23,42,0.98));
            position: relative;
            overflow: hidden;
        }
        .page-bg::before {
            content: '';
            position: absolute; inset: 0;
            background: linear-gradient(180deg, rgba(15,23,42,0.32), rgba(15,23,42,0.76));
            pointer-events: none;
        }
        /* Decorative blobs */
        .blob-1 {
            position: absolute; width: 400px; height: 400px; border-radius: 50%;
            background: radial-gradient(circle, rgba(236,72,153,0.18) 0%, transparent 70%);
            top: -100px; right: -80px; pointer-events: none;
        }
        .blob-2 {
            position: absolute; width: 350px; height: 350px; border-radius: 50%;
            background: radial-gradient(circle, rgba(29,78,216,0.20) 0%, transparent 70%);
            bottom: -80px; left: -60px; pointer-events: none;
        }

        /* ── Card ── */
        .form-card {
            background: rgba(15, 23, 42, 0.72);
            border: 1px solid rgba(255,255,255,0.14);
            border-radius: 28px;
            box-shadow: 0 30px 80px rgba(0,0,0,0.35);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        /* ── Left panel ── */
        .left-panel {
            background: linear-gradient(160deg, #1D4ED8 0%, #7c3aed 50%, #EC4899 100%);
            border-radius: 20px 0 0 20px;
            padding: 48px 40px;
            display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .left-panel::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.05'%3E%3Ccircle cx='30' cy='30' r='20'/%3E%3C/g%3E%3C/svg%3E");
        }
        .left-panel::after {
            content: '';
            position: absolute;
            width: 300px; height: 300px; border-radius: 50%;
            background: rgba(255,255,255,0.06);
            bottom: -80px; right: -80px;
        }

        .perk-item {
            display: flex; align-items: flex-start; gap: 12px;
            margin-bottom: 20px;
        }
        .perk-icon {
            width: 36px; height: 36px; border-radius: 50%;
            background: rgba(255,255,255,0.15);
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0; font-size: 15px; color: #fff;
        }

        /* ── Form inputs ── */
        .field-group { position: relative; }

        .field-label {
            display: block;
            font-size: 11px; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase;
            color: #cbd5e1; margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            padding: 14px 16px;
            border: 1.5px solid rgba(255,255,255,0.14);
            border-radius: 16px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            color: #eef2ff;
            background: rgba(255,255,255,0.08);
            transition: all 0.25s;
            outline: none;
            backdrop-filter: blur(14px);
        }
        .field-input:focus {
            border-color: rgba(59,130,246,0.9);
            background: rgba(255,255,255,0.14);
            box-shadow: 0 0 0 4px rgba(59,130,246,0.12);
        }
        .field-input.error-state {
            border-color: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244,63,94,0.08);
        }
        .field-input::placeholder { color: rgba(203,213,225,0.75); }

        select.field-input { cursor: pointer; }

        /* eye toggle */
        .eye-btn {
            position: absolute; right: 14px; top: 38px;
            background: none; border: none; cursor: pointer;
            color: #9ca3af; font-size: 15px;
            transition: color 0.2s;
        }
        .eye-btn:hover { color: #1D4ED8; }

        /* error text */
        .err-msg {
            font-size: 12px; color: #f43f5e;
            margin-top: 5px; display: none;
        }
        .err-msg.show { display: block; }

        /* password strength */
        .strength-bar {
            height: 4px; border-radius: 4px; margin-top: 8px;
            background: #e5e7eb; overflow: hidden;
        }
        .strength-fill {
            height: 100%; border-radius: 4px; width: 0%;
            transition: width 0.4s, background 0.4s;
        }

        /* ── Submit button ── */
        .btn-submit {
            width: 100%;
            padding: 15px;
            border-radius: 16px;
            border: none; cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 15px; font-weight: 700; letter-spacing: 0.3px;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6, #ec4899);
            transition: opacity 0.25s, transform 0.2s, box-shadow 0.25s;
            box-shadow: 0 16px 36px rgba(236,72,153,0.24);
        }
        .btn-submit:hover {
            opacity: 0.92; transform: translateY(-2px);
            box-shadow: 0 14px 32px rgba(29,78,216,0.4);
        }
        .btn-submit:active { transform: translateY(0); }

        /* ── Divider ── */
        .divider {
            display: flex; align-items: center; gap: 12px;
            color: #9ca3af; font-size: 12px; margin: 4px 0;
        }
        .divider::before, .divider::after {
            content: ''; flex: 1; height: 1px; background: #e5e7eb;
        }

        /* ── Step indicators ── */
        .step-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: #e5e7eb; transition: background 0.3s, width 0.3s;
            border-radius: 4px;
        }
        .step-dot.active { background: linear-gradient(135deg,#1D4ED8,#EC4899); width: 24px; }

        /* ── Checkbox custom ── */
        .custom-check { display: flex; align-items: flex-start; gap: 10px; cursor: pointer; }
        .custom-check input[type="checkbox"] { accent-color: #1D4ED8; width: 16px; height: 16px; margin-top: 2px; flex-shrink: 0; cursor: pointer; }

        /* Alert boxes */
        .alert-success {
            background: rgba(52,211,153,0.16); border: 1px solid rgba(52,211,153,0.28);
            border-left: 4px solid rgba(52,211,153,0.95); color: #d1fae5;
            padding: 14px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 20px;
        }
        .alert-error {
            background: rgba(244,63,94,0.14); border: 1px solid rgba(244,63,94,0.28);
            border-left: 4px solid rgba(244,63,94,0.95); color: #fecdd3;
            padding: 14px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .left-panel { display: none; }
            .form-card { border-radius: 20px; }
        }
    </style>
</head>
<body>

<div class="page-bg flex items-center justify-center px-4 py-16">
    <div class="blob-1"></div>
    <div class="blob-2"></div>

    <div class="form-card w-full max-w-3xl relative z-10 p-0 overflow-hidden">

        <div style="padding: 48px 44px;">

            <!-- Top row -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
                <div>
                    <h1 style="font-family:'Playfair Display',serif; font-size:1.75rem; font-weight:900; color:#eef2ff; margin-bottom:4px;">
                        Sign Up
                    </h1>
                    <p style="font-size:13px; color:#cbd5e1;">
                        Already have an account?
                        <a href="applicant-login.php" style="color:#1D4ED8; font-weight:600; text-decoration:none;">Sign in →</a>
                    </p>
                </div>
                <!-- Step dots -->
                <div style="display:flex; gap:6px; align-items:center;">
                    <div class="step-dot active" id="dot1"></div>
                    <div class="step-dot" id="dot2"></div>
                </div>
            </div>

            <!-- Alerts -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert-success">
                    <strong>Welcome to Odela Events!</strong> Your account has been created. <a href="applicant-login.php" style="color:#15803d; font-weight:600;">Sign in now →</a>
                </div>
            <?php endif; ?>
            <?php if (isset($_GET['error'])): ?>
                <div class="alert-error">
                    <strong>Oops!</strong> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form action="./process/signup_process.php" method="POST" id="signupForm">

                <!-- ── STEP 1: Personal Info ── -->
                <div id="step1">
                    <p style="font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#EC4899; margin-bottom:18px;">Step 1 of 2 — Personal Information</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                        <!-- Full Name -->
                        <div class="field-group" style="grid-column: span 2;">
                            <label class="field-label">Full Name <span style="color:#f43f5e;">*</span></label>
                            <input type="text" id="fullname" name="fullname" class="field-input" placeholder="e.g. Prosper Success">
                            <p class="err-msg" id="err-fullname">Full name must be at least 3 characters.</p>
                        </div>

                        <!-- Email -->
                        <div class="field-group">
                            <label class="field-label">Email Address <span style="color:#f43f5e;">*</span></label>
                            <input type="email" id="email" name="email" class="field-input" placeholder="you@example.com">
                            <p class="err-msg" id="err-email">Please enter a valid email address.</p>
                        </div>

                    
                    </div>

                    <button type="button" onclick="goToStep(2)"
                        style="width:100%; padding:13px; border-radius:12px; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:14px; font-weight:700; color:#fff; background:linear-gradient(135deg,#1D4ED8,#EC4899); transition:opacity 0.2s; margin-top:4px;">
                        Continue →
                    </button>
                </div>

                <!-- ── STEP 2: Password ── -->
                <div id="step2" style="display:none;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#EC4899; margin-bottom:18px;">Step 2 of 2 — Secure Your Account</p>

                    <div style="display:grid; gap:18px; margin-bottom:18px;">

                        <!-- Password -->
                        <div class="field-group">
                            <label class="field-label">Password <span style="color:#f43f5e;">*</span></label>
                            <input type="password" id="password" name="password" class="field-input" placeholder="Create a strong password" oninput="checkStrength(this.value)" style="padding-right:44px;">
                            <button type="button" class="eye-btn" onclick="togglePass('password', this)"><i class="fas fa-eye"></i></button>
                            <!-- Strength bar -->
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <p id="strengthLabel" style="font-size:11px; color:#9ca3af; margin-top:4px;">Min. 8 characters, 1 uppercase, 1 number</p>
                            <p class="err-msg" id="err-password">Password must be 8+ chars with 1 uppercase & 1 number.</p>
                        </div>

                        <!-- Confirm Password -->
                        <div class="field-group">
                            <label class="field-label">Confirm Password <span style="color:#f43f5e;">*</span></label>
                            <input type="password" id="confirm_password" name="confirm_password" class="field-input" placeholder="Repeat your password" style="padding-right:44px;">
                            <button type="button" class="eye-btn" onclick="togglePass('confirm_password', this)"><i class="fas fa-eye"></i></button>
                            <p class="err-msg" id="err-confirm">Passwords do not match.</p>
                        </div>
                    </div>

                    <!-- Terms -->
                    <label class="custom-check" style="margin-bottom:24px;">
                        <input type="checkbox" id="terms" name="terms">
                        <span style="font-size:13px; color:#374151; line-height:1.5;">
                            I agree to the <a href="#" style="color:#1D4ED8; font-weight:600; text-decoration:none;">Terms of Service</a>
                            and <a href="#" style="color:#EC4899; font-weight:600; text-decoration:none;">Privacy Policy</a>
                        </span>
                    </label>
                    <p class="err-msg" id="err-terms" style="margin-top:-18px; margin-bottom:16px;">You must agree to the terms to continue.</p>

                    <div style="display:flex; gap:12px;">
                        <button type="button" onclick="goToStep(1)"
                            style="flex:1; padding:15px; border-radius:12px; border:1.5px solid #e5e7eb; cursor:pointer; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:#374151; background:#fff;">
                            ← Back
                        </button>
                        <button type="submit" class="btn-submit" style="flex:2;">
                            Create My Account
                        </button>
                    </div>
                </div>

            </form>

            <!-- Divider -->
            <div class="divider" style="margin-top:28px;">or sign up with</div>

            <!-- Social buttons -->
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-top:16px;">
                <button style="padding:11px; border-radius:12px; border:1.5px solid #e5e7eb; background:#fff; cursor:pointer; font-size:13px; font-weight:600; color:#374151; display:flex; align-items:center; justify-content:center; gap:8px; transition:border-color 0.2s;">
                    <img src="https://www.google.com/favicon.ico" width="16" height="16" alt="Google"> Google
                </button>
                <button style="padding:11px; border-radius:12px; border:1.5px solid #e5e7eb; background:#fff; cursor:pointer; font-size:13px; font-weight:600; color:#374151; display:flex; align-items:center; justify-content:center; gap:8px; transition:border-color 0.2s;">
                    <i class="fab fa-facebook" style="color:#1877f2; font-size:16px;"></i> Facebook
                </button>
            </div>

        </div><!-- /right panel -->
    </div><!-- /form-card -->
</div>

<script>
/* ══ Step Navigation ══ */
function goToStep(n) {
    if (n === 2 && !validateStep1()) return;

    [1,2].forEach(i => {
        document.getElementById('step' + i).style.display = i === n ? 'block' : 'none';
        const dot = document.getElementById('dot' + i);
        dot.classList.toggle('active', i <= n);
        dot.style.width = i === n ? '24px' : (i < n ? '24px' : '8px');
    });
}

/* ══ Step 1 validation ══ */
function validateStep1() {
    let ok = true;
    const fn = document.getElementById('fullname');
    const em = document.getElementById('email');

    clearErr(['err-fullname','err-email']);
    fn.classList.remove('error-state');
    em.classList.remove('error-state');

    if (fn.value.trim().length < 3) { showErr('err-fullname'); fn.classList.add('error-state'); ok = false; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em.value.trim())) { showErr('err-email'); em.classList.add('error-state'); ok = false; }
    return ok;
}

document.getElementById('signupForm').addEventListener('submit', function(e) {
    let ok = true;
    const pw  = document.getElementById('password');
    const cpw = document.getElementById('confirm_password');
    const tr  = document.getElementById('terms');

    clearErr(['err-password','err-confirm','err-terms']);
    pw.classList.remove('error-state');
    cpw.classList.remove('error-state');

    if (!/^(?=.*[A-Z])(?=.*\d).{8,}$/.test(pw.value)) {
        showErr('err-password'); pw.classList.add('error-state'); ok = false;
    }
    if (pw.value !== cpw.value) {
        showErr('err-confirm'); cpw.classList.add('error-state'); ok = false;
    }
    if (!tr.checked) { showErr('err-terms'); ok = false; }

    if (!ok) e.preventDefault();
});

/* ══ Password strength ══ */
function checkStrength(val) {
    const fill  = document.getElementById('strengthFill');
    const label = document.getElementById('strengthLabel');
    let score = 0;
    if (val.length >= 8)          score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const map = [
        { w: '0%',   bg: '#e5e7eb', txt: 'Min. 8 characters, 1 uppercase, 1 number' },
        { w: '25%',  bg: '#f43f5e', txt: 'Weak — add uppercase letters and numbers' },
        { w: '50%',  bg: '#f97316', txt: 'Fair — add special characters' },
        { w: '75%',  bg: '#eab308', txt: 'Good — almost there!' },
        { w: '100%', bg: '#22c55e', txt: 'Strong password ✓' },
    ];
    fill.style.width      = map[score].w;
    fill.style.background = map[score].bg;
    label.textContent     = map[score].txt;
    label.style.color     = map[score].bg;
}

/* ══ Eye toggle ══ */
function togglePass(id, btn) {
    const el = document.getElementById(id);
    const isText = el.type === 'text';
    el.type = isText ? 'password' : 'text';
    btn.innerHTML = isText ? '<i class="fas fa-eye"></i>' : '<i class="fas fa-eye-slash"></i>';
}

/* ══ Helpers ══ */
function showErr(id) {
    const el = document.getElementById(id);
    if (el) el.classList.add('show');
}
function clearErr(ids) {
    ids.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.classList.remove('show');
    });
}
</script>

<?php include './includes/footer.php'; ?>
</body>
</html>
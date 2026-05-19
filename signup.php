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
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #1e3a8a 70%, #be185d 100%);
            position: relative;
            overflow: hidden;
        }
        .page-bg::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none'%3E%3Ccircle cx='40' cy='40' r='1.5' fill='%23ffffff' fill-opacity='0.04'/%3E%3C/g%3E%3C/svg%3E");
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
            background: rgba(255, 255, 255, 0.97);
            border-radius: 24px;
            box-shadow: 0 40px 80px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.1);
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
            color: #374151; margin-bottom: 6px;
        }

        .field-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 14px; font-family: 'Inter', sans-serif;
            color: #111827;
            background: #fafafa;
            transition: all 0.25s;
            outline: none;
        }
        .field-input:focus {
            border-color: #1D4ED8;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(29,78,216,0.08);
        }
        .field-input.error-state {
            border-color: #f43f5e;
            box-shadow: 0 0 0 4px rgba(244,63,94,0.08);
        }
        .field-input::placeholder { color: #9ca3af; }

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
            border-radius: 14px;
            border: none; cursor: pointer;
            font-family: 'Inter', sans-serif;
            font-size: 15px; font-weight: 700; letter-spacing: 0.3px;
            color: #fff;
            background: linear-gradient(135deg, #1D4ED8, #EC4899);
            transition: opacity 0.25s, transform 0.2s, box-shadow 0.25s;
            box-shadow: 0 8px 24px rgba(29,78,216,0.35);
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
            background: #f0fdf4; border: 1px solid #86efac;
            border-left: 4px solid #22c55e; color: #15803d;
            padding: 14px 16px; border-radius: 12px; font-size: 14px; margin-bottom: 20px;
        }
        .alert-error {
            background: #fff1f2; border: 1px solid #fda4af;
            border-left: 4px solid #f43f5e; color: #be123c;
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

    <div class="form-card w-full max-w-5xl relative z-10" style="display:grid; grid-template-columns: 380px 1fr;">

        <!-- ════════════════════════════
             LEFT PANEL
        ════════════════════════════ -->
        <div class="left-panel relative z-10">

            <!-- Brand -->
            <div>
                <a href="../index.php" style="text-decoration:none; display:flex; align-items:center; gap:8px; margin-bottom:40px;">
                    <span style="font-family:'Georgia',serif; font-size:1.4rem; font-weight:700; color:#fff;">Odela Events</span>
                    <span style="width:8px;height:8px;border-radius:50%; background:rgba(255,255,255,0.6);display:inline-block;"></span>
                </a>

                <h2 class="font-display" style="font-size:2rem; font-weight:900; color:#fff; line-height:1.2; margin-bottom:12px;">
                    Create Your Account
                </h2>
                <p style="color:rgba(255,255,255,0.7); font-size:14px; line-height:1.7; margin-bottom:36px;">
                    Join thousands of clients who trust Odela Events to make their celebrations unforgettable.
                </p>

                <div>
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-calendar-check"></i></div>
                        <div>
                            <p style="color:#fff; font-weight:600; font-size:14px; margin-bottom:2px;">Easy Event Booking</p>
                            <p style="color:rgba(255,255,255,0.6); font-size:12px; line-height:1.5;">Book and manage your events from your personal dashboard.</p>
                        </div>
                    </div>
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-utensils"></i></div>
                        <div>
                            <p style="color:#fff; font-weight:600; font-size:14px; margin-bottom:2px;">Custom Menu Builder</p>
                            <p style="color:rgba(255,255,255,0.6); font-size:12px; line-height:1.5;">Select dishes, quantities, and packages tailored to your event.</p>
                        </div>
                    </div>
                    <div class="perk-item">
                        <div class="perk-icon"><i class="fas fa-headset"></i></div>
                        <div>
                            <p style="color:#fff; font-weight:600; font-size:14px; margin-bottom:2px;">Dedicated Support</p>
                            <p style="color:rgba(255,255,255,0.6); font-size:12px; line-height:1.5;">A personal event coordinator assigned to every booking.</p>
                        </div>
                    </div>
                    <div class="perk-item" style="margin-bottom:0;">
                        <div class="perk-icon"><i class="fas fa-shield-alt"></i></div>
                        <div>
                            <p style="color:#fff; font-weight:600; font-size:14px; margin-bottom:2px;">Secure & Private</p>
                            <p style="color:rgba(255,255,255,0.6); font-size:12px; line-height:1.5;">Your data and payment details are always fully protected.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer note -->
            <p style="color:rgba(255,255,255,0.45); font-size:12px; position:relative; z-index:2;">
                &copy; <?php echo date('Y'); ?> Odela Events. All rights reserved.
            </p>
        </div>

        <!-- ════════════════════════════
             RIGHT PANEL — FORM
        ════════════════════════════ -->
        <div style="padding: 48px 44px; overflow-y: auto;">

            <!-- Top row -->
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:32px;">
                <div>
                    <h1 style="font-family:'Playfair Display',serif; font-size:1.75rem; font-weight:900; color:#111827; margin-bottom:4px;">
                        Sign Up
                    </h1>
                    <p style="font-size:13px; color:#6b7280;">
                        Already have an account?
                        <a href="applicant-login.php" style="color:#1D4ED8; font-weight:600; text-decoration:none;">Sign in →</a>
                    </p>
                </div>
                <!-- Step dots -->
                <div style="display:flex; gap:6px; align-items:center;">
                    <div class="step-dot active" id="dot1"></div>
                    <div class="step-dot" id="dot2"></div>
                    <div class="step-dot" id="dot3"></div>
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
                    <p style="font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#EC4899; margin-bottom:18px;">Step 1 of 3 — Personal Information</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                        <!-- Full Name -->
                        <div class="field-group" style="grid-column: span 2;">
                            <label class="field-label">Full Name <span style="color:#f43f5e;">*</span></label>
                            <input type="text" id="fullname" name="fullname" class="field-input" placeholder="e.g. Aisha Bello">
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

                <!-- ── STEP 2: Program Info ── -->
                <div id="step2" style="display:none;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#EC4899; margin-bottom:18px;">Step 2 of 3 — Event Preferences</p>

                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px; margin-bottom:18px;">
                        <!-- Service Area -->
                        <div class="field-group">
                            <label class="field-label">Service Interest <span style="color:#f43f5e;">*</span></label>
                            <select id="category" name="category" class="field-input">
                                <option value="">Select a service</option>
                                <option value="Wedding Catering">Wedding Catering</option>
                                <option value="Corporate Events">Corporate Events</option>
                                <option value="Birthday Parties">Birthday Parties</option>
                                <option value="Naming Ceremonies">Naming Ceremonies</option>
                                <option value="Buffet Setup">Buffet Setup</option>
                                <option value="Small Chops Only">Small Chops Only</option>
                                <option value="Full Service Package">Full Service Package</option>
                                <option value="Custom Package">Custom Package</option>
                            </select>
                            <p class="err-msg" id="err-trade">Please select a service area.</p>
                        </div>

                        <!--Package -->
                        <div class="field-group">
                            <label class="field-label">Preferred Package <span style="color:#f43f5e;">*</span></label>
                            <select id="package" name="package" class="field-input">
                                <option value="">Select a package</option>
                                <option value="Intimate (Up to 50)">Intimate — Up to 50 guests</option>
                                <option value="Grand Celebration (Up to 200)">Grand Celebration — Up to 200 guests</option>
                                <option value="Corporate">Corporate Events</option>
                                <option value="Custom">Custom / Let Us Advise</option>
                            </select>
                            <p class="err-msg" id="err-dept">Please select a package.</p>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px; margin-top:4px;">
                        <button type="button" onclick="goToStep(1)"
                            style="flex:1; padding:13px; border-radius:12px; border:1.5px solid #e5e7eb; cursor:pointer; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:#374151; background:#fff; transition:background 0.2s;">
                            ← Back
                        </button>
                        <button type="button" onclick="goToStep(3)"
                            style="flex:2; padding:13px; border-radius:12px; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:14px; font-weight:700; color:#fff; background:linear-gradient(135deg,#1D4ED8,#EC4899);">
                            Continue →
                        </button>
                    </div>
                </div>

                <!-- ── STEP 3: Password ── -->
                <div id="step3" style="display:none;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:2px; text-transform:uppercase; color:#EC4899; margin-bottom:18px;">Step 3 of 3 — Secure Your Account</p>

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
                        <button type="button" onclick="goToStep(2)"
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
    if (n === 3 && !validateStep2()) return;

    [1,2,3].forEach(i => {
        document.getElementById('step' + i).style.display = i === n ? 'block' : 'none';
        const dot = document.getElementById('dot' + i);
        dot.classList.toggle('active', i <= n);
        // pill width for active
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

/* ══ Step 2 validation ══ */
function validateStep2() {
    let ok = true;
    const ta = document.getElementById('category');
    const dp = document.getElementById('package');

    clearErr(['err-trade','err-dept']);
    ta.classList.remove('error-state');
    dp.classList.remove('error-state');

    if (!ta.value) { showErr('err-trade'); ta.classList.add('error-state'); ok = false; }
    if (!dp.value) { showErr('err-dept');  dp.classList.add('error-state'); ok = false; }
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
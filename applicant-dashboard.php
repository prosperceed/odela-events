<?php
session_start();
include './config/db.php';

if (!isset($_SESSION['applicant_id'])) {
    header("Location: applicant-login.php");
    exit();
}

$applicant_id   = $_SESSION['applicant_id'];
$applicant_name = $_SESSION['applicant_name'];

// ── Fetch user details ──
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $applicant_id);
$stmt->execute();
$applicant = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$applicant) { session_destroy(); header("Location: applicant-login.php"); exit(); }

// ── Fetch user bookings ──
$bookings = [];
$bStmt = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
$bStmt->bind_param("i", $applicant_id);
$bStmt->execute();
$bookings = $bStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$bStmt->close();

// ── Fetch user messages + admin replies ──
$messages = [];
$mStmt = $conn->prepare("SELECT * FROM messages WHERE email = ? ORDER BY created_at DESC");
$mStmt->bind_param("s", $applicant['email']);
$mStmt->execute();
$messages = $mStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$mStmt->close();

// ── Handle new booking ──
$booking_success = $booking_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'book') {
    $event_type   = $conn->real_escape_string(trim($_POST['event_type']));
    $event_date   = $conn->real_escape_string($_POST['event_date']);
    $user_email = $conn->real_escape_string($_POST['user_email']);
    $guest_count  = (int)$_POST['guest_count'];
    $package      = $conn->real_escape_string($_POST['package']);
    $dishes       = $conn->real_escape_string($_POST['dishes']);
    $venue        = $conn->real_escape_string(trim($_POST['venue']));
    $special_note = $conn->real_escape_string(trim($_POST['special_note']));

    if (!$event_type || !$event_date || !$guest_count || !$package) {
        $booking_error = 'Please fill all required fields.';
    } elseif (strtotime($event_date) < strtotime('+3 days')) {
        $booking_error = 'Event date must be at least 3 days from today.';
    } else {
        $ins = $conn->prepare("INSERT INTO bookings (user_id, event_type, event_date,user_email, guest_count, package, dishes, venue, special_note, status, created_at) VALUES (?,?,?,?,?,?,?,?,?,'Pending',NOW())");
        $ins->bind_param("ississsss", $applicant_id, $event_type, $event_date, $user_email, $guest_count, $package, $dishes, $venue, $special_note);
        if ($ins->execute()) {
            $booking_success = 'Booking submitted! Our team will confirm within 24 hours.';
            // Refresh bookings
            $bStmt2 = $conn->prepare("SELECT * FROM bookings WHERE user_id = ? ORDER BY created_at DESC");
            $bStmt2->bind_param("i", $applicant_id);
            $bStmt2->execute();
            $bookings = $bStmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            $bStmt2->close();
        } else {
            error_log('Booking insert failed: ' . $ins->error);
            $booking_error = 'Something went wrong. Please try again.';
        }
        $ins->close();
    }
}

// ── Handle new message ──
$msg_success = $msg_error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'message') {
    $subject = $conn->real_escape_string(trim($_POST['subject']));
    $body    = $conn->real_escape_string(trim($_POST['body']));

    if (!$subject || !$body) {
        $msg_error = 'Please fill in both subject and message.';
    } else {
        $ins = $conn->prepare("INSERT INTO messages (fullname, email, subject, message, status, created_at) VALUES (?,?,?,?, 'Unread', NOW())");
        $ins->bind_param("ssss", $applicant['fullname'], $applicant['email'], $subject, $body);
        if ($ins->execute()) {
            $msg_success = 'Message sent! Admin will reply to your registered email.';
            $mStmt2 = $conn->prepare("SELECT * FROM messages WHERE email = ? ORDER BY created_at DESC");
            $mStmt2->bind_param("s", $applicant['email']);
        } else {
            $msg_error = 'Failed to send message. Please try again.';
        }
        $ins->close();
    }
}

// ── Cancel booking ──
if (isset($_GET['cancel_booking'])) {
    $bid = (int)$_GET['cancel_booking'];
    $conn->query("UPDATE bookings SET status='Cancelled' WHERE id=$bid AND user_id=$applicant_id AND status='Pending'");
    header("Location: applicant-dashboard.php?tab=bookings");
    exit();
}

// ── Stats ──
$total_bookings    = count($bookings);
$active_bookings   = count(array_filter($bookings, function($b) { return $b['status'] === 'Confirmed'; }));
$pending_bookings  = count(array_filter($bookings, function($b) { return $b['status'] === 'Pending'; }));
$total_messages    = count($messages);

$active_tab = $_POST['tab'] ?? $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard — Odela Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --blue:       #1D4ED8;
            --blue-dark:  #1E3A8A;
            --blue-light: #DBEAFE;
            --pink:       #EC4899;
            --pink-dark:  #BE185D;
            --pink-light: #FCE7F3;
        }

        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F0F4FF; }
        .font-display { font-family: 'Playfair Display', serif; }

        /* ── Sidebar ── */
        .sidebar {
            width: 260px; min-height: 100vh;
            background: linear-gradient(160deg, #0f172a 0%, #1e3a8a 60%, #1D4ED8 100%);
            position: fixed; left: 0; top: 0;
            display: flex; flex-direction: column;
            box-shadow: 4px 0 30px rgba(29,78,216,0.18);
            z-index: 100;
        }
        .sidebar-logo {
            padding: 28px 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-nav { flex: 1; padding: 16px 12px; overflow-y: auto; }

        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px; border-radius: 12px;
            color: rgba(255,255,255,0.65); font-size: 14px; font-weight: 500;
            text-decoration: none; cursor: pointer; border: none; background: none;
            width: 100%; text-align: left; margin-bottom: 4px;
            transition: all 0.2s;
        }
        .nav-item:hover { background: rgba(255,255,255,0.08); color: #fff; }
        .nav-item.active {
            background: rgba(236,72,153,0.18);
            color: #fff;
            border-left: 3px solid var(--pink);
            padding-left: 13px;
        }
        .nav-item .nav-icon { width: 20px; text-align: center; font-size: 15px; }
        .nav-badge {
            margin-left: auto; background: var(--pink);
            color: #fff; font-size: 10px; font-weight: 700;
            padding: 2px 7px; border-radius: 50px; min-width: 20px; text-align: center;
        }

        .sidebar-footer {
            padding: 16px 12px;
            border-top: 1px solid rgba(255,255,255,0.08);
        }
        .avatar-circle {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, var(--blue), var(--pink));
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 15px; color: #fff; flex-shrink: 0;
        }

        /* ── Main content ── */
        .main-content { margin-left: 260px; padding: 32px; min-height: 100vh; }

        /* ── Top bar ── */
        .top-bar {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            border-radius: 16px; padding: 16px 24px;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
            box-shadow: 0 2px 20px rgba(29,78,216,0.06);
            border: 1px solid rgba(255,255,255,0.8);
        }

        /* ── Stat cards ── */
        .stat-card {
            background: #fff; border-radius: 18px; padding: 24px;
            border: 1px solid rgba(29,78,216,0.08);
            box-shadow: 0 4px 20px rgba(29,78,216,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative; overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 32px rgba(29,78,216,0.12); }
        .stat-card::before {
            content: ''; position: absolute;
            top: 0; right: 0; width: 80px; height: 80px;
            border-radius: 50%; opacity: 0.07;
            transform: translate(20px, -20px);
        }
        .stat-card.blue::before  { background: var(--blue); }
        .stat-card.pink::before  { background: var(--pink); }
        .stat-card.green::before { background: #22c55e; }
        .stat-card.amber::before { background: #f59e0b; }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2.4rem; font-weight: 900; line-height: 1;
        }

        /* ── Section cards ── */
        .section-card {
            background: #fff; border-radius: 20px; padding: 28px;
            border: 1px solid rgba(29,78,216,0.07);
            box-shadow: 0 4px 20px rgba(29,78,216,0.05);
            margin-bottom: 24px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; font-weight: 900; color: #111827;
            margin-bottom: 20px;
        }

        /* ── Form inputs ── */
        .field-label {
            display: block; font-size: 11px; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase; color: #374151; margin-bottom: 6px;
        }
        .field-input {
            width: 100%; padding: 11px 14px; border: 1.5px solid #e5e7eb;
            border-radius: 10px; font-size: 14px; font-family: 'Inter', sans-serif;
            color: #111827; background: #fafafa; outline: none; transition: all 0.2s;
        }
        .field-input:focus {
            border-color: var(--blue);
            background: #fff; box-shadow: 0 0 0 4px rgba(29,78,216,0.08);
        }
        .field-input::placeholder { color: #9ca3af; }
        select.field-input { cursor: pointer; }

        /* ── Booking card ── */
        .booking-card {
            border: 1.5px solid #f0f0f0; border-radius: 14px;
            padding: 20px; margin-bottom: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
            position: relative; overflow: hidden;
        }
        .booking-card:hover { border-color: rgba(29,78,216,0.2); box-shadow: 0 4px 16px rgba(29,78,216,0.07); }
        .booking-card::before {
            content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
        }
        .booking-card.Pending::before   { background: #f59e0b; }
        .booking-card.Confirmed::before { background: #22c55e; }
        .booking-card.Cancelled::before { background: #f43f5e; }
        .booking-card.Completed::before { background: var(--blue); }

        /* ── Status pills ── */
        .status-pill {
            display: inline-flex; align-items: center; gap: 5px;
            padding: 4px 12px; border-radius: 50px; font-size: 11px; font-weight: 700;
            letter-spacing: 0.5px; text-transform: uppercase;
        }
        .pill-pending   { background: #fef3c7; color: #92400e; }
        .pill-confirmed { background: #dcfce7; color: #15803d; }
        .pill-cancelled { background: #fee2e2; color: #991b1b; }
        .pill-completed { background: #dbeafe; color: #1e40af; }

        /* ── Message thread ── */
        .message-card {
            border: 1.5px solid #f0f0f0; border-radius: 14px;
            overflow: hidden; margin-bottom: 14px;
        }
        .message-header {
            padding: 14px 18px;
            background: linear-gradient(135deg, #eff6ff, #fdf2f8);
            display: flex; justify-content: space-between; align-items: center;
        }
        .reply-bubble {
            background: linear-gradient(135deg, #eff6ff, #f0fdf4);
            border-left: 3px solid var(--blue);
            padding: 14px 18px; margin: 0 18px 16px;
            border-radius: 0 10px 10px 0; font-size: 14px; color: #374151; line-height: 1.7;
        }
        .reply-bubble.admin-reply {
            background: linear-gradient(135deg, #fdf2f8, #fff7ed);
            border-left-color: var(--pink);
        }

        /* ── Submit buttons ── */
        .btn-primary {
            padding: 13px 28px; border-radius: 12px; border: none; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 14px; font-weight: 700;
            color: #fff; background: linear-gradient(135deg, var(--blue), var(--pink));
            transition: opacity 0.2s, transform 0.2s;
            box-shadow: 0 6px 20px rgba(29,78,216,0.3);
        }
        .btn-primary:hover { opacity: 0.9; transform: translateY(-1px); }

        .btn-ghost {
            padding: 10px 20px; border-radius: 10px; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 600;
            color: #374151; background: #fff; border: 1.5px solid #e5e7eb;
            transition: all 0.2s; text-decoration: none; display: inline-block;
        }
        .btn-ghost:hover { border-color: var(--blue); color: var(--blue); }

        .btn-danger {
            padding: 8px 16px; border-radius: 8px; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600;
            color: #f43f5e; background: #fff1f2; border: 1px solid #fecdd3; transition: all 0.2s;
        }
        .btn-danger:hover { background: #ffe4e6; }

        /* ── Alerts ── */
        .alert-ok  { background:#f0fdf4; border:1px solid #86efac; border-left:4px solid #22c55e; color:#15803d; padding:13px 16px; border-radius:10px; font-size:14px; margin-bottom:16px; }
        .alert-err { background:#fff1f2; border:1px solid #fda4af; border-left:4px solid #f43f5e; color:#be123c; padding:13px 16px; border-radius:10px; font-size:14px; margin-bottom:16px; }

        /* ── Hero greeting ── */
        .hero-greeting {
            border-radius: 20px; padding: 32px 36px; margin-bottom: 28px; color: #fff;
            background: linear-gradient(135deg, #1E3A8A 0%, #1D4ED8 45%, #EC4899 100%);
            position: relative; overflow: hidden;
        }
        .hero-greeting::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23fff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='3'/%3E%3C/g%3E%3C/svg%3E");
        }

        /* ── Dish checkbox grid ── */
        .dish-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(160px,1fr)); gap: 10px; }
        .dish-check { display: none; }
        .dish-label {
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            padding: 12px 8px; border-radius: 10px; border: 1.5px solid #e5e7eb;
            cursor: pointer; font-size: 12px; font-weight: 600; color: #374151;
            text-align: center; gap: 6px; background: #fafafa;
            transition: all 0.2s;
        }
        .dish-label:hover { border-color: var(--blue); background: #eff6ff; }
        .dish-check:checked + .dish-label {
            border-color: var(--pink); background: var(--pink-light); color: var(--pink-dark);
        }
        .dish-label i { font-size: 20px; }

        /* Tabs hidden content */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* Mobile responsive */
        @media (max-width: 900px) {
            .sidebar { width: 100%; min-height: auto; position: relative; flex-direction: row; flex-wrap: wrap; }
            .main-content { margin-left: 0; padding: 16px; }
        }
    </style>
</head>
<body>


<!-- SIDE BAR-->

<div class="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <a href="index.php" style="text-decoration:none; display:flex; align-items:center; gap:8px;">
            <span style="font-family:'Georgia',serif; font-size:1.2rem; font-weight:700; color:#fff;">Odela Events</span>
            <span style="width:7px;height:7px;border-radius:50%;background:var(--pink);display:inline-block;"></span>
        </a>
        <p style="color:rgba(255,255,255,0.45); font-size:11px; margin-top:4px; letter-spacing:0.5px;">Client Dashboard</p>
    </div>

    <!-- Nav -->
    <div class="sidebar-nav">
        <p style="color:rgba(255,255,255,0.3); font-size:10px; letter-spacing:2px; text-transform:uppercase; padding: 8px 16px 6px; margin-bottom:4px;">Main</p>
        <button class="nav-item <?php echo $active_tab==='overview' ? 'active' : ''; ?>" onclick="switchTab('overview')">
            <span class="nav-icon"><i class="fas fa-th-large"></i></span> Overview
        </button>
        <button class="nav-item <?php echo $active_tab==='book' ? 'active' : ''; ?>" onclick="switchTab('book')">
            <span class="nav-icon"><i class="fas fa-calendar-plus"></i></span> New Booking
        </button>
        <button class="nav-item <?php echo $active_tab==='bookings' ? 'active' : ''; ?>" onclick="switchTab('bookings')">
            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span> My Bookings
            <?php if ($pending_bookings > 0): ?>
                <span class="nav-badge"><?php echo $pending_bookings; ?></span>
            <?php endif; ?>
        </button>
        <button class="nav-item <?php echo $active_tab==='messages' ? 'active' : ''; ?>" onclick="switchTab('messages')">
            <span class="nav-icon"><i class="fas fa-comments"></i></span> Messages
            <?php
            $unread = count(array_filter($messages, function($m) { return isset($m['status']) && $m['status'] === 'Unread'; }));
            if ($unread > 0): ?>
                <span class="nav-badge"><?php echo $unread; ?></span>
            <?php endif; ?>
        </button>

        <p style="color:rgba(255,255,255,0.3); font-size:10px; letter-spacing:2px; text-transform:uppercase; padding: 16px 16px 6px; margin-bottom:4px;">Account</p>

        <button class="nav-item <?php echo $active_tab==='profile' ? 'active' : ''; ?>" onclick="switchTab('profile')">
            <span class="nav-icon"><i class="fas fa-user"></i></span> My Profile
        </button>
        <a href="index.php" class="nav-item">
            <span class="nav-icon"><i class="fas fa-home"></i></span> Back to Site
        </a>
    </div>

    <!-- User footer -->
    <div class="sidebar-footer">
        <div style="display:flex; align-items:center; gap:10px; margin-bottom:12px;">
            <div class="avatar-circle"><?php echo strtoupper(substr($applicant_name,0,1)); ?></div>
            <div style="min-width:0;">
                <p style="color:#fff; font-size:13px; font-weight:600; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($applicant_name); ?></p>
                <p style="color:rgba(255,255,255,0.45); font-size:11px; margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($applicant['email']); ?></p>
            </div>
        </div>
        <a href="applicant-logout.php"
           onclick="return confirm('Sign out of your account?')"
           style="display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:10px; background:rgba(244,63,94,0.12); color:#f87171; font-size:13px; font-weight:600; text-decoration:none; transition:background 0.2s;">
            <i class="fas fa-sign-out-alt"></i> Sign Out
        </a>
    </div>
</div>

<!-- ════════════════════════════════════════
     MAIN CONTENT
════════════════════════════════════════ -->
<div class="main-content">

    <!-- Top bar -->
    <div class="top-bar">
        <div>
            <p style="font-size:13px; color:#9ca3af; margin-bottom:2px;"><?php echo date('l, F j, Y'); ?></p>
            <p style="font-size:15px; font-weight:600; color:#111827;">
                Good <?php echo (date('H') < 12) ? 'morning' : ((date('H') < 17) ? 'afternoon' : 'evening'); ?>,
                <?php echo htmlspecialchars(explode(' ', $applicant_name)[0]); ?> 👋
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:12px;">
            <button onclick="switchTab('book')"
                style="padding:10px 20px; border-radius:50px; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:700; color:#fff; background:linear-gradient(135deg,var(--blue),var(--pink)); box-shadow:0 4px 14px rgba(29,78,216,0.3);">
                <i class="fas fa-plus mr-1"></i> New Booking
            </button>
        </div>
    </div>

    <!-- ════ TAB: OVERVIEW ════ -->
    <div id="tab-overview" class="tab-pane <?php echo $active_tab==='overview' ? 'active' : ''; ?>">

        <!-- Greeting hero -->
        <div class="hero-greeting">
            <div style="position:relative; z-index:1;">
                <p style="font-size:11px; letter-spacing:3px; text-transform:uppercase; color:rgba(255,255,255,0.6); margin-bottom:8px;">Client Portal</p>
                <h1 class="font-display" style="font-size:2rem; font-weight:900; margin-bottom:8px;">
                    Welcome back, <?php echo htmlspecialchars(explode(' ', $applicant_name)[0]); ?>!
                </h1>
                <p style="color:rgba(255,255,255,0.75); font-size:15px; max-width:480px; line-height:1.6;">
                    Manage your event bookings, track status updates, and communicate with our team — all in one place.
                </p>
                <div style="display:flex; gap:12px; margin-top:20px; flex-wrap:wrap;">
                    <button onclick="switchTab('book')" style="padding:11px 24px; border-radius:50px; border:2px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); color:#fff; font-size:13px; font-weight:700; cursor:pointer; backdrop-filter:blur(4px); transition:background 0.2s;">
                        <i class="fas fa-calendar-plus mr-2"></i>Book an Event
                    </button>
                    <button onclick="switchTab('messages')" style="padding:11px 24px; border-radius:50px; border:2px solid rgba(255,255,255,0.4); background:rgba(255,255,255,0.12); color:#fff; font-size:13px; font-weight:700; cursor:pointer; transition:background 0.2s;">
                        <i class="fas fa-comment mr-2"></i>Send a Message
                    </button>
                </div>
            </div>
        </div>

        <!-- Stats row -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(180px,1fr)); gap:16px; margin-bottom:28px;">
            <div class="stat-card blue">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#6b7280;">Total Bookings</p>
                    <span style="width:36px;height:36px;border-radius:10px;background:#dbeafe;display:flex;align-items:center;justify-content:center;"><i class="fas fa-calendar" style="color:var(--blue); font-size:15px;"></i></span>
                </div>
                <p class="stat-num" style="color:var(--blue);"><?php echo $total_bookings; ?></p>
                <p style="font-size:12px; color:#9ca3af; margin-top:4px;">All time</p>
            </div>

            <div class="stat-card green">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#6b7280;">Confirmed</p>
                    <span style="width:36px;height:36px;border-radius:10px;background:#dcfce7;display:flex;align-items:center;justify-content:center;"><i class="fas fa-check-circle" style="color:#22c55e; font-size:15px;"></i></span>
                </div>
                <p class="stat-num" style="color:#22c55e;"><?php echo $active_bookings; ?></p>
                <p style="font-size:12px; color:#9ca3af; margin-top:4px;">Confirmed events</p>
            </div>

            <div class="stat-card amber">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#6b7280;">Pending</p>
                    <span style="width:36px;height:36px;border-radius:10px;background:#fef3c7;display:flex;align-items:center;justify-content:center;"><i class="fas fa-clock" style="color:#f59e0b; font-size:15px;"></i></span>
                </div>
                <p class="stat-num" style="color:#f59e0b;"><?php echo $pending_bookings; ?></p>
                <p style="font-size:12px; color:#9ca3af; margin-top:4px;">Awaiting confirmation</p>
            </div>

            <div class="stat-card pink">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:12px;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:1.5px; text-transform:uppercase; color:#6b7280;">Messages</p>
                    <span style="width:36px;height:36px;border-radius:10px;background:var(--pink-light);display:flex;align-items:center;justify-content:center;"><i class="fas fa-comments" style="color:var(--pink); font-size:15px;"></i></span>
                </div>
                <p class="stat-num" style="color:var(--pink);"><?php echo $total_messages; ?></p>
                <p style="font-size:12px; color:#9ca3af; margin-top:4px;">Sent to support</p>
            </div>
        </div>

        <!-- Recent bookings preview -->
        <div class="section-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                <h2 class="section-title" style="margin-bottom:0;">Recent Bookings</h2>
                <button onclick="switchTab('bookings')" class="btn-ghost" style="font-size:12px; padding:8px 16px;">View All →</button>
            </div>

            <?php if (empty($bookings)): ?>
                <div style="text-align:center; padding:40px 20px;">
                    <i class="fas fa-calendar-plus" style="font-size:48px; color:#e5e7eb; margin-bottom:16px; display:block;"></i>
                    <p style="color:#9ca3af; font-size:15px; margin-bottom:16px;">No bookings yet.</p>
                    <button onclick="switchTab('book')" class="btn-primary">Book Your First Event</button>
                </div>
            <?php else: ?>
                <?php foreach (array_slice($bookings, 0, 3) as $b): ?>
                <div class="booking-card <?php echo htmlspecialchars($b['status']); ?>">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                        <div>
                            <p style="font-weight:700; font-size:15px; color:#111827; margin-bottom:4px;"><?php echo htmlspecialchars($b['event_type']); ?></p>
                            <p style="font-size:13px; color:#6b7280;">
                                <i class="fas fa-calendar mr-1"></i><?php echo date('D, M j Y', strtotime($b['event_date'])); ?>
                                &nbsp;·&nbsp;<i class="fas fa-users mr-1"></i><?php echo number_format($b['guest_count']); ?> guests
                                &nbsp;·&nbsp;<i class="fas fa-box mr-1"></i><?php echo htmlspecialchars($b['package']); ?>
                            </p>
                        </div>
                        <span class="status-pill pill-<?php echo strtolower($b['status']); ?>">
                            <i class="fas fa-circle" style="font-size:6px;"></i>
                            <?php echo $b['status']; ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Profile summary -->
        <div class="section-card">
            <h2 class="section-title">Account Summary</h2>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">
                <div>
                    <p class="field-label" style="margin-bottom:4px;">Full Name</p>
                    <p style="font-size:15px; color:#111827; font-weight:500;"><?php echo htmlspecialchars($applicant['fullname']); ?></p>
                </div>
                <div>
                    <p class="field-label" style="margin-bottom:4px;">Email</p>
                    <p style="font-size:15px; color:#111827; font-weight:500;"><?php echo htmlspecialchars($applicant['email']); ?></p>
                </div>
                <div>
                    <p class="field-label" style="margin-bottom:4px;">Service Interest</p>
                    <p style="font-size:15px; color:#111827; font-weight:500;"><?php echo htmlspecialchars($applicant['trade_area'] ?? '—'); ?></p>
                </div>
                <div>
                    <p class="field-label" style="margin-bottom:4px;">Preferred Package</p>
                    <p style="font-size:15px; color:#111827; font-weight:500;"><?php echo htmlspecialchars($applicant['department'] ?? '—'); ?></p>
                </div>
            </div>
        </div>
    </div><!-- /overview -->

    <!-- ════ TAB: NEW BOOKING ════ -->
    <div id="tab-book" class="tab-pane <?php echo $active_tab==='book' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.8rem; font-weight:900; color:#111827; margin-bottom:6px;">New Booking</h1>
        <p style="color:#6b7280; font-size:14px; margin-bottom:24px;">Fill in your event details and we'll confirm within 24 hours.</p>

        <?php if ($booking_success): ?><div class="alert-ok"><i class="fas fa-check-circle mr-2"></i><?php echo $booking_success; ?></div><?php endif; ?>
        <?php if ($booking_error):   ?><div class="alert-err"><i class="fas fa-exclamation-circle mr-2"></i><?php echo $booking_error; ?></div><?php endif; ?>

        <form method="POST" id="bookingForm">
            <input type="hidden" name="action" value="book">
            <input type="hidden" name="tab" value="book">

            <!-- Step 1: Event Details -->
            <div class="section-card">
                <h2 class="section-title"><span style="color:var(--pink);">01.</span> Event Details</h2>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:18px;">
                    <div>
                        <label class="field-label">Event Type <span style="color:#f43f5e;">*</span></label>
                        <select name="event_type" class="field-input" required>
                            <option value="">Select event type</option>
                            <option>Wedding Reception</option>
                            <option>Birthday Party</option>
                            <option>Naming Ceremony</option>
                            <option>Corporate Dinner</option>
                            <option>Product Launch</option>
                            <option>Conference / Summit</option>
                            <option>Burial / Memorial</option>
                            <option>House Warming</option>
                            <option>Graduation Party</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Event Date <span style="color:#f43f5e;">*</span></label>
                        <input type="date" name="event_date" class="field-input" min="<?php echo date('Y-m-d', strtotime('+3 days')); ?>" required>
                    </div>

                     <div>
                        <label class="field-label">Your email <span style="color:#f43f5e;">*</span></label>
                        <input type="email" name="user_email" class="field-input"  required>
                    </div>
                    <div>
                        <label class="field-label">Expected Guest Count <span style="color:#f43f5e;">*</span></label>
                        <input type="number" name="guest_count" class="field-input" placeholder="e.g. 150" min="10" max="5000" required>
                    </div>
                    <div>
                        <label class="field-label">Venue / Location <span style="color:#f43f5e;">*</span></label>
                        <input type="text" name="venue" class="field-input" placeholder="e.g. Oriental Hotel, Lagos" required>
                    </div>
                </div>
            </div>

            <!-- Step 2: Package -->
            <div class="section-card">
                <h2 class="section-title"><span style="color:var(--pink);">02.</span> Choose a Package</h2>
                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:14px;">
                    <?php
                    $packages = [
                        ['value'=>'Intimate (Up to 50)',     'label'=>'Intimate',     'sub'=>'Up to 50 guests',     'icon'=>'fa-heart',         'color'=>'#EC4899', 'bg'=>'#fce7f3'],
                        ['value'=>'Grand (Up to 200)',       'label'=>'Grand',         'sub'=>'Up to 200 guests',    'icon'=>'fa-glass-cheers',  'color'=>'#1D4ED8', 'bg'=>'#dbeafe'],
                        ['value'=>'Corporate',               'label'=>'Corporate',     'sub'=>'Any size',            'icon'=>'fa-building',      'color'=>'#7c3aed', 'bg'=>'#ede9fe'],
                        ['value'=>'Full Service Premium',    'label'=>'Premium',       'sub'=>'All-inclusive',       'icon'=>'fa-crown',         'color'=>'#f59e0b', 'bg'=>'#fef3c7'],
                        ['value'=>'Small Chops Only',        'label'=>'Small Chops',   'sub'=>'Starter bites only',  'icon'=>'fa-utensils',      'color'=>'#22c55e', 'bg'=>'#dcfce7'],
                        ['value'=>'Custom',                  'label'=>'Custom',        'sub'=>'We\'ll advise you',   'icon'=>'fa-sliders-h',     'color'=>'#374151', 'bg'=>'#f3f4f6'],
                    ];
                    foreach ($packages as $pkg): ?>
                    <label style="cursor:pointer;">
                        <input type="radio" value="<?php echo $pkg['value']; ?>" style="display:none;" class="pkg-radio">
                        <div class="pkg-card" style="border:2px solid #e5e7eb; border-radius:14px; padding:18px; text-align:center; transition:all 0.2s; background:#fff;">
                            <div style="width:44px;height:44px;border-radius:12px;background:<?php echo $pkg['bg']; ?>;display:flex;align-items:center;justify-content:center;margin:0 auto 10px;">
                                <i class="fas <?php echo $pkg['icon']; ?>" style="color:<?php echo $pkg['color']; ?>; font-size:18px;"></i>
                            </div>
                            <p style="font-weight:700; font-size:14px; color:#111827; margin-bottom:3px;"><?php echo $pkg['label']; ?></p>
                            <p style="font-size:11px; color:#9ca3af;"><?php echo $pkg['sub']; ?></p>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="package" id="package_hidden">
                <p id="pkg-err" style="color:#f43f5e; font-size:12px; margin-top:8px; display:none;">Please select a package.</p>
            </div>

            <!-- Step 3: Dishes -->
            <div class="section-card">
                <h2 class="section-title"><span style="color:var(--pink);">03.</span> Select Dishes <span style="font-size:13px; font-weight:400; color:#9ca3af;">(choose all that apply)</span></h2>
                <div class="dish-grid">
                    <?php
                    $dishes_list = [
                        ['name'=>'Party Jollof Rice',    'icon'=>'fa-bowl-food'],
                        ['name'=>'Fried Rice',           'icon'=>'fa-utensils'],
                        ['name'=>'Egusi Soup + Swallow', 'icon'=>'fa-bowl-food'],
                        ['name'=>'Peppered Turkey',      'icon'=>'fa-drumstick-bite'],
                        ['name'=>'BBQ Fish',             'icon'=>'fa-fish'],
                        ['name'=>'Suya Platter',         'icon'=>'fa-fire'],
                        ['name'=>'Small Chops',          'icon'=>'fa-cookie-bite'],
                        ['name'=>'Ofada Rice',           'icon'=>'fa-seedling'],
                        ['name'=>'Pasta',                'icon'=>'fa-utensils'],
                        ['name'=>'Custom Cake',          'icon'=>'fa-birthday-cake'],
                        ['name'=>'Dessert Bar',          'icon'=>'fa-ice-cream'],
                        ['name'=>'Drinks Package',       'icon'=>'fa-wine-glass-alt'],
                    ];
                    foreach ($dishes_list as $d): $slug = strtolower(str_replace([' ','&','/','+'],['-','','',''],$d['name'])); ?>
                    <div>
                        <input type="checkbox" name="dishes_check[]" value="<?php echo $d['name']; ?>" id="dish_<?php echo $slug; ?>" class="dish-check">
                        <label for="dish_<?php echo $slug; ?>" class="dish-label">
                            <i class="fas <?php echo $d['icon']; ?>"></i>
                            <?php echo $d['name']; ?>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
                <input type="hidden" name="dishes" id="dishes_hidden">
            </div>

            <!-- Step 4: Notes -->
            <div class="section-card">
                <h2 class="section-title"><span style="color:var(--pink);">04.</span> Special Notes</h2>
                <label class="field-label">Any dietary requirements, special requests, or extra information?</label>
                <textarea name="special_note" class="field-input" rows="4" placeholder="e.g. Bride is vegetarian, please include a veg option. Prefer halal meat only. Setup must be completed by 3pm…"></textarea>
            </div>

            <div style="display:flex; gap:14px;">
                <button type="submit" class="btn-primary" style="padding:15px 36px; font-size:15px;">
                    <i class="fas fa-paper-plane mr-2"></i>Submit Booking Request
                </button>
                <button type="reset" class="btn-ghost" style="padding:15px 24px;">Clear Form</button>
            </div>
        </form>
    </div><!-- /book -->

    <!-- ════ TAB: MY BOOKINGS ════ -->
    <div id="tab-bookings" class="tab-pane <?php echo $active_tab==='bookings' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.8rem; font-weight:900; color:#111827; margin-bottom:6px;">My Bookings</h1>
        <p style="color:#6b7280; font-size:14px; margin-bottom:24px;">Track, view, and manage all your event bookings.</p>

        <!-- Filter pills -->
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:20px;">
            <?php foreach (['All','Pending','Confirmed','Completed','Cancelled'] as $f): ?>
            <button onclick="filterBookings('<?php echo $f; ?>', this)"
                style="padding:8px 18px; border-radius:50px; font-size:12px; font-weight:700; cursor:pointer; border:1.5px solid #e5e7eb; background:#fff; color:#374151; transition:all 0.2s;"
                class="filter-btn <?php echo $f==='All' ? 'filter-active' : ''; ?>">
                <?php echo $f; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <?php if (empty($bookings)): ?>
        <div class="section-card" style="text-align:center; padding:60px 20px;">
            <i class="fas fa-calendar-times" style="font-size:52px; color:#e5e7eb; display:block; margin-bottom:16px;"></i>
            <p style="font-size:16px; font-weight:600; color:#6b7280; margin-bottom:8px;">No bookings yet</p>
            <p style="font-size:14px; color:#9ca3af; margin-bottom:20px;">Your event bookings will appear here once you make a request.</p>
            <button onclick="switchTab('book')" class="btn-primary">Make Your First Booking</button>
        </div>
        <?php else: ?>
        <div id="bookings-list">
            <?php foreach ($bookings as $b): ?>
            <div class="booking-card section-card <?php echo htmlspecialchars($b['status']); ?>" data-status="<?php echo $b['status']; ?>" style="margin-bottom:16px; padding:22px 26px;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:12px; margin-bottom:14px;">
                    <div>
                        <p style="font-family:'Playfair Display',serif; font-size:1.1rem; font-weight:700; color:#111827; margin-bottom:4px;"><?php echo htmlspecialchars($b['event_type']); ?></p>
                        <p style="font-size:12px; color:#9ca3af; font-family:'Inter',monospace;">Booking #<?php echo str_pad($b['id'],5,'0',STR_PAD_LEFT); ?> &nbsp;·&nbsp; Submitted <?php echo date('M j, Y', strtotime($b['created_at'])); ?></p>
                    </div>
                    <span class="status-pill pill-<?php echo strtolower($b['status']); ?>">
                        <i class="fas fa-circle" style="font-size:6px;"></i>
                        <?php echo $b['status']; ?>
                    </span>
                </div>

                <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:14px; margin-bottom:16px;">
                    <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                        <p style="font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9ca3af; margin-bottom:4px;">Event Date</p>
                        <p style="font-size:14px; font-weight:600; color:#111827;"><?php echo date('D, M j Y', strtotime($b['event_date'])); ?></p>
                    </div>
                    <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                        <p style="font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9ca3af; margin-bottom:4px;">Guests</p>
                        <p style="font-size:14px; font-weight:600; color:#111827;"><?php echo number_format($b['guest_count']); ?></p>
                    </div>
                    <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                        <p style="font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9ca3af; margin-bottom:4px;">Package</p>
                        <p style="font-size:14px; font-weight:600; color:#111827;"><?php echo htmlspecialchars($b['package']); ?></p>
                    </div>
                    <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                        <p style="font-size:10px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9ca3af; margin-bottom:4px;">Venue</p>
                        <p style="font-size:14px; font-weight:600; color:#111827; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;"><?php echo htmlspecialchars($b['venue'] ?? '—'); ?></p>
                    </div>
                </div>

                <?php if (!empty($b['dishes'])): ?>
                <div style="margin-bottom:14px;">
                    <p style="font-size:11px; font-weight:700; letter-spacing:1px; text-transform:uppercase; color:#9ca3af; margin-bottom:8px;">Requested Dishes</p>
                    <div style="display:flex; flex-wrap:wrap; gap:6px;">
                        <?php foreach (explode(',', $b['dishes']) as $d): ?>
                        <span style="background:var(--pink-light); color:var(--pink-dark); font-size:11px; font-weight:600; padding:4px 12px; border-radius:50px;"><?php echo htmlspecialchars(trim($d)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($b['special_note'])): ?>
                <div style="background:#f8f9fb; border-left:3px solid var(--blue); padding:12px 14px; border-radius:0 10px 10px 0; margin-bottom:14px;">
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:4px;">Special Notes</p>
                    <p style="font-size:13px; color:#374151; line-height:1.6;"><?php echo htmlspecialchars($b['special_note']); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($b['admin_note'])): ?>
                <div style="background:linear-gradient(135deg,#fdf2f8,#fff7ed); border-left:3px solid var(--pink); padding:12px 14px; border-radius:0 10px 10px 0; margin-bottom:14px;">
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--pink-dark); margin-bottom:4px;">Message from Odela Events</p>
                    <p style="font-size:13px; color:#374151; line-height:1.6;"><?php echo htmlspecialchars($b['admin_note']); ?></p>
                    <?php if (!empty($b['replied_at'])): ?>
                    <p style="font-size:11px; color:#9ca3af; margin:6px 0 0 0;">Updated <?php echo date('M j, Y \a\t g:ia', strtotime($b['replied_at'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($b['admin_reply'])): ?>
                <div style="background:#fdf2f8; border-left:3px solid #ec4899; border-radius:0 0 10px 10px; padding:12px 14px; margin-bottom:14px;">
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#be185d; margin-bottom:6px;">Admin Reply</p>
                    <p style="font-size:13px; color:#374151; line-height:1.6;"><?php echo htmlspecialchars($b['admin_reply']); ?></p>
                </div>
                <?php endif; ?>

                <!-- Actions -->
                <?php if ($b['status'] === 'Pending'): ?>
                <div style="display:flex; gap:10px;">
                    <a href="applicant-dashboard.php?cancel_booking=<?php echo $b['id']; ?>&tab=bookings"
                       onclick="return confirm('Cancel this booking request?')"
                       class="btn-danger">
                        <i class="fas fa-times mr-1"></i>Cancel Booking
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div><!-- /bookings -->

    <!-- ════ TAB: MESSAGES ════ -->
    <div id="tab-messages" class="tab-pane <?php echo $active_tab==='messages' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.8rem; font-weight:900; color:#111827; margin-bottom:6px;">Messages</h1>
        <p style="color:#6b7280; font-size:14px; margin-bottom:24px;">Send inquiries to our team and view their replies here.</p>

        <!-- New message form -->
        <div class="section-card" style="margin-bottom:28px;">
            <h2 class="section-title">Send a Message</h2>
            <?php if ($msg_success): ?><div class="alert-ok"><i class="fas fa-check-circle mr-2"></i><?php echo $msg_success; ?></div><?php endif; ?>
            <?php if ($msg_error):   ?><div class="alert-err"><i class="fas fa-exclamation-circle mr-2"></i><?php echo $msg_error; ?></div><?php endif; ?>

            <form method="POST" id="messageForm">
                <input type="hidden" name="action" value="message">
                <input type="hidden" name="tab" value="messages">
                <div style="display:grid; gap:16px;">
                    <div>
                        <label class="field-label">Subject <span style="color:#f43f5e;">*</span></label>
                        <select name="subject" class="field-input" required>
                            <option value="">Select a topic</option>
                            <option>Booking Enquiry</option>
                            <option>Menu / Dish Questions</option>
                            <option>Pricing & Packages</option>
                            <option>Change Booking Date</option>
                            <option>Payment & Invoice</option>
                            <option>Complaint / Feedback</option>
                            <option>Other</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Message <span style="color:#f43f5e;">*</span></label>
                        <textarea name="body" class="field-input" rows="5" placeholder="Write your message here... Be as detailed as possible so our team can assist you quickly." required></textarea>
                    </div>
                    <div style="display:flex; gap:12px;">
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-paper-plane mr-2"></i>Send Message
                        </button>
                        <button type="reset" class="btn-ghost">Clear</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Message history -->
        <h2 style="font-family:'Playfair Display',serif; font-size:1.2rem; font-weight:700; color:#111827; margin-bottom:14px;">Message History</h2>

        <?php if (empty($messages)): ?>
        <div class="section-card" style="text-align:center; padding:40px 20px;">
            <i class="fas fa-comments" style="font-size:48px; color:#e5e7eb; display:block; margin-bottom:14px;"></i>
            <p style="color:#9ca3af; font-size:14px;">No messages yet. Send one above and our team will respond within 24 hours.</p>
        </div>
        <?php else: ?>
            <?php foreach ($messages as $m): ?>
            <div class="message-card" style="margin-bottom:14px;">
                <div class="message-header">
                    <div>
                        <p style="font-weight:700; font-size:14px; color:#111827;"><?php echo htmlspecialchars($m['subject'] ?? 'No subject'); ?></p>
                        <p style="font-size:11px; color:#9ca3af; margin-top:2px;">Sent <?php echo date('M j, Y \a\t g:ia', strtotime($m['created_at'] ?? 'now')); ?></p>
                    </div>
                    <?php if (!empty($m['status'])): ?>
                    <span style="background:#fef3c7; color:#92400e; font-size:11px; font-weight:700; padding:4px 10px; border-radius:50px;">
                        <?php echo htmlspecialchars($m['status']); ?>
                    </span>
                    <?php endif; ?>
                </div>

                <div style="padding:14px 18px;">
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:6px;">Your Message</p>
                    <p style="font-size:14px; color:#374151; line-height:1.7;"><?php echo nl2br(htmlspecialchars($m['message'] ?? '')); ?></p>
                </div>
                <?php if (!empty($m['admin_reply'])): ?>
                <div style="padding:14px 18px; background:#fdf2f8; border-left:3px solid #ec4899; border-radius:0 0 10px 10px;">
                    <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#be185d; margin-bottom:6px;">Admin Reply</p>
                    <p style="font-size:14px; color:#374151; line-height:1.7; margin-bottom:8px;"><?php echo nl2br(htmlspecialchars($m['admin_reply'])); ?></p>
                    <?php if (!empty($m['replied_at'])): ?>
                    <p style="font-size:11px; color:#9ca3af; margin:0;">Replied <?php echo date('M j, Y \a\t g:ia', strtotime($m['replied_at'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div><!-- /messages -->

    <!-- ════ TAB: PROFILE ════ -->
    <div id="tab-profile" class="tab-pane <?php echo $active_tab==='profile' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.8rem; font-weight:900; color:#111827; margin-bottom:6px;">My Profile</h1>
        <p style="color:#6b7280; font-size:14px; margin-bottom:24px;">Your account details and preferences.</p>

        <div class="section-card">
            <!-- Avatar row -->
            <div style="display:flex; align-items:center; gap:20px; margin-bottom:32px; padding-bottom:24px; border-bottom:1px solid #f0f0f0;">
                <div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--pink));display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:2rem;font-weight:900;color:#fff;flex-shrink:0;">
                    <?php echo strtoupper(substr($applicant_name,0,1)); ?>
                </div>
                <div>
                    <p style="font-family:'Playfair Display',serif; font-size:1.3rem; font-weight:700; color:#111827;"><?php echo htmlspecialchars($applicant['fullname']); ?></p>
                    <p style="font-size:13px; color:#9ca3af;"><?php echo htmlspecialchars($applicant['email']); ?></p>
                    <p style="font-size:12px; color:var(--pink); font-weight:600; margin-top:4px;">Client since <?php echo date('F Y', strtotime($applicant['created_at'])); ?></p>
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px;">
                <div>
                    <p class="field-label">Full Name</p>
                    <p style="font-size:15px; color:#111827; font-weight:500; padding:10px 14px; background:#f8f9fb; border-radius:10px;"><?php echo htmlspecialchars($applicant['fullname']); ?></p>
                </div>
                <div>
                    <p class="field-label">Email Address</p>
                    <p style="font-size:15px; color:#111827; font-weight:500; padding:10px 14px; background:#f8f9fb; border-radius:10px;"><?php echo htmlspecialchars($applicant['email']); ?></p>
                </div>
                <div>
                    <p class="field-label">Date of Birth</p>
                    <p style="font-size:15px; color:#111827; font-weight:500; padding:10px 14px; background:#f8f9fb; border-radius:10px;"><?php echo !empty($applicant['dob']) ? date('F j, Y', strtotime($applicant['dob'])) : '—'; ?></p>
                </div>
                <div>
                    <p class="field-label">Service Interest</p>
                    <p style="font-size:15px; color:#111827; font-weight:500; padding:10px 14px; background:#f8f9fb; border-radius:10px;"><?php echo htmlspecialchars($applicant['dishes'] ?? '—'); ?></p>
                </div>
                <div>
                    <p class="field-label">Preferred Package</p>
                    <p style="font-size:15px; color:#111827; font-weight:500; padding:10px 14px; background:#f8f9fb; border-radius:10px;"><?php echo htmlspecialchars($applicant['package'] ?? '—'); ?></p>
                </div>
                <div>
                    <p class="field-label">Account ID</p>
                    <p style="font-size:15px; color:#111827; font-weight:500; font-family:monospace; padding:10px 14px; background:#f8f9fb; border-radius:10px;">#<?php echo str_pad($applicant['id'],6,'0',STR_PAD_LEFT); ?></p>
                </div>
            </div>

            <div style="margin-top:24px; padding-top:20px; border-top:1px solid #f0f0f0; display:flex; gap:12px;">
                <a href="contact.php" class="btn-primary" style="text-decoration:none; font-size:14px;">
                    <i class="fas fa-envelope mr-2"></i>Contact Support
                </a>
                <a href="applicant-logout.php" onclick="return confirm('Sign out?')" class="btn-ghost" style="color:#f43f5e; border-color:#fecdd3;">
                    <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                </a>
            </div>
        </div>
    </div><!-- /profile -->

</div><!-- /main-content -->

<style>
.filter-active {
    background: linear-gradient(135deg,var(--blue),var(--pink)) !important;
    color: #fff !important;
    border-color: transparent !important;
}
</style>

<script>
/* ── Tab switching ── */
function switchTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');

    // Match sidebar button
    document.querySelectorAll('.nav-item').forEach(btn => {
        if (btn.getAttribute('onclick') === "switchTab('" + name + "')") {
            btn.classList.add('active');
        }
    });

    // Update URL without reload
    history.replaceState(null,'','?tab='+name);
}

/* ── Package radio visual ── */
document.querySelectorAll('.pkg-radio').forEach(radio => {
    radio.addEventListener('change', function () {
        document.querySelectorAll('.pkg-card').forEach(c => {
            c.style.borderColor = '#e5e7eb';
            c.style.background  = '#fff';
        });
        const card = this.nextElementSibling;
        card.style.borderColor = '#EC4899';
        card.style.background  = '#fdf2f8';
        document.getElementById('package_hidden').value = this.value;
        document.getElementById('pkg-err').style.display = 'none';
    });
});

/* ── Dishes → hidden input ── */
document.getElementById('bookingForm')?.addEventListener('submit', function(e) {
    // Collect dishes
    const checked = [...document.querySelectorAll('.dish-check:checked')].map(c => c.value);
    document.getElementById('dishes_hidden').value = checked.join(',');

    // Require package selection
    if (!document.getElementById('package_hidden').value) {
        document.getElementById('pkg-err').style.display = 'block';
        e.preventDefault();
        document.getElementById('pkg-err').scrollIntoView({behavior:'smooth', block:'center'});
    }
});

/* ── Booking filter ── */
function filterBookings(status, btn) {
    document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('filter-active'));
    btn.classList.add('filter-active');

    document.querySelectorAll('#bookings-list .booking-card').forEach(card => {
        if (status === 'All' || card.dataset.status === status) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
}

/* ── Init correct tab from URL ── */
const urlTab = new URLSearchParams(window.location.search).get('tab');
if (urlTab) switchTab(urlTab);
</script>

<?php $conn->close(); ?>
</body>
</html>
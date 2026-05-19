<?php
session_start();
include '../config/db.php';

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

$admin_name = htmlspecialchars($_SESSION['admin_name']);

// Determine optional message columns so dashboard works on different schemas.
$checkColumn = function($table, $column) use ($conn) {
    $result = $conn->query("SHOW COLUMNS FROM `$table` LIKE '" . $conn->real_escape_string($column) . "'");
    return $result && $result->num_rows > 0;
};

$messages_has_user_id     = $checkColumn('messages', 'user_id');
$messages_has_status      = $checkColumn('messages', 'status');
$messages_has_admin_reply = $checkColumn('messages', 'admin_reply');
$messages_has_replied_at  = $checkColumn('messages', 'replied_at');

if (!$messages_has_admin_reply) {
    $conn->query("ALTER TABLE messages ADD COLUMN admin_reply TEXT NULL");
    $messages_has_admin_reply = $checkColumn('messages', 'admin_reply');
}

if (!$messages_has_replied_at) {
    $conn->query("ALTER TABLE messages ADD COLUMN replied_at DATETIME NULL");
    $messages_has_replied_at = $checkColumn('messages', 'replied_at');
}

// Check & create booking columns for admin replies
$bookings_has_admin_reply = $checkColumn('bookings', 'admin_reply');
$bookings_has_replied_at  = $checkColumn('bookings', 'replied_at');

if (!$bookings_has_admin_reply) {
    $conn->query("ALTER TABLE bookings ADD COLUMN admin_reply TEXT NULL");
    $bookings_has_admin_reply = $checkColumn('bookings', 'admin_reply');
}

if (!$bookings_has_replied_at) {
    $conn->query("ALTER TABLE bookings ADD COLUMN replied_at DATETIME NULL");
    $bookings_has_replied_at = $checkColumn('bookings', 'replied_at');
}

// ── Statistics ──────────────────────────────────────────
$stats = [];

$queries = [
    'total_users'       => "SELECT COUNT(*) FROM users",
    'total_bookings'    => "SELECT COUNT(*) FROM bookings",
    'pending_bookings'  => "SELECT COUNT(*) FROM bookings WHERE status='Pending'",
    'confirmed_bookings'=> "SELECT COUNT(*) FROM bookings WHERE status='Confirmed'",
    'total_messages'    => "SELECT COUNT(*) FROM messages",
    'unread_messages'   => $messages_has_status ? "SELECT COUNT(*) FROM messages WHERE status='Unread'" : "SELECT 0",
    'completed_bookings'=> "SELECT COUNT(*) FROM bookings WHERE status='Completed'",
];



foreach ($queries as $key => $sql) {
    $r = $conn->query($sql);
    $stats[$key] = $r ? $r->fetch_row()[0] : 0;
}

// ── Handle booking status update ──────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // Update booking status + optional admin note/reply
    if ($_POST['action'] === 'update_booking') {
        $bid    = (int)$_POST['booking_id'];
        $status = $conn->real_escape_string($_POST['status']);
        $note   = $conn->real_escape_string(trim($_POST['admin_note'] ?? ''));
        
        $sql = "UPDATE bookings SET status='$status', admin_note='$note', updated_at=NOW()";
        
        // Add admin_reply and replied_at if columns exist and reply is provided
        if ($bookings_has_admin_reply && $bookings_has_replied_at && !empty($note)) {
            $sql .= ", admin_reply='$note', replied_at=NOW()";
        } elseif ($bookings_has_admin_reply && !empty($note)) {
            $sql .= ", admin_reply='$note'";
        }
        
        $sql .= " WHERE id=$bid";
        $conn->query($sql);
        $toast = "Booking #$bid updated to <strong>$status</strong>.";
    }

    // Reply to a message
    if ($_POST['action'] === 'reply_message') {
        if ($messages_has_admin_reply) {
            $mid   = (int)$_POST['message_id'];
            $reply = trim($_POST['reply'] ?? '');
            
            // Validate reply is not empty
            if (empty($reply)) {
                $toast = "Reply cannot be empty.";
            } else {
                $reply_safe = $conn->real_escape_string($reply);
                $sql   = "UPDATE messages SET admin_reply='$reply_safe'";
                if ($messages_has_status) {
                    $sql .= ", status='Read'";
                }
                if ($messages_has_replied_at) {
                    $sql .= ", replied_at=NOW()";
                }
                $sql .= " WHERE id=$mid";
                $conn->query($sql);
                $toast = "Reply sent successfully.";
            }
        } else {
            $toast = "Message reply feature is unavailable for the current database schema.";
        }
    }

    // Delete user
    if ($_POST['action'] === 'delete_user') {
        $uid = (int)$_POST['user_id'];
        $user_email = '';
        $result = $conn->query("SELECT email FROM users WHERE id=$uid");
        if ($result && $row = $result->fetch_assoc()) {
            $user_email = $conn->real_escape_string($row['email']);
        }
        $conn->query("DELETE FROM bookings WHERE user_id=$uid");
        if ($messages_has_user_id) {
            $conn->query("DELETE FROM messages WHERE user_id=$uid");
        } elseif ($user_email !== '') {
            $conn->query("DELETE FROM messages WHERE email='$user_email'");
        }
        $conn->query("DELETE FROM users WHERE id=$uid");
        $toast = "User and all associated records deleted.";
    }

    // Delete booking
    if ($_POST['action'] === 'delete_booking') {
        $bid = (int) $_POST['booking_id'];
        $conn->query("DELETE FROM bookings WHERE id=$bid");
        $toast = "Booking deleted.";
    }

    // Delete message
    if ($_POST['action'] === 'delete_message') {
        $mid = (int)$_POST['message_id'];
        $conn->query("DELETE FROM messages WHERE id=$mid");
        $toast = "Message deleted.";
    }
}

// ── Fetch data ──────────────────────────────────────────
// Recent users
$users = $conn->query("SELECT id, fullname, email, created_at FROM users ORDER BY created_at DESC LIMIT 50")->fetch_all(MYSQLI_ASSOC);

// All bookings with user info
$bookings = $conn->query("
    SELECT b.*, u.fullname AS user_name, u.email AS user_email
    FROM bookings b
    LEFT JOIN users u ON b.user_id = u.id
    ORDER BY b.created_at DESC
    LIMIT 100
")->fetch_all(MYSQLI_ASSOC);

// All messages
if ($messages_has_user_id) {
    $messages = $conn->query("
        SELECT m.*, u.fullname AS user_name, u.email AS user_email
        FROM messages m
        LEFT JOIN users u ON m.user_id = u.id
        ORDER BY m.created_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);
} else {
    $messages = $conn->query("
        SELECT m.*
        FROM messages m
        ORDER BY m.created_at DESC
        LIMIT 100
    ")->fetch_all(MYSQLI_ASSOC);
}

// Monthly bookings for mini chart (last 6 months)
$chart_data = [];
for ($i = 5; $i >= 0; $i--) {
    $month = date('Y-m', strtotime("-$i months"));
    $label = date('M', strtotime("-$i months"));
    $r = $conn->query("SELECT COUNT(*) FROM bookings WHERE DATE_FORMAT(created_at,'%Y-%m')='$month'");
    $chart_data[] = ['label' => $label, 'count' => (int)$r->fetch_row()[0]];
}

$active_tab = $_GET['tab'] ?? 'overview';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin — Odela Events</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600;700&display=swap');

        :root {
            --blue:       #1D4ED8;
            --blue-dark:  #1E3A8A;
            --blue-light: #DBEAFE;
            --blue-xlight:#EFF6FF;
            --pink:       #EC4899;
            --pink-dark:  #BE185D;
            --pink-light: #FCE7F3;
        }

        * { box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #F0F4FF; margin: 0; }
        .font-display { font-family: 'Playfair Display', serif; }

        /* ══ SIDEBAR ══════════════════════════════════════════ */
        .sidebar {
            width: 265px; min-height: 100vh;
            background: linear-gradient(170deg, #0a0f2c 0%, #0f1f5c 45%, #1D4ED8 100%);
            position: fixed; left: 0; top: 0; z-index: 200;
            display: flex; flex-direction: column;
            box-shadow: 4px 0 40px rgba(29,78,216,0.22);
        }
        .sidebar-top {
            padding: 26px 22px 18px;
            border-bottom: 1px solid rgba(255,255,255,0.07);
        }
        .sidebar-nav { flex: 1; padding: 14px 10px; overflow-y: auto; }

        .nav-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: 2.5px;
            text-transform: uppercase; color: rgba(255,255,255,0.28);
            padding: 12px 14px 6px; margin-top: 6px;
        }
        .nav-item {
            display: flex; align-items: center; gap: 11px;
            padding: 11px 14px; border-radius: 10px;
            color: rgba(255,255,255,0.6); font-size: 13.5px; font-weight: 500;
            text-decoration: none; cursor: pointer;
            border: none; background: none; width: 100%;
            text-align: left; margin-bottom: 3px;
            transition: all 0.2s; position: relative;
        }
        .nav-item:hover  { background: rgba(255,255,255,0.07); color: #fff; }
        .nav-item.active {
            background: rgba(236,72,153,0.15);
            color: #fff; border-left: 3px solid var(--pink);
            padding-left: 11px;
        }
        .nav-icon { width: 18px; text-align: center; font-size: 14px; flex-shrink: 0; }
        .nav-badge {
            margin-left: auto; background: var(--pink); color: #fff;
            font-size: 10px; font-weight: 800;
            padding: 2px 7px; border-radius: 50px; min-width: 20px; text-align: center;
            animation: pulse-badge 2s infinite;
        }
        @keyframes pulse-badge {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .sidebar-footer {
            padding: 14px 10px;
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .admin-avatar {
            width: 38px; height: 38px; border-radius: 50%;
            background: linear-gradient(135deg, var(--pink), var(--blue));
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 14px; color: #fff; flex-shrink: 0;
        }

        /* ══ MAIN CONTENT ══════════════════════════════════════ */
        .main-content { margin-left: 265px; padding: 28px 32px; min-height: 100vh; }

        /* ══ TOP BAR ═══════════════════════════════════════════ */
        .top-bar {
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(14px);
            border-radius: 16px; padding: 15px 24px;
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 26px;
            box-shadow: 0 2px 24px rgba(29,78,216,0.07);
            border: 1px solid rgba(255,255,255,0.85);
        }

        /* ══ STAT CARDS ════════════════════════════════════════ */
        .stat-card {
            background: #fff; border-radius: 18px; padding: 22px 24px;
            border: 1px solid rgba(29,78,216,0.07);
            box-shadow: 0 4px 20px rgba(29,78,216,0.05);
            transition: transform 0.25s, box-shadow 0.25s;
            position: relative; overflow: hidden;
        }
        .stat-card:hover { transform: translateY(-3px); box-shadow: 0 12px 36px rgba(29,78,216,0.12); }
        .stat-blob {
            position: absolute; width: 90px; height: 90px; border-radius: 50%;
            top: -20px; right: -20px; opacity: 0.08;
        }
        .stat-num {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem; font-weight: 900; line-height: 1;
        }
        .stat-icon {
            width: 44px; height: 44px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin-bottom: 14px;
        }

        /* ══ SECTION CARD ══════════════════════════════════════ */
        .section-card {
            background: #fff; border-radius: 20px; padding: 26px 28px;
            border: 1px solid rgba(29,78,216,0.06);
            box-shadow: 0 4px 20px rgba(29,78,216,0.05);
            margin-bottom: 22px;
        }
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.2rem; font-weight: 900; color: #111827; margin-bottom: 18px;
        }

        /* ══ TABLE ══════════════════════════════════════════════ */
        .admin-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
        .admin-table th {
            background: linear-gradient(135deg, #0f1f5c, #1D4ED8);
            color: rgba(255,255,255,0.85); padding: 13px 16px;
            text-align: left; font-size: 10.5px; letter-spacing: 1.8px;
            text-transform: uppercase; font-weight: 700;
        }
        .admin-table th:first-child { border-radius: 10px 0 0 0; }
        .admin-table th:last-child  { border-radius: 0 10px 0 0; }
        .admin-table td {
            padding: 13px 16px; border-bottom: 1px solid #f3f4f6;
            color: #374151; vertical-align: middle;
        }
        .admin-table tr:hover td { background: #fafbff; }
        .admin-table tr:last-child td { border-bottom: none; }

        /* ══ STATUS PILLS ═══════════════════════════════════════ */
        .pill {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 4px 11px; border-radius: 50px;
            font-size: 11px; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase;
        }
        .pill-pending   { background: #fef3c7; color: #92400e; }
        .pill-confirmed { background: #dcfce7; color: #15803d; }
        .pill-completed { background: #dbeafe; color: #1e40af; }
        .pill-cancelled { background: #fee2e2; color: #991b1b; }
        .pill-unread    { background: var(--pink-light); color: var(--pink-dark); }
        .pill-read      { background: #f0fdf4; color: #15803d; }

        /* ══ FORMS ══════════════════════════════════════════════ */
        .field-label {
            display: block; font-size: 10.5px; font-weight: 700;
            letter-spacing: 1.5px; text-transform: uppercase; color: #374151; margin-bottom: 5px;
        }
        .field-input {
            width: 100%; padding: 10px 13px; border: 1.5px solid #e5e7eb;
            border-radius: 10px; font-size: 13.5px; font-family: 'Inter', sans-serif;
            color: #111827; background: #fafafa; outline: none; transition: all 0.2s;
        }
        .field-input:focus {
            border-color: var(--blue);
            background: #fff; box-shadow: 0 0 0 4px rgba(29,78,216,0.08);
        }
        .field-input::placeholder { color: #9ca3af; }

        /* ══ BUTTONS ════════════════════════════════════════════ */
        .btn-primary {
            padding: 10px 22px; border-radius: 10px; border: none; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 13px; font-weight: 700;
            color: #fff; background: linear-gradient(135deg, var(--blue), var(--pink));
            transition: opacity 0.2s, transform 0.2s;
            box-shadow: 0 4px 16px rgba(29,78,216,0.3);
        }
        .btn-primary:hover { opacity: 0.88; transform: translateY(-1px); }
        .btn-sm {
            padding: 7px 14px; border-radius: 8px; border: none; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 12px; font-weight: 600;
            transition: all 0.2s;
        }
        .btn-blue   { background: var(--blue-light); color: var(--blue-dark); }
        .btn-blue:hover { background: var(--blue); color: #fff; }
        .btn-pink   { background: var(--pink-light); color: var(--pink-dark); }
        .btn-pink:hover { background: var(--pink); color: #fff; }
        .btn-red    { background: #fee2e2; color: #991b1b; }
        .btn-red:hover { background: #f43f5e; color: #fff; }
        .btn-green  { background: #dcfce7; color: #15803d; }
        .btn-green:hover { background: #22c55e; color: #fff; }

        /* ══ MODAL ══════════════════════════════════════════════ */
        .modal-overlay {
            position: fixed; inset: 0; background: rgba(10,15,50,0.55);
            backdrop-filter: blur(4px); z-index: 500;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .modal-box {
            background: #fff; border-radius: 20px; padding: 32px;
            width: 100%; max-width: 520px; max-height: 90vh; overflow-y: auto;
            box-shadow: 0 40px 80px rgba(0,0,0,0.25);
            animation: modal-in 0.25s ease;
        }
        @keyframes modal-in {
            from { opacity: 0; transform: scale(0.95) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem; font-weight: 900; color: #111827; margin-bottom: 20px;
        }

        /* ══ TOAST ══════════════════════════════════════════════ */
        .toast {
            position: fixed; bottom: 28px; right: 28px; z-index: 999;
            background: linear-gradient(135deg, #1E3A8A, #EC4899);
            color: #fff; padding: 14px 22px; border-radius: 12px;
            font-size: 14px; font-weight: 500;
            box-shadow: 0 8px 32px rgba(29,78,216,0.35);
            animation: toast-in 0.35s ease, toast-out 0.35s ease 3.5s forwards;
        }
        @keyframes toast-in  { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        @keyframes toast-out { from { opacity:1; transform:translateY(0);    } to { opacity:0; transform:translateY(20px); } }

        /* ══ TABS ═══════════════════════════════════════════════ */
        .tab-pane { display: none; }
        .tab-pane.active { display: block; }

        /* ══ MINI BAR CHART ════════════════════════════════════ */
        .bar-wrap { display: flex; align-items: flex-end; gap: 8px; height: 70px; }
        .bar-col   { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: 1; }
        .bar-fill  {
            width: 100%; border-radius: 6px 6px 0 0;
            background: linear-gradient(to top, var(--blue), var(--pink));
            transition: height 0.6s ease;
        }
        .bar-label { font-size: 10px; font-weight: 600; color: #9ca3af; }

        /* ══ SEARCH ════════════════════════════════════════════ */
        .search-input {
            padding: 9px 14px 9px 38px;
            border: 1.5px solid #e5e7eb; border-radius: 10px;
            font-size: 13px; font-family: 'Inter', sans-serif;
            outline: none; background: #fafafa; color: #111827;
            transition: all 0.2s; width: 240px;
        }
        .search-input:focus { border-color: var(--blue); background: #fff; width: 280px; }
        .search-wrap { position: relative; }
        .search-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #9ca3af; font-size: 13px; }

        /* ══ HERO STRIP ════════════════════════════════════════ */
        .admin-hero {
            background: linear-gradient(135deg, #0f1f5c 0%, #1D4ED8 50%, #EC4899 100%);
            border-radius: 20px; padding: 28px 32px; margin-bottom: 24px; color: #fff;
            position: relative; overflow: hidden;
        }
        .admin-hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Ccircle cx='30' cy='30' r='2' fill='%23fff' fill-opacity='0.04'/%3E%3C/svg%3E");
        }

        /* Alert boxes */
        .alert-ok  { background:#f0fdf4; border-left:4px solid #22c55e; color:#15803d; padding:12px 16px; border-radius:10px; font-size:13.5px; margin-bottom:18px; }

        @media (max-width: 900px) {
            .sidebar      { width:100%; min-height:auto; position:relative; }
            .main-content { margin-left:0; padding:16px; }
        }
    </style>
</head>
<body>

<!-- ══════════════════════════════════════════════
     SIDEBAR
══════════════════════════════════════════════ -->
<div class="sidebar">
    <div class="sidebar-top">
        <a href="../index.php" style="text-decoration:none; display:flex; align-items:center; gap:8px; margin-bottom:4px;">
            <span style="font-family:'Georgia',serif; font-size:1.2rem; font-weight:700; color:#fff;">Odela Events</span>
            <span style="width:7px;height:7px;border-radius:50%;background:var(--pink);display:inline-block;"></span>
        </a>
        <p style="color:rgba(255,255,255,0.35); font-size:10.5px; letter-spacing:1.5px; text-transform:uppercase;">Admin Control Panel</p>
    </div>

    <div class="sidebar-nav">
        <div class="nav-section-label">Dashboard</div>
        <button class="nav-item <?php echo $active_tab==='overview' ? 'active' : ''; ?>" onclick="switchTab('overview')">
            <span class="nav-icon"><i class="fas fa-th-large"></i></span> Overview
        </button>

        <div class="nav-section-label">Management</div>
        <button class="nav-item <?php echo $active_tab==='bookings' ? 'active' : ''; ?>" onclick="switchTab('bookings')">
            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span> Bookings
            <?php if ($stats['pending_bookings'] > 0): ?>
                <span class="nav-badge"><?php echo $stats['pending_bookings']; ?></span>
            <?php endif; ?>
        </button>
        <button class="nav-item <?php echo $active_tab==='users' ? 'active' : ''; ?>" onclick="switchTab('users')">
            <span class="nav-icon"><i class="fas fa-users"></i></span> Clients
        </button>
        <button class="nav-item <?php echo $active_tab==='messages' ? 'active' : ''; ?>" onclick="switchTab('messages')">
            <span class="nav-icon"><i class="fas fa-comments"></i></span> Messages
            <?php if ($stats['unread_messages'] > 0): ?>
                <span class="nav-badge"><?php echo $stats['unread_messages']; ?></span>
            <?php endif; ?>
        </button>

        <div class="nav-section-label">System</div>
        <a href="../index.php" class="nav-item">
            <span class="nav-icon"><i class="fas fa-globe"></i></span> View Site
        </a>
        <a href="logout.php" onclick="return confirm('Sign out?')" class="nav-item" style="color:rgba(248,113,113,0.8);">
            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span> Sign Out
        </a>
    </div>

    <!-- Admin info -->
    <div class="sidebar-footer">
        <div style="display:flex; align-items:center; gap:10px; padding:10px 12px; background:rgba(255,255,255,0.06); border-radius:10px;">
            <div class="admin-avatar"><?php echo strtoupper(substr($_SESSION['admin_name'],0,1)); ?></div>
            <div style="min-width:0;">
                <p style="color:#fff; font-size:12.5px; font-weight:600; margin:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?php echo $admin_name; ?></p>
                <p style="color:rgba(255,255,255,0.4); font-size:10.5px; margin:0;">Super Admin</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════
     MAIN CONTENT
══════════════════════════════════════════════ -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="top-bar">
        <div>
            <p style="font-size:12px; color:#9ca3af; margin-bottom:1px;"><?php echo date('l, F j, Y'); ?></p>
            <p style="font-size:15px; font-weight:600; color:#111827;">
                Good <?php echo date('H') < 12 ? 'morning' : (date('H') < 17 ? 'afternoon' : 'evening'); ?>,
                <?php echo htmlspecialchars(explode(' ', $_SESSION['admin_name'])[0]); ?> 👋
            </p>
        </div>
        <div style="display:flex; align-items:center; gap:10px;">
            <?php if ($stats['unread_messages'] > 0): ?>
            <button onclick="switchTab('messages')"
                style="position:relative; background:var(--pink-light); border:none; border-radius:10px; padding:9px 16px; cursor:pointer; font-size:13px; font-weight:600; color:var(--pink-dark);">
                <i class="fas fa-bell mr-1"></i><?php echo $stats['unread_messages']; ?> Unread
            </button>
            <?php endif; ?>
            <button onclick="switchTab('bookings')"
                style="padding:9px 20px; border-radius:10px; border:none; cursor:pointer; font-size:13px; font-weight:700; color:#fff; background:linear-gradient(135deg,var(--blue),var(--pink));">
                <i class="fas fa-plus mr-1"></i> New Entry
            </button>
        </div>
    </div>

    <?php if (isset($toast)): ?>
        <div class="toast"><i class="fas fa-check-circle mr-2"></i><?php echo $toast; ?></div>
    <?php endif; ?>

    <!-- ════ TAB: OVERVIEW ════════════════════════════════ -->
    <div id="tab-overview" class="tab-pane <?php echo $active_tab==='overview' ? 'active' : ''; ?>">

        <!-- Hero -->
        <div class="admin-hero">
            <div style="position:relative; z-index:1;">
                <p style="font-size:10.5px; letter-spacing:3px; text-transform:uppercase; color:rgba(255,255,255,0.55); margin-bottom:6px;">Odela Events Admin</p>
                <h1 class="font-display" style="font-size:1.9rem; font-weight:900; margin-bottom:6px;">Business Overview</h1>
                <p style="color:rgba(255,255,255,0.7); font-size:14px; max-width:480px; line-height:1.6;">
                    Monitor your bookings, client activity, and messages from one centralised panel.
                </p>
            </div>
        </div>

        <!-- Stat Grid -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(175px,1fr)); gap:16px; margin-bottom:24px;">

            <div class="stat-card">
                <div class="stat-blob" style="background:var(--blue);"></div>
                <div class="stat-icon" style="background:var(--blue-light);">
                    <i class="fas fa-users" style="color:var(--blue);"></i>
                </div>
                <p class="stat-num" style="color:var(--blue);"><?php echo $stats['total_users']; ?></p>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-top:4px;">Total Clients</p>
            </div>

            <div class="stat-card">
                <div class="stat-blob" style="background:#22c55e;"></div>
                <div class="stat-icon" style="background:#dcfce7;">
                    <i class="fas fa-calendar-check" style="color:#16a34a;"></i>
                </div>
                <p class="stat-num" style="color:#16a34a;"><?php echo $stats['confirmed_bookings']; ?></p>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-top:4px;">Confirmed</p>
            </div>

            <div class="stat-card">
                <div class="stat-blob" style="background:#f59e0b;"></div>
                <div class="stat-icon" style="background:#fef3c7;">
                    <i class="fas fa-clock" style="color:#d97706;"></i>
                </div>
                <p class="stat-num" style="color:#d97706;"><?php echo $stats['pending_bookings']; ?></p>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-top:4px;">Pending</p>
            </div>

            <div class="stat-card">
                <div class="stat-blob" style="background:var(--pink);"></div>
                <div class="stat-icon" style="background:var(--pink-light);">
                    <i class="fas fa-comments" style="color:var(--pink);"></i>
                </div>
                <p class="stat-num" style="color:var(--pink);"><?php echo $stats['unread_messages']; ?></p>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-top:4px;">Unread Msgs</p>
            </div>

            <div class="stat-card">
                <div class="stat-blob" style="background:#7c3aed;"></div>
                <div class="stat-icon" style="background:#ede9fe;">
                    <i class="fas fa-clipboard-list" style="color:#7c3aed;"></i>
                </div>
                <p class="stat-num" style="color:#7c3aed;"><?php echo $stats['total_bookings']; ?></p>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-top:4px;">All Bookings</p>
            </div>

            <div class="stat-card">
                <div class="stat-blob" style="background:#0891b2;"></div>
                <div class="stat-icon" style="background:#cffafe;">
                    <i class="fas fa-check-double" style="color:#0891b2;"></i>
                </div>
                <p class="stat-num" style="color:#0891b2;"><?php echo $stats['completed_bookings']; ?></p>
                <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-top:4px;">Completed</p>
            </div>

        </div>

        <div style="display:grid; grid-template-columns:1fr 1fr; gap:20px;">

            <!-- Mini bar chart -->
            <div class="section-card">
                <h2 class="section-title">Bookings — Last 6 Months</h2>
                <?php
                $max_count = max(array_column($chart_data, 'count')) ?: 1;
                ?>
                <div class="bar-wrap">
                    <?php foreach ($chart_data as $cd):
                        $height = max(6, round(($cd['count'] / $max_count) * 60));
                    ?>
                    <div class="bar-col">
                        <span style="font-size:11px; font-weight:700; color:var(--blue);"><?php echo $cd['count']; ?></span>
                        <div class="bar-fill" style="height:<?php echo $height; ?>px;"></div>
                        <span class="bar-label"><?php echo $cd['label']; ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="section-card">
                <h2 class="section-title">Quick Actions</h2>
                <div style="display:grid; gap:10px;">
                    <button onclick="switchTab('bookings')" style="display:flex; align-items:center; gap:12px; padding:13px 16px; border-radius:12px; border:none; cursor:pointer; background:var(--blue-xlight); text-align:left; transition:background 0.2s; width:100%;">
                        <div style="width:36px;height:36px;border-radius:10px;background:var(--blue-light);display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-calendar" style="color:var(--blue);"></i></div>
                        <div><p style="font-weight:700; font-size:13.5px; color:#111827; margin:0;">Manage Bookings</p><p style="font-size:11.5px; color:#6b7280; margin:0;"><?php echo $stats['pending_bookings']; ?> awaiting review</p></div>
                    </button>
                    <button onclick="switchTab('messages')" style="display:flex; align-items:center; gap:12px; padding:13px 16px; border-radius:12px; border:none; cursor:pointer; background:var(--pink-light); text-align:left; transition:background 0.2s; width:100%;">
                        <div style="width:36px;height:36px;border-radius:10px;background:#fce7f3;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-reply" style="color:var(--pink);"></i></div>
                        <div><p style="font-weight:700; font-size:13.5px; color:#111827; margin:0;">Reply to Messages</p><p style="font-size:11.5px; color:#6b7280; margin:0;"><?php echo $stats['unread_messages']; ?> unread messages</p></div>
                    </button>
                    <button onclick="switchTab('users')" style="display:flex; align-items:center; gap:12px; padding:13px 16px; border-radius:12px; border:none; cursor:pointer; background:#f0fdf4; text-align:left; transition:background 0.2s; width:100%;">
                        <div style="width:36px;height:36px;border-radius:10px;background:#dcfce7;display:flex;align-items:center;justify-content:center;flex-shrink:0;"><i class="fas fa-users" style="color:#16a34a;"></i></div>
                        <div><p style="font-weight:700; font-size:13.5px; color:#111827; margin:0;">View Clients</p><p style="font-size:11.5px; color:#6b7280; margin:0;"><?php echo $stats['total_users']; ?> registered clients</p></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- Recent Bookings preview -->
        <div class="section-card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 class="section-title" style="margin:0;">Latest Bookings</h2>
                <button onclick="switchTab('bookings')" style="font-size:12.5px; font-weight:600; color:var(--blue); background:none; border:none; cursor:pointer;">View all →</button>
            </div>
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Client</th><th>Event Type</th>
                            <th>Event Date</th><th>Guests</th><th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($bookings, 0, 6) as $b): ?>
                        <tr>
                            <td style="font-family:monospace; color:#9ca3af; font-size:12px;">#<?php echo str_pad($b['id'],4,'0',STR_PAD_LEFT); ?></td>
                            <td>
                                <p style="font-weight:600; color:#111827; margin:0;"><?php echo htmlspecialchars($b['user_name'] ?? '—'); ?></p>
                                <p style="font-size:11.5px; color:#9ca3af; margin:0;"><?php echo htmlspecialchars($b['user_email'] ?? ''); ?></p>
                            </td>
                            <td><?php echo htmlspecialchars($b['event_type']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($b['event_date'])); ?></td>
                            <td><?php echo number_format($b['guest_count']); ?></td>
                            <td><span class="pill pill-<?php echo strtolower($b['status']); ?>"><i class="fas fa-circle" style="font-size:5px;"></i> <?php echo $b['status']; ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#9ca3af; padding:30px;">No bookings yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /overview -->

    <!-- ════ TAB: BOOKINGS ═══════════════════════════════ -->
    <div id="tab-bookings" class="tab-pane <?php echo $active_tab==='bookings' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.7rem; font-weight:900; color:#111827; margin-bottom:4px;">All Bookings</h1>
        <p style="color:#6b7280; font-size:13.5px; margin-bottom:20px;">Review, confirm, and communicate on every event booking.</p>

        <!-- Filter pills -->
        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:18px;">
            <?php foreach (['All','Pending','Confirmed','Completed','Cancelled'] as $f): ?>
            <button onclick="filterTable('bookings-tbody', '<?php echo $f; ?>', this)"
                style="padding:7px 18px; border-radius:50px; font-size:12px; font-weight:700; cursor:pointer; border:1.5px solid #e5e7eb; background:#fff; color:#374151; transition:all 0.2s;"
                class="filter-btn-b <?php echo $f==='All' ? 'f-active' : ''; ?>">
                <?php echo $f; ?>
            </button>
            <?php endforeach; ?>
            <div class="search-wrap" style="margin-left:auto;">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search bookings…" oninput="searchTable('bookings-tbody', this.value)">
            </div>
        </div>

        <div class="section-card" style="padding:0; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th><th>Client</th><th>Event</th><th>Date</th>
                            <th>Guests</th><th>Package</th><th>Status</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                        <?php foreach ($bookings as $b): ?>
                        <tr data-status="<?php echo $b['status']; ?>" data-search="<?php echo strtolower($b['user_name'].$b['event_type'].$b['package']); ?>">
                            <td style="font-family:monospace; color:#9ca3af; font-size:12px;">#<?php echo str_pad($b['id'],4,'0',STR_PAD_LEFT); ?></td>
                            <td>
                                <p style="font-weight:600; color:#111827; margin:0; white-space:nowrap;"><?php echo htmlspecialchars($b['user_name'] ?? '—'); ?></p>
                                <p style="font-size:11.5px; color:#9ca3af; margin:0;"><?php echo htmlspecialchars($b['user_email'] ?? ''); ?></p>
                            </td>
                            <td><?php echo htmlspecialchars($b['event_type']); ?></td>
                            <td style="white-space:nowrap;"><?php echo date('M j, Y', strtotime($b['event_date'])); ?></td>
                            <td><?php echo number_format($b['guest_count']); ?></td>
                            <td style="font-size:12px;"><?php echo htmlspecialchars($b['package']); ?></td>
                            <td><span class="pill pill-<?php echo strtolower($b['status']); ?>"><i class="fas fa-circle" style="font-size:5px;"></i> <?php echo $b['status']; ?></span></td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button class="btn-sm btn-blue" onclick="openBookingModal(<?php echo htmlspecialchars(json_encode($b)); ?>)">
                                        <i class="fas fa-edit"></i> Manage
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this booking?')">
                                        <input type="hidden" name="action" value="delete_booking">
                                        <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
                                        <button type="submit" class="btn-sm btn-red"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($bookings)): ?>
                        <tr><td colspan="8" style="text-align:center; padding:40px; color:#9ca3af;">No bookings yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /bookings -->

    <!-- ════ TAB: USERS/CLIENTS ══════════════════════════ -->
    <div id="tab-users" class="tab-pane <?php echo $active_tab==='users' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.7rem; font-weight:900; color:#111827; margin-bottom:4px;">Clients</h1>
        <p style="color:#6b7280; font-size:13.5px; margin-bottom:20px;">All registered clients from the users table.</p>

        <div style="display:flex; justify-content:flex-end; margin-bottom:16px;">
            <div class="search-wrap">
                <i class="fas fa-search search-icon"></i>
                <input type="text" class="search-input" placeholder="Search clients…" oninput="searchTable('users-tbody', this.value)">
            </div>
        </div>

        <div class="section-card" style="padding:0; overflow:hidden;">
            <div style="overflow-x:auto;">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#ID</th><th>Full Name</th><th>Email</th><th>Phone</th>
                            <th>Registered</th><th>Bookings</th><th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="users-tbody">
                        <?php foreach ($users as $u):
                            $ubook = $conn->query("SELECT COUNT(*) FROM bookings WHERE user_id={$u['id']}")->fetch_row()[0];
                        ?>
                        <tr data-search="<?php echo strtolower($u['fullname'].$u['email']); ?>">
                            <td style="font-family:monospace; color:#9ca3af; font-size:12px;">#<?php echo str_pad($u['id'],4,'0',STR_PAD_LEFT); ?></td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--pink));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:13px;flex-shrink:0;">
                                        <?php echo strtoupper(substr($u['fullname'],0,1)); ?>
                                    </div>
                                    <p style="font-weight:600; color:#111827; margin:0;"><?php echo htmlspecialchars($u['fullname']); ?></p>
                                </div>
                            </td>
                            <td style="color:#6b7280;"><?php echo htmlspecialchars($u['email']); ?></td>
                            <td style="color:#6b7280;"><?php echo htmlspecialchars($u['phone'] ?? '—'); ?></td>
                            <td style="white-space:nowrap; color:#6b7280; font-size:12.5px;"><?php echo date('M j, Y', strtotime($u['created_at'])); ?></td>
                            <td>
                                <span style="background:var(--blue-light); color:var(--blue-dark); padding:3px 10px; border-radius:50px; font-size:12px; font-weight:700;"><?php echo $ubook; ?></span>
                            </td>
                            <td>
                                <div style="display:flex; gap:6px;">
                                    <button class="btn-sm btn-blue" onclick="switchTab('bookings'); setTimeout(()=>{ document.querySelector('.search-input').value='<?php echo addslashes(strtolower($u['fullname'])); ?>'; searchTable('bookings-tbody','<?php echo addslashes(strtolower($u['fullname'])); ?>'); }, 200);">
                                        <i class="fas fa-calendar"></i> Bookings
                                    </button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this client and ALL their data?')">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                        <button type="submit" class="btn-sm btn-red"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($users)): ?>
                        <tr><td colspan="7" style="text-align:center; padding:40px; color:#9ca3af;">No clients registered yet.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div><!-- /users -->

    <!-- ════ TAB: MESSAGES ═══════════════════════════════ -->
    <div id="tab-messages" class="tab-pane <?php echo $active_tab==='messages' ? 'active' : ''; ?>">
        <h1 class="font-display" style="font-size:1.7rem; font-weight:900; color:#111827; margin-bottom:4px;">Messages</h1>
        <p style="color:#6b7280; font-size:13.5px; margin-bottom:20px;">All client enquiries — click a message to read and reply.</p>

        <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:16px;">
            <?php foreach (['All','Unread','Read'] as $f): ?>
            <button onclick="filterTable('messages-tbody', '<?php echo $f; ?>', this)"
                style="padding:7px 18px; border-radius:50px; font-size:12px; font-weight:700; cursor:pointer; border:1.5px solid #e5e7eb; background:#fff; color:#374151; transition:all 0.2s;"
                class="filter-btn-m <?php echo $f==='All' ? 'f-active' : ''; ?>">
                <?php echo $f; ?><?php if ($f==='Unread' && $stats['unread_messages'] > 0): ?> (<?php echo $stats['unread_messages']; ?>)<?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>

        <div id="messages-list">
            <?php foreach ($messages as $m): ?>
            <div class="section-card" style="cursor:pointer; padding:0; overflow:hidden;" data-status="<?php echo htmlspecialchars($m['status'] ?? ''); ?>">

                <!-- Message header row -->
                <div onclick="toggleMessage(<?php echo $m['id']; ?>)"
                    style="display:flex; justify-content:space-between; align-items:center; padding:16px 22px; border-bottom:1px solid #f3f4f6; background:<?php echo (!empty($m['status']) && $m['status']==='Unread') ? 'linear-gradient(135deg,#eff6ff,#fdf2f8)' : '#fff'; ?>;">
                    <div style="display:flex; align-items:center; gap:14px;">
                        <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--blue),var(--pink));display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:14px;flex-shrink:0;">
                            <?php echo strtoupper(substr($m['user_name'] ?? 'U', 0, 1)); ?>
                        </div>
                        <div>
                            <p style="font-weight:700; font-size:14px; color:#111827; margin:0;"><?php echo htmlspecialchars($m['user_name'] ?? 'Unknown'); ?>
                                <?php if (!empty($m['status']) && $m['status']==='Unread'): ?>
                                <span style="display:inline-block; width:7px; height:7px; border-radius:50%; background:var(--pink); margin-left:6px; vertical-align:middle;"></span>
                                <?php endif; ?>
                            </p>
                            <p style="font-size:12px; color:#9ca3af; margin:0;"><?php echo htmlspecialchars($m['subject'] ?? 'No subject'); ?> &nbsp;·&nbsp; <?php echo date('M j, Y g:ia', strtotime($m['created_at'] ?? 'now')); ?></p>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:10px;">
                        <?php if (!empty($m['status'])): ?>
                            <span class="pill pill-<?php echo strtolower($m['status']); ?>"><?php echo htmlspecialchars($m['status']); ?></span>
                        <?php endif; ?>
                        <i class="fas fa-chevron-down" id="chevron-<?php echo $m['id']; ?>" style="color:#9ca3af; font-size:12px; transition:transform 0.3s;"></i>
                    </div>
                </div>

                <!-- Expandable body -->
                <div id="msg-body-<?php echo $m['id']; ?>" style="display:none; padding:20px 22px;">

                    <!-- Client message -->
                    <div style="background:#f8f9fb; border-left:3px solid var(--blue); padding:14px 16px; border-radius:0 10px 10px 0; margin-bottom:18px;">
                        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--blue-dark); margin-bottom:6px;">Client Message</p>
                        <p style="font-size:14px; color:#374151; line-height:1.7; white-space:pre-wrap;"><?php echo htmlspecialchars($m['message']); ?></p>
                        <p style="font-size:11px; color:#9ca3af; margin-top:8px;"><i class="fas fa-envelope mr-1"></i><?php echo htmlspecialchars($m['user_email'] ?? ''); ?></p>
                    </div>

                    <!-- Existing reply -->
                    <?php if ($messages_has_admin_reply && !empty($m['admin_reply'])): ?>
                    <div style="background:linear-gradient(135deg,#fdf2f8,#fff7ed); border-left:3px solid var(--pink); padding:14px 16px; border-radius:0 10px 10px 0; margin-bottom:18px;">
                        <p style="font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--pink-dark); margin-bottom:6px;">Your Reply
                            <?php if ($messages_has_replied_at && !empty($m['replied_at'])): ?>
                                <span style="font-weight:400; color:#9ca3af; text-transform:none; letter-spacing:0;">· <?php echo date('M j, Y g:ia', strtotime($m['replied_at'])); ?></span>
                            <?php endif; ?>
                        </p>
                        <p style="font-size:14px; color:#374151; line-height:1.7; white-space:pre-wrap;"><?php echo htmlspecialchars($m['admin_reply']); ?></p>
                    </div>
                    <?php endif; ?>

                    <?php if ($messages_has_admin_reply): ?>
                    <form method="POST">
                        <input type="hidden" name="action" value="reply_message">
                        <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                        <label class="field-label"><?php echo !empty($m['admin_reply']) ? 'Update Reply' : 'Write a Reply'; ?></label>
                        <textarea name="reply" class="field-input" rows="4" placeholder="Type your reply to this client…" required style="resize:vertical; margin-bottom:12px;"><?php echo htmlspecialchars($m['admin_reply'] ?? ''); ?></textarea>
                        <div style="display:flex; gap:10px; align-items:flex-start;">
                            <button type="submit" class="btn-primary" style="font-size:13px; padding:10px 24px;">
                                <i class="fas fa-paper-plane mr-2"></i><?php echo !empty($m['admin_reply']) ? 'Update Reply' : 'Send Reply'; ?>
                            </button>
                        </div>
                    </form>
                    <?php else: ?>
                    <div style="margin-bottom:14px; color:#6b7280; font-size:13px;">
                        Reply feature is unavailable for current message schema.
                    </div>
                    <?php endif; ?>

                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this message?')">
                        <input type="hidden" name="action" value="delete_message">
                        <input type="hidden" name="message_id" value="<?php echo $m['id']; ?>">
                        <button type="submit" class="btn-sm btn-red" style="height:100%; padding:10px 16px;">
                            <i class="fas fa-trash mr-1"></i> Delete
                        </button>
                    </form>
                </div>

            </div><!-- /message card -->
            <?php endforeach; ?>
            <?php if (empty($messages)): ?>
            <div class="section-card" style="text-align:center; padding:50px;">
                <i class="fas fa-inbox" style="font-size:48px; color:#e5e7eb; display:block; margin-bottom:14px;"></i>
                <p style="color:#9ca3af; font-size:15px;">No messages yet.</p>
            </div>
            <?php endif; ?>
        </div>
    </div><!-- /messages -->

</div><!-- /main-content -->

<!-- ══════════════════════════════════════════════
     BOOKING MODAL
══════════════════════════════════════════════ -->
<div id="bookingModal" style="display:none;" class="modal-overlay" onclick="if(event.target===this) closeBookingModal()">
    <div class="modal-box">
        <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
            <h2 class="modal-title" style="margin:0;">Manage Booking <span id="modal-booking-id" style="color:var(--pink);"></span></h2>
            <button onclick="closeBookingModal()" style="background:none; border:none; cursor:pointer; color:#9ca3af; font-size:18px;"><i class="fas fa-times"></i></button>
        </div>

        <!-- Info grid -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
            <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:3px;">Client</p>
                <p style="font-size:13.5px; font-weight:600; color:#111827;" id="modal-client">—</p>
            </div>
            <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:3px;">Event</p>
                <p style="font-size:13.5px; font-weight:600; color:#111827;" id="modal-event">—</p>
            </div>
            <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:3px;">Date</p>
                <p style="font-size:13.5px; font-weight:600; color:#111827;" id="modal-date">—</p>
            </div>
            <div style="background:#f8f9fb; padding:12px; border-radius:10px;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:3px;">Guests</p>
                <p style="font-size:13.5px; font-weight:600; color:#111827;" id="modal-guests">—</p>
            </div>
            <div style="background:#f8f9fb; padding:12px; border-radius:10px; grid-column:span 2;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#9ca3af; margin-bottom:3px;">Package</p>
                <p style="font-size:13.5px; font-weight:600; color:#111827;" id="modal-package">—</p>
            </div>
            <div id="modal-dishes-wrap" style="background:var(--pink-light); padding:12px; border-radius:10px; grid-column:span 2; display:none;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:var(--pink-dark); margin-bottom:6px;">Requested Dishes</p>
                <p style="font-size:13px; color:#374151;" id="modal-dishes">—</p>
            </div>
            <div id="modal-note-wrap" style="background:#fffbeb; border-left:3px solid #f59e0b; padding:12px; border-radius:0 10px 10px 0; grid-column:span 2; display:none;">
                <p style="font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:1px; color:#92400e; margin-bottom:4px;">Client Notes</p>
                <p style="font-size:13px; color:#374151; line-height:1.7;" id="modal-note">—</p>
            </div>
        </div>

        <!-- Update form -->
        <form method="POST" id="modalForm">
            <input type="hidden" name="action" value="update_booking">
            <input type="hidden" name="booking_id" id="modal-bid">

            <label class="field-label">Update Status</label>
            <select name="status" id="modal-status" class="field-input" style="margin-bottom:14px;">
                <option value="Pending">Pending</option>
                <option value="Confirmed">Confirmed</option>
                <option value="Completed">Completed</option>
                <option value="Cancelled">Cancelled</option>
            </select>

            <label class="field-label">Message to Client <span style="font-weight:400; color:#9ca3af; text-transform:none; letter-spacing:0;">(shown on their dashboard)</span></label>
            <textarea name="admin_note" id="modal-admin-note" class="field-input" rows="4" placeholder="e.g. Your booking is confirmed! Our team will arrive at 12pm. Please ensure parking is available." style="resize:vertical; margin-bottom:16px;"></textarea>

            <div style="display:flex; gap:10px;">
                <button type="submit" class="btn-primary" style="flex:1;">
                    <i class="fas fa-save mr-2"></i>Save Changes
                </button>
                <button type="button" onclick="closeBookingModal()" style="padding:11px 22px; border-radius:10px; border:1.5px solid #e5e7eb; background:#fff; cursor:pointer; font-size:13px; font-weight:600; color:#374151;">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.f-active {
    background: linear-gradient(135deg, var(--blue), var(--pink)) !important;
    color: #fff !important; border-color: transparent !important;
}
</style>

<script>
/* ── Tab switching ── */
function switchTab(name) {
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    document.querySelectorAll('.nav-item').forEach(btn => {
        if (btn.getAttribute('onclick') === `switchTab('${name}')`) btn.classList.add('active');
    });
    history.replaceState(null, '', '?tab=' + name);
}

/* ── Filter table rows by status ── */
function filterTable(tbodyId, status, btn) {
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;

    // Clear active state on sibling buttons
    const btnClass = tbodyId === 'bookings-tbody' ? '.filter-btn-b' : '.filter-btn-m';
    document.querySelectorAll(btnClass).forEach(b => b.classList.remove('f-active'));
    btn.classList.add('f-active');

    tbody.querySelectorAll('tr').forEach(row => {
        const s = row.dataset.status || '';
        row.style.display = (status === 'All' || s === status) ? '' : 'none';
    });

    // For messages list (card-based)
    if (tbodyId === 'messages-tbody') return;
    const msgList = document.getElementById('messages-list');
    if (!msgList) return;
    msgList.querySelectorAll('[data-status]').forEach(card => {
        const s = card.dataset.status || '';
        card.style.display = (status === 'All' || s === status) ? '' : 'none';
    });
}

/* ── Search table ── */
function searchTable(tbodyId, q) {
    q = q.toLowerCase();
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    tbody.querySelectorAll('tr').forEach(row => {
        row.style.display = (!q || (row.dataset.search || '').includes(q)) ? '' : 'none';
    });
}

/* ── Booking modal ── */
function openBookingModal(b) {
    document.getElementById('bookingModal').style.display = 'flex';
    document.getElementById('modal-booking-id').textContent = '#' + String(b.id).padStart(4,'0');
    document.getElementById('modal-bid').value   = b.id;
    document.getElementById('modal-client').textContent  = b.user_name  || '—';
    document.getElementById('modal-event').textContent   = b.event_type || '—';
    document.getElementById('modal-guests').textContent  = Number(b.guest_count).toLocaleString();
    document.getElementById('modal-package').textContent = b.package    || '—';
    document.getElementById('modal-date').textContent    = b.event_date ? new Date(b.event_date).toLocaleDateString('en-GB',{day:'numeric',month:'short',year:'numeric'}) : '—';
    document.getElementById('modal-admin-note').value    = b.admin_note || '';
    document.getElementById('modal-status').value        = b.status     || 'Pending';

    const dishWrap = document.getElementById('modal-dishes-wrap');
    if (b.dishes) { dishWrap.style.display='block'; document.getElementById('modal-dishes').textContent = b.dishes; }
    else { dishWrap.style.display='none'; }

    const noteWrap = document.getElementById('modal-note-wrap');
    if (b.special_note) { noteWrap.style.display='block'; document.getElementById('modal-note').textContent = b.special_note; }
    else { noteWrap.style.display='none'; }

    document.body.style.overflow = 'hidden';
}
function closeBookingModal() {
    document.getElementById('bookingModal').style.display = 'none';
    document.body.style.overflow = '';
}

/* ── Toggle message expand ── */
function toggleMessage(id) {
    const body    = document.getElementById('msg-body-' + id);
    const chevron = document.getElementById('chevron-' + id);
    const isOpen  = body.style.display !== 'none';
    body.style.display    = isOpen ? 'none' : 'block';
    chevron.style.transform = isOpen ? '' : 'rotate(180deg)';
}

/* ── Auto-remove toast after 4s ── */
const toast = document.querySelector('.toast');
if (toast) setTimeout(() => toast.remove(), 4000);

/* ── Init tab from URL ── */
const urlTab = new URLSearchParams(window.location.search).get('tab');
if (urlTab) switchTab(urlTab);
</script>

<?php $conn->close(); ?>
</body>
</html>
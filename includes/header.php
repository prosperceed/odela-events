<?php
session_start();
// normalize current script path and detect admin context robustly
$__php_self = $_SERVER['PHP_SELF'] ?? '';
$__in_admin = ($__php_self !== '' && strpos($__php_self, '/admin/') !== false);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">\

<link rel="preconnect" href="https://googleapis.com">
<link rel="preconnect" href="https://gstatic.com" crossorigin>
<link href="https://googleapis.com/css2?family=Inter:wght@400;500;600&family=Lato:wght@400;700&family=Montserrat:wght@700&display=swap" rel="stylesheet">
    <title>Odela Events</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: {
          // This overrides the default 'font-sans' class to use Inter
          sans: ['Inter', 'sans-serif'],
          // This creates the 'font-heading' class for Montserrat
          heading: ['Montserrat', 'sans-serif'],
          // This creates the 'font-lato' class for Lato
          lato: ['Lato', 'sans-serif'],
        }
      }
    }
  }
</script>
    <style>
        .glass-nav {
            background: rgba(15, 23, 60, 0.72);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
        }

        .nav-link {
            position: relative;
            color: rgba(255, 255, 255, 0.78);
            padding: 6px 0;
            font-size: 14px;
            font-weight: 500;
            letter-spacing: 0.4px;
            text-decoration: none;
            transition: color 0.25s;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0;
            width: 0; height: 2px;
            background: linear-gradient(90deg, #1D4ED8, #EC4899);
            border-radius: 2px;
            transition: width 0.3s ease;
        }
        .nav-link:hover,
        .nav-link.active { color: #ffffff; }
        .nav-link:hover::after,
        .nav-link.active::after { width: 100%; }

        .btn-book {
            background: linear-gradient(135deg, #1D4ED8, #EC4899);
            color: #fff;
            padding: 9px 22px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.3px;
            text-decoration: none;
            transition: opacity 0.25s, transform 0.2s;
            white-space: nowrap;
        }
        .btn-book:hover { opacity: 0.88; transform: translateY(-1px); }

        .btn-ghost {
            color: rgba(255,255,255,0.8);
            padding: 9px 20px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.2);
            transition: all 0.25s;
            white-space: nowrap;
        }
        .btn-ghost:hover {
            background: rgba(255,255,255,0.08);
            border-color: rgba(255,255,255,0.4);
            color: #fff;
        }

        .btn-logout {
            color: rgba(255,255,255,0.65);
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.2s;
        }
        .btn-logout:hover { color: #f87171; }

        .brand-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: linear-gradient(135deg, #1D4ED8, #EC4899);
            display: inline-block; margin-left: 2px; vertical-align: middle;
        }

        /* Mobile drawer */
        #mobileMenu {
            background: rgba(10, 15, 45, 0.96);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255,255,255,0.07);
        }
        .mobile-link {
            display: block;
            color: rgba(255,255,255,0.75);
            font-size: 15px;
            font-weight: 500;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            text-decoration: none;
            transition: color 0.2s;
        }
        .mobile-link:hover { color: #fff; }

        /* Hamburger lines */
        .burger-line {
            display: block; width: 22px; height: 2px;
            background: rgba(255,255,255,0.8); border-radius: 2px;
            transition: all 0.3s;
        }
        .burger-open .burger-line:nth-child(1) { transform: rotate(45deg) translate(5px, 5px); }
        .burger-open .burger-line:nth-child(2) { opacity: 0; }
        .burger-open .burger-line:nth-child(3) { transform: rotate(-45deg) translate(5px, -5px); }
    </style>
</head>
<body>

<nav class="glass-nav sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-10">
        <div class="flex items-center justify-between h-[68px]">

            <!-- ── Brand ── -->
                <a href="<?php echo $__in_admin ? '../index.php' : 'index.php'; ?>"
               style="text-decoration:none; display:flex; align-items:center; gap:6px;">
                <span style="font-family:'Georgia',serif; font-size:1.25rem; font-weight:700; color:#fff; letter-spacing:0.3px;">
                    Odela Events
                </span>
                <!-- <span class="brand-dot"></span> -->
            </a>

            <!-- ── Desktop Nav Links ── -->
            <div class="hidden md:flex items-center gap-8">
                     <a href="<?php echo $__in_admin ? '../index.php' : 'index.php'; ?>"
                         class="nav-link <?php echo (strpos($__php_self, 'index.php') !== false && $__in_admin === false) ? 'active' : ''; ?>">
                    Home
                </a>
                     <a href="<?php echo $__in_admin ? '../menu.php' : 'menu.php'; ?>"
                         class="nav-link <?php echo strpos($__php_self, 'menu.php') !== false ? 'active' : ''; ?>">
                    Menu
                </a>
                     <a href="<?php echo $__in_admin ? '../contact.php' : 'contact.php'; ?>"
                         class="nav-link <?php echo strpos($__php_self, 'contact.php') !== false ? 'active' : ''; ?>">
                    Contact
                </a>
            </div>

            <!-- ── Desktop Auth Buttons ── -->
            <div class="hidden md:flex items-center gap-3">
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <span style="color:rgba(255,255,255,0.6); font-size:13px;">
                        <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
                    </span>
                          <a href="<?php echo $__in_admin ? 'dashboard.php' : 'admin/dashboard.php'; ?>"
                              class="btn-ghost">Dashboard</a>
                          <a href="<?php echo $__in_admin ? 'logout.php' : 'admin/logout.php'; ?>"
                              class="btn-logout">Sign out</a>

                <?php elseif (isset($_SESSION['applicant_id'])): ?>
                    <span style="color:rgba(255,255,255,0.6); font-size:13px;">
                        <?php echo htmlspecialchars($_SESSION['applicant_name']); ?>
                    </span>
                          <a href="<?php echo $__in_admin ? '../applicant-dashboard.php' : 'applicant-dashboard.php'; ?>"
                              class="btn-ghost">My Bookings</a>
                          <a href="<?php echo $__in_admin ? '../applicant-logout.php' : 'applicant-logout.php'; ?>"
                              class="btn-logout">Sign out</a>

                <?php else: ?>
                          <a href="<?php echo $__in_admin ? '../signup.php' : 'signup.php'; ?>"
                              class="btn-ghost">Sign Up</a>
                          <a href="<?php echo $__in_admin ? '../book.php' : 'book.php'; ?>"
                              class="btn-book">Book Now</a>
                <?php endif; ?>
            </div>

            <!-- ── Mobile Burger ── -->
            <button id="burgerBtn" onclick="toggleMobileMenu()"
                    class="md:hidden flex flex-col gap-[5px] p-2 focus:outline-none">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </button>

        </div><!-- /flex row -->
    </div>

    <!-- ── Mobile Drawer ── -->
    <div id="mobileMenu" class="hidden md:hidden px-5 pb-5 pt-2">
        <a href="<?php echo $__in_admin ? '../index.php' : 'index.php'; ?>"
           class="mobile-link">Home</a>
        <a href="<?php echo $__in_admin ? '../menu.php' : 'menu.php'; ?>"
           class="mobile-link">Menu</a>
        <a href="<?php echo $__in_admin ? '../contact.php' : 'contact.php'; ?>"
           class="mobile-link">Contact</a>

        <div style="padding-top:16px; display:flex; flex-direction:column; gap:10px;">
            <?php if (isset($_SESSION['admin_id'])): ?>
                     <a href="<?php echo $__in_admin ? 'dashboard.php' : 'admin/dashboard.php'; ?>"
                         class="btn-ghost" style="text-align:center;">Dashboard</a>
                     <a href="<?php echo $__in_admin ? 'logout.php' : 'admin/logout.php'; ?>"
                         style="text-align:center; color:#f87171; font-size:14px;">Sign out</a>

            <?php elseif (isset($_SESSION['applicant_id'])): ?>
                     <a href="<?php echo $__in_admin ? '../applicant-dashboard.php' : 'applicant-dashboard.php'; ?>"
                         class="btn-ghost" style="text-align:center;">My Bookings</a>
                     <a href="<?php echo $__in_admin ? '../applicant-logout.php' : 'applicant-logout.php'; ?>"
                         style="text-align:center; color:#f87171; font-size:14px;">Sign out</a>

            <?php else: ?>
                     <a href="<?php echo $__in_admin ? '../signup.php' : 'signup.php'; ?>"
                         class="btn-ghost" style="text-align:center;">Sign Up</a>
                     <a href="<?php echo $__in_admin ? '../book.php' : 'book.php'; ?>"
                         class="btn-book" style="text-align:center;">Book Now</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const btn  = document.getElementById('burgerBtn');
        menu.classList.toggle('hidden');
        btn.classList.toggle('burger-open');
    }
</script>
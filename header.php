<?php
if (session_status() === PHP_SESSION_NONE) session_start();
$prefix  = (strpos($_SERVER['PHP_SELF'], '/includes/') !== false) ? '../' : '';
$current = basename($_SERVER['PHP_SELF']);
function nav_active($file, $current) { return $file === $current ? 'active' : ''; }
?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
<link rel="stylesheet" href="<?= $prefix ?>css/design.css">
<link rel="stylesheet" href="<?= $prefix ?>css/head.css">
<div class="site-flag"></div>

<header class="header">
    <div class="top-head">
        <div class="menu-toll-links">
            <div class="menu-btn" onclick="toggleNav()">
                <i class="fa-solid fa-bars"></i> <span>Menu</span>
            </div>
            <div class="toll-links">
                <span><a href="<?= $prefix ?>index.php"><i class="fa-solid fa-house"></i></a></span>
                <span><i class="fa-solid fa-phone"></i> Toll-Free: <strong>1950</strong></span>
            </div>
        </div>

        <div class="soc-links">
            <ul>
                <li><a href="#" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a></li>
                <li><a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a></li>
                <li><a href="#" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a></li>
                <li><a href="#" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a></li>
            </ul>
        </div>

        <div class="accessibility-controls">
            <button onclick="changeFont(2)" aria-label="Increase text size">A+</button>
            <button onclick="changeFont(-2)" aria-label="Decrease text size">A-</button>
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="user-greeting"><i class="fa-solid fa-circle-user"></i> <span><?= htmlspecialchars($_SESSION['username'] ?? '') ?></span></span>
                <a href="<?= $prefix ?>logout.php" class="btn btn-logout"><i class="fa-solid fa-right-from-bracket"></i> <span>Logout</span></a>
            <?php else: ?>
                <a href="<?= $prefix ?>login.php" class="btn"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <?php endif; ?>
        </div>
    </div>

    <nav class="main-nav" id="mainNav">
        <ul>
            <li><a class="<?= nav_active('index.php',$current) ?>" href="<?= $prefix ?>index.php"><i class="fa-solid fa-house"></i> Home</a></li>
            <li><a class="<?= nav_active('voters.php',$current) ?>" href="<?= $prefix ?>voters.php"><i class="fa-solid fa-users"></i> Services</a></li>
            <li><a class="<?= nav_active('candidates.php',$current) ?>" href="<?= $prefix ?>candidates.php"><i class="fa-solid fa-user-tie"></i> Candidates</a></li>
            <li><a class="<?= nav_active('results.php',$current) ?>" href="<?= $prefix ?>results.php"><i class="fa-solid fa-chart-column"></i> Results</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li><a class="<?= nav_active('vote.php',$current) ?>" href="<?= $prefix ?>vote.php"><i class="fa-solid fa-check-to-slot"></i> Cast Vote</a></li>
                <li><a class="<?= nav_active('dashboard.php',$current) ?>" href="<?= $prefix ?>includes/dashboard.php"><i class="fa-solid fa-gauge"></i> Dashboard</a></li>
                <?php if (!empty($_SESSION['is_admin'])): ?>
                <li><a href="<?= $prefix ?>admin/index.php"><i class="fa-solid fa-shield-halved"></i> Admin</a></li>
                <?php endif; ?>
            <?php endif; ?>
        </ul>
        <div class="nav-social">
            <a href="https://www.facebook.com" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="https://www.instagram.com" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="https://x.com" aria-label="Twitter"><i class="fa-brands fa-twitter"></i></a>
            <a href="https://www.youtube.com" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
        </div>
    </nav>
</header>

<script>
function toggleNav() { document.getElementById('mainNav').classList.toggle('open'); }
function changeFont(delta) {
    const current = parseFloat(getComputedStyle(document.body).fontSize);
    document.body.style.fontSize = (current + delta) + 'px';
}
</script>

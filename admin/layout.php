<?php
/* Admin layout shell — open.
   Caller must, BEFORE including this:
     require '../includes/db.php'; require '../includes/functions.php';
     require_admin();
     $page_title = '...'; $active = 'dashboard';   // nav key
   Then include this file, echo content, then include 'layout_end.php'.
*/
if (!isset($page_title)) $page_title = 'Admin';
if (!isset($active))     $active = '';

$nav = [
    'dashboard'      => ['index.php',          'fa-gauge-high',       'Dashboard'],
    'elections'      => ['elections.php',       'fa-landmark',         'Elections'],
    'candidates'     => ['candidates.php',      'fa-user-tie',         'Candidates'],
    'constituencies' => ['constituencies.php',  'fa-map-location-dot', 'Constituencies'],
    'voters'         => ['voters.php',          'fa-users',            'Voters'],
    'announcements'  => ['announcements.php',   'fa-bullhorn',         'Announcements'],
    'results'        => ['results.php',         'fa-chart-column',     'Results'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($page_title) ?> · Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="../css/design.css">
    <link rel="stylesheet" href="admin.css">
</head>
<body>
<div class="admin-shell">
    <aside class="admin-sidebar">
        <div class="admin-brand" onclick="document.getElementById('adminNav').classList.toggle('open')">
            <div style="display:flex;align-items:center;gap:10px">
                <i class="fa-solid fa-check-to-slot"></i>
                <div>Voting Admin<small>Election Commission</small></div>
            </div>
        </div>
        <div class="admin-sidebar-nav" id="adminNav">
            <nav>
                <ul class="admin-nav">
                    <?php foreach ($nav as $key => [$href, $icon, $label]): ?>
                        <li>
                            <a href="<?= $href ?>" class="<?= $active === $key ? 'active' : '' ?>">
                                <i class="fa-solid <?= $icon ?>"></i> <?= $label ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </nav>
            <div class="admin-sidebar-foot">
                <a href="../index.php"><i class="fa-solid fa-house"></i> View Site</a>
                <a href="../logout.php" style="margin-top:8px"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
            </div>
        </div>
    </aside>

    <div class="admin-main">
        <div class="admin-topbar">
            <h1><?= e($page_title) ?></h1>
            <span class="who"><i class="fa-solid fa-circle-user"></i> <?= e($_SESSION['username'] ?? 'admin') ?></span>
        </div>
        <div class="admin-content">
            <?php render_flash(); ?>

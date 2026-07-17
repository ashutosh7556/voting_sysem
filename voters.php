<?php
require 'includes/db.php';
require 'includes/functions.php';

$services = [
    ['fa-users',           'Electors',              'Register, verify, and manage your voter profile.',          'register.php'],
    ['fa-user-tie',        'Candidates & Parties',  'Browse candidates, parties, bios and manifestos.',          'candidates.php'],
    ['fa-check-to-slot',   'Cast Your Vote',        'Vote securely in active elections for your constituency.',   'vote.php'],
    ['fa-chart-column',    'Results & Publications', 'View live and published election results.',                 'results.php'],
    ['fa-gauge',           'Voter Dashboard',       'Track your voting status, constituency, and verification.',  'includes/dashboard.php'],
    ['fa-circle-question', 'Voter Education',       'Learn how online voting works — see the FAQ.',              'index.php#faq'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voter Services · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-hero">
    <h1><i class="fa-solid fa-hand-holding-heart"></i> Voter Services</h1>
    <p>Everything you need to participate in the democratic process</p>
</div>

<div class="services-grid">
    <?php foreach ($services as [$icon, $title, $desc, $href]): ?>
    <a class="service-card" href="<?= $href ?>">
        <div class="service-ico"><i class="fa-solid <?= $icon ?>"></i></div>
        <h3><?= e($title) ?></h3>
        <p><?= e($desc) ?></p>
        <span class="service-go">Open <i class="fa-solid fa-arrow-right"></i></span>
    </a>
    <?php endforeach; ?>
</div>

<style>
.services-grid { max-width: var(--maxw); margin: 40px auto; padding: 0 24px;
    display: grid; grid-template-columns: repeat(auto-fill, minmax(280px,1fr)); gap: 22px; }
.service-card { background: #fff; border: 1px solid var(--color-border); border-radius: var(--radius);
    box-shadow: var(--shadow-sm); padding: 28px 24px; text-decoration: none; color: var(--color-text);
    display: flex; flex-direction: column; transition: transform var(--transition), box-shadow var(--transition); }
.service-card:hover { transform: translateY(-4px); box-shadow: var(--shadow); }
.service-ico { width: 56px; height: 56px; border-radius: var(--radius); background: var(--navy-050);
    color: var(--navy-700); display: flex; align-items: center; justify-content: center; font-size: 24px; margin-bottom: 16px; }
.service-card h3 { font-size: var(--fs-md); margin: 0 0 8px; }
.service-card p { color: var(--slate-500); font-size: var(--fs-sm); margin: 0 0 16px; flex: 1; }
.service-go { color: var(--saffron-dark); font-weight: 700; font-size: var(--fs-sm); }
.service-card:hover .service-go { color: var(--saffron); }
</style>

<?php include 'footer.php'; ?>
</body>
</html>

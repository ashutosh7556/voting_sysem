<?php
require 'includes/db.php';
require 'includes/functions.php';

$election = get_active_election($conn);
$announcements = $conn->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3")->fetchAll();

$stat_voters = (int)$conn->query("SELECT COUNT(*) FROM voters WHERE is_admin = 0")->fetchColumn();
$stat_cands  = (int)$conn->query("SELECT COUNT(*) FROM candidates WHERE status='active'")->fetchColumn();
$stat_votes  = (int)$conn->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$stat_cons   = (int)$conn->query("SELECT COUNT(*) FROM constituencies")->fetchColumn();

$ann_icon = ['info'=>'fa-circle-info','success'=>'fa-circle-check','warning'=>'fa-triangle-exclamation','danger'=>'fa-circle-exclamation'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Election Commission · Online Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/index.css">
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>
<?php include 'header.php'; ?>

<!-- Hero -->
<section class="hero">
    <div class="hero-inner">
        <h1>Your Vote, Your Voice</h1>
        <p>Secure, transparent online voting for the Election Commission. Register, verify, and cast your ballot from anywhere.</p>
        <div class="hero-cta">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="vote.php" class="btn-accent"><i class="fa-solid fa-check-to-slot"></i> Cast Your Vote</a>
                <a href="results.php" class="btn-ghost"><i class="fa-solid fa-chart-column"></i> View Results</a>
            <?php else: ?>
                <a href="register.php" class="btn-accent"><i class="fa-solid fa-user-plus"></i> Register to Vote</a>
                <a href="login.php" class="btn-ghost"><i class="fa-solid fa-right-to-bracket"></i> Login</a>
            <?php endif; ?>
        </div>
        <form class="home-search" onsubmit="return doSearch(event)">
            <input type="text" id="searchInput" placeholder="Search candidates or parties...">
            <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>
    </div>
</section>

<!-- Active election -->
<?php if ($election): ?>
<section class="home-section" style="padding-bottom:0">
    <div class="live-election">
        <div>
            <span class="badge badge-green"><i class="fa-solid fa-circle"></i> Live Now</span>
            <h2 style="margin-top:8px"><?= e($election['title']) ?></h2>
            <p class="text-muted mb-0"><?= e($election['description']) ?></p>
            <p style="margin:10px 0 0">Voting closes in: <span class="cd" id="countdown" data-end="<?= e($election['end_date']) ?>">…</span></p>
        </div>
        <div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="vote.php" class="btn-accent"><i class="fa-solid fa-check-to-slot"></i> Vote Now</a>
            <?php else: ?>
                <a href="login.php" class="btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Login to Vote</a>
            <?php endif; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- Stats -->
<section class="home-stats">
    <div class="home-stats-inner">
        <div class="home-stat"><div class="num"><?= number_format($stat_voters) ?></div><div class="lbl">Registered Voters</div></div>
        <div class="home-stat"><div class="num"><?= number_format($stat_cands) ?></div><div class="lbl">Candidates</div></div>
        <div class="home-stat"><div class="num"><?= number_format($stat_votes) ?></div><div class="lbl">Votes Cast</div></div>
        <div class="home-stat"><div class="num"><?= number_format($stat_cons) ?></div><div class="lbl">Constituencies</div></div>
    </div>
</section>

<!-- Announcements -->
<?php if ($announcements): ?>
<section class="home-section">
    <h2 class="section-title">Announcements</h2>
    <p class="section-sub">Latest updates from the Election Commission</p>
    <div class="ann-list">
        <?php foreach ($announcements as $a): ?>
        <div class="ann-card <?= e($a['type']) ?>">
            <h4><i class="fa-solid <?= $ann_icon[$a['type']] ?? 'fa-circle-info' ?>"></i> <?= e($a['title']) ?></h4>
            <p><?= nl2br(e($a['body'])) ?></p>
            <span class="date"><i class="fa-regular fa-calendar"></i> <?= date('d M Y', strtotime($a['created_at'])) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- How it works -->
<section class="home-section" style="background:var(--slate-050)">
    <h2 class="section-title">How It Works</h2>
    <p class="section-sub">Four simple steps to cast your vote</p>
    <div class="steps">
        <div class="step"><div class="n">1</div><i class="fa-solid fa-user-plus"></i><h4>Register</h4><p>Create your account with your Voter ID and details.</p></div>
        <div class="step"><div class="n">2</div><i class="fa-solid fa-user-check"></i><h4>Get Verified</h4><p>The Commission verifies your identity and constituency.</p></div>
        <div class="step"><div class="n">3</div><i class="fa-solid fa-users-viewfinder"></i><h4>Review Candidates</h4><p>Read bios and manifestos of your constituency candidates.</p></div>
        <div class="step"><div class="n">4</div><i class="fa-solid fa-check-to-slot"></i><h4>Cast Your Vote</h4><p>Vote securely — one ballot per election, fully confidential.</p></div>
    </div>
</section>

<!-- FAQ -->
<section class="home-section">
    <h2 class="section-title">Frequently Asked Questions</h2>
    <p class="section-sub">Everything you need to know</p>
    <div class="faq">
        <?php
        $faqs = [
            ['Who can vote?', 'Any registered voter with a valid Voter ID whose account has been verified by the Election Commission.'],
            ['Is my vote secret?', 'Yes. Your ballot is confidential. Administrators can see that you voted, but individual choices are protected.'],
            ['Can I change my vote?', 'No. Once cast, a vote is final and cannot be changed. Please review your choice before confirming.'],
            ['When are results published?', 'Results are published by the Election Commission after voting closes, or live if the election is configured for public results.'],
        ];
        foreach ($faqs as $f): ?>
        <div class="faq-item">
            <button type="button" class="faq-q" onclick="this.parentElement.classList.toggle('open')">
                <span><?= e($f[0]) ?></span> <i class="fa-solid fa-chevron-down"></i>
            </button>
            <div class="faq-a"><p><?= e($f[1]) ?></p></div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- Quick actions -->
<section class="home-quick">
    <div class="quick-actions">
        <a href="candidates.php" class="action-card"><i class="fa-solid fa-user-tie"></i><span>View Candidates</span></a>
        <a href="vote.php" class="action-card"><i class="fa-solid fa-check-to-slot"></i><span>Cast Your Vote</span></a>
        <a href="results.php" class="action-card"><i class="fa-solid fa-chart-column"></i><span>Live Results</span></a>
        <a href="register.php" class="action-card"><i class="fa-solid fa-user-plus"></i><span>Register to Vote</span></a>
    </div>
</section>

<?php include 'footer.php'; ?>
<script>
function doSearch(e){
    e.preventDefault();
    var q = document.getElementById('searchInput').value.trim();
    if (q) window.location.href = 'candidates.php?search=' + encodeURIComponent(q);
    return false;
}
(function(){
    var el = document.getElementById('countdown');
    if (!el) return;
    var end = new Date(el.dataset.end.replace(' ','T')).getTime();
    function tick(){
        var d = end - Date.now();
        if (d <= 0){ el.textContent = 'Voting closed'; return; }
        var days=Math.floor(d/864e5), h=Math.floor(d%864e5/36e5), m=Math.floor(d%36e5/6e4), s=Math.floor(d%6e4/1e3);
        el.textContent = days+'d '+h+'h '+m+'m '+s+'s';
    }
    tick(); setInterval(tick,1000);
})();
</script>
</body>
</html>

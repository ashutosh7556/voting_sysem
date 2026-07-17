<?php
require 'db.php';
require 'functions.php';
require_login('../login.php');

$user_id = $_SESSION['user_id'];

// voter + constituency
$stmt = $conn->prepare("SELECT v.*, c.name AS cons_name, c.state AS cons_state
    FROM voters v LEFT JOIN constituencies c ON c.id = v.constituency_id WHERE v.id = ?");
$stmt->execute([$user_id]);
$voter = $stmt->fetch();

$election = get_active_election($conn);
$voted_for = null;
$has_voted = false;
if ($election) {
    $has_voted = has_voted($conn, $user_id, (int)$election['id']);
    if ($has_voted) {
        $stmt = $conn->prepare("SELECT c.name, c.party FROM votes v JOIN candidates c ON c.id = v.candidate_id
            WHERE v.voter_id = ? AND v.election_id = ?");
        $stmt->execute([$user_id, $election['id']]);
        $voted_for = $stmt->fetch();
    }
}

$total_votes = (int)$conn->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$total_cands = (int)$conn->query("SELECT COUNT(*) FROM candidates WHERE status='active'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="../css/pages.css">
</head>
<body>
<?php include '../header.php'; ?>

<div class="page-hero">
    <h1><i class="fa-solid fa-gauge"></i> Voter Dashboard</h1>
    <p>Welcome back, <strong><?= e($voter['full_name'] ?: $voter['username']) ?></strong></p>
</div>

<div class="dashboard-container">

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card">
            <i class="fa-solid fa-check-to-slot"></i>
            <div><span class="stat-number"><?= $total_votes ?></span><span class="stat-label">Total Votes Cast</span></div>
        </div>
        <div class="stat-card">
            <i class="fa-solid fa-user-tie"></i>
            <div><span class="stat-number"><?= $total_cands ?></span><span class="stat-label">Active Candidates</span></div>
        </div>
        <div class="stat-card <?= $has_voted ? 'voted' : 'not-voted' ?>">
            <i class="fa-solid <?= $has_voted ? 'fa-circle-check' : 'fa-circle-xmark' ?>"></i>
            <div><span class="stat-number"><?= $has_voted ? 'Voted' : ($election ? 'Not Voted' : '—') ?></span>
                 <span class="stat-label">Your Status</span></div>
        </div>
    </div>

    <!-- Active election -->
    <div class="dashboard-card">
        <h3><i class="fa-solid fa-landmark"></i> Current Election</h3>
        <?php if ($election): ?>
            <div class="election-banner">
                <h3><?= e($election['title']) ?> <span class="badge badge-green"><i class="fa-solid fa-circle"></i> Live</span></h3>
                <p class="text-muted mb-0"><?= e($election['description']) ?></p>
                <p class="mb-0" style="margin-top:8px">Closes: <span class="cd" id="countdown" data-end="<?= e($election['end_date']) ?>">…</span></p>
            </div>

            <?php if ($has_voted): ?>
                <div class="vote-status voted">
                    <i class="fa-solid fa-circle-check"></i>
                    You voted for <strong><?= e($voted_for['name'] ?? '') ?></strong>
                    <?php if (!empty($voted_for['party'])): ?><span class="party-badge"><?= e($voted_for['party']) ?></span><?php endif; ?>
                </div>
            <?php elseif (empty($voter['is_verified'])): ?>
                <div class="vote-status not-voted">
                    <i class="fa-solid fa-user-clock"></i> Your account is pending verification. You can vote once verified.
                </div>
            <?php else: ?>
                <div class="vote-status not-voted">
                    <i class="fa-solid fa-circle-xmark"></i> You haven't voted yet.
                    <a href="../vote.php" class="btn-vote"><i class="fa-solid fa-check-to-slot"></i> Cast Vote Now</a>
                </div>
            <?php endif; ?>
        <?php else: ?>
            <p class="text-muted mb-0">No active election at the moment. Check back later.</p>
        <?php endif; ?>
    </div>

    <!-- Voter profile -->
    <div class="dashboard-card">
        <h3><i class="fa-solid fa-id-card"></i> Your Details</h3>
        <div class="info-grid">
            <div class="info-item"><div class="k">Voter ID</div><div class="v"><?= e($voter['voter_id']) ?></div></div>
            <div class="info-item"><div class="k">Constituency</div><div class="v"><?= $voter['cons_name'] ? e($voter['cons_name']) : '—' ?></div></div>
            <div class="info-item"><div class="k">State</div><div class="v"><?= e($voter['state'] ?: ($voter['cons_state'] ?? '—')) ?></div></div>
            <div class="info-item"><div class="k">Verification</div><div class="v"><?= $voter['is_verified'] ? 'Verified' : 'Pending' ?></div></div>
        </div>
    </div>

    <!-- Quick links -->
    <div class="dashboard-card">
        <h3><i class="fa-solid fa-link"></i> Quick Links</h3>
        <div class="quick-actions">
            <a href="../candidates.php" class="action-card"><i class="fa-solid fa-user-tie"></i><span>Candidates</span></a>
            <a href="../vote.php" class="action-card"><i class="fa-solid fa-check-to-slot"></i><span>Cast Vote</span></a>
            <a href="../results.php" class="action-card"><i class="fa-solid fa-chart-column"></i><span>Results</span></a>
            <a href="../index.php" class="action-card"><i class="fa-solid fa-house"></i><span>Home</span></a>
        </div>
    </div>
</div>

<?php include '../footer.php'; ?>
<script>
(function(){
    var el = document.getElementById('countdown');
    if (!el) return;
    var end = new Date(el.dataset.end.replace(' ','T')).getTime();
    function tick(){
        var d = end - Date.now();
        if (d <= 0){ el.textContent = 'Election closed'; return; }
        var days=Math.floor(d/864e5), h=Math.floor(d%864e5/36e5), m=Math.floor(d%36e5/6e4), s=Math.floor(d%6e4/1e3);
        el.textContent = days+'d '+h+'h '+m+'m '+s+'s';
    }
    tick(); setInterval(tick,1000);
})();
</script>
</body>
</html>

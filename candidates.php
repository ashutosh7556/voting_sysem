<?php
require 'includes/db.php';
require 'includes/functions.php';

// ── Detail view ──
$detail = null;
if (isset($_GET['id'])) {
    $stmt = $conn->prepare("SELECT ca.*, co.name AS cons_name, co.state AS cons_state, el.title AS elect_title
        FROM candidates ca
        LEFT JOIN constituencies co ON co.id = ca.constituency_id
        LEFT JOIN elections el ON el.id = ca.election_id
        WHERE ca.id = ?");
    $stmt->execute([(int)$_GET['id']]);
    $detail = $stmt->fetch();
}

// ── List view ──
$search = trim($_GET['search'] ?? '');
if (!$detail) {
    $sql = "SELECT ca.*, co.name AS cons_name FROM candidates ca
            LEFT JOIN constituencies co ON co.id = ca.constituency_id
            WHERE ca.status = 'active'";
    $params = [];
    if ($search !== '') {
        $sql .= " AND (ca.name LIKE ? OR ca.party LIKE ?)";
        $params = ["%$search%","%$search%"];
    }
    $sql .= " ORDER BY ca.party, ca.name";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $candidates = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Candidates · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>
<?php include 'header.php'; ?>

<?php if ($detail): ?>
<!-- ── Candidate profile ── -->
<div class="page-hero"><h1><i class="fa-solid fa-user-tie"></i> Candidate Profile</h1></div>
<div class="profile-wrap">
    <a href="candidates.php" class="btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to all candidates</a>
    <div class="profile-head" style="margin-top:14px">
        <?php if (!empty($detail['photo'])): ?>
            <img src="uploads/candidates/<?= e($detail['photo']) ?>" alt="<?= e($detail['name']) ?>" style="border-radius:50%;object-fit:cover;border:3px solid var(--navy-700)">
        <?php else: ?>
            <div class="photo-placeholder"><i class="fa-solid fa-user"></i></div>
        <?php endif; ?>
        <div>
            <h2 style="margin:0"><?= e($detail['name']) ?></h2>
            <span class="party-badge"><?= e($detail['party']) ?></span>
            <div class="profile-meta">
                <?php if ($detail['cons_name']): ?><span><i class="fa-solid fa-location-dot"></i> <?= e($detail['cons_name']) ?>, <?= e($detail['cons_state']) ?></span><?php endif; ?>
                <?php if ($detail['age']): ?><span><i class="fa-solid fa-cake-candles"></i> <?= (int)$detail['age'] ?> yrs</span><?php endif; ?>
                <?php if ($detail['education']): ?><span><i class="fa-solid fa-graduation-cap"></i> <?= e($detail['education']) ?></span><?php endif; ?>
                <?php if ($detail['symbol']): ?><span><i class="fa-solid fa-star-of-life"></i> <?= e($detail['symbol']) ?></span><?php endif; ?>
            </div>
        </div>
    </div>

    <?php if (!empty($detail['bio'])): ?>
    <div class="profile-section"><h3><i class="fa-solid fa-circle-info"></i> Biography</h3><p class="mb-0"><?= nl2br(e($detail['bio'])) ?></p></div>
    <?php endif; ?>
    <?php if (!empty($detail['manifesto'])): ?>
    <div class="profile-section"><h3><i class="fa-solid fa-scroll"></i> Manifesto</h3><p class="mb-0"><?= nl2br(e($detail['manifesto'])) ?></p></div>
    <?php endif; ?>

    <?php if (isset($_SESSION['user_id'])): ?>
    <div class="profile-section text-center">
        <a href="vote.php?candidate_id=<?= $detail['id'] ?>" class="btn-vote"><i class="fa-solid fa-check-to-slot"></i> Vote for <?= e($detail['name']) ?></a>
    </div>
    <?php endif; ?>
</div>

<?php else: ?>
<!-- ── Candidate list ── -->
<div class="page-hero">
    <h1><i class="fa-solid fa-user-tie"></i> Candidates</h1>
    <form method="GET" class="search-form">
        <input type="text" name="search" placeholder="Search by name or party..." value="<?= e($search) ?>">
        <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
</div>

<div class="candidates-grid">
    <?php if (empty($candidates)): ?>
        <p class="no-data"><i class="fa-solid fa-circle-info"></i> No candidates found.</p>
    <?php else: foreach ($candidates as $c): ?>
        <div class="candidate-card">
            <div class="candidate-photo">
                <?php if (!empty($c['photo'])): ?>
                    <img src="uploads/candidates/<?= e($c['photo']) ?>" alt="<?= e($c['name']) ?>">
                <?php else: ?>
                    <div class="photo-placeholder"><i class="fa-solid fa-user"></i></div>
                <?php endif; ?>
            </div>
            <div class="candidate-info">
                <h3><?= e($c['name']) ?></h3>
                <span class="party-badge"><?= e($c['party']) ?></span>
                <p class="constituency"><i class="fa-solid fa-location-dot"></i> <?= $c['cons_name'] ? e($c['cons_name']) : 'All constituencies' ?></p>
            </div>
            <a href="candidates.php?id=<?= $c['id'] ?>" class="btn-outline"><i class="fa-solid fa-circle-info"></i> View Profile</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="vote.php?candidate_id=<?= $c['id'] ?>" class="btn-vote"><i class="fa-solid fa-check-to-slot"></i> Vote</a>
            <?php endif; ?>
        </div>
    <?php endforeach; endif; ?>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
</body>
</html>

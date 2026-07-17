<?php
require 'includes/db.php';
require 'includes/functions.php';
require_login('login.php');

$user_id = $_SESSION['user_id'];
$message = ''; $type = '';

// voter (for constituency + verification)
$stmt = $conn->prepare("SELECT id, constituency_id, is_verified FROM voters WHERE id = ?");
$stmt->execute([$user_id]);
$voter = $stmt->fetch();

$election = get_active_election($conn);

$already_voted = false;
if ($election) $already_voted = has_voted($conn, $user_id, (int)$election['id']);

// candidate query: this election, active, in voter's constituency or all-constituency (NULL)
function load_candidates(PDO $conn, $election, $voter) {
    $sql = "SELECT * FROM candidates WHERE election_id = ? AND status = 'active'
            AND (constituency_id IS NULL OR constituency_id = ?)
            ORDER BY party, name";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$election['id'], $voter['constituency_id']]);
    return $stmt->fetchAll();
}

// ── Handle submission ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    if (!$election) {
        $message = "No active election."; $type = "error";
    } elseif (empty($voter['is_verified'])) {
        $message = "Your account must be verified before voting."; $type = "error";
    } elseif ($already_voted) {
        $message = "You have already voted in this election."; $type = "info";
    } else {
        $cid = (int)($_POST['candidate_id'] ?? 0);
        // validate candidate is a legal choice
        $valid = false;
        foreach (load_candidates($conn, $election, $voter) as $c) {
            if ((int)$c['id'] === $cid) { $valid = true; break; }
        }
        if (!$valid) {
            $message = "Invalid candidate selected."; $type = "error";
        } else {
            try {
                $stmt = $conn->prepare("INSERT INTO votes (election_id, voter_id, candidate_id, voted_at) VALUES (?,?,?,NOW())");
                $stmt->execute([$election['id'], $user_id, $cid]);
                $message = "Your vote has been cast successfully!"; $type = "success";
                $already_voted = true;
            } catch (PDOException $e) {
                // unique constraint = double vote race
                $message = "You have already voted in this election."; $type = "info";
                $already_voted = true;
            }
        }
    }
}

$candidates = $election ? load_candidates($conn, $election, $voter) : [];
$preselect  = isset($_GET['candidate_id']) ? (int)$_GET['candidate_id'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cast Vote · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/pages.css">
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-hero"><h1><i class="fa-solid fa-check-to-slot"></i> Cast Your Vote</h1></div>

<div class="vote-container">
    <?php if ($message): ?>
        <div class="alert alert-<?= $type ?>">
            <i class="fa-solid <?= $type==='success'?'fa-circle-check':($type==='info'?'fa-circle-info':'fa-circle-exclamation') ?>"></i>
            <?= e($message) ?>
            <?php if ($type === 'success'): ?><a href="results.php" class="btn-link">View Results &rarr;</a><?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!$election): ?>
        <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i> No active election right now.</div>

    <?php elseif ($already_voted): ?>
        <?php if (!$message): ?>
        <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i> You have already voted in this election.
            <a href="results.php" class="btn-link">View Results &rarr;</a></div>
        <?php endif; ?>

    <?php elseif (empty($voter['is_verified'])): ?>
        <div class="alert alert-warning"><i class="fa-solid fa-user-clock"></i> Your account is pending verification. You cannot vote yet.</div>

    <?php else: ?>
        <div class="election-banner">
            <h3><?= e($election['title']) ?></h3>
            <p class="text-muted mb-0"><?= e($election['description']) ?></p>
        </div>

        <?php if (empty($candidates)): ?>
            <div class="alert alert-info"><i class="fa-solid fa-circle-info"></i> No candidates available for your constituency in this election.</div>
        <?php else: ?>
        <form method="POST" id="voteForm">
            <?= csrf_field() ?>
            <p class="vote-instruction">Select one candidate and click <strong>Cast Vote</strong>.</p>
            <div class="vote-list">
                <?php foreach ($candidates as $c): ?>
                <label class="vote-option <?= $preselect === (int)$c['id'] ? 'selected' : '' ?>">
                    <input type="radio" name="candidate_id" value="<?= $c['id'] ?>" <?= $preselect === (int)$c['id'] ? 'checked' : '' ?> required>
                    <div class="vote-candidate-info">
                        <div class="vote-photo">
                            <?php if (!empty($c['photo'])): ?>
                                <img src="uploads/candidates/<?= e($c['photo']) ?>" alt="">
                            <?php else: ?>
                                <div class="photo-placeholder"><i class="fa-solid fa-user"></i></div>
                            <?php endif; ?>
                        </div>
                        <div>
                            <strong><?= e($c['name']) ?></strong>
                            <span class="party-badge"><?= e($c['party']) ?></span>
                            <?php if (!empty($c['symbol'])): ?><div class="candidate-symbol"><i class="fa-solid fa-star-of-life"></i> <?= e($c['symbol']) ?></div><?php endif; ?>
                        </div>
                    </div>
                    <i class="fa-solid fa-circle-check vote-check-icon"></i>
                </label>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn-primary" onclick="showConfirm()"><i class="fa-solid fa-check-to-slot"></i> Cast Vote</button>
        </form>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Confirmation modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal">
        <i class="fa-solid fa-triangle-exclamation modal-icon"></i>
        <h3>Confirm Your Vote</h3>
        <p>You are voting for: <strong id="selectedName"></strong></p>
        <p class="modal-warning">This action cannot be undone.</p>
        <div class="modal-actions">
            <button class="btn-secondary" onclick="closeConfirm()">Cancel</button>
            <button class="btn-primary" onclick="document.getElementById('voteForm').submit()">Confirm Vote</button>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
<script>
document.querySelectorAll('.vote-option').forEach(function(label){
    label.addEventListener('click', function(){
        document.querySelectorAll('.vote-option').forEach(function(l){ l.classList.remove('selected'); });
        this.classList.add('selected');
    });
});
function showConfirm(){
    var sel = document.querySelector('input[name="candidate_id"]:checked');
    if (!sel){ alert('Please select a candidate.'); return; }
    document.getElementById('selectedName').textContent = sel.closest('.vote-option').querySelector('strong').textContent;
    document.getElementById('confirmModal').classList.add('show');
}
function closeConfirm(){ document.getElementById('confirmModal').classList.remove('show'); }
</script>
</body>
</html>

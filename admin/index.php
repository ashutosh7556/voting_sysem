<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Dashboard';
$active = 'dashboard';

// ── Stats ──
$total_voters   = (int)$conn->query("SELECT COUNT(*) FROM voters WHERE is_admin = 0")->fetchColumn();
$verified       = (int)$conn->query("SELECT COUNT(*) FROM voters WHERE is_admin = 0 AND is_verified = 1")->fetchColumn();
$total_cands    = (int)$conn->query("SELECT COUNT(*) FROM candidates")->fetchColumn();
$total_votes    = (int)$conn->query("SELECT COUNT(*) FROM votes")->fetchColumn();
$total_elect    = (int)$conn->query("SELECT COUNT(*) FROM elections")->fetchColumn();
$active_elect   = get_active_election($conn);

// ── Chart: votes per candidate in active election (or overall) ──
if ($active_elect) {
    $stmt = $conn->prepare("
        SELECT c.name, COUNT(v.id) AS votes
        FROM candidates c
        LEFT JOIN votes v ON v.candidate_id = c.id AND v.election_id = :eid
        WHERE c.election_id = :eid2
        GROUP BY c.id ORDER BY votes DESC");
    $stmt->execute([':eid' => $active_elect['id'], ':eid2' => $active_elect['id']]);
    $chart_rows = $stmt->fetchAll();
} else {
    $chart_rows = $conn->query("
        SELECT c.name, COUNT(v.id) AS votes
        FROM candidates c LEFT JOIN votes v ON v.candidate_id = c.id
        GROUP BY c.id ORDER BY votes DESC LIMIT 8")->fetchAll();
}
$chart_labels = array_column($chart_rows, 'name');
$chart_data   = array_map('intval', array_column($chart_rows, 'votes'));

// ── Recent registrations ──
$recent_voters = $conn->query("
    SELECT username, full_name, created_at, is_verified
    FROM voters WHERE is_admin = 0
    ORDER BY created_at DESC LIMIT 6")->fetchAll();

include 'layout.php';
?>

<!-- Stat cards -->
<div class="admin-stats">
    <div class="admin-stat">
        <div class="ico navy"><i class="fa-solid fa-users"></i></div>
        <div><div class="num"><?= $total_voters ?></div><div class="lbl">Registered Voters</div></div>
    </div>
    <div class="admin-stat">
        <div class="ico green"><i class="fa-solid fa-user-check"></i></div>
        <div><div class="num"><?= $verified ?></div><div class="lbl">Verified Voters</div></div>
    </div>
    <div class="admin-stat">
        <div class="ico saffron"><i class="fa-solid fa-user-tie"></i></div>
        <div><div class="num"><?= $total_cands ?></div><div class="lbl">Candidates</div></div>
    </div>
    <div class="admin-stat">
        <div class="ico navy"><i class="fa-solid fa-check-to-slot"></i></div>
        <div><div class="num"><?= $total_votes ?></div><div class="lbl">Votes Cast</div></div>
    </div>
    <div class="admin-stat">
        <div class="ico saffron"><i class="fa-solid fa-landmark"></i></div>
        <div><div class="num"><?= $total_elect ?></div><div class="lbl">Elections</div></div>
    </div>
</div>

<!-- Active election banner -->
<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-satellite-dish"></i> Active Election</h3>
        <a href="elections.php" class="btn btn-secondary btn-sm">Manage</a></div>
    <div class="panel-body">
        <?php if ($active_elect): ?>
            <strong style="font-size:var(--fs-md)"><?= e($active_elect['title']) ?></strong>
            <span class="badge badge-green" style="margin-left:8px"><i class="fa-solid fa-circle"></i> Live</span>
            <p class="text-muted" style="margin:6px 0 0">
                <?= date('d M Y, H:i', strtotime($active_elect['start_date'])) ?>
                &rarr; <?= date('d M Y, H:i', strtotime($active_elect['end_date'])) ?>
            </p>
        <?php else: ?>
            <p class="text-muted" style="margin:0">No active election right now. <a href="elections.php">Create or activate one.</a></p>
        <?php endif; ?>
    </div>
</div>

<div class="grid dashboard-grid">
    <!-- Chart -->
    <div class="panel">
        <div class="panel-head"><h3><i class="fa-solid fa-chart-column"></i> Votes by Candidate</h3></div>
        <div class="panel-body">
            <?php if ($chart_data && array_sum($chart_data) > 0): ?>
                <canvas id="votesChart" height="140"></canvas>
            <?php else: ?>
                <p class="empty"><i class="fa-solid fa-inbox"></i><br>No votes recorded yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Recent registrations -->
    <div class="panel">
        <div class="panel-head"><h3><i class="fa-solid fa-user-plus"></i> Recent Registrations</h3></div>
        <div class="panel-body flush">
            <?php if ($recent_voters): ?>
            <table class="table">
                <tbody>
                <?php foreach ($recent_voters as $rv): ?>
                    <tr>
                        <td><strong><?= e($rv['full_name'] ?: $rv['username']) ?></strong><br>
                            <small class="text-muted">@<?= e($rv['username']) ?></small></td>
                        <td style="text-align:right">
                            <?= $rv['is_verified']
                                ? '<span class="badge badge-green">Verified</span>'
                                : '<span class="badge badge-muted">Pending</span>' ?><br>
                            <small class="text-muted"><?= date('d M', strtotime($rv['created_at'])) ?></small>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
                <p class="empty">No registrations yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php if ($chart_data && array_sum($chart_data) > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('votesChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode($chart_labels) ?>,
        datasets: [{
            label: 'Votes',
            data: <?= json_encode($chart_data) ?>,
            backgroundColor: '#1b4172',
            borderRadius: 6
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
    }
});
</script>
<?php endif; ?>

<?php include 'layout_end.php'; ?>

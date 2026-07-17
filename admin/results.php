<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Results';
$active = 'results';

$elections = $conn->query("SELECT id,title,status FROM elections ORDER BY created_at DESC")->fetchAll();

// selected election: query param, else active, else latest
$sel = (int)($_GET['election'] ?? 0);
if (!$sel) {
    $a = get_active_election($conn);
    $sel = $a['id'] ?? ($elections[0]['id'] ?? 0);
}

$rows = [];
$total = 0;
$election = null;
if ($sel) {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE id=?");
    $stmt->execute([$sel]);
    $election = $stmt->fetch();

    $stmt = $conn->prepare("
        SELECT c.name, c.party,
               (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = c.id AND v.election_id = :eid) AS votes
        FROM candidates c
        WHERE c.election_id = :eid2
        ORDER BY votes DESC, c.name");
    $stmt->execute([':eid' => $sel, ':eid2' => $sel]);
    $rows = $stmt->fetchAll();
    $total = array_sum(array_map('intval', array_column($rows, 'votes')));
}
$max = $rows ? max(array_map('intval', array_column($rows, 'votes'))) : 0;

include 'layout.php';
?>

<div class="panel">
    <div class="panel-head">
        <h3><i class="fa-solid fa-chart-column"></i> Election Results</h3>
        <form method="GET" class="flex gap-2">
            <select class="form-control" name="election" onchange="this.form.submit()" style="width:auto">
                <?php foreach ($elections as $el): ?>
                    <option value="<?= $el['id'] ?>" <?= $el['id']==$sel?'selected':'' ?>><?= e($el['title']) ?> (<?= $el['status'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>
    <div class="panel-body">
        <?php if (!$election): ?>
            <p class="empty">No elections to show.</p>
        <?php else: ?>
            <p class="text-muted mt-0">Total votes cast: <strong><?= $total ?></strong>
               <?php if ($election['status'] !== 'completed'): ?>
                   · <span class="badge badge-saffron">Provisional — voting <?= e($election['status']) ?></span>
               <?php endif; ?>
            </p>

            <?php if ($rows && $total > 0): ?>
                <canvas id="resChart" height="110" style="margin:12px 0 24px"></canvas>

                <table class="table">
                    <thead><tr><th>#</th><th>Candidate</th><th>Party</th><th>Votes</th><th>Share</th><th style="width:180px"></th></tr></thead>
                    <tbody>
                    <?php foreach ($rows as $i => $r):
                        $v = (int)$r['votes'];
                        $pct = $total ? round($v / $total * 100, 1) : 0;
                        $win = ($v === $max && $max > 0);
                    ?>
                        <tr>
                            <td><?= $i+1 ?></td>
                            <td><strong><?= e($r['name']) ?></strong> <?= $win ? '<i class="fa-solid fa-trophy" style="color:var(--saffron)"></i>' : '' ?></td>
                            <td><?= e($r['party']) ?></td>
                            <td><?= $v ?></td>
                            <td><?= $pct ?>%</td>
                            <td><div class="bar-track"><div class="bar-fill <?= $win?'win':'' ?>" style="width:<?= $pct ?>%"></div></div></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="empty"><i class="fa-solid fa-inbox"></i><br>No votes recorded for this election.</p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php if ($rows && $total > 0): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('resChart'), {
    type: 'bar',
    data: {
        labels: <?= json_encode(array_column($rows,'name')) ?>,
        datasets: [{ label:'Votes', data: <?= json_encode(array_map('intval',array_column($rows,'votes'))) ?>,
            backgroundColor: <?= json_encode(array_map(fn($r)=> (int)$r['votes']===$max ? '#ff9933' : '#1b4172', $rows)) ?>,
            borderRadius:6 }]
    },
    options: { plugins:{legend:{display:false}}, scales:{ y:{beginAtZero:true, ticks:{precision:0}} } }
});
</script>
<?php endif; ?>

<?php include 'layout_end.php'; ?>

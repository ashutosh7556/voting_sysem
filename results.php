<?php
require 'includes/db.php';
require 'includes/functions.php';

$elections = $conn->query("SELECT id,title,status,show_results,end_date FROM elections ORDER BY created_at DESC")->fetchAll();

$sel = (int)($_GET['election'] ?? 0);
if (!$sel) {
    $a = get_active_election($conn);
    $sel = $a['id'] ?? ($elections[0]['id'] ?? 0);
}

$election = null; $results = []; $total = 0;
if ($sel) {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE id=?");
    $stmt->execute([$sel]);
    $election = $stmt->fetch();
}

// reveal rule: completed, OR explicitly public, OR voting window ended
$revealed = false;
if ($election) {
    $ended = strtotime($election['end_date']) < time();
    $revealed = ($election['status'] === 'completed') || (int)$election['show_results'] === 1 || $ended;
}

if ($election && $revealed) {
    $stmt = $conn->prepare("
        SELECT c.name, c.party,
               (SELECT COUNT(*) FROM votes v WHERE v.candidate_id=c.id AND v.election_id=:e) AS vote_count
        FROM candidates c WHERE c.election_id=:e2
        ORDER BY vote_count DESC, c.name");
    $stmt->execute([':e'=>$sel, ':e2'=>$sel]);
    $results = $stmt->fetchAll();
    $total = array_sum(array_map('intval', array_column($results,'vote_count')));
}
$labels = json_encode(array_column($results,'name'));
$votes  = json_encode(array_map('intval', array_column($results,'vote_count')));
$colors = json_encode(['#1b4172','#138808','#ff9933','#c0392b','#27548a','#0e6606','#e67e00','#96271a']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Results · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/pages.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>
<?php include 'header.php'; ?>

<div class="page-hero">
    <h1><i class="fa-solid fa-chart-column"></i> Election Results</h1>
    <?php if ($election): ?><p><?= e($election['title']) ?></p><?php endif; ?>
</div>

<div class="results-container">
    <?php if ($elections): ?>
    <form method="GET" style="margin-bottom:20px;text-align:center">
        <select name="election" class="form-control" onchange="this.form.submit()" style="width:auto;display:inline-block">
            <?php foreach ($elections as $el): ?>
                <option value="<?= $el['id'] ?>" <?= $el['id']==$sel?'selected':'' ?>><?= e($el['title']) ?> (<?= e($el['status']) ?>)</option>
            <?php endforeach; ?>
        </select>
    </form>
    <?php endif; ?>

    <?php if (!$election): ?>
        <p class="no-data"><i class="fa-solid fa-circle-info"></i> No elections available.</p>

    <?php elseif (!$revealed): ?>
        <div class="alert alert-info">
            <i class="fa-solid fa-lock"></i>
            Results for this election will be published after voting closes on
            <strong><?= date('d M Y, H:i', strtotime($election['end_date'])) ?></strong>.
        </div>

    <?php elseif (empty($results) || $total === 0): ?>
        <p class="no-data"><i class="fa-solid fa-inbox"></i> No votes recorded for this election.</p>

    <?php else: ?>
        <p class="text-center text-muted">Total votes cast: <strong><?= $total ?></strong>
            <?php if ($election['status'] !== 'completed'): ?> · <span class="badge badge-saffron">Provisional</span><?php endif; ?>
        </p>

        <div class="charts-row">
            <div class="chart-box"><h3>Vote Distribution</h3><canvas id="barChart"></canvas></div>
            <div class="chart-box"><h3>Vote Share</h3><canvas id="pieChart"></canvas></div>
        </div>

        <div class="results-table-wrap">
            <table class="results-table">
                <thead><tr><th>#</th><th>Candidate</th><th>Party</th><th>Votes</th><th>Share</th><th>Progress</th></tr></thead>
                <tbody>
                <?php foreach ($results as $i => $r):
                    $v = (int)$r['vote_count'];
                    $pct = $total > 0 ? round($v/$total*100, 1) : 0;
                    $lead = ($i === 0 && $v > 0);
                ?>
                    <tr <?= $lead ? 'class="leading"' : '' ?>>
                        <td><?= $i+1 ?> <?= $lead ? '<i class="fa-solid fa-trophy trophy"></i>' : '' ?></td>
                        <td><?= e($r['name']) ?></td>
                        <td><span class="party-badge"><?= e($r['party']) ?></span></td>
                        <td><strong><?= $v ?></strong></td>
                        <td><?= $pct ?>%</td>
                        <td><div class="progress-bar"><div class="progress-fill <?= $lead?'win':'' ?>" style="width:<?= $pct ?>%"></div></div></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <script>
        const labels = <?= $labels ?>, votes = <?= $votes ?>, colors = <?= $colors ?>;
        new Chart(document.getElementById('barChart'), {
            type:'bar',
            data:{ labels, datasets:[{ label:'Votes', data:votes, backgroundColor:colors, borderRadius:6 }] },
            options:{ responsive:true, plugins:{legend:{display:false}}, scales:{y:{beginAtZero:true,ticks:{precision:0}}} }
        });
        new Chart(document.getElementById('pieChart'), {
            type:'doughnut',
            data:{ labels, datasets:[{ data:votes, backgroundColor:colors }] },
            options:{ responsive:true }
        });
        </script>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
</body>
</html>

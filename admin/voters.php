<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Voters';
$active = 'voters';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    $id = (int)($_POST['id'] ?? 0);

    if ($action === 'verify') {
        $conn->prepare("UPDATE voters SET is_verified=1 WHERE id=? AND is_admin=0")->execute([$id]);
        flash('success', 'Voter verified.');
    } elseif ($action === 'unverify') {
        $conn->prepare("UPDATE voters SET is_verified=0 WHERE id=? AND is_admin=0")->execute([$id]);
        flash('info', 'Verification revoked.');
    } elseif ($action === 'reset_vote') {
        $conn->prepare("DELETE FROM votes WHERE voter_id=?")->execute([$id]);
        flash('warning', 'Voter\'s votes reset.');
    } elseif ($action === 'delete') {
        $conn->prepare("DELETE FROM voters WHERE id=? AND is_admin=0")->execute([$id]);
        flash('success', 'Voter deleted.');
    }
    header('Location: voters.php'); exit;
}

$q = trim($_GET['q'] ?? '');
$sql = "
    SELECT v.*, c.name AS cons_name,
           (SELECT COUNT(*) FROM votes vt WHERE vt.voter_id = v.id) AS votes_cast
    FROM voters v
    LEFT JOIN constituencies c ON c.id = v.constituency_id
    WHERE v.is_admin = 0";
$params = [];
if ($q !== '') {
    $sql .= " AND (v.username LIKE ? OR v.full_name LIKE ? OR v.voter_id LIKE ? OR v.email LIKE ?)";
    $like = "%$q%"; $params = [$like,$like,$like,$like];
}
$sql .= " ORDER BY v.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$voters = $stmt->fetchAll();

include 'layout.php';
?>

<div class="panel">
    <div class="panel-head">
        <h3><i class="fa-solid fa-users"></i> Registered Voters (<?= count($voters) ?>)</h3>
        <form method="GET" class="flex gap-2">
            <input class="form-control" type="text" name="q" placeholder="Search name / ID / email" value="<?= e($q) ?>" style="width:220px">
            <button class="btn-secondary btn-sm"><i class="fa-solid fa-magnifying-glass"></i></button>
            <?php if ($q): ?><a class="btn-ghost btn-sm" href="voters.php">Clear</a><?php endif; ?>
        </form>
    </div>
    <div class="panel-body flush">
        <?php if ($voters): ?>
        <table class="table">
            <thead><tr><th>Voter</th><th>Voter ID</th><th>Constituency</th><th>Verified</th><th>Voted</th><th>Registered</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($voters as $v): ?>
                <tr>
                    <td><strong><?= e($v['full_name'] ?: $v['username']) ?></strong><br>
                        <small class="text-muted">@<?= e($v['username']) ?> · <?= e($v['email']) ?></small></td>
                    <td><?= e($v['voter_id']) ?></td>
                    <td><?= $v['cons_name'] ? e($v['cons_name']) : '<span class="text-muted">—</span>' ?></td>
                    <td><?= $v['is_verified']
                        ? '<span class="badge badge-green">Verified</span>'
                        : '<span class="badge badge-muted">Pending</span>' ?></td>
                    <td><?= $v['votes_cast'] > 0
                        ? '<span class="badge badge-navy">'.$v['votes_cast'].'</span>'
                        : '<span class="text-muted">No</span>' ?></td>
                    <td><small><?= date('d M Y', strtotime($v['created_at'])) ?></small></td>
                    <td>
                        <div class="row-actions">
                            <?php if ($v['is_verified']): ?>
                                <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="unverify"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button class="icon-btn warn" title="Revoke verification"><i class="fa-solid fa-user-xmark"></i></button></form>
                            <?php else: ?>
                                <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="verify"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                                    <button class="icon-btn ok" title="Verify"><i class="fa-solid fa-user-check"></i></button></form>
                            <?php endif; ?>
                            <?php if ($v['votes_cast'] > 0): ?>
                            <form method="POST" onsubmit="return confirm('Reset this voter\'s votes?')"><?= csrf_field() ?><input type="hidden" name="action" value="reset_vote"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <button class="icon-btn warn" title="Reset vote"><i class="fa-solid fa-rotate-left"></i></button></form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Delete this voter permanently?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $v['id'] ?>">
                                <button class="icon-btn del" title="Delete"><i class="fa-solid fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="empty">No voters found.</p><?php endif; ?>
    </div>
</div>

<?php include 'layout_end.php'; ?>

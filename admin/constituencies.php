<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Constituencies';
$active = 'constituencies';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [trim($_POST['name']), trim($_POST['state']), trim($_POST['description'])];
        if ($id) {
            $conn->prepare("UPDATE constituencies SET name=?,state=?,description=? WHERE id=?")->execute([...$data, $id]);
            flash('success', 'Constituency updated.');
        } else {
            $conn->prepare("INSERT INTO constituencies (name,state,description) VALUES (?,?,?)")->execute($data);
            flash('success', 'Constituency added.');
        }
    } elseif ($action === 'delete') {
        $conn->prepare("DELETE FROM constituencies WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Constituency deleted.');
    }
    header('Location: constituencies.php'); exit;
}

$rows = $conn->query("
    SELECT c.*,
           (SELECT COUNT(*) FROM voters v WHERE v.constituency_id = c.id) AS voter_count,
           (SELECT COUNT(*) FROM candidates ca WHERE ca.constituency_id = c.id) AS cand_count
    FROM constituencies c ORDER BY c.state, c.name")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM constituencies WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

include 'layout.php';
?>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-<?= $edit ? 'pen' : 'plus' ?>"></i> <?= $edit ? 'Edit' : 'New' ?> Constituency</h3>
        <?php if ($edit): ?><a href="constituencies.php" class="btn btn-secondary btn-sm">Cancel edit</a><?php endif; ?></div>
    <div class="panel-body">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
            <div class="form-grid">
                <div class="form-group"><label>Name</label><input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
                <div class="form-group"><label>State</label><input type="text" name="state" required value="<?= e($edit['state'] ?? '') ?>"></div>
                <div class="form-group" style="grid-column:1/-1"><label>Description</label><textarea name="description" rows="2"><?= e($edit['description'] ?? '') ?></textarea></div>
            </div>
            <div class="form-actions"><button class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $edit ? 'Update' : 'Add' ?></button></div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-list"></i> All Constituencies</h3></div>
    <div class="panel-body flush">
        <?php if ($rows): ?>
        <table class="table">
            <thead><tr><th>Name</th><th>State</th><th>Voters</th><th>Candidates</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['name']) ?></strong></td>
                    <td><?= e($r['state']) ?></td>
                    <td><?= (int)$r['voter_count'] ?></td>
                    <td><?= (int)$r['cand_count'] ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn edit" href="constituencies.php?edit=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST" onsubmit="return confirm('Delete this constituency?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="icon-btn del"><i class="fa-solid fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="empty">No constituencies yet.</p><?php endif; ?>
    </div>
</div>

<?php include 'layout_end.php'; ?>

<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Announcements';
$active = 'announcements';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $data = [trim($_POST['title']), trim($_POST['body']), $_POST['type'], isset($_POST['is_active']) ? 1 : 0];
        if ($id) {
            $conn->prepare("UPDATE announcements SET title=?,body=?,type=?,is_active=? WHERE id=?")->execute([...$data, $id]);
            flash('success', 'Announcement updated.');
        } else {
            $conn->prepare("INSERT INTO announcements (title,body,type,is_active,created_by) VALUES (?,?,?,?,?)")->execute([...$data, $_SESSION['user_id']]);
            flash('success', 'Announcement posted.');
        }
    } elseif ($action === 'toggle') {
        $conn->prepare("UPDATE announcements SET is_active = 1 - is_active WHERE id=?")->execute([(int)$_POST['id']]);
        flash('info', 'Visibility toggled.');
    } elseif ($action === 'delete') {
        $conn->prepare("DELETE FROM announcements WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Announcement deleted.');
    }
    header('Location: announcements.php'); exit;
}

$rows = $conn->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM announcements WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}
$type_badge = ['info'=>'badge-navy','success'=>'badge-green','warning'=>'badge-saffron','danger'=>'badge-danger'];

include 'layout.php';
?>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-<?= $edit ? 'pen' : 'plus' ?>"></i> <?= $edit ? 'Edit' : 'New' ?> Announcement</h3>
        <?php if ($edit): ?><a href="announcements.php" class="btn btn-secondary btn-sm">Cancel edit</a><?php endif; ?></div>
    <div class="panel-body">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
            <div class="form-grid">
                <div class="form-group" style="grid-column:1/-1"><label>Title</label><input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>"></div>
                <div class="form-group">
                    <label>Type</label>
                    <select name="type">
                        <?php foreach (['info','success','warning','danger'] as $t): ?>
                            <option value="<?= $t ?>" <?= ($edit['type'] ?? 'info') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:26px">
                    <input type="checkbox" name="is_active" id="ia" style="width:auto" <?= (!isset($edit) || !empty($edit['is_active'])) ? 'checked' : '' ?>>
                    <label for="ia" style="margin:0">Active (visible on site)</label>
                </div>
                <div class="form-group" style="grid-column:1/-1"><label>Body</label><textarea name="body" rows="3" required><?= e($edit['body'] ?? '') ?></textarea></div>
            </div>
            <div class="form-actions"><button class="btn-primary"><i class="fa-solid fa-paper-plane"></i> <?= $edit ? 'Update' : 'Post' ?></button></div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-list"></i> All Announcements</h3></div>
    <div class="panel-body flush">
        <?php if ($rows): ?>
        <table class="table">
            <thead><tr><th>Title</th><th>Type</th><th>Status</th><th>Posted</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><strong><?= e($r['title']) ?></strong><br><small class="text-muted"><?= e(mb_strimwidth($r['body'],0,80,'…')) ?></small></td>
                    <td><span class="badge <?= $type_badge[$r['type']] ?>"><?= ucfirst($r['type']) ?></span></td>
                    <td><?= $r['is_active'] ? '<span class="badge badge-green">Active</span>' : '<span class="badge badge-muted">Hidden</span>' ?></td>
                    <td><small><?= date('d M Y', strtotime($r['created_at'])) ?></small></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn edit" href="announcements.php?edit=<?= $r['id'] ?>"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST"><?= csrf_field() ?><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="icon-btn warn" title="Toggle visibility"><i class="fa-solid fa-eye"></i></button></form>
                            <form method="POST" onsubmit="return confirm('Delete this announcement?')"><?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $r['id'] ?>">
                                <button class="icon-btn del"><i class="fa-solid fa-trash"></i></button></form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="empty">No announcements yet.</p><?php endif; ?>
    </div>
</div>

<?php include 'layout_end.php'; ?>

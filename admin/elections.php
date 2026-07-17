<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Elections';
$active = 'elections';

// ── Actions ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id    = (int)($_POST['id'] ?? 0);
        $data  = [
            trim($_POST['title']),
            trim($_POST['description']),
            $_POST['start_date'],
            $_POST['end_date'],
            $_POST['status'],
            isset($_POST['show_results']) ? 1 : 0,
            ($_POST['constituency_id'] ?: null),
        ];
        if ($id) {
            $stmt = $conn->prepare("UPDATE elections SET title=?,description=?,start_date=?,end_date=?,status=?,show_results=?,constituency_id=? WHERE id=?");
            $stmt->execute([...$data, $id]);
            flash('success', 'Election updated.');
        } else {
            $stmt = $conn->prepare("INSERT INTO elections (title,description,start_date,end_date,status,show_results,constituency_id,created_by) VALUES (?,?,?,?,?,?,?,?)");
            $stmt->execute([...$data, $_SESSION['user_id']]);
            flash('success', 'Election created.');
        }
    } elseif ($action === 'activate') {
        // only one active at a time
        $conn->prepare("UPDATE elections SET status='completed' WHERE status='active'")->execute();
        $conn->prepare("UPDATE elections SET status='active' WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Election activated.');
    } elseif ($action === 'complete') {
        $conn->prepare("UPDATE elections SET status='completed' WHERE id=?")->execute([(int)$_POST['id']]);
        flash('info', 'Election marked completed.');
    } elseif ($action === 'delete') {
        $conn->prepare("DELETE FROM elections WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Election deleted.');
    }
    header('Location: elections.php'); exit;
}

// ── Data ──
$constituencies = $conn->query("SELECT id,name,state FROM constituencies ORDER BY name")->fetchAll();
$elections = $conn->query("
    SELECT e.*, c.name AS cons_name,
           (SELECT COUNT(*) FROM votes v WHERE v.election_id = e.id) AS vote_count
    FROM elections e LEFT JOIN constituencies c ON c.id = e.constituency_id
    ORDER BY e.created_at DESC")->fetchAll();

// edit target
$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM elections WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

$status_badge = [
    'draft'     => 'badge-muted',
    'upcoming'  => 'badge-navy',
    'active'    => 'badge-green',
    'completed' => 'badge-saffron',
];

include 'layout.php';
?>

<!-- Create / edit form -->
<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-<?= $edit ? 'pen' : 'plus' ?>"></i> <?= $edit ? 'Edit' : 'New' ?> Election</h3>
        <?php if ($edit): ?><a href="elections.php" class="btn btn-secondary btn-sm">Cancel edit</a><?php endif; ?></div>
    <div class="panel-body">
        <form method="POST">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
            <div class="form-grid">
                <div class="form-group" style="grid-column:1/-1">
                    <label>Title</label>
                    <input type="text" name="title" required value="<?= e($edit['title'] ?? '') ?>">
                </div>
                <div class="form-group" style="grid-column:1/-1">
                    <label>Description</label>
                    <textarea name="description" rows="2"><?= e($edit['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label>Start date &amp; time</label>
                    <input type="datetime-local" name="start_date" required
                        value="<?= isset($edit['start_date']) ? date('Y-m-d\TH:i', strtotime($edit['start_date'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label>End date &amp; time</label>
                    <input type="datetime-local" name="end_date" required
                        value="<?= isset($edit['end_date']) ? date('Y-m-d\TH:i', strtotime($edit['end_date'])) : '' ?>">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <?php foreach (['draft','upcoming','active','completed'] as $s): ?>
                            <option value="<?= $s ?>" <?= ($edit['status'] ?? 'draft') === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Constituency (optional)</label>
                    <select name="constituency_id">
                        <option value="">All constituencies</option>
                        <?php foreach ($constituencies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($edit['constituency_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= e($c['name']) ?> (<?= e($c['state']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group" style="display:flex;align-items:center;gap:8px;margin-top:26px">
                    <input type="checkbox" name="show_results" id="sr" style="width:auto" <?= !empty($edit['show_results']) ? 'checked' : '' ?>>
                    <label for="sr" style="margin:0">Publish results publicly</label>
                </div>
            </div>
            <div class="form-actions">
                <button class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $edit ? 'Update' : 'Create' ?></button>
            </div>
        </form>
    </div>
</div>

<!-- List -->
<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-list"></i> All Elections</h3></div>
    <div class="panel-body flush">
        <?php if ($elections): ?>
        <table class="table">
            <thead><tr><th>Title</th><th>Window</th><th>Scope</th><th>Status</th><th>Votes</th><th>Results</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($elections as $el): ?>
                <tr>
                    <td><strong><?= e($el['title']) ?></strong></td>
                    <td><small><?= date('d M Y', strtotime($el['start_date'])) ?> &rarr; <?= date('d M Y', strtotime($el['end_date'])) ?></small></td>
                    <td><?= $el['cons_name'] ? e($el['cons_name']) : '<span class="text-muted">All</span>' ?></td>
                    <td><span class="badge <?= $status_badge[$el['status']] ?>"><?= ucfirst($el['status']) ?></span></td>
                    <td><?= (int)$el['vote_count'] ?></td>
                    <td><?= $el['show_results'] ? '<span class="badge badge-green">Public</span>' : '<span class="badge badge-muted">Hidden</span>' ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn edit" href="elections.php?edit=<?= $el['id'] ?>" title="Edit"><i class="fa-solid fa-pen"></i></a>
                            <?php if ($el['status'] !== 'active'): ?>
                            <form method="POST" onsubmit="return confirm('Activate this election? Any other active election will be completed.')">
                                <?= csrf_field() ?><input type="hidden" name="action" value="activate"><input type="hidden" name="id" value="<?= $el['id'] ?>">
                                <button class="icon-btn ok" title="Activate"><i class="fa-solid fa-play"></i></button>
                            </form>
                            <?php else: ?>
                            <form method="POST" onsubmit="return confirm('Mark this election completed?')">
                                <?= csrf_field() ?><input type="hidden" name="action" value="complete"><input type="hidden" name="id" value="<?= $el['id'] ?>">
                                <button class="icon-btn warn" title="Complete"><i class="fa-solid fa-stop"></i></button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" onsubmit="return confirm('Delete election and all its votes?')">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $el['id'] ?>">
                                <button class="icon-btn del" title="Delete"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="empty">No elections yet.</p><?php endif; ?>
    </div>
</div>

<?php include 'layout_end.php'; ?>

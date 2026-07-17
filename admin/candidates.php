<?php
require '../includes/db.php';
require '../includes/functions.php';
require_admin();

$page_title = 'Candidates';
$active = 'candidates';

$UPLOAD_DIR = __DIR__ . '/../uploads/candidates';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);

        // photo (optional)
        $photo = $_POST['existing_photo'] ?? null;
        if (!empty($_FILES['photo']['name'])) {
            $up = upload_image($_FILES['photo'], $UPLOAD_DIR);
            if ($up === false) { flash('error', 'Photo upload failed (max 2MB, JPG/PNG/WEBP/GIF).'); header('Location: candidates.php'); exit; }
            $photo = $up;
        }
        $logo = $_POST['existing_logo'] ?? null;
        if (!empty($_FILES['party_logo']['name'])) {
            $up = upload_image($_FILES['party_logo'], __DIR__ . '/../uploads/parties');
            if ($up !== false) $logo = $up;
        }

        $fields = [
            trim($_POST['name']),
            trim($_POST['party']),
            $logo,
            trim($_POST['symbol']),
            ($_POST['constituency_id'] ?: null),
            ($_POST['election_id'] ?: null),
            ($_POST['age'] !== '' ? (int)$_POST['age'] : null),
            trim($_POST['education']),
            trim($_POST['bio']),
            trim($_POST['manifesto']),
            $_POST['status'],
            $photo,
        ];
        if ($id) {
            $stmt = $conn->prepare("UPDATE candidates SET name=?,party=?,party_logo=?,symbol=?,constituency_id=?,election_id=?,age=?,education=?,bio=?,manifesto=?,status=?,photo=? WHERE id=?");
            $stmt->execute([...$fields, $id]);
            flash('success', 'Candidate updated.');
        } else {
            $stmt = $conn->prepare("INSERT INTO candidates (name,party,party_logo,symbol,constituency_id,election_id,age,education,bio,manifesto,status,photo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->execute($fields);
            flash('success', 'Candidate added.');
        }
    } elseif ($action === 'delete') {
        $conn->prepare("DELETE FROM candidates WHERE id=?")->execute([(int)$_POST['id']]);
        flash('success', 'Candidate deleted.');
    }
    header('Location: candidates.php'); exit;
}

$constituencies = $conn->query("SELECT id,name,state FROM constituencies ORDER BY name")->fetchAll();
$elections      = $conn->query("SELECT id,title FROM elections ORDER BY created_at DESC")->fetchAll();
$candidates = $conn->query("
    SELECT ca.*, co.name AS cons_name, el.title AS elect_title,
           (SELECT COUNT(*) FROM votes v WHERE v.candidate_id = ca.id) AS vote_count
    FROM candidates ca
    LEFT JOIN constituencies co ON co.id = ca.constituency_id
    LEFT JOIN elections el ON el.id = ca.election_id
    ORDER BY ca.id DESC")->fetchAll();

$edit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE id=?");
    $stmt->execute([(int)$_GET['edit']]);
    $edit = $stmt->fetch();
}

include 'layout.php';
?>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-<?= $edit ? 'pen' : 'plus' ?>"></i> <?= $edit ? 'Edit' : 'New' ?> Candidate</h3>
        <?php if ($edit): ?><a href="candidates.php" class="btn btn-secondary btn-sm">Cancel edit</a><?php endif; ?></div>
    <div class="panel-body">
        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="save">
            <input type="hidden" name="id" value="<?= $edit['id'] ?? '' ?>">
            <input type="hidden" name="existing_photo" value="<?= e($edit['photo'] ?? '') ?>">
            <input type="hidden" name="existing_logo" value="<?= e($edit['party_logo'] ?? '') ?>">
            <div class="form-grid">
                <div class="form-group"><label>Full name</label><input type="text" name="name" required value="<?= e($edit['name'] ?? '') ?>"></div>
                <div class="form-group"><label>Party</label><input type="text" name="party" required value="<?= e($edit['party'] ?? '') ?>"></div>
                <div class="form-group"><label>Symbol</label><input type="text" name="symbol" placeholder="e.g. Lotus, Hand" value="<?= e($edit['symbol'] ?? '') ?>"></div>
                <div class="form-group"><label>Age</label><input type="number" name="age" min="18" max="120" value="<?= e((string)($edit['age'] ?? '')) ?>"></div>
                <div class="form-group"><label>Education</label><input type="text" name="education" value="<?= e($edit['education'] ?? '') ?>"></div>
                <div class="form-group">
                    <label>Constituency</label>
                    <select name="constituency_id">
                        <option value="">— none —</option>
                        <?php foreach ($constituencies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($edit['constituency_id'] ?? '') == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Election</label>
                    <select name="election_id">
                        <option value="">— none —</option>
                        <?php foreach ($elections as $el): ?>
                            <option value="<?= $el['id'] ?>" <?= ($edit['election_id'] ?? '') == $el['id'] ? 'selected' : '' ?>><?= e($el['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="active"   <?= ($edit['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= ($edit['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="form-group"><label>Candidate photo</label><input type="file" name="photo" accept="image/*"></div>
                <div class="form-group"><label>Party logo</label><input type="file" name="party_logo" accept="image/*"></div>
                <div class="form-group" style="grid-column:1/-1"><label>Bio</label><textarea name="bio" rows="2"><?= e($edit['bio'] ?? '') ?></textarea></div>
                <div class="form-group" style="grid-column:1/-1"><label>Manifesto</label><textarea name="manifesto" rows="3"><?= e($edit['manifesto'] ?? '') ?></textarea></div>
            </div>
            <div class="form-actions"><button class="btn-primary"><i class="fa-solid fa-floppy-disk"></i> <?= $edit ? 'Update' : 'Add' ?></button></div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-head"><h3><i class="fa-solid fa-list"></i> All Candidates</h3></div>
    <div class="panel-body flush">
        <?php if ($candidates): ?>
        <table class="table">
            <thead><tr><th></th><th>Name</th><th>Party</th><th>Constituency</th><th>Election</th><th>Status</th><th>Votes</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($candidates as $ca): ?>
                <tr>
                    <td><?= $ca['photo']
                        ? '<img class="thumb" src="../uploads/candidates/'.e($ca['photo']).'" alt="">'
                        : '<span class="thumb-ph"><i class="fa-solid fa-user"></i></span>' ?></td>
                    <td><strong><?= e($ca['name']) ?></strong></td>
                    <td><?= e($ca['party']) ?></td>
                    <td><?= $ca['cons_name'] ? e($ca['cons_name']) : '<span class="text-muted">—</span>' ?></td>
                    <td><small><?= $ca['elect_title'] ? e($ca['elect_title']) : '—' ?></small></td>
                    <td><span class="badge <?= $ca['status']==='active'?'badge-green':'badge-muted' ?>"><?= ucfirst($ca['status']) ?></span></td>
                    <td><?= (int)$ca['vote_count'] ?></td>
                    <td>
                        <div class="row-actions">
                            <a class="icon-btn edit" href="candidates.php?edit=<?= $ca['id'] ?>"><i class="fa-solid fa-pen"></i></a>
                            <form method="POST" onsubmit="return confirm('Delete this candidate?')">
                                <?= csrf_field() ?><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="<?= $ca['id'] ?>">
                                <button class="icon-btn del"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?><p class="empty">No candidates yet.</p><?php endif; ?>
    </div>
</div>

<?php include 'layout_end.php'; ?>

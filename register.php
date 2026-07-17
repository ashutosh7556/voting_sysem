<?php
require 'includes/db.php';
require 'includes/functions.php';

if (isset($_SESSION['user_id'])) { header("Location: includes/dashboard.php"); exit; }

$error = $success = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $old = $_POST;

    $voter_id   = trim($_POST['voter_id'] ?? '');
    $full_name  = trim($_POST['full_name'] ?? '');
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $dob        = $_POST['dob'] ?? '';
    $gender     = $_POST['gender'] ?? '';
    $address    = trim($_POST['address'] ?? '');
    $state      = trim($_POST['state'] ?? '');
    $district   = trim($_POST['district'] ?? '');
    $cons_id    = $_POST['constituency_id'] ?: null;
    $password   = $_POST['password'] ?? '';
    $confirm    = $_POST['confirm_password'] ?? '';

    if (!$voter_id || !$full_name || !$username || !$email || !$password) {
        $error = "Please fill all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email address.";
    } elseif ($password !== $confirm) {
        $error = "Passwords do not match.";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM voters WHERE username=? OR voter_id=? OR email=?");
        $stmt->execute([$username, $voter_id, $email]);
        if ($stmt->fetch()) {
            $error = "Username, Voter ID, or email already registered.";
        } else {
            // optional photo
            $photo = null;
            if (!empty($_FILES['photo']['name'])) {
                $photo = upload_image($_FILES['photo'], __DIR__ . '/uploads/voters');
                if ($photo === false) { $error = "Photo upload failed (max 2MB image)."; }
            }
            if (!$error) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO voters
                    (voter_id, username, full_name, email, phone, dob, gender, address, state, district, constituency_id, photo, password)
                    VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$voter_id,$username,$full_name,$email,$phone,($dob?:null),($gender?:null),
                    $address,$state,$district,$cons_id,$photo,$hash]);
                $success = "Registration successful! Your account is pending verification. You can now log in.";
                $old = [];
            }
        }
    }
}

$constituencies = $conn->query("SELECT id,name,state FROM constituencies ORDER BY state,name")->fetchAll();
function old($k){ global $old; return e($old[$k] ?? ''); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/design.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card wide">
      <div class="auth-inner">
        <div class="auth-logo"><i class="fa-solid fa-check-to-slot"></i></div>
        <h2>Voter Registration</h2>
        <p class="auth-subtitle">Create your account to participate in elections</p>

        <?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div><?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fa-solid fa-circle-check"></i> <?= e($success) ?></div>
            <p class="auth-footer"><a href="login.php">Proceed to Login &rarr;</a></p>
        <?php else: ?>

        <form method="POST" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <div class="form-row">
                <div class="form-group"><label>Voter ID *</label><input type="text" name="voter_id" required value="<?= old('voter_id') ?>"></div>
                <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required value="<?= old('full_name') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Username *</label><input type="text" name="username" required value="<?= old('username') ?>"></div>
                <div class="form-group"><label>Email *</label><input type="email" name="email" required value="<?= old('email') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>Phone</label><input type="tel" name="phone" value="<?= old('phone') ?>"></div>
                <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" value="<?= old('dob') ?>"></div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender">
                        <option value="">— select —</option>
                        <?php foreach (['male','female','other'] as $g): ?>
                            <option value="<?= $g ?>" <?= (($old['gender']??'')===$g)?'selected':'' ?>><?= ucfirst($g) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Constituency</label>
                    <select name="constituency_id">
                        <option value="">— select —</option>
                        <?php foreach ($constituencies as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= (($old['constituency_id']??'')==$c['id'])?'selected':'' ?>>
                                <?= e($c['name']) ?> (<?= e($c['state']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group"><label>State</label><input type="text" name="state" value="<?= old('state') ?>"></div>
                <div class="form-group"><label>District</label><input type="text" name="district" value="<?= old('district') ?>"></div>
            </div>
            <div class="form-group"><label>Address</label><textarea name="address" rows="2"><?= old('address') ?></textarea></div>
            <div class="form-group"><label>Photo (optional)</label><input type="file" name="photo" accept="image/*"></div>
            <div class="form-row">
                <div class="form-group"><label>Password *</label><input type="password" name="password" placeholder="Min 6 characters" required></div>
                <div class="form-group"><label>Confirm Password *</label><input type="password" name="confirm_password" required></div>
            </div>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-user-plus"></i> Register</button>
        </form>
        <p class="auth-footer">Already registered? <a href="login.php">Login here</a></p>

        <?php endif; ?>
      </div>
    </div>
</div>
</body>
</html>

<?php
require 'includes/db.php';
require 'includes/functions.php';

if (isset($_SESSION['user_id'])) { header("Location: includes/dashboard.php"); exit; }

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    csrf_verify();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username !== '' && $password !== '') {
        $stmt = $conn->prepare("SELECT * FROM voters WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            session_regenerate_id(true);              // prevent session fixation
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = !empty($user['is_admin']);
            header("Location: " . (!empty($user['is_admin']) ? 'admin/index.php' : 'includes/dashboard.php'));
            exit;
        }
        $error = "Invalid username or password.";
    } else {
        $error = "Please fill in both fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login · Voting System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer"/>
    <link rel="stylesheet" href="css/design.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body class="auth-body">
<div class="auth-wrapper">
    <div class="auth-card">
      <div class="auth-inner">
        <div class="auth-logo"><i class="fa-solid fa-check-to-slot"></i></div>
        <h2>Voter Login</h2>
        <p class="auth-subtitle">Sign in to cast your vote</p>

        <?php if ($error): ?><div class="alert alert-error"><i class="fa-solid fa-circle-exclamation"></i> <?= e($error) ?></div><?php endif; ?>

        <form method="POST" action="">
            <?= csrf_field() ?>
            <div class="form-group">
                <label for="username"><i class="fa-solid fa-user"></i> Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="form-group">
                <label for="password"><i class="fa-solid fa-lock"></i> Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit" class="btn-primary"><i class="fa-solid fa-right-to-bracket"></i> Login</button>
        </form>
        <p class="auth-footer">Don't have an account? <a href="register.php">Register here</a></p>
      </div>
    </div>
</div>
</body>
</html>

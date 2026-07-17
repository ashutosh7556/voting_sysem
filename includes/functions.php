<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// ── CSRF ─────────────────────────────────────────────────────
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}
function csrf_verify(): void {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        http_response_code(403);
        die('Invalid CSRF token. <a href="javascript:history.back()">Go back</a>');
    }
}

// ── AUTH GUARDS ──────────────────────────────────────────────
function require_login(string $redirect = '../login.php'): void {
    if (empty($_SESSION['user_id'])) {
        header("Location: $redirect"); exit;
    }
}
function require_admin(string $redirect = '../login.php'): void {
    if (empty($_SESSION['user_id']) || empty($_SESSION['is_admin'])) {
        header("Location: $redirect"); exit;
    }
}

// ── XSS ──────────────────────────────────────────────────────
function e(string $s): string {
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

// ── FILE UPLOAD ──────────────────────────────────────────────
function upload_image(array $file, string $dir) {
    $allowed = ['image/jpeg','image/png','image/webp','image/gif'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    if ($file['error'] !== UPLOAD_ERR_OK)   return false;
    if ($file['size'] > $maxSize)           return false;
    if (!in_array($file['type'], $allowed)) return false;
    $ext  = pathinfo($file['name'], PATHINFO_EXTENSION);
    $name = bin2hex(random_bytes(8)) . '.' . strtolower($ext);
    $dest = rtrim($dir, '/') . '/' . $name;
    return move_uploaded_file($file['tmp_name'], $dest) ? $name : false;
}

// ── ELECTION HELPERS ─────────────────────────────────────────
function get_active_election(PDO $conn) {
    $stmt = $conn->prepare("
        SELECT * FROM elections
        WHERE status = 'active'
          AND start_date <= NOW()
          AND end_date   >= NOW()
        ORDER BY start_date DESC LIMIT 1
    ");
    $stmt->execute();
    return $stmt->fetch();
}

function has_voted(PDO $conn, int $voter_id, int $election_id): bool {
    $stmt = $conn->prepare("SELECT id FROM votes WHERE voter_id = ? AND election_id = ?");
    $stmt->execute([$voter_id, $election_id]);
    return (bool)$stmt->fetch();
}

function voter_constituency(PDO $conn, int $voter_id) {
    $stmt = $conn->prepare("
        SELECT c.* FROM constituencies c
        JOIN voters v ON v.constituency_id = c.id
        WHERE v.id = ?
    ");
    $stmt->execute([$voter_id]);
    return $stmt->fetch();
}

// ── FLASH MESSAGES ───────────────────────────────────────────
function flash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}
function get_flash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $f;
    }
    return null;
}
function render_flash(): void {
    $f = get_flash();
    if (!$f) return;
    $icons = ['success'=>'fa-circle-check','error'=>'fa-circle-exclamation','info'=>'fa-circle-info','warning'=>'fa-triangle-exclamation'];
    $icon  = $icons[$f['type']] ?? 'fa-circle-info';
    echo '<div class="flash flash-'.$f['type'].'"><i class="fa-solid '.$icon.'"></i> '.e($f['msg']).'</div>';
}

// ── PAGINATION ───────────────────────────────────────────────
function paginate(int $total, int $perPage, int $current): array {
    $pages = (int)ceil($total / $perPage);
    return ['total'=>$total,'pages'=>$pages,'current'=>$current,'offset'=>($current-1)*$perPage,'perPage'=>$perPage];
}

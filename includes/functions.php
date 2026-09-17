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
/** Absolute filesystem directory for an upload category. */
function upload_dir(string $subdir): string {
    return UPLOAD_PATH . '/' . trim($subdir, '/\\');
}

/** Public URL for a stored upload, or null when there is no file. */
function image_url(string $subdir, ?string $file): ?string {
    if (empty($file)) return null;
    return UPLOAD_URL . '/' . trim($subdir, '/') . '/' . rawurlencode($file);
}

/**
 * Store an uploaded image under $subdir (e.g. 'candidates').
 * Returns the stored filename, or false on rejection.
 */
function upload_image(array $file, string $subdir) {
    $maxSize = 2 * 1024 * 1024; // 2MB
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) return false;
    if (($file['size'] ?? 0) > $maxSize)                          return false;
    if (!is_uploaded_file($file['tmp_name']))                     return false;

    // Trust the file's own bytes, not the client-supplied MIME type.
    $info = @getimagesize($file['tmp_name']);
    if ($info === false) return false;
    $extByType = [
        IMAGETYPE_JPEG => 'jpg',
        IMAGETYPE_PNG  => 'png',
        IMAGETYPE_GIF  => 'gif',
        IMAGETYPE_WEBP => 'webp',
    ];
    if (!isset($extByType[$info[2]])) return false;

    $dir = upload_dir($subdir);
    if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
        error_log("upload: cannot create directory $dir");
        return false;
    }

    $name = bin2hex(random_bytes(8)) . '.' . $extByType[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        error_log("upload: move_uploaded_file failed into $dir");
        return false;
    }
    return $name;
}

// ── SQL PORTABILITY ──────────────────────────────────────────
/**
 * Case-insensitive pattern match operator for the active driver.
 * MySQL LIKE is already case-insensitive; Postgres needs ILIKE.
 */
function like_op(): string {
    return DB_DRIVER === 'pgsql' ? 'ILIKE' : 'LIKE';
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

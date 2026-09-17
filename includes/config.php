<?php
// ── Helper: read env with fallback ────────────────────────────
function env_or(string $key, ?string $default = null): ?string {
    $v = getenv($key);
    if ($v === false || $v === '') return $default;
    return $v;
}

// ── Environment ───────────────────────────────────────────────
// APP_ENV wins. Fall back to host sniffing so local dev needs no setup.
$host = $_SERVER['HTTP_HOST'] ?? '';
$looksLocal = ($host === 'localhost'
    || strpos($host, 'localhost:') === 0
    || strpos($host, '127.') === 0
    || strpos($host, '192.168.') === 0);

define('APP_ENV', env_or('APP_ENV', $looksLocal ? 'local' : 'production'));
$isLocal = (APP_ENV === 'local');

// Error display must be decided before anything can fail.
if ($isLocal) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);   // log everything, display nothing
}

// ── Database ──────────────────────────────────────────────────
define('DB_DRIVER', env_or('DB_DRIVER', $isLocal ? 'mysql' : 'pgsql'));

if ($isLocal) {
    // Local defaults target WAMP/MySQL. Override via env for local Postgres.
    define('DB_HOST', env_or('DB_HOST', 'localhost'));
    define('DB_PORT', env_or('DB_PORT', DB_DRIVER === 'pgsql' ? '5432' : '3306'));
    define('DB_NAME', env_or('DB_NAME', 'voting-system'));
    define('DB_USER', env_or('DB_USER', 'root'));
    define('DB_PASS', env_or('DB_PASS', 'root'));
    define('DB_SSLMODE', env_or('DB_SSLMODE', ''));   // no TLS for local socket
} else {
    // Production: credentials come from the environment only. No fallbacks,
    // so nothing sensitive lives in the repo.
    $required = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'];
    $missing  = [];
    foreach ($required as $k) {
        if (env_or($k) === null) $missing[] = $k;
    }
    if ($missing) {
        error_log('config: missing required env vars: ' . implode(', ', $missing));
        http_response_code(500);
        die('Service temporarily unavailable.');
    }
    define('DB_HOST', env_or('DB_HOST'));
    define('DB_PORT', env_or('DB_PORT', DB_DRIVER === 'pgsql' ? '5432' : '3306'));
    define('DB_NAME', env_or('DB_NAME'));
    define('DB_USER', env_or('DB_USER'));
    define('DB_PASS', env_or('DB_PASS'));
    define('DB_SSLMODE', env_or('DB_SSLMODE', 'require'));
}

// ── Timezone ──────────────────────────────────────────────────
// Pinned explicitly: without this, Render runs UTC and WAMP runs system time,
// so NOW() in the election-window check disagrees with date() in the views.
// Applied to PHP here and to the DB session in db.php.
define('APP_TZ', env_or('APP_TZ', 'Asia/Kolkata'));
date_default_timezone_set(APP_TZ);

// ── App ───────────────────────────────────────────────────────
define('APP_NAME', 'Online Voting System');

// rtrim guards against the double slash that APP_URL . '/uploads/' produced.
define('APP_URL', rtrim(
    env_or('APP_URL', $isLocal ? 'http://localhost/voting_sysem' : 'https://voting-sysem.onrender.com'),
    '/'
));

// UPLOAD_PATH: point at a Render persistent disk mount in production.
// UPLOAD_URL:  point at object storage (e.g. Supabase Storage) to serve
//              uploads from outside the container.
define('UPLOAD_PATH', rtrim(env_or('UPLOAD_PATH', __DIR__ . '/../uploads'), '/\\'));
define('UPLOAD_URL',  rtrim(env_or('UPLOAD_URL', APP_URL . '/uploads'), '/'));

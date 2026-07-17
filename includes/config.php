<?php
// ── Environment auto-detect ───────────────────────────────────
$host = $_SERVER['HTTP_HOST'] ?? '';
$isLocal = ($host === 'localhost'
    || strpos($host, 'localhost:') === 0
    || strpos($host, '127.') === 0
    || strpos($host, '192.168.') === 0);

// ── Database ──────────────────────────────────────────────────
if ($isLocal) {
    define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
    define('DB_NAME', getenv('DB_NAME') ?: 'voting-system');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASS', getenv('DB_PASS') ?: 'root');
    define('APP_URL', getenv('APP_URL') ?: 'http://localhost/voting_sysem');
    define('APP_ENV', getenv('APP_ENV') ?: 'local');
} else {
    define('DB_HOST', getenv('DB_HOST') ?: 'sql107.infinityfree.com');
    define('DB_NAME', getenv('DB_NAME') ?: 'if0_42432200_voting_system');
    define('DB_USER', getenv('DB_USER') ?: 'if0_42432200');
    define('DB_PASS', getenv('DB_PASS') ?: 'Ashu1234X9');
    define('APP_URL', getenv('APP_URL') ?: 'https://securevoting.infinityfreeapp.com');
    define('APP_ENV', getenv('APP_ENV') ?: 'production');
}

// ── App ───────────────────────────────────────────────────────
define('APP_NAME',    'Online Voting System');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL',  APP_URL . '/uploads/');

// Hide errors in production
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

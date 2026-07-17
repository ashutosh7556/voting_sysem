<?php
// ── Database ──────────────────────────────────────────────────
// On InfinityFree: set these as environment variables in your
// hosting control panel, OR replace the fallback values below
// with your InfinityFree DB credentials before deploying.
define('DB_HOST', getenv('DB_HOST') ?: 'sql107.infinityfree.com');
define('DB_NAME', getenv('DB_NAME') ?: 'if0_42432200_XXX');
define('DB_USER', getenv('DB_USER') ?: 'if0_42432200');
define('DB_PASS', getenv('DB_PASS') ?: 'Ashu1234X9');

// ── App ───────────────────────────────────────────────────────
define('APP_NAME',    'Online Voting System');
define('APP_URL',     getenv('APP_URL') ?: 'https://securevoting.infinityfreeapp.com');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('UPLOAD_URL',  APP_URL . '/uploads/');

// ── Environment ───────────────────────────────────────────────
define('APP_ENV', getenv('APP_ENV') ?: 'production'); // 'local' or 'production'

// Hide errors in production
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    error_reporting(0);
} else {
    ini_set('display_errors', 1);
    error_reporting(E_ALL);
}

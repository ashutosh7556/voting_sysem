<?php
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = __DIR__ . $path;

if ($path === '/' || $path === '') {
    require __DIR__ . '/index.php';
} elseif (is_file($file)) {
    return false; // serve static file as-is
} else {
    require __DIR__ . '/index.php';
}

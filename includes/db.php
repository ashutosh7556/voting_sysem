<?php
require_once __DIR__ . '/config.php';

try {
    $conn = new PDO(
        "pgsql:host=" . DB_HOST .
        ";port=" . DB_PORT .
        ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS
    );

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die(json_encode([
        'error' => 'Connection failed: ' . $e->getMessage()
    ]));
}
?>

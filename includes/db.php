<?php
require_once __DIR__ . '/config.php';

try {
    if (!in_array(DB_DRIVER, PDO::getAvailableDrivers(), true)) {
        throw new PDOException(
            'PDO driver "' . DB_DRIVER . '" not enabled. Available: '
            . implode(', ', PDO::getAvailableDrivers())
        );
    }

    $dsn = DB_DRIVER . ':host=' . DB_HOST
         . ';port=' . DB_PORT
         . ';dbname=' . DB_NAME;

    if (DB_DRIVER === 'mysql') {
        $dsn .= ';charset=utf8mb4';
    } elseif (DB_DRIVER === 'pgsql' && DB_SSLMODE !== '') {
        $dsn .= ';sslmode=' . DB_SSLMODE;
    }

    $conn = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT            => 10,
    ]);

    // Align the DB session clock with PHP's, so NOW() in the election-window
    // check and date() in the views agree on both engines.
    if (DB_DRIVER === 'pgsql') {
        $stmt = $conn->prepare('SET TIME ZONE ' . $conn->quote(APP_TZ));
        $stmt->execute();
    } else {
        // MySQL may lack the named-timezone tables, so send a numeric offset.
        $offset = (new DateTime('now', new DateTimeZone(APP_TZ)))->format('P');
        $conn->prepare('SET time_zone = ?')->execute([$offset]);
    }

} catch (PDOException $e) {
    error_log('db: connection failed: ' . $e->getMessage());

    if (APP_ENV === 'local') {
        die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
    }
    http_response_code(503);
    die('Service temporarily unavailable. Please try again shortly.');
}

<?php

if (!isset($pdo)) {
    $envPath = __DIR__ . '/../.env';
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
                continue;
            }
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if (!getenv($key)) {
                putenv("{$key}={$val}");
                $_ENV[$key] = $val;
            }
        }
    }

    $db_host = getenv("DB_HOST") ?: "localhost";
    $db_port = getenv("DB_PORT") ?: "3306";
    $db_name = getenv("DB_NAME") ?: "classia_db";
    $db_user = getenv("DB_USER") ?: "classia_user";
    $db_pass = getenv("DB_PASSWORD");
    $db_pass = $db_pass === false ? "" : $db_pass;

    $dsn = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (PDOException $e) {
        error_log("Error de conexión a BD: " . $e->getMessage());
        throw new RuntimeException("No se pudo conectar a la base de datos.");
    }
}

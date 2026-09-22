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

    $db_driver = strtolower(getenv("DB_DRIVER") ?: (getenv("DB_CONNECTION") ?: ""));
    $db_host   = getenv("DB_HOST") ?: "localhost";
    $db_port   = getenv("DB_PORT") ?: "";
    $db_name   = getenv("DB_NAME") ?: "classia_db";
    $db_user   = getenv("DB_USER") ?: "classia_user";
    $db_pass   = getenv("DB_PASSWORD");
    $db_pass   = $db_pass === false ? "" : $db_pass;
    $db_ssl    = getenv("DB_SSLMODE") ?: "require";

    // Auto-detectar driver si no se especifica explícitamente
    if (empty($db_driver)) {
        if (str_contains($db_host, 'supabase.co') || str_contains($db_host, 'supabase.com') || $db_port === '5432' || $db_port === '6543') {
            $db_driver = 'pgsql';
        } else {
            $db_driver = 'mysql';
        }
    }

    if ($db_driver === 'pgsql' || $db_driver === 'postgres' || $db_driver === 'postgresql') {
        $port = $db_port ?: "5432";
        $dsn  = "pgsql:host={$db_host};port={$port};dbname={$db_name};sslmode={$db_ssl}";
    } else {
        $port = $db_port ?: "3306";
        $dsn  = "mysql:host={$db_host};port={$port};dbname={$db_name};charset=utf8mb4";
    }

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_PERSISTENT         => true,
        ]);
    } catch (PDOException $e) {
        $motor = ($db_driver === 'pgsql' || $db_driver === 'postgres' || $db_driver === 'postgresql') ? 'PostgreSQL/Supabase' : 'MySQL';
        error_log("Error de conexión a BD ({$motor}): " . $e->getMessage());
        throw new RuntimeException("No se pudo conectar a la base de datos ({$motor}). Verifique las credenciales en .env.");
    }
}


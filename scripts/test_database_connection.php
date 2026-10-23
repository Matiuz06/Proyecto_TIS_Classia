<?php

try {
    require_once __DIR__ . "/../config/database.php";

    if (!$pdo instanceof PDO) {
        throw new RuntimeException("La conexión PDO no está disponible.");
    }

    $driverName = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    $serverVer  = $pdo->getAttribute(PDO::ATTR_SERVER_VERSION);

    // Consulta de prueba
    $stmt = $pdo->query("SELECT 1 AS conexion_ok");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Listar tablas según el motor
    $tablas = [];
    if ($driverName === 'pgsql') {
        $q = $pdo->query("SELECT tablename FROM pg_tables WHERE schemaname = 'public' ORDER BY tablename");
        $tablas = $q->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $q = $pdo->query("SHOW TABLES");
        $tablas = $q->fetchAll(PDO::FETCH_COLUMN);
    }

    $totalTablas = count($tablas);
    $motorLabel = ($driverName === 'pgsql') ? 'PostgreSQL (Supabase)' : 'MySQL / MariaDB (Local)';

    echo "========================================================\n";
    echo " DIAGNÓSTICO DE CONEXIÓN A BASE DE DATOS — CLASSIA\n";
    echo "========================================================\n";
    echo " Motor detectado:      {$motorLabel}\n";
    echo " Driver PDO:           {$driverName}\n";
    echo " Versión del servidor: {$serverVer}\n";
    echo " Estado de consulta:   OK (SELECT 1)\n";
    echo " Total de tablas:      {$totalTablas}\n";
    if ($totalTablas > 0) {
        echo " Tablas encontradas:   " . implode(", ", array_slice($tablas, 0, 8)) . ($totalTablas > 8 ? " ... y " . ($totalTablas - 8) . " más." : "") . "\n";
    }
    echo "========================================================\n";

    // Chequeo de Supabase Storage
    require_once __DIR__ . "/../php/utils/supabase_storage.php";
    $storageActivo = supabase_esta_activo();
    $storageCfg = supabase_config();
    echo " Supabase Storage:     " . ($storageActivo ? "ACTIVO (Bucket: {$storageCfg['bucket_name']})" : "INACTIVO / DESHABILITADO") . "\n";
    echo "========================================================\n";

} catch (Throwable $e) {
    echo "========================================================\n";
    echo " ERROR EN CONEXIÓN A BASE DE DATOS\n";
    echo "========================================================\n";
    echo " Mensaje: " . $e->getMessage() . "\n";
    echo "========================================================\n";
    exit(1);
}


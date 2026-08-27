<?php

try {
    require_once __DIR__ . "/../config/database.php";

    if (!$pdo instanceof PDO) {
        throw new RuntimeException("La conexion PDO no esta disponible.");
    }

    $pdo->query("SELECT 1");

    echo "Conexión a la base de datos correcta.";
} catch (Throwable $e) {
    echo "No se pudo comprobar la conexión a la base de datos.";
}

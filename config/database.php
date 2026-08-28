<?php

$host = getenv("DB_HOST") ?: "localhost";
$port = getenv("DB_PORT") ?: "3306";
$database = getenv("DB_NAME") ?: "classia_db";
$user = getenv("DB_USER") ?: "root";
$password = getenv("DB_PASSWORD");
$password = $password === false ? "" : $password;

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    throw new RuntimeException("No se pudo conectar a la base de datos.");
}

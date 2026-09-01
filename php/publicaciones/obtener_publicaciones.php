<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

$id_usuario_autenticado = $_SESSION['id_usuario'] ?? 2;

$stmt_categorias = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria ASC");
$categorias = $stmt_categorias->fetchAll();

$sql_publicaciones = "SELECT p.*, c.nombre_categoria 
                      FROM publicaciones p 
                      JOIN categorias c ON p.id_categoria = c.id_categoria 
                      WHERE p.id_usuario = :id_usuario 
                      ORDER BY p.fecha_creacion DESC";
$stmt_pubs = $pdo->prepare($sql_publicaciones);
$stmt_pubs->execute(['id_usuario' => $id_usuario_autenticado]);
$publicaciones_proveedor = $stmt_pubs->fetchAll();

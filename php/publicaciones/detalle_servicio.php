<?php

require_once __DIR__ . '/../../config/database.php';

$id_servicio = isset($_GET['id']) ? (int)$_GET['id'] : null;
$servicio = null;

if ($id_servicio) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido 
            FROM publicaciones p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.id_publicacion = :id AND p.tipo = 'Servicio'
        ");
        $stmt->execute(['id' => $id_servicio]);
        $servicio = $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error al consultar detalle del servicio: " . $e->getMessage());
        $servicio = null;
    }
}

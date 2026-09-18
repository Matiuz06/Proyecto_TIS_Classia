<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/i18n.php';
require_once __DIR__ . '/../auth/sesion.php';

iniciar_sesion();

// Asegurar token CSRF en sesión
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$publicaciones_recientes = [];
$categorias_home = [];

try {
    $stmt_recientes = $pdo->prepare(
        "SELECT p.id_publicacion, p.titulo, p.descripcion, p.precio, p.tipo, p.modalidad, p.imagen,
                c.nombre_categoria, u.nombre AS docente_nombre, u.apellido AS docente_apellido
         FROM publicaciones p
         JOIN categorias c ON p.id_categoria = c.id_categoria
         JOIN usuarios u ON p.id_usuario = u.id_usuario
         WHERE p.estado = 'Activo'
         ORDER BY p.fecha_creacion DESC
         LIMIT 4"
    );
    $stmt_recientes->execute();
    $publicaciones_recientes = $stmt_recientes->fetchAll();

    $stmt_categorias = $pdo->prepare(
        "SELECT nombre_categoria
         FROM categorias
         ORDER BY nombre_categoria ASC
         LIMIT 12"
    );
    $stmt_categorias->execute();
    $categorias_home = $stmt_categorias->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar publicaciones recientes en home: " . $e->getMessage());
    $publicaciones_recientes = [];
    $categorias_home = [];
}

<?php

require_once __DIR__ . '/../../config/database.php';

$id_proveedor = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$proveedor = null;
$publicaciones_docente = [];
$resenas = [];
$promedio_calificacion = 0.0;
$total_resenas = 0;

if ($id_proveedor > 0) {
    try {
        // Datos del proveedor
        $stmt_prov = $pdo->prepare("
            SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.telefono, u.foto_perfil, u.fecha_registro, r.nombre_rol
            FROM usuarios u
            JOIN roles r ON u.id_rol = r.id_rol
            WHERE u.id_usuario = :id
            LIMIT 1
        ");
        $stmt_prov->execute(['id' => $id_proveedor]);
        $proveedor = $stmt_prov->fetch();

        if ($proveedor) {
            // Trabajos / publicaciones activas
            $stmt_pubs = $pdo->prepare("
                SELECT p.*, c.nombre_categoria
                FROM publicaciones p
                JOIN categorias c ON p.id_categoria = c.id_categoria
                WHERE p.id_usuario = :id AND p.estado = 'Activo'
                ORDER BY p.fecha_creacion DESC
            ");
            $stmt_pubs->execute(['id' => $id_proveedor]);
            $publicaciones_docente = $stmt_pubs->fetchAll();

            // Reseñas de sus publicaciones
            $stmt_res = $pdo->prepare("
                SELECT v.id_valoracion, v.puntuacion, v.comentario, v.fecha_valoracion,
                       u.nombre AS autor_resena_nombre, u.apellido AS autor_resena_apellido,
                       p.titulo AS publicacion_titulo, p.tipo AS publicacion_tipo
                FROM valoraciones v
                JOIN publicaciones p ON v.id_publicacion = p.id_publicacion
                JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE p.id_usuario = :id
                ORDER BY v.fecha_valoracion DESC
            ");
            $stmt_res->execute(['id' => $id_proveedor]);
            $resenas = $stmt_res->fetchAll();
            $total_resenas = count($resenas);

            if ($total_resenas > 0) {
                $suma = array_sum(array_column($resenas, 'puntuacion'));
                $promedio_calificacion = round($suma / $total_resenas, 1);
            }
        }
    } catch (PDOException $e) {
        error_log("Error al consultar perfil del proveedor: " . $e->getMessage());
        $proveedor = null;
    }
}

<?php

require_once __DIR__ . '/../../config/database.php';

$id_servicio = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$servicio = null;
$resenas = [];
$promedio_calificacion = 0.0;
$total_resenas = 0;
$servicios_relacionados = [];

if ($id_servicio > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido,
                   u.id_usuario AS autor_id, u.foto_perfil AS autor_foto
            FROM publicaciones p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.id_publicacion = :id AND p.tipo = 'Servicio' AND p.estado = 'Activo'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id_servicio]);
        $servicio = $stmt->fetch();

        if ($servicio) {
            // Reseñas reales
            $stmt_res = $pdo->prepare("
                SELECT v.*, u.nombre, u.apellido, u.foto_perfil
                FROM valoraciones v
                JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE v.id_publicacion = :id
                ORDER BY v.fecha_valoracion DESC
            ");
            $stmt_res->execute(['id' => $id_servicio]);
            $resenas = $stmt_res->fetchAll();
            $total_resenas = count($resenas);

            if ($total_resenas > 0) {
                $suma = array_sum(array_column($resenas, 'puntuacion'));
                $promedio_calificacion = round($suma / $total_resenas, 1);
            }

            // Servicios relacionados reales
            $stmt_rel = $pdo->prepare("
                SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido
                FROM publicaciones p
                JOIN categorias c ON p.id_categoria = c.id_categoria
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.tipo = 'Servicio' AND p.id_publicacion != :id AND p.estado = 'Activo'
                ORDER BY (p.id_categoria = :id_categoria) DESC, p.fecha_creacion DESC
                LIMIT 3
            ");
            $stmt_rel->execute([
                'id' => $id_servicio,
                'id_categoria' => (int) $servicio['id_categoria']
            ]);
            $servicios_relacionados = $stmt_rel->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error al consultar detalle del servicio: " . $e->getMessage());
        $servicio = null;
    }
}

<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

$id_curso = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$curso = null;
$resenas = [];
$promedio_calificacion = 0.0;
$total_resenas = 0;
$cursos_relacionados = [];
$comprado = false;

$usuario_actual = usuario_actual();

if ($id_curso > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido,
                   u.id_usuario AS autor_id, u.foto_perfil AS autor_foto
            FROM publicaciones p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.id_publicacion = :id AND p.tipo = 'Curso' AND p.estado = 'Activo'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id_curso]);
        $curso = $stmt->fetch();

        if ($curso) {
            // Verificar si el usuario actual ya contrató o compró el curso
            if ($usuario_actual) {
                $stmt_compra = $pdo->prepare("
                    SELECT COUNT(*) 
                    FROM detalles_contratacion dc
                    JOIN contrataciones c ON dc.id_contratacion = c.id_contratacion
                    WHERE c.id_usuario = :id_usuario AND dc.id_publicacion = :id_publicacion
                      AND c.estado IN ('Completada', 'En Proceso')
                ");
                $stmt_compra->execute([
                    'id_usuario' => (int) $usuario_actual['id_usuario'],
                    'id_publicacion' => $id_curso,
                ]);
                $comprado = ((int) $stmt_compra->fetchColumn()) > 0;
            }

            // Reseñas del curso
            $stmt_res = $pdo->prepare("
                SELECT v.*, u.nombre, u.apellido, u.foto_perfil
                FROM valoraciones v
                JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE v.id_publicacion = :id
                ORDER BY v.fecha_valoracion DESC
            ");
            $stmt_res->execute(['id' => $id_curso]);
            $resenas = $stmt_res->fetchAll();
            $total_resenas = count($resenas);

            if ($total_resenas > 0) {
                $suma = array_sum(array_column($resenas, 'puntuacion'));
                $promedio_calificacion = round($suma / $total_resenas, 1);
            }

            // Cursos relacionados
            $stmt_rel = $pdo->prepare("
                SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido
                FROM publicaciones p
                JOIN categorias c ON p.id_categoria = c.id_categoria
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.tipo = 'Curso' AND p.id_publicacion != :id AND p.estado = 'Activo'
                ORDER BY (p.id_categoria = :id_categoria) DESC, p.fecha_creacion DESC
                LIMIT 3
            ");
            $stmt_rel->execute([
                'id' => $id_curso,
                'id_categoria' => (int) $curso['id_categoria']
            ]);
            $cursos_relacionados = $stmt_rel->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error al consultar detalle del curso: " . $e->getMessage());
        $curso = null;
    }
}

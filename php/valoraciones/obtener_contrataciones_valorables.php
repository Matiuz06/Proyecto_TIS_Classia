<?php

require_once __DIR__ . '/../../config/database.php';

// Obtiene las contrataciones del usuario con su titulo, estado y valoracion
function obtener_contrataciones_usuario($arg1, $arg2 = null): array
{
    global $pdo;
    if ($arg1 instanceof PDO) {
        $db = $arg1;
        $id_usuario = (int) $arg2;
    } else {
        $id_usuario = (int) $arg1;
        $db = ($arg2 instanceof PDO) ? $arg2 : $pdo;
    }

    $sql = "SELECT c.*, dc.id_publicacion, p.titulo, p.tipo,
                   v.id_valoracion, v.puntuacion, v.comentario, v.fecha_valoracion
            FROM contrataciones c
            JOIN detalles_contratacion dc ON dc.id_contratacion = c.id_contratacion
            JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
            LEFT JOIN valoraciones v ON v.id_contratacion = c.id_contratacion AND v.id_usuario = c.id_usuario
            WHERE c.id_usuario = :id_usuario
            ORDER BY c.fecha_contratacion DESC";

    $stmt = $db->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);

    return $stmt->fetchAll() ?: [];
}

// Retorna las contrataciones del usuario agrupadas por cursos y servicios.
function obtener_resumen_contrataciones_usuario($arg1, $arg2 = null): array
{
    $contrataciones = obtener_contrataciones_usuario($arg1, $arg2);
    $cursos = [];
    $servicios = [];

    foreach ($contrataciones as $item) {
        if ($item['tipo'] === 'Curso') {
            $cursos[] = $item;
        } else {
            $servicios[] = $item;
        }
    }

    return [
        'cursos'    => $cursos,
        'servicios' => $servicios,
    ];
}

// Prepara los datos necesarios para la vista de valoración de contrataciones.
function obtener_contexto_valoracion($arg1, $arg2 = 0, $arg3 = null): array
{
    global $pdo;
    if ($arg1 instanceof PDO) {
        $db = $arg1;
        $id_usuario = (int) $arg2;
        $id_contratacion_solicitada = (int) $arg3;
    } else {
        $id_usuario = (int) $arg1;
        $id_contratacion_solicitada = (int) $arg2;
        $db = ($arg3 instanceof PDO) ? $arg3 : $pdo;
    }

    $mis_contrataciones = obtener_contrataciones_usuario($id_usuario, $db);

    $contrataciones_pendientes = [];
    foreach ($mis_contrataciones as $c) {
        if (in_array($c['estado'], ['Completada', 'En Proceso'], true) && empty($c['id_valoracion'])) {
            $contrataciones_pendientes[] = $c;
        }
    }

    $contratacion_seleccionada = null;
    $error_mensaje = '';

    if ($id_contratacion_solicitada > 0) {
        foreach ($mis_contrataciones as $c) {
            if ((int) $c['id_contratacion'] === $id_contratacion_solicitada) {
                $contratacion_seleccionada = $c;
                break;
            }
        }

        if (!$contratacion_seleccionada) {
            $error_mensaje = 'La contratación seleccionada no existe o no pertenece a tu cuenta.';
        } elseif (!empty($contratacion_seleccionada['id_valoracion'])) {
            $error_mensaje = 'Esta contratación ya fue valorada anteriormente.';
        } elseif (!in_array($contratacion_seleccionada['estado'], ['Completada', 'En Proceso'], true)) {
            $error_mensaje = 'Esta contratación todavía no se puede valorar porque su estado es ' . htmlspecialchars($contratacion_seleccionada['estado']) . '.';
        }
    } elseif (!empty($contrataciones_pendientes)) {
        $contratacion_seleccionada = $contrataciones_pendientes[0];
    }

    return [
        'contrataciones_pendientes' => $contrataciones_pendientes,
        'contratacion_seleccionada' => $contratacion_seleccionada,
        'error_mensaje'             => $error_mensaje,
    ];
}


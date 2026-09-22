<?php

/**
 * Responsabilidad: Prepara listados reutilizables de publicaciones para vistas y paneles.
 */

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';

// Obtiene las publicaciones activas organizadas por cursos y servicios para el catálogo
function obtener_publicaciones_catalogo(PDO $pdo): array
{
    $sql = "SELECT id_publicacion, titulo, descripcion, precio, tipo 
            FROM publicaciones 
            WHERE estado = 'Activo'";

    $stmt = $pdo->query($sql);
    $publicaciones = $stmt->fetchAll();

    $cursos = [];
    $servicios = [];

    foreach ($publicaciones as $publicacion) {
        if ($publicacion['tipo'] === 'Curso') {
            $cursos[] = $publicacion;
        } elseif ($publicacion['tipo'] === 'Servicio') {
            $servicios[] = $publicacion;
        }
    }

    return [
        'cursos'    => $cursos,
        'servicios' => $servicios,
    ];
}

// Obtiene las categorias para listas o selectores
function obtener_categorias(PDO $pdo): array
{
    $stmt = $pdo->query("SELECT * FROM categorias ORDER BY nombre_categoria ASC");
    return $stmt->fetchAll();
}

// Obtiene las publicaciones de un usuario especifico
function obtener_publicaciones_usuario(PDO $pdo, int $id_usuario): array
{
    $sql = "SELECT p.*, c.nombre_categoria 
            FROM publicaciones p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE p.id_usuario = :id_usuario 
            ORDER BY p.fecha_creacion DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id_usuario' => $id_usuario]);
    return $stmt->fetchAll();
}

// Variables predeterminadas si no estan ya definidas en la vista
$usuario_actual_auth = usuario_actual();
$id_usuario_autenticado = (int) ($usuario_actual_auth['id_usuario'] ?? 0);

if (!isset($categorias)) {
    $categorias = obtener_categorias($pdo);
}

if (!isset($publicaciones_proveedor) && $id_usuario_autenticado > 0) {
    $publicaciones_proveedor = obtener_publicaciones_usuario($pdo, $id_usuario_autenticado);
}

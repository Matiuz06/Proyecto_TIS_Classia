<?php

require_once __DIR__ . '/../auth/session.php';
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

// Variables predeterminadas para vistas de administración y proveedor que incluyen este archivo
$usuario = usuario_actual();
$id_usuario_autenticado = (int) ($usuario['id_usuario'] ?? 0);

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

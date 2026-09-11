<?php

require_once __DIR__ . '/../../config/database.php';

$busqueda = trim($_GET['busqueda'] ?? '');
$tipo_filtro = trim($_GET['tipo'] ?? '');
$categoria_filtro = isset($_GET['categoria']) ? (int) $_GET['categoria'] : 0;

$categorias = [];
try {
    $stmt_cat = $pdo->query("SELECT id_categoria, nombre_categoria FROM categorias ORDER BY nombre_categoria ASC");
    $categorias = $stmt_cat->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar categorias: " . $e->getMessage());
}

$sql = "SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido 
        FROM publicaciones p 
        JOIN categorias c ON p.id_categoria = c.id_categoria 
        JOIN usuarios u ON p.id_usuario = u.id_usuario 
        WHERE p.estado = 'Activo'";
$params = [];

if ($busqueda !== '') {
    $sql .= " AND (p.titulo LIKE :busqueda OR p.descripcion LIKE :busqueda OR c.nombre_categoria LIKE :busqueda OR u.nombre LIKE :busqueda OR u.apellido LIKE :busqueda)";
    $params['busqueda'] = '%' . $busqueda . '%';
}

if ($tipo_filtro === 'curso') {
    $sql .= " AND p.tipo = 'Curso'";
} elseif ($tipo_filtro === 'servicio') {
    $sql .= " AND p.tipo = 'Servicio'";
}

if ($categoria_filtro > 0) {
    $sql .= " AND p.id_categoria = :categoria";
    $params['categoria'] = $categoria_filtro;
}

$sql .= " ORDER BY p.fecha_creacion DESC";

try {
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $publicaciones = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error al consultar catalogo: " . $e->getMessage());
    $publicaciones = [];
}

$cursos = array_filter($publicaciones, fn($p) => $p['tipo'] === 'Curso');
$servicios = array_filter($publicaciones, fn($p) => $p['tipo'] === 'Servicio');

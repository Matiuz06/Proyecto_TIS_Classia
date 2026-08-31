<?php
/**
 * Lógica funcional para crear publicaciones (REQ-SER-NN / CU-09).
 * Permite a un docente/proveedor publicar nuevos cursos y servicios en el catálogo.
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

// Generar Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];

// Obtener ID del proveedor autenticado (con fallback al ID 2 del schema para entorno de desarrollo/demo)
$id_usuario_autenticado = $_SESSION['id_usuario'] ?? 2;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $id_categoria = (int)($_POST['id_categoria'] ?? 0);
    $token_recibido = $_POST['csrf_token'] ?? '';

    // Validar CSRF
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $errores[] = "La sesión del formulario expiró. Por favor, recargá la página e intentá nuevamente.";
    }

    // 1. Validaciones de campos requeridos
    if (empty($titulo) || empty($descripcion) || empty($precio) || empty($tipo) || $id_categoria <= 0) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    // 2. Validar tipo
    if (!in_array($tipo, ['Curso', 'Servicio'], true)) {
        $errores[] = "El tipo de publicación seleccionado no es válido.";
    }

    // 3. Validar precio positivo
    if (!is_numeric($precio) || (float)$precio <= 0) {
        $errores[] = "El precio debe ser un número mayor a cero.";
    }

    // 4. Verificar categoría existente
    if (empty($errores)) {
        $stmt_cat = $pdo->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = :id");
        $stmt_cat->execute(['id' => $id_categoria]);
        if (!$stmt_cat->fetch()) {
            $errores[] = "La categoría seleccionada no existe.";
        }
    }

    // 5. Inserción mediante consulta preparada
    if (empty($errores)) {
        try {
            $sql = "INSERT INTO publicaciones (titulo, descripcion, precio, tipo, estado, id_usuario, id_categoria) 
                    VALUES (:titulo, :descripcion, :precio, :tipo, 'Activo', :id_usuario, :id_categoria)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo' => $titulo,
                'descripcion' => $descripcion,
                'precio' => (float)$precio,
                'tipo' => $tipo,
                'id_usuario' => $id_usuario_autenticado,
                'id_categoria' => $id_categoria
            ]);

            header("Location: panelProveedor.php?mensaje=creada");
            exit;
        } catch (PDOException $e) {
            error_log("Error SQL al crear publicación: " . $e->getMessage());
            $errores[] = "Ocurrió un error al guardar la publicación. Por favor, intentá nuevamente.";
        }
    }
}

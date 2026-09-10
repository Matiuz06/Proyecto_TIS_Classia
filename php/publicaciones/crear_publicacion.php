<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/upload_helper.php';

requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], '../../views/usuario.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];

$usuario = usuario_actual();
$id_usuario_autenticado = (int) ($usuario['id_usuario'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $id_categoria = (int)($_POST['id_categoria'] ?? 0);
    $token_recibido = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $errores[] = "La sesión del formulario expiró. Por favor, recargá la página e intentá nuevamente.";
    }

    if (empty($titulo) || empty($descripcion) || empty($precio) || empty($tipo) || $id_categoria <= 0) {
        $errores[] = "Todos los campos obligatorios deben ser completados.";
    }

    if (!in_array($tipo, ['Curso', 'Servicio'], true)) {
        $errores[] = "El tipo de publicación seleccionado no es válido.";
    }

    if (!is_numeric($precio) || (float)$precio <= 0) {
        $errores[] = "El precio debe ser un número mayor a cero.";
    }

    if (empty($errores)) {
        $stmt_cat = $pdo->prepare("SELECT id_categoria FROM categorias WHERE id_categoria = :id");
        $stmt_cat->execute(['id' => $id_categoria]);
        if (!$stmt_cat->fetch()) {
            $errores[] = "La categoría seleccionada no existe.";
        }
    }

    $ruta_imagen = null;
    if (empty($errores) && isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
        $res_upload = guardar_imagen_subida($_FILES['imagen'], 'publicaciones', 5);
        if ($res_upload['ok']) {
            $ruta_imagen = $res_upload['ruta'];
        } else {
            $errores[] = $res_upload['error'];
        }
    }

    if (empty($errores)) {
        try {
            $sql = "INSERT INTO publicaciones (titulo, descripcion, precio, tipo, estado, imagen, id_usuario, id_categoria) 
                    VALUES (:titulo, :descripcion, :precio, :tipo, 'Activo', :imagen, :id_usuario, :id_categoria)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo'       => $titulo,
                'descripcion'  => $descripcion,
                'precio'       => (float)$precio,
                'tipo'         => $tipo,
                'imagen'       => $ruta_imagen,
                'id_usuario'   => $id_usuario_autenticado,
                'id_categoria' => $id_categoria
            ]);

            header("Location: panel-proveedor.php?mensaje=creada");
            exit;
        } catch (PDOException $e) {
            error_log("Error SQL al crear publicación: " . $e->getMessage());
            if ($ruta_imagen) {
                eliminar_imagen_subida($ruta_imagen);
            }
            $errores[] = "Ocurrió un error al guardar la publicación. Por favor, intentá nuevamente.";
        }
    }
}

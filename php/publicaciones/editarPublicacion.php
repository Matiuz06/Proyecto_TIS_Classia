<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];
$publicacion = null;
$id_usuario_autenticado = $_SESSION['id_usuario'] ?? 2;

$id_publicacion = (int)($_GET['id'] ?? $_POST['id_publicacion'] ?? 0);

if ($id_publicacion > 0) {
    $stmt_check = $pdo->prepare("SELECT * FROM publicaciones WHERE id_publicacion = :id_pub AND id_usuario = :id_user");
    $stmt_check->execute([
        'id_pub' => $id_publicacion,
        'id_user' => $id_usuario_autenticado
    ]);
    $publicacion = $stmt_check->fetch();

    if (!$publicacion) {
        $errores[] = "No tenés permisos para modificar esta publicación o la misma no existe.";
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $errores[] = "Publicación no especificada.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($errores) && $publicacion) {
    $token_recibido = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $errores[] = "La sesión del formulario expiró. Por favor, recargá la página e intentá nuevamente.";
    }

    if (isset($_POST['cambiar_estado'])) {
        $nuevo_estado = $_POST['cambiar_estado'];
        if (in_array($nuevo_estado, ['Activo', 'Inactivo', 'Pausado'], true)) {
            try {
                $stmt_estado = $pdo->prepare("UPDATE publicaciones SET estado = :estado WHERE id_publicacion = :id_pub AND id_usuario = :id_user");
                $stmt_estado->execute([
                    'estado' => $nuevo_estado,
                    'id_pub' => $id_publicacion,
                    'id_user' => $id_usuario_autenticado
                ]);
                header("Location: panelProveedor.php?mensaje=estado_actualizado");
                exit;
            } catch (PDOException $e) {
                error_log("Error al cambiar estado de publicación: " . $e->getMessage());
                $errores[] = "No se pudo cambiar el estado de la publicación.";
            }
        } else {
            $errores[] = "El estado seleccionado no es válido.";
        }
    } else {
        $titulo = trim($_POST['titulo'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = trim($_POST['precio'] ?? '');
        $tipo = trim($_POST['tipo'] ?? '');
        $id_categoria = (int)($_POST['id_categoria'] ?? 0);
        $estado = trim($_POST['estado'] ?? 'Activo');

        if (empty($titulo) || empty($descripcion) || empty($precio) || empty($tipo) || $id_categoria <= 0) {
            $errores[] = "Todos los campos son obligatorios.";
        }

        if (!in_array($tipo, ['Curso', 'Servicio'], true)) {
            $errores[] = "El tipo de publicación seleccionado no es válido.";
        }

        if (!in_array($estado, ['Activo', 'Inactivo', 'Pausado'], true)) {
            $errores[] = "El estado seleccionado no es válido.";
        }

        if (!is_numeric($precio) || (float)$precio <= 0) {
            $errores[] = "El precio debe ser un número mayor a cero.";
        }

        if (empty($errores)) {
            try {
                $sql = "UPDATE publicaciones 
                        SET titulo = :titulo, descripcion = :descripcion, precio = :precio, tipo = :tipo, id_categoria = :id_categoria, estado = :estado 
                        WHERE id_publicacion = :id_pub AND id_usuario = :id_user";
                $stmt_update = $pdo->prepare($sql);
                $stmt_update->execute([
                    'titulo' => $titulo,
                    'descripcion' => $descripcion,
                    'precio' => (float)$precio,
                    'tipo' => $tipo,
                    'id_categoria' => $id_categoria,
                    'estado' => $estado,
                    'id_pub' => $id_publicacion,
                    'id_user' => $id_usuario_autenticado
                ]);

                header("Location: panelProveedor.php?mensaje=actualizada");
                exit;
            } catch (PDOException $e) {
                error_log("Error SQL al actualizar publicación: " . $e->getMessage());
                $errores[] = "Ocurrió un error al actualizar la publicación.";
            }
        }
    }
}

<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/upload_helper.php';

requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], '../../views/usuario.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];
$publicacion = null;
$usuario = usuario_actual();
$id_usuario_autenticado = (int) ($usuario['id_usuario'] ?? 0);
$es_admin_usuario = es_admin();

$id_publicacion = (int)($_GET['id'] ?? $_POST['id_publicacion'] ?? 0);

if ($id_publicacion > 0) {
    if ($es_admin_usuario) {
        $stmt_check = $pdo->prepare("SELECT * FROM publicaciones WHERE id_publicacion = :id_pub");
        $stmt_check->execute(['id_pub' => $id_publicacion]);
    } else {
        $stmt_check = $pdo->prepare("SELECT * FROM publicaciones WHERE id_publicacion = :id_pub AND id_usuario = :id_user");
        $stmt_check->execute([
            'id_pub' => $id_publicacion,
            'id_user' => $id_usuario_autenticado
        ]);
    }
    $publicacion = $stmt_check->fetch();

    if (!$publicacion) {
        $errores[] = "No tenés permisos para modificar esta publicación o la misma no existe.";
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
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
                $stmt_estado = $pdo->prepare("UPDATE publicaciones SET estado = :estado WHERE id_publicacion = :id_pub");
                $stmt_estado->execute([
                    'estado' => $nuevo_estado,
                    'id_pub' => $id_publicacion,
                ]);
                header("Location: panel-proveedor.php?mensaje=estado_actualizado");
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
        $modalidad = trim($_POST['modalidad'] ?? '');
        $nivel_experiencia = trim($_POST['nivel_experiencia'] ?? '');
        $duracion_horas = trim($_POST['duracion_horas'] ?? '');
        $id_categoria = (int)($_POST['id_categoria'] ?? 0);
        $estado = trim($_POST['estado'] ?? 'Activo');
        $eliminar_imagen = !empty($_POST['eliminar_imagen']);

        if (empty($titulo) || empty($descripcion) || empty($precio) || empty($tipo) || $id_categoria <= 0) {
            $errores[] = "Todos los campos obligatorios deben ser completados.";
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
        if ($modalidad !== '' && !in_array($modalidad, ['virtual', 'presencial', 'hibrida', 'producto-entregable'], true)) {
            $errores[] = "La modalidad seleccionada no es válida.";
        }
        if ($nivel_experiencia !== '' && !in_array($nivel_experiencia, ['inicial', 'intermedio', 'avanzado', 'depende-categoria'], true)) {
            $errores[] = "El nivel de experiencia seleccionado no es válido.";
        }
        if ($duracion_horas !== '' && (!ctype_digit($duracion_horas) || (int) $duracion_horas <= 0)) {
            $errores[] = "La duración debe ser una cantidad de horas válida.";
        }

        $ruta_imagen = $publicacion['imagen'];

        if ($eliminar_imagen && $ruta_imagen) {
            eliminar_imagen_subida($ruta_imagen);
            $ruta_imagen = null;
        }

        if (empty($errores) && isset($_FILES['imagen']) && $_FILES['imagen']['error'] !== UPLOAD_ERR_NO_FILE) {
            $res_upload = guardar_imagen_subida($_FILES['imagen'], 'publicaciones', 5);
            if ($res_upload['ok']) {
                if ($ruta_imagen && $ruta_imagen !== $res_upload['ruta']) {
                    eliminar_imagen_subida($ruta_imagen);
                }
                $ruta_imagen = $res_upload['ruta'];
            } else {
                $errores[] = $res_upload['error'];
            }
        }

        if (empty($errores)) {
            try {
                $sql = "UPDATE publicaciones 
                        SET titulo = :titulo, descripcion = :descripcion, precio = :precio, tipo = :tipo,
                            modalidad = :modalidad, nivel_experiencia = :nivel_experiencia, duracion_horas = :duracion_horas,
                            id_categoria = :id_categoria, estado = :estado, imagen = :imagen
                        WHERE id_publicacion = :id_pub";
                $stmt_update = $pdo->prepare($sql);
                $stmt_update->execute([
                    'titulo'       => $titulo,
                    'descripcion'  => $descripcion,
                    'precio'       => (float)$precio,
                    'tipo'         => $tipo,
                    'modalidad'    => $modalidad !== '' ? $modalidad : null,
                    'nivel_experiencia' => $nivel_experiencia !== '' ? $nivel_experiencia : null,
                    'duracion_horas' => $duracion_horas !== '' ? (int) $duracion_horas : null,
                    'id_categoria' => $id_categoria,
                    'estado'       => $estado,
                    'imagen'       => $ruta_imagen,
                    'id_pub'       => $id_publicacion,
                ]);

                header("Location: panel-proveedor.php?mensaje=actualizada");
                exit;
            } catch (PDOException $e) {
                error_log("Error SQL al actualizar publicación: " . $e->getMessage());
                $errores[] = "Ocurrió un error al actualizar la publicación.";
            }
        }
    }
}

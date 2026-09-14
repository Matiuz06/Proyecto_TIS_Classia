<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/upload_helper.php';
require_once __DIR__ . '/publicacion_helpers.php';

requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], '../../views/usuario.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];
$usuario = usuario_actual();
$id_usuario_autenticado = (int) ($usuario['id_usuario'] ?? 0);
$categorias = obtener_categorias($pdo);
$plantillas_servicios = plantillas_servicio();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
<<<<<<< HEAD
=======
    $titulo = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio = trim($_POST['precio'] ?? '');
    $tipo = trim($_POST['tipo'] ?? '');
    $modalidad = trim($_POST['modalidad'] ?? '');
    $nivel_experiencia = trim($_POST['nivel_experiencia'] ?? '');
    $duracion_horas = trim($_POST['duracion_horas'] ?? '');
    $id_categoria = (int)($_POST['id_categoria'] ?? 0);
>>>>>>> 187b1cca0ceb4e5a72ed2c4a005b042108a51e1e
    $token_recibido = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $errores[] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    }
<<<<<<< HEAD
    $errores = array_merge($errores, validar_datos_publicacion($_POST));
=======
    if ($modalidad !== '' && !in_array($modalidad, ['virtual', 'presencial', 'hibrida', 'producto-entregable'], true)) {
        $errores[] = "La modalidad seleccionada no es válida.";
    }
    if ($nivel_experiencia !== '' && !in_array($nivel_experiencia, ['inicial', 'intermedio', 'avanzado', 'depende-categoria'], true)) {
        $errores[] = "El nivel de experiencia seleccionado no es válido.";
    }
    if ($duracion_horas !== '' && (!ctype_digit($duracion_horas) || (int) $duracion_horas <= 0)) {
        $errores[] = "La duración debe ser una cantidad de horas válida.";
    }
>>>>>>> 187b1cca0ceb4e5a72ed2c4a005b042108a51e1e

    $categoria = ['ok'=>false,'id'=>0,'error'=>''];
    if (empty($errores)) {
        try {
            $categoria = resolver_categoria_publicacion(
                $pdo,
                $id_usuario_autenticado,
                (int)($_POST['id_categoria'] ?? 0),
                $_POST['nueva_categoria'] ?? '',
                $_POST['descripcion_categoria'] ?? ''
            );
            if (!$categoria['ok']) $errores[] = $categoria['error'];
        } catch (PDOException $e) {
            error_log('Categoría publicación: ' . $e->getMessage());
            $errores[] = 'No se pudo crear o seleccionar la categoría.';
        }
    }

    $ruta_imagen = null;
    if (empty($errores) && isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $res = guardar_imagen_subida($_FILES['imagen'], 'publicaciones', 5);
        if ($res['ok']) $ruta_imagen = $res['ruta'];
        else $errores[] = $res['error'];
    }

    if (empty($errores)) {
        try {
<<<<<<< HEAD
            $stmt = $pdo->prepare("INSERT INTO publicaciones
                (titulo,descripcion,precio,tipo,modalidad,nivel_experiencia,duracion_horas,cupos,disponibilidad,tipo_servicio,estado,imagen,id_usuario,id_categoria)
                VALUES (:titulo,:descripcion,:precio,:tipo,:modalidad,:nivel,:duracion,:cupos,:disponibilidad,:tipo_servicio,'Activo',:imagen,:usuario,:categoria)");
            $stmt->execute([
                'titulo'=>trim($_POST['titulo']),
                'descripcion'=>trim($_POST['descripcion']),
                'precio'=>(float)$_POST['precio'],
                'tipo'=>$_POST['tipo'],
                'modalidad'=>trim($_POST['modalidad'] ?? '') ?: null,
                'nivel'=>trim($_POST['nivel_experiencia'] ?? '') ?: null,
                'duracion'=>trim($_POST['duracion_horas'] ?? '') !== '' ? (int)$_POST['duracion_horas'] : null,
                'cupos'=>trim($_POST['cupos'] ?? '') !== '' ? (int)$_POST['cupos'] : null,
                'disponibilidad'=>trim($_POST['disponibilidad'] ?? '') ?: null,
                'tipo_servicio'=>$_POST['tipo']==='Servicio' ? (trim($_POST['tipo_servicio'] ?? '') ?: null) : null,
                'imagen'=>$ruta_imagen,
                'usuario'=>$id_usuario_autenticado,
                'categoria'=>$categoria['id'],
=======
                $sql = "INSERT INTO publicaciones (titulo, descripcion, precio, tipo, modalidad, nivel_experiencia, duracion_horas, estado, imagen, id_usuario, id_categoria)
                    VALUES (:titulo, :descripcion, :precio, :tipo, :modalidad, :nivel_experiencia, :duracion_horas, 'Activo', :imagen, :id_usuario, :id_categoria)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                'titulo'       => $titulo,
                'descripcion'  => $descripcion,
                'precio'       => (float)$precio,
                'tipo'         => $tipo,
                'modalidad'    => $modalidad !== '' ? $modalidad : null,
                'nivel_experiencia' => $nivel_experiencia !== '' ? $nivel_experiencia : null,
                'duracion_horas' => $duracion_horas !== '' ? (int) $duracion_horas : null,
                'imagen'       => $ruta_imagen,
                'id_usuario'   => $id_usuario_autenticado,
                'id_categoria' => $id_categoria
>>>>>>> 187b1cca0ceb4e5a72ed2c4a005b042108a51e1e
            ]);
            $id = (int)$pdo->lastInsertId();
            if ($_POST['tipo'] === 'Curso') {
                header("Location: gestionar-contenido-curso.php?id={$id}&mensaje=creada");
            } else {
                header('Location: panel-proveedor.php?mensaje=creada');
            }
            exit;
        } catch (PDOException $e) {
            error_log('Crear publicación: ' . $e->getMessage());
            if ($ruta_imagen) eliminar_imagen_subida($ruta_imagen);
            $errores[] = 'No se pudo guardar la publicación.';
        }
    }
}

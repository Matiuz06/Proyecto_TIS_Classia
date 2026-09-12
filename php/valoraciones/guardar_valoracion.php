<?php

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../../config/database.php';

const LOGIN_URL = '../../views/login.php';
const VALORACION_BASE_URL = '../../views/valoracion.php';
const USUARIO_URL = '../../views/usuario.php?mensaje=valoracion_guardada';

iniciar_sesion();

if (!esta_autenticado()) {
    header('Location: ' . LOGIN_URL);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ' . VALORACION_BASE_URL);
    exit;
}

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$errores = [];

$token_recibido  = $_POST['csrf_token'] ?? '';
$id_contratacion = (int) ($_POST['id_contratacion'] ?? 0);
$puntuacion      = (int) ($_POST['puntuacion'] ?? 0);
$comentario      = trim($_POST['comentario'] ?? '');

// Validar token de seguridad del formulario
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_recibido)) {
    $errores[] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
}

// Validar contratación
if ($id_contratacion <= 0) {
    $errores[] = 'Debe seleccionar una contratación válida.';
}

// Validar que la puntuación sea entre 1 y 5
if ($puntuacion < 1 || $puntuacion > 5) {
    $errores[] = 'La puntuación debe ser un número entre 1 y 5.';
}

// Validaciones con la base de datos
$contratacion = null;
if (empty($errores)) {
    $stmt = $pdo->prepare('SELECT c.*, dc.id_publicacion 
                           FROM contrataciones c 
                           JOIN detalles_contratacion dc ON dc.id_contratacion = c.id_contratacion 
                           WHERE c.id_contratacion = :id_contratacion 
                           LIMIT 1');
    $stmt->execute(['id_contratacion' => $id_contratacion]);
    $contratacion = $stmt->fetch();

    if (!$contratacion) {
        $errores[] = 'La contratación no existe.';
    } elseif ((int) $contratacion['id_usuario'] !== $id_usuario) {
        $errores[] = 'No podés valorar una contratación que no te pertenece.';
    } elseif (!in_array($contratacion['estado'], ['Completada', 'En Proceso'], true)) {
        $errores[] = "Solo podés valorar contrataciones en estado 'Completada' o 'En Proceso'.";
    } else {
        $stmt_val = $pdo->prepare('SELECT id_valoracion FROM valoraciones WHERE id_contratacion = :id_contratacion AND id_usuario = :id_usuario LIMIT 1');
        $stmt_val->execute([
            'id_contratacion' => $id_contratacion,
            'id_usuario'      => $id_usuario,
        ]);

        if ($stmt_val->fetch()) {
            $errores[] = 'Ya valoraste esta contratación anteriormente.';
        }
    }
}

// Si hubo errores, guardar en sesión y volver a la vista del formulario
if (!empty($errores)) {
    $_SESSION['valoracion_errores'] = $errores;
    $_SESSION['valoracion_input'] = [
        'id_contratacion' => $id_contratacion,
        'puntuacion'      => $puntuacion,
        'comentario'      => $comentario,
    ];

    $redirect_url = VALORACION_BASE_URL;
    if ($id_contratacion > 0) {
        $redirect_url .= '?contratacion=' . $id_contratacion;
    }

    header('Location: ' . $redirect_url);
    exit;
}

// Guardar en la base de datos
try {
    $sql = 'INSERT INTO valoraciones (puntuacion, comentario, fecha_valoracion, id_usuario, id_publicacion, id_contratacion) 
            VALUES (:puntuacion, :comentario, CURRENT_TIMESTAMP, :id_usuario, :id_publicacion, :id_contratacion)';

    $stmt_insert = $pdo->prepare($sql);
    $stmt_insert->execute([
        'puntuacion'      => $puntuacion,
        'comentario'      => ($comentario !== '' ? $comentario : null),
        'id_usuario'      => $id_usuario,
        'id_publicacion'  => (int) $contratacion['id_publicacion'],
        'id_contratacion' => $id_contratacion,
    ]);

    header('Location: ' . USUARIO_URL);
    exit;
} catch (PDOException $e) {
    error_log('Error al guardar valoracion: ' . $e->getMessage());
    $_SESSION['valoracion_errores'] = ['Ocurrió un error al guardar la valoración. Por favor, intentá nuevamente.'];
    $_SESSION['valoracion_input'] = [
        'id_contratacion' => $id_contratacion,
        'puntuacion'      => $puntuacion,
        'comentario'      => $comentario,
    ];

    $redirect_url = VALORACION_BASE_URL;
    if ($id_contratacion > 0) {
        $redirect_url .= '?contratacion=' . $id_contratacion;
    }

    header('Location: ' . $redirect_url);
    exit;
}

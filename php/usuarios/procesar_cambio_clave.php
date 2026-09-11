<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/cambiar-contrasena.php');
    exit;
}

$token_recibido = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
    $_SESSION['error_clave'] = 'La sesión del formulario expiró. Intentá nuevamente.';
    header('Location: ../../views/cambiar-contrasena.php');
    exit;
}

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];

$actual    = $_POST['contrasena_actual'] ?? '';
$nueva     = $_POST['contrasena_nueva'] ?? '';
$confirmar = $_POST['contrasena_confirmar'] ?? '';

if ($actual === '' || $nueva === '' || $confirmar === '') {
    $_SESSION['error_clave'] = 'Todos los campos son obligatorios.';
    header('Location: ../../views/cambiar-contrasena.php');
    exit;
}

if ($nueva !== $confirmar) {
    $_SESSION['error_clave'] = 'La nueva contraseña y su confirmación no coinciden.';
    header('Location: ../../views/cambiar-contrasena.php');
    exit;
}

if (strlen($nueva) < 6) {
    $_SESSION['error_clave'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    header('Location: ../../views/cambiar-contrasena.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT password_hash FROM usuarios WHERE id_usuario = :id LIMIT 1");
    $stmt->execute(['id' => $id_usuario]);
    $userRow = $stmt->fetch();

    if (!$userRow || !password_verify($actual, $userRow['password_hash'])) {
        $_SESSION['error_clave'] = 'La contraseña actual ingresada es incorrecta.';
        header('Location: ../../views/cambiar-contrasena.php');
        exit;
    }

    $nuevo_hash = password_hash($nueva, PASSWORD_DEFAULT);
    $stmt_update = $pdo->prepare("UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id");
    $stmt_update->execute(['hash' => $nuevo_hash, 'id' => $id_usuario]);

    $_SESSION['exito_perfil'] = 'Contraseña actualizada correctamente.';
    header('Location: ../../views/usuario.php');
    exit;
} catch (PDOException $e) {
    error_log("Error al cambiar contraseña: " . $e->getMessage());
    $_SESSION['error_clave'] = 'Ocurrió un error al procesar el cambio de contraseña.';
    header('Location: ../../views/cambiar-contrasena.php');
    exit;
}

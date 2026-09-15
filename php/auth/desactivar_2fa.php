<?php

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

$token_csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_csrf)) {
    $_SESSION['2fa_setup_error'] = 'La sesión del formulario expiró.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

$id_usuario = (int) $_SESSION['usuario_id'];
$contrasena = $_POST['contrasena_confirmar'] ?? '';

if ($contrasena === '') {
    $_SESSION['2fa_setup_error'] = 'Debés ingresar tu contraseña para desactivar 2FA.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT password_hash FROM usuarios WHERE id_usuario = :id LIMIT 1');
    $stmt->execute(['id' => $id_usuario]);
    $u = $stmt->fetch();

    if (!$u || !password_verify($contrasena, $u['password_hash'])) {
        $_SESSION['2fa_setup_error'] = 'Contraseña incorrecta.';
        header('Location: ../../views/configurar-2fa.php');
        exit;
    }

    $stmt_up = $pdo->prepare('UPDATE usuarios SET dos_factores_activo = 0, dos_factores_secreto = NULL, dos_factores_backup_codes = NULL WHERE id_usuario = :id');
    $stmt_up->execute(['id' => $id_usuario]);

    header('Location: ../../views/usuario.php?2fa_desactivado=1');
    exit;
} catch (PDOException $e) {
    error_log('Error al desactivar 2FA: ' . $e->getMessage());
    $_SESSION['2fa_setup_error'] = 'Ocurrió un error al desactivar la autenticación en dos pasos.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

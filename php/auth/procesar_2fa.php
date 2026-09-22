<?php

/**
 * Responsabilidad: Verifica el segundo factor antes de completar el inicio de sesión.
 */

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

$pending = $_SESSION['2fa_pending'] ?? null;
if (!$pending || empty($pending['id_usuario']) || (time() - ($pending['tiempo'] ?? 0)) > 300) {
    unset($_SESSION['2fa_pending']);
    $_SESSION['login_error'] = 'La sesión de autenticación en dos pasos ha expirado. Por favor, ingresá nuevamente.';
    header('Location: ../../views/login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/verificar-2fa.php');
    exit;
}

$token_csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_csrf)) {
    $_SESSION['2fa_error'] = 'La sesión del formulario expiró. Intentá nuevamente.';
    header('Location: ../../views/verificar-2fa.php');
    exit;
}

$codigo = trim($_POST['codigo_2fa'] ?? '');
if ($codigo === '') {
    $_SESSION['2fa_error'] = 'Por favor, ingresá el código de 6 dígitos o un código de respaldo.';
    header('Location: ../../views/verificar-2fa.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT dos_factores_secreto, dos_factores_backup_codes FROM usuarios WHERE id_usuario = :id LIMIT 1');
    $stmt->execute(['id' => (int) $pending['id_usuario']]);
    $user_2fa = $stmt->fetch();

    if (!$user_2fa || empty($user_2fa['dos_factores_secreto'])) {
        unset($_SESSION['2fa_pending']);
        $_SESSION['login_error'] = 'Error en la configuración 2FA de la cuenta.';
        header('Location: ../../views/login.php');
        exit;
    }

    $secret = TOTP::desencriptarSecreto($user_2fa['dos_factores_secreto'] ?? '');
    $backup_codes_json = $user_2fa['dos_factores_backup_codes'] ?? '[]';
    $es_valido = false;

    if (!empty($secret) && preg_match('/^[0-9]{6}$/', $codigo)) {
        $es_valido = TOTP::verificarCodigo($secret, $codigo);
    }

    if (!$es_valido) {
        $codigo_limpio = str_replace('-', '', $codigo);
        if (TOTP::verificarYConsumirCodigoRespaldo($codigo_limpio, $backup_codes_json)) {
            $es_valido = true;
            $stmt_up = $pdo->prepare('UPDATE usuarios SET dos_factores_backup_codes = :codes WHERE id_usuario = :id');
            $stmt_up->execute(['codes' => $backup_codes_json, 'id' => (int) $pending['id_usuario']]);
        }
    }

    if (!$es_valido) {
        $_SESSION['2fa_error'] = 'El código ingresado es incorrecto o ya fue utilizado.';
        header('Location: ../../views/verificar-2fa.php');
        exit;
    }

    establecer_usuario_sesion(
        (int) $pending['id_usuario'],
        $pending['nombre'],
        $pending['email'],
        (int) $pending['id_rol'],
        $pending['foto_perfil'] ?? null
    );

    unset($_SESSION['2fa_pending']);

    if ((int) $pending['id_rol'] === 3) {
        header('Location: ../../views/panel-administrador.php');
    } elseif ((int) $pending['id_rol'] === 2) {
        header('Location: ../../views/panel-proveedor.php');
    } else {
        header('Location: ../../views/usuario.php');
    }
    exit;
} catch (PDOException $e) {
    error_log('Error al verificar 2FA: ' . $e->getMessage());
    $_SESSION['2fa_error'] = 'Ocurrió un error al verificar el código. Intentá nuevamente.';
    header('Location: ../../views/verificar-2fa.php');
    exit;
}

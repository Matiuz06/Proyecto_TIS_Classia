<?php

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/password_policy.php';
require_once __DIR__ . '/../utils/mailer.php';

iniciar_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

$token_csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_csrf)) {
    $_SESSION['error_restablecer'] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

$token_reset = trim($_POST['token'] ?? '');
$email = trim($_POST['correo_recuperacion'] ?? '');
$nueva = $_POST['nueva_contrasena'] ?? '';

if ($token_reset !== '') {
    if ($nueva === '') {
        $_SESSION['error_restablecer'] = 'Debes ingresar la nueva contraseña.';
        header('Location: ../../views/restablecer-contrasena.php?token=' . rawurlencode($token_reset));
        exit;
    }

    $errores_password = validar_contrasena($nueva);
    if (!empty($errores_password)) {
        $_SESSION['error_restablecer'] = implode(' ', $errores_password);
        header('Location: ../../views/restablecer-contrasena.php?token=' . rawurlencode($token_reset));
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE password_reset_token = :token AND password_reset_expira > NOW() LIMIT 1');
        $stmt->execute(['token' => $token_reset]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $_SESSION['error_restablecer'] = 'El enlace de recuperación no es válido o ya expiró.';
            header('Location: ../../views/restablecer-contrasena.php');
            exit;
        }

        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt_up = $pdo->prepare('UPDATE usuarios SET password_hash = :hash, password_reset_token = NULL, password_reset_expira = NULL WHERE id_usuario = :id');
        $stmt_up->execute([
            'hash' => $hash,
            'id' => (int) $usuario['id_usuario'],
        ]);

        $_SESSION['success_restablecer'] = 'Tu contraseña fue restablecida correctamente. Ya podés iniciar sesión.';
        header('Location: ../../views/login.php');
        exit;
    } catch (PDOException $e) {
        error_log('Error al guardar nueva contraseña: ' . $e->getMessage());
        $_SESSION['error_restablecer'] = 'Ocurrió un error al procesar la recuperación.';
        header('Location: ../../views/restablecer-contrasena.php');
        exit;
    }
}

if ($email === '') {
    $_SESSION['error_restablecer'] = 'Debes indicar tu correo electrónico.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_restablecer'] = 'El correo electrónico no es válido.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1');
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        $token_generado = bin2hex(random_bytes(32));
        $expira = date('Y-m-d H:i:s', time() + 3600);

        $stmt_up = $pdo->prepare('UPDATE usuarios SET password_reset_token = :token, password_reset_expira = :expira WHERE id_usuario = :id');
        $stmt_up->execute([
            'token' => $token_generado,
            'expira' => $expira,
            'id' => (int) $usuario['id_usuario'],
        ]);

        $config = cargar_configuracion_correo();
        $enlace = rtrim($config['base_url'], '/') . '/views/restablecer-contrasena.php?token=' . rawurlencode($token_generado);
        $contenido = plantilla_correo(
            'Restablecé tu contraseña de Classia',
            '<p>Hacé clic en el siguiente enlace para definir una nueva contraseña:</p>'
            . '<p><a href="' . htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') . '">Restablecer contraseña</a></p>'
            . '<p>Este enlace vence en 1 hora.</p>'
            . '<p>Si no solicitaste este cambio, podés ignorar este mensaje.</p>'
        );

        $correo_enviado = enviar_correo($email, 'Recuperá tu contraseña en Classia', $contenido);
        if (!$correo_enviado) {
            $_SESSION['error_restablecer'] = 'No pudimos enviar el correo de recuperación en este momento. Intentá nuevamente más tarde.';
            header('Location: ../../views/restablecer-contrasena.php');
            exit;
        }
    }

    $_SESSION['success_restablecer'] = 'Si el correo está registrado, te enviamos un enlace para restablecer tu contraseña.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
} catch (PDOException $e) {
    error_log('Error al solicitar restablecimiento de contraseña: ' . $e->getMessage());
    $_SESSION['error_restablecer'] = 'Ocurrió un error al procesar la solicitud.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

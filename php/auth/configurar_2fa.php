<?php

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('login.php');

// Token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$id_usuario = id_usuario_actual();

try {
    $stmt = $pdo->prepare('SELECT email, dos_factores_activo, dos_factores_secreto FROM usuarios WHERE id_usuario = :id LIMIT 1');
    $stmt->execute(['id' => $id_usuario]);
    $user_info = $stmt->fetch();
} catch (PDOException $e) {
    error_log('Error al consultar estado 2FA: ' . $e->getMessage());
    $user_info = null;
}

$esta_activo = !empty($user_info['dos_factores_activo']);
$error_setup = $_SESSION['2fa_setup_error'] ?? '';
unset($_SESSION['2fa_setup_error']);

$backup_codes_nuevos = $_SESSION['2fa_backup_codes_mostrar'] ?? null;
unset($_SESSION['2fa_backup_codes_mostrar']);

$setup_secret = '';
$setup_backup = null;
$otpauth_uri  = '';
$qr_image_url = '';

if (!$esta_activo && $user_info) {
    if (empty($_SESSION['2fa_setup_secret'])) {
        $_SESSION['2fa_setup_secret'] = TOTP::generarSecreto(16);
        $_SESSION['2fa_setup_backup'] = TOTP::generarCodigosRespaldo(8);
    }
    $setup_secret = $_SESSION['2fa_setup_secret'];
    $setup_backup = $_SESSION['2fa_setup_backup'];
    $otpauth_uri  = TOTP::generarUri($user_info['email'] ?? 'usuario', $setup_secret, 'Classia');
    $qr_image_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($otpauth_uri);
}

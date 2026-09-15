<?php

require_once __DIR__ . '/sesion.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

$token_csrf = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token_csrf)) {
    $_SESSION['2fa_setup_error'] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

$secret = $_SESSION['2fa_setup_secret'] ?? '';
$backup = $_SESSION['2fa_setup_backup'] ?? null;
$codigo = trim($_POST['codigo_confirmacion'] ?? '');

if (empty($secret) || empty($backup) || empty($codigo)) {
    $_SESSION['2fa_setup_error'] = 'Datos incompletos para activar 2FA. Intentá nuevamente.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

if (!TOTP::verificarCodigo($secret, $codigo)) {
    $_SESSION['2fa_setup_error'] = 'El código de 6 dígitos ingresado es incorrecto. Verificá la hora de tu dispositivo e intentá de nuevo.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

$id_usuario = id_usuario_actual();

try {
    $stmt = $pdo->prepare('UPDATE usuarios SET dos_factores_activo = 1, dos_factores_secreto = :sec, dos_factores_backup_codes = :codes WHERE id_usuario = :id');
    $stmt->execute([
        'sec'   => $secret,
        'codes' => json_encode($backup['hashes']),
        'id'    => $id_usuario,
    ]);

    unset($_SESSION['2fa_setup_secret']);

    $_SESSION['2fa_backup_codes_mostrar'] = $backup['claros'];
    unset($_SESSION['2fa_setup_backup']);

    header('Location: ../../views/configurar-2fa.php?exito=1');
    exit;
} catch (PDOException $e) {
    error_log('Error al activar 2FA: ' . $e->getMessage());
    $_SESSION['2fa_setup_error'] = 'Ocurrió un error al guardar la configuración en la base de datos.';
    header('Location: ../../views/configurar-2fa.php');
    exit;
}

<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

$email = trim($_POST['correo_recuperacion'] ?? '');
$nueva = $_POST['nueva_contrasena'] ?? '';

if ($email === '' || $nueva === '') {
    $_SESSION['error_restablecer'] = 'Todos los campos son obligatorios.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_restablecer'] = 'El correo electrónico no es válido.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

if (strlen($nueva) < 6) {
    $_SESSION['error_restablecer'] = 'La nueva contraseña debe tener al menos 6 caracteres.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user) {
        $hash = password_hash($nueva, PASSWORD_DEFAULT);
        $stmt_up = $pdo->prepare("UPDATE usuarios SET password_hash = :hash WHERE id_usuario = :id");
        $stmt_up->execute(['hash' => $hash, 'id' => (int) $user['id_usuario']]);
    }

    // Por seguridad siempre confirmamos el proceso sin revelar existencia
    $_SESSION['login_error'] = 'Si el correo está registrado, la contraseña fue restablecida con éxito. Ya podés iniciar sesión.';
    header('Location: ../../views/login.php');
    exit;
} catch (PDOException $e) {
    error_log("Error al restablecer contraseña: " . $e->getMessage());
    $_SESSION['error_restablecer'] = 'Ocurrió un error al procesar la solicitud.';
    header('Location: ../../views/restablecer-contrasena.php');
    exit;
}

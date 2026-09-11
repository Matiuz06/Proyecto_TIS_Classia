<?php

require_once __DIR__ . '/../auth/session.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/usuario.php');
    exit;
}

$token_recibido = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
    $_SESSION['error_perfil'] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    header('Location: ../../views/usuario.php');
    exit;
}

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];

$nombre   = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email    = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');

if ($nombre === '' || $email === '') {
    $_SESSION['error_perfil'] = 'El nombre y el correo electrónico son campos obligatorios.';
    header('Location: ../../views/usuario.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_perfil'] = 'El correo electrónico ingresado no tiene un formato válido.';
    header('Location: ../../views/usuario.php');
    exit;
}

if ($telefono !== '' && !preg_match('/^[0-9+\-\s()]{6,30}$/', $telefono)) {
    $_SESSION['error_perfil'] = 'El número de teléfono tiene un formato inválido.';
    header('Location: ../../views/usuario.php');
    exit;
}

try {
    // Verificar si el email ya está en uso por otro usuario
    $stmt_check = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :id LIMIT 1");
    $stmt_check->execute(['email' => $email, 'id' => $id_usuario]);
    if ($stmt_check->fetch()) {
        $_SESSION['error_perfil'] = 'El correo electrónico ya está registrado por otro usuario.';
        header('Location: ../../views/usuario.php');
        exit;
    }

    $stmt_update = $pdo->prepare(
        "UPDATE usuarios 
         SET nombre = :nombre, apellido = :apellido, email = :email, telefono = :telefono 
         WHERE id_usuario = :id"
    );
    $stmt_update->execute([
        'nombre'   => $nombre,
        'apellido' => $apellido,
        'email'    => $email,
        'telefono' => $telefono !== '' ? $telefono : null,
        'id'       => $id_usuario,
    ]);

    // Actualizar datos de sesión
    $_SESSION['usuario_nombre'] = $nombre . ($apellido !== '' ? ' ' . $apellido : '');
    $_SESSION['usuario_email']  = $email;

    $_SESSION['exito_perfil'] = 'Tus datos personales se actualizaron con éxito.';
} catch (PDOException $e) {
    error_log("Error al actualizar datos de usuario: " . $e->getMessage());
    $_SESSION['error_perfil'] = 'Ocurrió un error al guardar los cambios. Intentá nuevamente.';
}

header('Location: ../../views/usuario.php');
exit;

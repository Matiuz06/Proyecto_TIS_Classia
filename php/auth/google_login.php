<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !isset($_GET['email'])) {
    header('Location: ../../views/login-google.php');
    exit;
}

$email = trim($_POST['email'] ?? $_GET['email'] ?? '');
$nombre = trim($_POST['nombre'] ?? $_GET['nombre'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['google_error'] = 'Por favor ingresá una cuenta de Google válida.';
    header('Location: ../../views/login-google.php');
    exit;
}

try {
    // 1. Comprobar si el correo ya existe en la BD para EVITAR duplicados
    $stmt = $pdo->prepare("
        SELECT id_usuario, nombre, apellido, email, id_rol, foto_perfil
        FROM usuarios
        WHERE email = :email
        LIMIT 1
    ");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch();

    if ($usuario) {
        // El usuario ya existe -> Iniciar sesión directamente sin duplicar
        establecer_usuario_sesion(
            (int) $usuario['id_usuario'],
            $usuario['nombre'] . (!empty($usuario['apellido']) ? ' ' . $usuario['apellido'] : ''),
            $usuario['email'],
            (int) $usuario['id_rol'],
            $usuario['foto_perfil'] ?? null
        );
    } else {
        // El usuario no existe -> Registrar nuevo usuario
        $partesNombre = explode('@', $email)[0];
        $primerNombre = !empty($nombre) ? $nombre : ucfirst($partesNombre);
        $apellido = 'Google';
        $passwordAleatoria = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);

        $stmt_insert = $pdo->prepare("
            INSERT INTO usuarios (nombre, apellido, email, password_hash, id_rol, fecha_registro)
            VALUES (:nombre, :apellido, :email, :pass, :rol, CURRENT_TIMESTAMP)
        ");
        $stmt_insert->execute([
            'nombre'   => $primerNombre,
            'apellido' => $apellido,
            'email'    => $email,
            'pass'     => $passwordAleatoria,
            'rol'      => ROL_ESTUDIANTE,
        ]);

        $nuevo_id = (int) $pdo->lastInsertId();

        establecer_usuario_sesion(
            $nuevo_id,
            $primerNombre . ' ' . $apellido,
            $email,
            ROL_ESTUDIANTE,
            null
        );
    }

    // Redirección según rol
    $rol_actual = (int) ($_SESSION['usuario']['id_rol'] ?? ROL_ESTUDIANTE);
    if ($rol_actual === ROL_ADMIN) {
        header('Location: ../../views/panel-administrador.php');
    } elseif ($rol_actual === ROL_DOCENTE) {
        header('Location: ../../views/panel-proveedor.php');
    } else {
        header('Location: ../../views/usuario.php');
    }
    exit;
} catch (PDOException $e) {
    error_log("Error en google_login: " . $e->getMessage());
    $_SESSION['google_error'] = 'Ocurrió un error al procesar el inicio con Google.';
    header('Location: ../../views/login-google.php');
    exit;
}

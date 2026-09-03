<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $contrasena = $_POST['contrasenia'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasenia'] ?? '';
    $token_recibido = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $errores[] = "La sesión del formulario expiró. Por favor, recargá la página e intentá nuevamente.";
    }

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($contrasena) || empty($confirmar_contrasena)) {
        $errores[] = "Todos los campos son obligatorios.";
    }
    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Ingresá un correo electrónico válido.";
    }

    if (!empty($contrasena)) {
        if (strlen($contrasena) < 8) {
            $errores[] = "La contraseña debe tener al menos 8 caracteres.";
        } elseif (!preg_match('/[A-Za-z]/', $contrasena) || !preg_match('/[0-9]/', $contrasena)) {
            $errores[] = "La contraseña debe incluir al menos una letra y un número.";
        }

        if ($contrasena !== $confirmar_contrasena) {
            $errores[] = "Las contraseñas no coinciden.";
        }
    }

    if (empty($errores)) {
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $correo]);
        if ($stmt->fetch()) {
            $errores[] = "No es posible registrar este correo electrónico.";
        }
    }

    if (empty($errores)) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $id_rol_cliente = 1;

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, email, password_hash, id_rol) VALUES (:nombre, :apellido, :email, :password_hash, :id_rol)");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'email' => $correo,
                'password_hash' => $hash,
                'id_rol' => $id_rol_cliente
            ]);

            session_regenerate_id(true);
            header("Location: login.php?registro=exitoso");
            exit;
        } catch (PDOException $e) {
            error_log("Error SQL en registro: " . $e->getMessage());
            $errores[] = "No se pudo completar el registro en este momento. Por favor, intentá nuevamente más tarde.";
        }
    }
}

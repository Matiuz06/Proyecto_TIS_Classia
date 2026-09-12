<?php

require_once __DIR__ . '/session.php';
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

if (esta_autenticado()) {
    $usr = usuario_actual();
    if ($usr['id_rol'] === 3) {
        header("Location: panel-administrador.php");
    } elseif ($usr['id_rol'] === 2) {
        header("Location: panel-proveedor.php");
    } else {
        header("Location: usuario.php");
    }
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $contrasena = $_POST['contrasena'] ?? ($_POST['contraseña'] ?? ($_POST['contrasenia'] ?? ''));

    if (empty($correo) || empty($contrasena)) {
        $errores[] = "Por favor, ingresá tu correo electrónico y contraseña.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id_usuario, nombre, apellido, email, password_hash, id_rol, email_verificado FROM usuarios WHERE email = :email LIMIT 1");
            $stmt->execute(['email' => $correo]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($contrasena, $usuario['password_hash'])) {
                if (!(int) $usuario['email_verificado']) {
                    $errores[] = "Confirmá tu correo electrónico antes de iniciar sesión.";
                }
            }

            if (!$errores && $usuario && password_verify($contrasena, $usuario['password_hash'])) {
                establecer_usuario_sesion(
                    (int)$usuario['id_usuario'],
                    $usuario['nombre'] . ' ' . $usuario['apellido'],
                    $usuario['email'],
                    (int)$usuario['id_rol']
                );
                $_SESSION['id_usuario'] = (int)$usuario['id_usuario'];

                if ((int)$usuario['id_rol'] === 3) {
                    header("Location: panel-administrador.php");
                } elseif ((int)$usuario['id_rol'] === 2) {
                    header("Location: panel-proveedor.php");
                } else {
                    header("Location: usuario.php");
                }
                exit;
            } elseif (!$usuario || !password_verify($contrasena, $usuario['password_hash'])) {
                $errores[] = "El correo electrónico o la contraseña ingresada son incorrectos.";
            }
        } catch (PDOException $e) {
            error_log("Error SQL en login: " . $e->getMessage());
            $errores[] = "Error al intentar iniciar sesión. Por favor, intentá nuevamente en unos momentos.";
        }
    }
}

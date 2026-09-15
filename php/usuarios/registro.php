<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../auth/password_policy.php';
require_once __DIR__ . '/../utils/mailer.php';
require_once __DIR__ . '/../utils/cedula_uy.php';
require_once __DIR__ . '/../utils/recaptcha.php';

if (esta_autenticado()) {
    header('Location: ../../views/usuario.php');
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $cedula = trim($_POST['cedula_identidad'] ?? '');
    $correo = strtolower(trim($_POST['correo'] ?? ''));
    $usuario = trim($_POST['usuario'] ?? '');
    $genero = trim($_POST['genero'] ?? 'sin-especificar');
    $contrasena = $_POST['contrasenia'] ?? '';
    $confirmar_contrasena = $_POST['confirmar_contrasenia'] ?? '';
    $token_recibido = $_POST['csrf_token'] ?? '';
    $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $errores[] = "La sesión del formulario expiró. Por favor, recargá la página e intentá nuevamente.";
    }

    $res_recaptcha = verificar_recaptcha($recaptcha_response);
    if (!$res_recaptcha['exito']) {
        $errores[] = $res_recaptcha['mensaje'];
    }

    if (empty($nombre) || empty($apellido) || empty($cedula) || empty($usuario) || empty($correo) || empty($contrasena) || empty($confirmar_contrasena)) {
        $errores[] = "Todos los campos son obligatorios.";
    }

    if (!empty($cedula)) {
        if (!validar_cedula_uruguaya($cedula)) {
            $errores[] = "La Cédula de Identidad ingresada no es válida (verificá el número y el dígito verificador).";
        }
    }

    if (!empty($correo) && !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $errores[] = "Ingresá un correo electrónico válido.";
    }
    if ($usuario !== '' && !preg_match('/^[A-Za-z0-9_.-]{3,30}$/', $usuario)) {
        $errores[] = "El nombre de usuario debe tener entre 3 y 30 caracteres y solo puede incluir letras, números, puntos, guiones y guiones bajos.";
    }

    if (!empty($contrasena)) {
        $errores_password = validar_contrasena($contrasena);
        foreach ($errores_password as $error_password) {
            $errores[] = $error_password;
        }

        if ($contrasena !== $confirmar_contrasena) {
            $errores[] = "Las contraseñas no coinciden.";
        }
    }

    if (empty($errores)) {
        $ci_limpia = limpiar_ci($cedula);
        $stmt = $pdo->prepare("SELECT id_usuario FROM usuarios WHERE email = :email OR nombre_usuario = :nombre_usuario OR cedula_identidad = :ci");
        $stmt->execute([
            'email' => $correo,
            'nombre_usuario' => $usuario,
            'ci' => $ci_limpia
        ]);
        if ($stmt->fetch()) {
            $errores[] = "El correo electrónico, nombre de usuario o Cédula de Identidad ya se encuentran registrados.";
        }
    }

    if (empty($errores)) {
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $id_rol_cliente = 1;
        $token_verificacion = bin2hex(random_bytes(32));
        $config_correo = cargar_configuracion_correo();
        $ci_guardar = limpiar_ci($cedula);

        try {
            $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, apellido, cedula_identidad, nombre_usuario, genero, email, password_hash, id_rol, email_verificado, email_verificacion_token, email_verificacion_expira) VALUES (:nombre, :apellido, :cedula, :nombre_usuario, :genero, :email, :password_hash, :id_rol, 0, :token, DATE_ADD(NOW(), INTERVAL 24 HOUR))");
            $stmt->execute([
                'nombre' => $nombre,
                'apellido' => $apellido,
                'cedula' => $ci_guardar,
                'nombre_usuario' => $usuario,
                'genero' => $genero,
                'email' => $correo,
                'password_hash' => $hash,
                'id_rol' => $id_rol_cliente,
                'token' => hash('sha256', $token_verificacion)
            ]);

            $enlace = $config_correo['base_url'] . '/php/auth/confirmar_correo.php?token=' . urlencode($token_verificacion);
            $correo_enviado = enviar_correo(
                $correo,
                'Confirmá tu correo electrónico - Classia',
                plantilla_correo('Confirmá tu cuenta de Classia', '<p>Hola ' . htmlspecialchars($nombre) . ',</p><p>Para activar tu cuenta, confirmá tu correo desde el siguiente enlace:</p><p><a href="' . htmlspecialchars($enlace) . '">Confirmar mi correo</a></p><p>El enlace vence en 24 horas.</p>')
            );

            establecer_usuario_sesion((int) $pdo->lastInsertId(), $nombre . ' ' . $apellido, $correo, $id_rol_cliente);
            $_SESSION['registro_correo_enviado'] = $correo_enviado;
            header("Location: ../views/confirmar-correo.php?resultado=pendiente");
            exit;
        } catch (PDOException $e) {
            error_log("Error SQL en registro: " . $e->getMessage());
            $errores[] = "No se pudo completar el registro en este momento. Por favor, intentá nuevamente más tarde.";
        }
    }
}

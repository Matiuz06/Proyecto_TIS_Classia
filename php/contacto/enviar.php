<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../utils/mailer.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores = [];
$enviado = false;
$nombre = trim($_POST['nombre'] ?? '');
$email = strtolower(trim($_POST['email'] ?? ''));
$asunto = trim($_POST['asunto'] ?? '');
$mensaje = trim($_POST['mensaje'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        $errores[] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    }
    if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
        $errores[] = 'Completá todos los campos obligatorios.';
    }
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresá un correo electrónico válido.';
    }
    if (mb_strlen($mensaje) > 5000) {
        $errores[] = 'El mensaje no puede superar los 5000 caracteres.';
    }

    if (!$errores) {
        $config = cargar_configuracion_correo();
        $contenido = plantilla_correo('Nueva consulta de contacto',
            '<p><strong>Nombre:</strong> ' . htmlspecialchars($nombre) . '</p>'
            . '<p><strong>Correo:</strong> ' . htmlspecialchars($email) . '</p>'
            . '<p><strong>Asunto:</strong> ' . htmlspecialchars($asunto) . '</p>'
            . '<p>' . nl2br(htmlspecialchars($mensaje)) . '</p>'
        );
        $respuesta = plantilla_correo('Recibimos tu consulta',
            '<p>Gracias por escribirnos. El equipo de Classia recibió tu mensaje y responderá dentro de 2 días hábiles.</p>'
        );

        $enviado = enviar_correo($config['to'], '[Classia] ' . $asunto, $contenido)
            && enviar_correo($email, 'Recibimos tu consulta - Classia', $respuesta);
        if (!$enviado) {
            $errores[] = 'No se pudo enviar el mensaje. Intentá nuevamente más tarde.';
        }
    }
}

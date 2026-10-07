<?php

/**
 * Responsabilidad: Actualiza datos personales del usuario y gestiona cambios de correo.
 */

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/cedula_uy.php';
require_once __DIR__ . '/../utils/mailer.php';

iniciar_sesion();
requerir_autenticacion('../../views/login.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/editar-perfil.php');
    exit;
}

$token_recibido = $_POST['csrf_token'] ?? '';
if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
    $_SESSION['error_perfil'] = 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.';
    header('Location: ../../views/editar-perfil.php');
    exit;
}

$usuario     = usuario_actual();
$id_usuario  = (int) $usuario['id_usuario'];
$COOLDOWN_DIAS = 30;

// ─── Cargar datos actuales del usuario ───────────────────────────────────────
$stmt_cur = $pdo->prepare('SELECT email, telefono, cedula_identidad, datos_cambiados_en, nuevo_email_pendiente FROM usuarios WHERE id_usuario = :id LIMIT 1');
$stmt_cur->execute(['id' => $id_usuario]);
$actual = $stmt_cur->fetch();

if (!$actual) {
    $_SESSION['error_perfil'] = 'No se encontró la cuenta. Intentá nuevamente.';
    header('Location: ../../views/editar-perfil.php');
    exit;
}

// ─── Determinar estado de cooldown ───────────────────────────────────────────
$cooldown_activo = false;
if (!empty($actual['datos_cambiados_en'])) {
    $diff = (new DateTime())->diff(new DateTime($actual['datos_cambiados_en']));
    if ((int) $diff->days < $COOLDOWN_DIAS) {
        $cooldown_activo = true;
    }
}

// ─── Datos básicos (nombre, apellido — sin cooldown) ─────────────────────────
$nombre   = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');

if ($nombre === '') {
    $_SESSION['error_perfil'] = 'El nombre es un campo obligatorio.';
    header('Location: ../../views/editar-perfil.php');
    exit;
}

// ─── CI (solo si el usuario no tiene una) ────────────────────────────────────
$tiene_cedula = !empty($actual['cedula_identidad']);
$cedula_input = trim($_POST['cedula_identidad'] ?? '');
if (!$tiene_cedula && $cedula_input !== '') {
    if (!validar_cedula_uruguaya($cedula_input)) {
        $_SESSION['error_perfil'] = 'La Cédula de Identidad ingresada no es válida.';
        header('Location: ../../views/editar-perfil.php');
        exit;
    }
    $ci_limpia = limpiar_ci($cedula_input);
    $ci_hash   = hash_ci($ci_limpia);
    $stmt_ci   = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE (cedula_hash = :h OR cedula_identidad = :p) AND id_usuario != :id LIMIT 1');
    $stmt_ci->execute(['h' => $ci_hash, 'p' => $ci_limpia, 'id' => $id_usuario]);
    if ($stmt_ci->fetch()) {
        $_SESSION['error_perfil'] = 'La Cédula de Identidad ya está registrada por otro usuario.';
        header('Location: ../../views/editar-perfil.php');
        exit;
    }
    $ci_cifrada = encriptar_ci($cedula_input);
}

// ─── Email y teléfono (con cooldown) ─────────────────────────────────────────
$email    = strtolower(trim($_POST['email'] ?? ''));
$telefono = trim($_POST['telefono'] ?? '');

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error_perfil'] = 'El correo electrónico ingresado no tiene un formato válido.';
    header('Location: ../../views/editar-perfil.php');
    exit;
}

if ($telefono !== '' && !preg_match('/^[0-9+\-\s()]{6,30}$/', $telefono)) {
    $_SESSION['error_perfil'] = 'El número de teléfono tiene un formato inválido.';
    header('Location: ../../views/editar-perfil.php');
    exit;
}

$email_cambio    = ($email !== $actual['email']);
$telefono_cambio = ($telefono !== ($actual['telefono'] ?? ''));
$hay_cambio_contacto = $email_cambio || $telefono_cambio;

if ($hay_cambio_contacto && $cooldown_activo) {
    $_SESSION['error_perfil'] = 'No podés cambiar el email ni el teléfono aún. El período de espera de 30 días no ha finalizado.';
    header('Location: ../../views/editar-perfil.php');
    exit;
}

try {
    $pdo->beginTransaction();

    // Guardar nombre, apellido (siempre) + CI (si no tenía)
    if (!$tiene_cedula && !empty($ci_cifrada)) {
        $pdo->prepare('UPDATE usuarios SET nombre = :n, apellido = :a, cedula_identidad = :ci, cedula_hash = :h WHERE id_usuario = :id AND cedula_identidad IS NULL')
            ->execute(['n' => $nombre, 'a' => $apellido, 'ci' => $ci_cifrada, 'h' => $ci_hash, 'id' => $id_usuario]);
    } else {
        $pdo->prepare('UPDATE usuarios SET nombre = :n, apellido = :a WHERE id_usuario = :id')
            ->execute(['n' => $nombre, 'a' => $apellido, 'id' => $id_usuario]);
    }

    // Teléfono (si cambió y no hay cooldown)
    if ($telefono_cambio && !$cooldown_activo) {
        $pdo->prepare('UPDATE usuarios SET telefono = :t, datos_cambiados_en = NOW() WHERE id_usuario = :id')
            ->execute(['t' => $telefono !== '' ? $telefono : null, 'id' => $id_usuario]);
    }

    // Email (si cambió y no hay cooldown — enviar verificación)
    if ($email_cambio && !$cooldown_activo) {
        // Verificar que no esté en uso
        $stmt_check = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email AND id_usuario != :id LIMIT 1');
        $stmt_check->execute(['email' => $email, 'id' => $id_usuario]);
        if ($stmt_check->fetch()) {
            $pdo->rollBack();
            $_SESSION['error_perfil'] = 'El correo electrónico ya está registrado por otro usuario.';
            header('Location: ../../views/editar-perfil.php');
            exit;
        }

        $token_nuevo_email  = bin2hex(random_bytes(32));
        $token_hash         = hash('sha256', $token_nuevo_email);
        $expira             = date('Y-m-d H:i:s', time() + 86400); // 24 horas

        $pdo->prepare('UPDATE usuarios SET nuevo_email_pendiente = :e, nuevo_email_token = :t, nuevo_email_expira = :x, datos_cambiados_en = NOW() WHERE id_usuario = :id')
            ->execute(['e' => $email, 't' => $token_hash, 'x' => $expira, 'id' => $id_usuario]);

        // Enviar correo de verificación al nuevo email
        $config = cargar_configuracion_correo();
        $enlace = rtrim($config['base_url'], '/') . '/php/auth/confirmar_nuevo_email.php?token=' . rawurlencode($token_nuevo_email);
        $contenido = plantilla_correo(
            'Confirmá tu nuevo email en Classia',
            '<p>Recibimos una solicitud para cambiar el email de tu cuenta de Classia.</p>'
            . '<p>Hacé clic en el siguiente enlace para confirmar <strong>' . htmlspecialchars($email, ENT_QUOTES, 'UTF-8') . '</strong> como tu nuevo email:</p>'
            . '<p><a href="' . htmlspecialchars($enlace, ENT_QUOTES, 'UTF-8') . '">Confirmar nuevo email</a></p>'
            . '<p>Este enlace vence en 24 horas. Si no solicitaste este cambio, podés ignorar este mensaje.</p>'
        );
        enviar_correo($email, 'Confirmá tu nuevo email — Classia', $contenido);

        $pdo->commit();
        $_SESSION['exito_perfil'] = 'Se envió un enlace de confirmación a ' . htmlspecialchars($email) . '. Revisá tu bandeja de entrada.';
        // Actualizar sesión con nombre/apellido
        $_SESSION['usuario']['nombre'] = $nombre;
        header('Location: ../../views/editar-perfil.php');
        exit;
    }

    $pdo->commit();

    // Actualizar sesión
    $_SESSION['usuario']['nombre'] = $nombre;
    if (!$email_cambio) {
        $_SESSION['usuario']['email'] = $email;
    }

    $_SESSION['exito_perfil'] = 'Tus datos personales se actualizaron con éxito.';
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Error al actualizar datos de usuario: ' . $e->getMessage());
    $_SESSION['error_perfil'] = 'Ocurrió un error al guardar los cambios. Intentá nuevamente.';
}

header('Location: ../../views/editar-perfil.php');
exit;




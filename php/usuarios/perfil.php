<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../valoraciones/obtener_contrataciones_valorables.php';

requerir_autenticacion('login.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$usuario    = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$rol_actual = nombre_rol((int) $usuario['id_rol']);

$mensaje_acceso = $_SESSION['mensaje_acceso'] ?? '';
unset($_SESSION['mensaje_acceso']);

$mensaje_error = $_SESSION['error_perfil'] ?? '';
unset($_SESSION['error_perfil']);

$mensaje_exito = $_SESSION['exito_perfil'] ?? '';
unset($_SESSION['exito_perfil']);

if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'valoracion_guardada') {
    $mensaje_exito = '¡Tu valoración fue registrada correctamente! Gracias por compartir tu opinión.';
}

try {
    $stmt_user = $pdo->prepare("SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = :id");
    $stmt_user->execute(['id' => $id_usuario]);
    $userData = $stmt_user->fetch() ?: $usuario;

    if ($userData) {
        if (!empty($userData['foto_perfil']) && ($usuario['foto_perfil'] ?? '') !== $userData['foto_perfil']) {
            actualizar_foto_sesion($userData['foto_perfil']);
        }
        if (isset($userData['id_rol']) && (int) $userData['id_rol'] !== (int) ($usuario['id_rol'] ?? 0)) {
            $_SESSION['usuario']['id_rol'] = (int) $userData['id_rol'];
            $rol_actual = $userData['nombre_rol'] ?? nombre_rol((int) $userData['id_rol']);
        }
    }

    $resumen_contrataciones = obtener_resumen_contrataciones_usuario($pdo, $id_usuario);
    $cursos_contratados     = $resumen_contrataciones['cursos'];
    $servicios_contratados  = $resumen_contrataciones['servicios'];
} catch (PDOException $e) {
    error_log("Error al consultar perfil de usuario: " . $e->getMessage());
    $userData = $usuario;
    $cursos_contratados = [];
    $servicios_contratados = [];
}

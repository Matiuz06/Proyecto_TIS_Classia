<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../valoraciones/obtener_contrataciones_valorables.php';

requerir_autenticacion('login.php');

$usuario    = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$rol_actual = nombre_rol((int) $usuario['id_rol']);

$mensaje_acceso = $_SESSION['mensaje_acceso'] ?? '';
unset($_SESSION['mensaje_acceso']);

$mensaje_exito = '';
if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'valoracion_guardada') {
    $mensaje_exito = '¡Tu valoración fue registrada correctamente! Gracias por compartir tu opinión.';
}

try {
    $stmt_user = $pdo->prepare("SELECT u.*, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = :id");
    $stmt_user->execute(['id' => $id_usuario]);
    $userData = $stmt_user->fetch() ?: $usuario;

    $resumen_contrataciones = obtener_resumen_contrataciones_usuario($pdo, $id_usuario);
    $cursos_contratados     = $resumen_contrataciones['cursos'];
    $servicios_contratados  = $resumen_contrataciones['servicios'];
} catch (PDOException $e) {
    error_log("Error al consultar perfil de usuario: " . $e->getMessage());
    $userData = $usuario;
    $cursos_contratados = [];
    $servicios_contratados = [];
}

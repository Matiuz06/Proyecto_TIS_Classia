<?php

/**
 * Responsabilidad: Cierra la sesión actual y redirige al login.
 */

require_once __DIR__ . "/sesion.php";
require_once __DIR__ . "/../utils/auditoria.php";
require_once __DIR__ . "/../utils/toast.php";

$uid = id_usuario_actual();
if ($uid > 0) {
    registrar_auditoria('LOGOUT', 'Usuario cerró sesión', 'INFO', null, $uid);
}

cerrar_sesion();
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
iniciar_sesion();
set_toast('info', 'Sesion cerrada');

header("Location: ../../index.php");
exit;

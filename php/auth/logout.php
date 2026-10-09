<?php

/**
 * Responsabilidad: Cierra la sesión actual y redirige al login.
 */

require_once __DIR__ . "/sesion.php";
require_once __DIR__ . "/../utils/auditoria.php";

$uid = id_usuario_actual();
if ($uid > 0) {
    registrar_auditoria('LOGOUT', 'Usuario cerró sesión', 'INFO', null, $uid);
}

cerrar_sesion();

header("Location: ../../index.php");
exit;

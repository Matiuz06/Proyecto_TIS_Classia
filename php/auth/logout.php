<?php

/**
 * Responsabilidad: Cierra la sesión actual y redirige al login.
 */

require_once __DIR__ . "/sesion.php";
require_once __DIR__ . "/../utils/toast.php";

cerrar_sesion();
if (session_status() === PHP_SESSION_ACTIVE) {
    session_write_close();
}
iniciar_sesion();
set_toast('info', 'Sesion cerrada');

header("Location: ../../index.php");
exit;

<?php

/**
 * Responsabilidad: Cierra la sesión actual y redirige al login.
 */

require_once __DIR__ . "/sesion.php";

cerrar_sesion();

header("Location: ../../index.php");
exit;

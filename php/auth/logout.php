<?php

require_once __DIR__ . "/session.php";

cerrar_sesion();

header("Location: ../../index.php");
exit;

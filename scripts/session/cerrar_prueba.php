<?php

require_once __DIR__ . "/../../php/auth/session.php";

cerrar_sesion();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Prueba de sesion - cerrar</title>
</head>
<body>
  <h1>Prueba de desarrollo: sesion cerrada</h1>
  <p>La sesion de prueba fue destruida.</p>
  <p><a href="verificar_prueba.php">Comprobar que ya no hay usuario</a></p>
</body>
</html>

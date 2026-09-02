<?php

require_once __DIR__ . "/../../php/auth/session.php";

establecer_usuario_sesion(
    1,
    "Usuario de prueba",
    "prueba@classia.local",
    1
);

$usuario = usuario_actual();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Prueba de sesion - iniciar</title>
</head>
<body>
  <h1>Prueba de desarrollo: sesion iniciada</h1>
  <p>Usuario ficticio guardado en <code>$_SESSION["usuario"]</code>.</p>
  <pre><?php echo htmlspecialchars(print_r($usuario, true), ENT_QUOTES, "UTF-8"); ?></pre>
  <p><a href="verificar_prueba.php">Verificar persistencia</a></p>
</body>
</html>

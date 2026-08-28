<?php

require_once __DIR__ . "/../../php/auth/session.php";

$usuario = usuario_actual();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Prueba de sesion - verificar</title>
</head>
<body>
  <h1>Prueba de desarrollo: verificar sesion</h1>

  <?php if ($usuario !== null): ?>
    <p>La sesion persiste y hay un usuario autenticado.</p>
    <pre><?php echo htmlspecialchars(print_r($usuario, true), ENT_QUOTES, "UTF-8"); ?></pre>
    <p><a href="cerrar_prueba.php">Cerrar sesion de prueba</a></p>
  <?php else: ?>
    <p>No hay usuario autenticado en sesion.</p>
    <p><a href="iniciar_prueba.php">Iniciar prueba</a></p>
  <?php endif; ?>
</body>
</html>

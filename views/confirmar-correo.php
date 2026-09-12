<?php

$resultado = $_GET['resultado'] ?? 'invalido';
$mensaje = $resultado === 'confirmado'
    ? 'Tu correo fue confirmado. Ya podés iniciar sesión.'
    : 'El enlace de confirmación no es válido o ya fue utilizado.';

$title = 'Confirmar correo — Classia';
$description = 'Confirmación de correo electrónico de Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>
<main class="auth-shell">
  <section class="auth-card" aria-labelledby="titulo-confirmacion">
    <h1 id="titulo-confirmacion"><?= htmlspecialchars($mensaje) ?></h1>
    <p><a href="login.php">Ir a iniciar sesión</a></p>
  </section>
</main>
<?php include '../includes/footer.php'; ?>
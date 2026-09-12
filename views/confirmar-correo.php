<?php

$resultado = $_GET['resultado'] ?? 'invalido';
$mensaje = match ($resultado) {
  'pendiente' => 'Revisá tu correo electrónico para confirmar tu cuenta antes de continuar.',
  'confirmado' => 'Tu correo fue confirmado. Ya podés continuar con la configuración inicial.',
  default => 'El enlace de confirmación no es válido o ya fue utilizado.',
};

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
    <?php if ($resultado === 'confirmado'): ?>
      <p><a href="primeros-pasos.php">Continuar con primeros pasos</a></p>
    <?php else: ?>
      <p><a href="login.php">Ir a iniciar sesión</a></p>
    <?php endif; ?>
  </section>
</main>
<?php include '../includes/footer.php'; ?>
<?php

/**
 * Responsabilidad: Vista de error 403 (Prohibido / Acceso denegado por restricciones de autorización).
 */

http_response_code(403);

require_once '../php/auth/sesion.php';
iniciar_sesion();

$title = 'Acceso denegado';
$description = 'No tenes permisos para acceder a esta seccion de Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$publicBase = rtrim(dirname(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/views/error-403.php'))), '/');
if ($publicBase === '' || $publicBase === '.') {
    $publicBase = '';
}
$skipOnboardingGuard = true;
$activePage = '';
$mostrarLogin = !esta_autenticado();
include '../includes/header.php';
?>

<main id="main-content" class="card-container motion-entry" aria-labelledby="error-title" aria-describedby="error-description">
  <section class="empty-state">
    <span class="badge" aria-label="Codigo de error">Error 403</span>
    <h1 id="error-title">Acceso denegado</h1>
    <p id="error-description">No tenes permisos suficientes para acceder a esta pagina.</p>
    <div class="empty-state-actions">
      <a class="btn" href="<?= $publicBase ?>/index.php">Volver al inicio</a>
      <?php if ($mostrarLogin): ?>
        <a class="btn btn-secondary" href="<?= $publicBase ?>/views/login.php">Iniciar sesion</a>
      <?php else: ?>
        <a class="btn btn-secondary" href="<?= $publicBase ?>/views/catalogo.php">Explorar catalogo</a>
      <?php endif; ?>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

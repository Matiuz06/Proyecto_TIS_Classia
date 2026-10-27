<?php

/**
 * Responsabilidad: Vista de error 500 (Falla o excepción no controlada en el servidor).
 */

http_response_code(500);

$title = 'Error interno';
$description = 'Ocurrio un problema interno en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$publicBase = rtrim(dirname(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/views/error-500.php'))), '/');
if ($publicBase === '' || $publicBase === '.') {
    $publicBase = '';
}
$skipOnboardingGuard = true;
$activePage = '';
include '../includes/header.php';
?>

<main id="main-content" class="card-container motion-entry" aria-labelledby="error-title" aria-describedby="error-description">
  <section class="empty-state">
    <span class="badge" aria-label="Codigo de error">Error 500</span>
    <h1 id="error-title">Error interno del servidor</h1>
    <p id="error-description">Ocurrio un problema inesperado. Intenta nuevamente en unos minutos.</p>
    <div class="empty-state-actions">
      <a class="btn" href="<?= $publicBase ?>/index.php">Volver al inicio</a>
      <a class="btn btn-secondary" href="<?= $publicBase ?>/views/contacto.php">Contactar soporte</a>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

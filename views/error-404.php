<?php

/**
 * Responsabilidad: Vista de error 404 (Página o recurso no encontrado en el servidor).
 */

http_response_code(404);

$title = 'Pagina no encontrada';
$description = 'La pagina solicitada no existe o fue movida en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$publicBase = rtrim(dirname(dirname(str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/views/error-404.php'))), '/');
if ($publicBase === '' || $publicBase === '.') {
    $publicBase = '';
}
$skipOnboardingGuard = true;
$activePage = '';
include '../includes/header.php';
?>

<main id="main-content" class="card-container motion-entry" aria-labelledby="error-title" aria-describedby="error-description">
  <section class="empty-state">
    <span class="badge" aria-label="Codigo de error">Error 404</span>
    <h1 id="error-title">Pagina no encontrada</h1>
    <p id="error-description">La direccion que intentaste abrir no existe, fue movida o ya no esta disponible.</p>
    <div class="empty-state-actions">
      <a class="btn" href="<?= $publicBase ?>/index.php">Volver al inicio</a>
      <a class="btn btn-secondary" href="<?= $publicBase ?>/views/catalogo.php">Explorar catalogo</a>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

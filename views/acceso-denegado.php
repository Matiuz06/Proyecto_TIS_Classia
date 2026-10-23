<?php

/**
 * Responsabilidad: Vista informativa de acceso denegado por restricciones de rol o permisos.
 */

require_once '../php/auth/sesion.php';

requerir_autenticacion('login.php');
http_response_code(403);

$title = 'Acceso denegado';
$description = 'Aviso de permisos insuficientes en Classia.';
$cssPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>

<main id="main-content" class="card-container motion-entry" aria-labelledby="error-title" aria-describedby="error-description">
  <section class="empty-state">
    <span class="badge" aria-label="Codigo de error">Error 403</span>
    <h1 id="error-title">Acceso denegado</h1>
    <p id="error-description">La cuenta con la que iniciaste sesion no tiene autorizacion para utilizar esta seccion.</p>
    <div class="empty-state-actions">
      <a class="btn" href="../index.php">Volver al inicio</a>
      <a class="btn btn-secondary" href="catalogo.php">Explorar catalogo</a>
    </div>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

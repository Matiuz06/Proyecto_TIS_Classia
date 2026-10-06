<?php

require_once '../config/database.php';
require_once '../php/certificados/CursoProgresoRepository.php';

$codigo = strtoupper(trim((string)($_GET['codigo'] ?? '')));
$certificado = null;
if ($codigo !== '') {
    $certificado = (new CursoProgresoRepository($pdo))->obtenerCertificadoPublico($codigo);
}

$title = 'Verificar certificado - Classia';
$description = 'Verificacion publica de certificados emitidos por Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'catalogo';
include '../includes/header.php';
?>

<main id="main-content" class="product-detail-container motion-entry">
  <section class="certificate-verify-card" aria-labelledby="verificar-certificado">
    <h1 id="verificar-certificado">Verificar certificado</h1>
    <form method="GET" class="certificate-verify-form">
      <label>
        Codigo de verificacion
        <input name="codigo" value="<?= htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8') ?>" placeholder="XXXXXXXXXXXX">
      </label>
      <button class="btn btn-primary-action" type="submit">Verificar</button>
    </form>

    <?php if ($codigo === ''): ?>
      <p class="text-muted">Ingresá el codigo indicado en el certificado.</p>
    <?php elseif (!$certificado): ?>
      <div class="alert alert-danger">Certificado no valido.</div>
    <?php else: ?>
      <div class="certificate-valid">
        <span class="badge badge-success-soft">Certificado valido</span>
        <dl>
          <div><dt>Alumno</dt><dd><?= htmlspecialchars(trim(($certificado['nombre'] ?? '') . ' ' . ($certificado['apellido'] ?? '')), ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Curso</dt><dd><?= htmlspecialchars($certificado['curso_titulo'] ?? '', ENT_QUOTES, 'UTF-8') ?></dd></div>
          <div><dt>Fecha</dt><dd><?= !empty($certificado['fecha_emision']) ? date('d/m/Y', strtotime((string)$certificado['fecha_emision'])) : '-' ?></dd></div>
          <div><dt>Porcentaje</dt><dd><?= htmlspecialchars(rtrim(rtrim(number_format((float)$certificado['porcentaje_aprobacion'], 2, '.', ''), '0'), '.'), ENT_QUOTES, 'UTF-8') ?>%</dd></div>
          <div><dt>Codigo</dt><dd><?= htmlspecialchars($certificado['codigo_verificacion'] ?? '', ENT_QUOTES, 'UTF-8') ?></dd></div>
        </dl>
      </div>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

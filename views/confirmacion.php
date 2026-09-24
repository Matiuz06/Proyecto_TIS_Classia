<?php

/**
 * Responsabilidad: Confirmación de contratación o matriculación exitosa.
 */

$contratacion = [];
$detalles = [];
$error = '';
require_once '../php/contrataciones/confirmacion.php';

$title       = 'Confirmación de Compra — Classia';
$description = 'Detalle de la contratación y estado de pago en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'carrito';
include '../includes/header.php';
?>

  <main class="card-container confirmation-card motion-entry" aria-labelledby="contratacion-confirmada">
    <?php if ($error !== '' || empty($contratacion)): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <p><a href="catalogo.php" class="btn btn-secondary">Volver al catálogo</a></p>
    <?php else: ?>
      <div class="confirmation-header">
        <span class="confirmation-badge-approved">
          <?= $contratacion['estado'] === 'En Proceso' ? '✓ Pago Aprobado' : htmlspecialchars($contratacion['estado']) ?>
        </span>
        <h1 id="contratacion-confirmada" class="confirmation-title">
          ¡Tu orden fue procesada con éxito!
        </h1>
        <p class="confirmation-subtitle">
          Orden #<?= (int) $contratacion['id_contratacion'] ?> · <?= date('d/m/Y H:i', strtotime($contratacion['fecha_contratacion'])) ?>
        </p>
      </div>

      <div class="alert alert-info u-mb-4">
        📧 Hemos enviado una copia del comprobante de pago a tu correo electrónico registrado.
      </div>

      <section class="confirmation-details-box">
        <h2 class="confirmation-details-title">Publicaciones Contratadas</h2>
        
        <ul class="confirmation-list">
          <?php foreach ($detalles as $detalle): ?>
            <li class="confirmation-list-item">
              <div>
                <strong class="u-text-sm u-block"><?= htmlspecialchars($detalle['titulo']) ?></strong>
                <span class="u-text-xs u-text-muted">
                  <?= htmlspecialchars($detalle['tipo']) ?> · Cantidad: <?= (int) $detalle['cantidad'] ?>
                </span>
              </div>
              <span class="u-font-bold u-text-sm">
                $<?= number_format((float) $detalle['subtotal'], 2, ',', '.') ?>
              </span>
            </li>
          <?php endforeach; ?>
        </ul>

        <div class="confirmation-total-row">
          <strong class="confirmation-total-label">Total Abonado:</strong>
          <strong class="confirmation-total-value">$<?= number_format((float) $contratacion['monto_total'], 2, ',', '.') ?> UYU</strong>
        </div>
      </section>

      <div class="confirmation-actions">
        <a href="comprobante.php?id_contratacion=<?= (int) $contratacion['id_contratacion'] ?>" class="btn btn-primary u-font-bold">
          🧾 Ver / Imprimir Comprobante
        </a>
        <a href="usuario.php" class="btn btn-secondary">
          Ir a mis cursos y servicios
        </a>
      </div>
    <?php endif; ?>
  </main>

<?php include '../includes/footer.php'; ?>

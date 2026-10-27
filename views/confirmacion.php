<?php

/**
 * Responsabilidad: Confirmación de orden de compra y visualización del estado de pago (Mercado Pago).
 */

require_once '../php/contrataciones/confirmacion.php';

$title = 'Confirmación de compra - Classia';
$description = 'Detalle de la contratación y estado de pago en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';

$estadoPago = $mensaje_pago !== '' ? $mensaje_pago : ($pago['estado_pago'] ?? 'Pendiente');
$aprobado = $estadoPago === 'Aprobado' && ($contratacion['estado'] ?? '') === 'En Proceso';
$rechazado = $estadoPago === 'Rechazado';
?>

<main id="main-content" class="card-container confirmation-card motion-entry" aria-labelledby="contratacion-confirmada">
  <?php if ($error !== '' || empty($contratacion)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <p><a href="catalogo.php" class="btn btn-secondary">Volver al catálogo</a></p>
  <?php else: ?>
    <div class="confirmation-header">
      <span class="confirmation-badge-approved <?= $aprobado ? '' : 'confirmation-badge-pending' ?>">
        <?= $aprobado ? 'Pago aprobado' : ($rechazado ? 'Pago rechazado' : 'Pago pendiente') ?>
      </span>
      <h1 id="contratacion-confirmada" class="confirmation-title">
        <?= $aprobado ? '¡Tu orden fue procesada con éxito!' : 'Estado de tu orden' ?>
      </h1>
      <p class="confirmation-subtitle">
        Orden #<?= (int) $contratacion['id_contratacion'] ?> · <?= date('d/m/Y H:i', strtotime($contratacion['fecha_contratacion'])) ?>
      </p>
    </div>

    <div class="alert <?= $aprobado ? 'alert-info' : 'alert-warning' ?> u-mb-4">
      <?php if ($aprobado): ?>
        El pago fue confirmado por Mercado Pago. Tus publicaciones ya están asociadas a tu cuenta.
      <?php elseif ($rechazado): ?>
        Mercado Pago rechazó el pago. Podés revisar la orden e intentar nuevamente.
      <?php else: ?>
        El pago todavía está pendiente de confirmación. No se habilitarán publicaciones hasta recibir la aprobación real.
      <?php endif; ?>
    </div>

    <section class="confirmation-details-box">
      <h2 class="confirmation-details-title">Publicaciones contratadas</h2>
      <ul class="confirmation-list">
        <?php foreach ($detalles as $detalle): ?>
          <li class="confirmation-list-item">
            <div>
              <strong class="u-text-sm u-block"><?= htmlspecialchars($detalle['titulo']) ?></strong>
              <span class="u-text-xs u-text-muted"><?= htmlspecialchars($detalle['tipo']) ?> · Cantidad: <?= (int) $detalle['cantidad'] ?></span>
            </div>
            <span class="u-font-bold u-text-sm">$<?= number_format((float) $detalle['subtotal'], 2, ',', '.') ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="confirmation-total-row">
        <strong class="confirmation-total-label">Total:</strong>
        <strong class="confirmation-total-value">$<?= number_format((float) $contratacion['monto_total'], 2, ',', '.') ?> UYU</strong>
      </div>
    </section>

    <div class="confirmation-actions">
      <?php if ($aprobado): ?>
        <a href="comprobante.php?id_contratacion=<?= (int) $contratacion['id_contratacion'] ?>" class="btn btn-primary u-font-bold">Ver / Imprimir comprobante</a>
      <?php endif; ?>
      <a href="usuario.php" class="btn btn-secondary">Ir a mis cursos y servicios</a>
    </div>
  <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

<?php

/** Responsabilidad: muestra el resumen antes de iniciar Checkout Pro. */

$currentUser = [];
$id_contratacion = 0;
$monto_total = 0.0;
$contratacion = null;
$detalles = [];
$mensaje_error = '';
require_once '../php/pagos/pasarela.php';

$title = 'Pasarela de pago';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

<div class="card-container payment-card motion-entry">
  <span class="brand-mark">Classia</span>
  <h1>Pasarela de Pago Segura</h1>

  <?php if ($mensaje_error !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($mensaje_error) ?></div>
  <?php endif; ?>

  <?php if ($id_contratacion <= 0 || !$contratacion): ?>
    <div class="alert alert-info">
      <p>No hay una orden de compra pendiente seleccionada.</p>
      <p><a href="carrito.php" class="btn mt-section">Ir al Carrito</a></p>
    </div>
  <?php else: ?>
    <div class="checkout-order-summary">
      <h3 class="checkout-order-summary__title">Resumen del pedido (#<?= $id_contratacion ?>)</h3>
      <ul class="checkout-order-summary__list">
        <?php foreach ($detalles as $det): ?>
          <li class="checkout-order-summary__item">
            <span><?= htmlspecialchars($det['titulo']) ?> (<?= htmlspecialchars($det['tipo']) ?>)</span>
            <strong>$<?= number_format((float) $det['subtotal'], 2, ',', '.') ?></strong>
          </li>
        <?php endforeach; ?>
      </ul>
      <div class="checkout-order-summary__total-row">
        <span class="checkout-order-summary__total-label">Total a pagar:</span>
        <span class="checkout-order-summary__total-amount">$<?= number_format($monto_total, 2, ',', '.') ?></span>
      </div>
    </div>

    <form action="../php/pagos/procesar_pago.php" method="POST" id="form-pasarela-pago">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
      <input type="hidden" name="id_contratacion" value="<?= $id_contratacion ?>" />
      <p class="alert alert-info">Serás redirigido a Mercado Pago para completar el pago de forma segura.</p>
      <button type="submit" class="btn btn-full mt-section">Ir a Mercado Pago · $<?= number_format($monto_total, 2, ',', '.') ?></button>
    </form>
  <?php endif; ?>

  <hr />
  <p class="text-center"><a href="carrito.php" class="link">Volver al Carrito</a></p>
  <div class="compliance-icons" aria-label="Logos de cumplimiento">
    <img src="../assets/images/iso-8583.png" alt="ISO 8583" />
    <img src="../assets/images/pci-dss.webp" alt="PCI DSS" />
    <img src="../assets/images/normas-iso.png" alt="ISO 20022" />
  </div>
</div>

<?php include '../includes/footer.php'; ?>

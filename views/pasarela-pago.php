<?php
require_once '../php/auth/roles.php';
require_once '../config/database.php';

requerir_autenticacion('login.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$currentUser = usuario_actual();
$id_usuario = (int) $currentUser['id_usuario'];
$id_contratacion = isset($_GET['id_contratacion']) ? (int) $_GET['id_contratacion'] : 0;
$monto_total = 0.0;
$contratacion = null;
$detalles = [];

if ($id_contratacion > 0) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM contrataciones WHERE id_contratacion = :id AND id_usuario = :user LIMIT 1");
        $stmt->execute(['id' => $id_contratacion, 'user' => $id_usuario]);
        $contratacion = $stmt->fetch();

        if ($contratacion) {
            $monto_total = (float) $contratacion['monto_total'];
            $stmt_det = $pdo->prepare("
                SELECT dc.*, p.titulo, p.tipo 
                FROM detalles_contratacion dc 
                JOIN publicaciones p ON dc.id_publicacion = p.id_publicacion 
                WHERE dc.id_contratacion = :id
            ");
            $stmt_det->execute(['id' => $id_contratacion]);
            $detalles = $stmt_det->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error al consultar contratacion para pago: " . $e->getMessage());
    }
}

$mensaje_error = $_SESSION['pago_error'] ?? '';
unset($_SESSION['pago_error']);

$title     = 'Pasarela de pago';
$cssPrefix = '..';
$jsPrefix  = '..';

$activePage = 'carrito';
include '../includes/header.php';
?>

    <div class="card-container payment-card motion-entry">
      <span class="brand-mark">Classia</span>

      <h1>Pasarela de Pago Segura</h1>

      <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($mensaje_error) ?>
        </div>
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
                <strong>$<?= number_format((float)$det['subtotal'], 2, ',', '.') ?></strong>
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

          <p>
            <label for="email"><strong>Correo electrónico:*</strong></label><br />
            <input
              type="email"
              id="email"
              name="email"
              value="<?= htmlspecialchars($currentUser['email'] ?? '') ?>"
              placeholder="correo@ejemplo.com"
              required />
          </p>

          <p>
            <label for="numero_tarjeta"><strong>Número de tarjeta:*</strong></label><br />
            <input
              type="text"
              id="numero_tarjeta"
              name="numero_tarjeta"
              placeholder="1234 5678 9012 3456"
              pattern="[0-9\s]{13,19}"
              title="De 13 a 19 dígitos numéricos"
              maxlength="19"
              inputmode="numeric"
              autocomplete="cc-number"
              required />
          </p>

          <div class="flex-row">
            <div>
              <label for="fecha_expiracion"><strong>Expiración:*</strong></label><br />
              <input
                type="text"
                id="fecha_expiracion"
                name="fecha_expiracion"
                placeholder="MM/AA"
                pattern="(0[1-9]|1[0-2])\/[0-9]{2}"
                title="Formato MM/AA (Ej: 08/28)"
                maxlength="5"
                inputmode="numeric"
                autocomplete="cc-exp"
                required />
            </div>
            <div>
              <label for="cvv"><strong>CVV:*</strong></label><br />
              <input
                type="password"
                id="cvv"
                name="cvv"
                placeholder="123"
                pattern="[0-9]{3,4}"
                title="3 o 4 dígitos numéricos"
                maxlength="4"
                inputmode="numeric"
                autocomplete="cc-csc"
                required />
            </div>
          </div>

          <button type="submit" class="btn btn-full mt-section">
            Pagar $<?= number_format($monto_total, 2, ',', '.') ?>
          </button>
        </form>

      <?php endif; ?>

      <hr />

      <p class="text-center">
        <a href="carrito.php" class="link">← Volver al Carrito</a>
      </p>

      <div class="compliance-icons" aria-label="Logos de cumplimiento">
        <img src="../assets/images/iso-8583.png" alt="ISO 8583" />
        <img src="../assets/images/pci-dss.webp" alt="PCI DSS" />
        <img src="../assets/images/normas-iso.png" alt="ISO 20022" />
      </div>
    </div>

<?php include '../includes/footer.php'; ?>

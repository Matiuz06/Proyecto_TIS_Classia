<?php
$title     = 'Pasarela de pago';
$cssPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

    <div class="card-container payment-card motion-entry">
      <span class="brand-mark">Classia</span>

      <h1>Pasarela de Pago</h1>

      <form action="confirmacion.php" method="GET">
        <p>
          <label for="email"><strong>Correo electrónico:*</strong></label
          ><br />
          <input
            type="email"
            id="email"
            name="email"
            placeholder="correo@ejemplo.com"
            required />
        </p>

        <p>
          <label for="numero_tarjeta"
            ><strong>Número de tarjeta:*</strong></label
          ><br />
          <input
            type="text"
            id="numero_tarjeta"
            name="numero_tarjeta"
            placeholder="4557 5563 3456 3509"
            required />
        </p>

        <div class="flex-row">
          <div>
            <label for="fecha_expiracion"><strong>Expiración:*</strong></label
            ><br />
            <input
              type="text"
              id="fecha_expiracion"
              name="fecha_expiracion"
              placeholder="MM/AA"
              required />
          </div>
          <div>
            <label for="cvv"><strong>CVV:*</strong></label
            ><br />
            <input type="text" id="cvv" name="cvv" placeholder="123" required />
          </div>
        </div>

        <button type="submit">Pagar</button>
      </form>
      <hr />

      <p>
        <a href="carrito.php">← Volver al Carrito</a>
      </p>

      <div class="compliance-icons" aria-label="Logos de cumplimiento">
        <img src="../assets/images/iso-8583.png" alt="ISO 8583" />
        <img
          src="../assets/images/pci-dss.webp"
          alt="PCI DSS" />
        <img
          src="../assets/images/normas-iso.png"
          alt="ISO 20022" />
      </div>
    </div>

<?php include '../includes/footer.php'; ?>

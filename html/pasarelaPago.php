<?php
$title     = 'Pasarela de pago';
$cssPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

    <!-- TARJETA DE PAGO -->
    <div class="card-container payment-card motion-entry">
      <!-- LOGO -->
      <span class="brand-mark">Classia</span>

      <!-- TÍTULO -->
      <h1>Pasarela de Pago</h1>

      <!-- FORMULARIO -->
      <form action="confirmacion.php" method="GET">
        <!-- EMAIL -->
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

        <!-- NÚMERO DE TARJETA -->
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

        <!-- FECHA Y CVV -->
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

        <!-- BOTÓN PAGAR -->
        <button type="submit">Pagar $99.97</button>
      </form>
      <hr />

      <p>
        <a href="carrito.php">← Volver al Carrito</a>
      </p>

      <!-- ICONOS DE COMPLIANCE -->
      <div class="compliance-icons" aria-label="Logos de cumplimiento">
        <img src="../assets/ISO-8583-300x300.png" alt="ISO 8583" />
        <img
          src="../assets/pci-dss-cc08e1eb387e5ecf5945f8e96dfbab09.webp"
          alt="PCI DSS" />
        <img
          src="../assets/normas-iso-1-1024x848-removebg-preview.png"
          alt="ISO 20022" />
      </div>
    </div>

<?php include '../includes/footer.php'; ?>

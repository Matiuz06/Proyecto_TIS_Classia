<?php
$title     = 'Confirmación de compra';
$cssPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

    <main
      class="card-container confirmation-card"
      aria-labelledby="pago-confirmado">
      <span class="badge">Pago simulado</span>
      <h1 id="pago-confirmado">¡Pago realizado con éxito!</h1>
      <p>Ya tenés acceso al curso de demostración.</p>
      <hr />
      <h2>Resumen</h2>
      <p>
        <strong>Curso:</strong> Robótica para principiantes —
        <strong>$29.99</strong>
      </p>
      <p><strong>Total pagado:</strong> $29.99</p>
      <p>Se envió la confirmación a tu correo electrónico.</p>
      <p><a href="curso.php" class="btn">Ir al curso</a></p>
      <p><a href="usuario.php">Ver mi perfil</a></p>
      <p><a href="catalogo.php">← Volver al catálogo</a></p>
    </main>

<?php include '../includes/footer.php'; ?>

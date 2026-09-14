<?php
$contratacion = [];
$detalles = [];
$error = '';
require_once '../php/contrataciones/confirmacion.php';

$title = 'Confirmacion de compra';
$cssPrefix = '..';
$jsPrefix    = '..';

$activePage = 'carrito';
include '../includes/header.php';
?>

    <main class="card-container confirmation-card" aria-labelledby="contratacion-confirmada">
      <?php if ($error !== '' || empty($contratacion)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <p><a href="catalogo.php">Volver al catalogo</a></p>
      <?php else: ?>
        <span class="badge">Contratacion pendiente</span>
        <h1 id="contratacion-confirmada">La contratacion fue registrada correctamente.</h1>
        <p>Numero de contratacion: <?php echo (int) $contratacion['id_contratacion']; ?></p>
        <p>Fecha: <?php echo htmlspecialchars($contratacion['fecha_contratacion']); ?></p>
        <p>Estado: <?php echo htmlspecialchars($contratacion['estado']); ?></p>
        <hr />
        <h2>Publicaciones contratadas</h2>
        <?php foreach ($detalles as $detalle): ?>
          <p>
            <strong><?php echo htmlspecialchars($detalle['titulo']); ?></strong>
            (<?php echo htmlspecialchars($detalle['tipo']); ?>) -
            Cantidad: <?php echo (int) $detalle['cantidad']; ?> -
            Subtotal: $<?php echo number_format((float) $detalle['subtotal'], 2, ',', '.'); ?>
          </p>
        <?php endforeach; ?>
        <p><strong>Total:</strong> $<?php echo number_format((float) $contratacion['monto_total'], 2, ',', '.'); ?></p>
        <p><a href="catalogo.php" class="btn">Volver al catálogo</a></p>
        <p><a href="usuario.php">Ver mi perfil</a></p>
        <p><a href="catalogo.php">Volver al catalogo</a></p>
      <?php endif; ?>
    </main>

<?php include '../includes/footer.php'; ?>

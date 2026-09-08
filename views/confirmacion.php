<?php
require_once '../php/auth/session.php';
require_once '../config/database.php';

requerir_autenticacion('login.php');

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$id_contratacion = filter_input(INPUT_GET, 'id_contratacion', FILTER_VALIDATE_INT);
$contratacion = null;
$detalles = [];
$error = '';

if (!$id_contratacion || $id_contratacion <= 0) {
    $error = 'Contratacion invalida.';
} else {
    try {
        $stmt = $pdo->prepare(
            "SELECT id_contratacion, fecha_contratacion, monto_total, estado
             FROM contrataciones
             WHERE id_contratacion = :id_contratacion AND id_usuario = :id_usuario"
        );
        $stmt->execute([
            'id_contratacion' => $id_contratacion,
            'id_usuario' => $id_usuario,
        ]);
        $contratacion = $stmt->fetch();

        if (!$contratacion) {
            $error = 'No tenes permisos para ver esta contratacion.';
        } else {
            $stmt = $pdo->prepare(
                "SELECT dc.cantidad, dc.precio_unitario, dc.subtotal, p.titulo, p.tipo
                 FROM detalles_contratacion dc
                 INNER JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
                 WHERE dc.id_contratacion = :id_contratacion
                 ORDER BY dc.id_detalle ASC"
            );
            $stmt->execute(['id_contratacion' => $id_contratacion]);
            $detalles = $stmt->fetchAll();
        }
    } catch (PDOException $e) {
        error_log('Error al consultar contratacion: ' . $e->getMessage());
        $error = 'No se pudo cargar la confirmacion.';
    }
}

$title = 'Confirmacion de compra';
$cssPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

    <main class="card-container confirmation-card" aria-labelledby="contratacion-confirmada">
      <?php if ($error !== ''): ?>
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
        <p><a href="curso.php" class="btn">Ir al curso</a></p>
        <p><a href="usuario.php">Ver mi perfil</a></p>
        <p><a href="catalogo.php">Volver al catalogo</a></p>
      <?php endif; ?>
    </main>

<?php include '../includes/footer.php'; ?>

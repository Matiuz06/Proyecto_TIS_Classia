<?php
require_once '../php/auth/roles.php';
require_once '../php/solicitudes/gestionar_solicitudes_docente.php';

requerir_rol(ROL_ADMIN, 'usuario.php');

$mensaje = '';
$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $accion         = $_POST['accion'] ?? '';
    $id_solicitud   = (int) ($_POST['id_solicitud_docente'] ?? 0);

    $resultado = procesar_decision_solicitud_docente($id_solicitud, $accion, $token_recibido, $_SESSION['csrf_token'] ?? '');
    $error   = $resultado['error'];
    $mensaje = $resultado['mensaje'];
}

$solicitudes = obtener_solicitudes_docente_pendientes();


$title = 'Solicitudes docentes';
$description = 'Revision de solicitudes para rol docente en Classia.';
$cssPrefix = '..';
$jsPrefix    = '..';

$activePage = 'panel-administrador';
include '../includes/header.php';
?>

<main>
  <section aria-labelledby="titulo-solicitudes-docente">
    <h1 id="titulo-solicitudes-docente">Solicitudes docentes pendientes</h1>

    <?php if ($mensaje !== ''): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (empty($solicitudes)): ?>
      <p>No hay solicitudes pendientes.</p>
    <?php else: ?>
      <?php foreach ($solicitudes as $solicitud): ?>
        <article class="content-card">
          <h2><?php echo htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['apellido']); ?></h2>
          <p>Email: <?php echo htmlspecialchars($solicitud['email']); ?></p>
          <p>Estado: <?php echo htmlspecialchars($solicitud['estado']); ?></p>
          <p>Fecha: <?php echo htmlspecialchars($solicitud['fecha_solicitud']); ?></p>
          <p>Motivo: <?php echo htmlspecialchars($solicitud['motivo'] ?? 'Sin motivo indicado.'); ?></p>

          <form method="POST" action="solicitudes-docente.php">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <input type="hidden" name="id_solicitud_docente" value="<?php echo (int) $solicitud['id_solicitud_docente']; ?>">
            <button type="submit" name="accion" value="aprobar">Aprobar</button>
            <button type="submit" name="accion" value="rechazar">Rechazar</button>
          </form>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

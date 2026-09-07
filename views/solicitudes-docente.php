<?php
require_once '../php/auth/roles.php';
require_once '../config/database.php';

requerir_rol(ROL_ADMIN, 'usuario.php');

$mensaje = '';
$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $accion = $_POST['accion'] ?? '';
    $id_solicitud = (int) ($_POST['id_solicitud_docente'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $error = 'La sesion del formulario expiro. Recarga la pagina e intenta nuevamente.';
    } elseif ($id_solicitud <= 0 || !in_array($accion, ['aprobar', 'rechazar'], true)) {
        $error = 'Solicitud invalida.';
    } elseif ($accion === 'aprobar') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT id_usuario
                 FROM solicitudes_docente
                 WHERE id_solicitud_docente = :id_solicitud AND estado = 'Pendiente'
                 FOR UPDATE"
            );
            $stmt->execute(['id_solicitud' => $id_solicitud]);
            $solicitud = $stmt->fetch();

            if (!$solicitud) {
                $pdo->rollBack();
                $error = 'La solicitud ya fue procesada.';
            } else {
                $stmt = $pdo->prepare(
                    "UPDATE solicitudes_docente
                     SET estado = 'Aprobada', fecha_respuesta = CURRENT_TIMESTAMP
                     WHERE id_solicitud_docente = :id_solicitud"
                );
                $stmt->execute(['id_solicitud' => $id_solicitud]);

                $stmt = $pdo->prepare(
                    "UPDATE usuarios SET id_rol = :id_rol WHERE id_usuario = :id_usuario"
                );
                $stmt->execute([
                    'id_rol' => ROL_DOCENTE,
                    'id_usuario' => $solicitud['id_usuario'],
                ]);

                $pdo->commit();
                $mensaje = 'Solicitud aprobada.';
            }
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            error_log('Error al aprobar solicitud docente: ' . $e->getMessage());
            $error = 'No se pudo aprobar la solicitud.';
        }
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE solicitudes_docente
                 SET estado = 'Rechazada', fecha_respuesta = CURRENT_TIMESTAMP
                 WHERE id_solicitud_docente = :id_solicitud AND estado = 'Pendiente'"
            );
            $stmt->execute(['id_solicitud' => $id_solicitud]);

            $mensaje = $stmt->rowCount() > 0 ? 'Solicitud rechazada.' : 'La solicitud ya fue procesada.';
        } catch (PDOException $e) {
            error_log('Error al rechazar solicitud docente: ' . $e->getMessage());
            $error = 'No se pudo rechazar la solicitud.';
        }
    }
}

$stmt = $pdo->query(
    "SELECT sd.id_solicitud_docente, sd.estado, sd.motivo, sd.fecha_solicitud,
            u.nombre, u.apellido, u.email
     FROM solicitudes_docente sd
     INNER JOIN usuarios u ON u.id_usuario = sd.id_usuario
     WHERE sd.estado = 'Pendiente'
     ORDER BY sd.fecha_solicitud ASC"
);
$solicitudes = $stmt->fetchAll();

$title = 'Solicitudes docentes';
$description = 'Revision de solicitudes para rol docente en Classia.';
$cssPrefix = '..';
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

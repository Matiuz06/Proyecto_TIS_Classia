<?php
require_once '../php/auth/roles.php';
require_once '../config/database.php';
require_once '../php/solicitudes/solicitar_docente.php';

requerir_rol(ROL_ESTUDIANTE, 'usuario.php');

$usuario    = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$mensaje    = '';
$error      = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$tiene_pendiente = tiene_solicitud_docente_pendiente($pdo, $id_usuario);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $motivo         = trim($_POST['motivo'] ?? '');

    $resultado = crear_solicitud_docente($pdo, $id_usuario, $motivo, $token_recibido, $_SESSION['csrf_token'] ?? '');
    $error   = $resultado['error'];
    $mensaje = $resultado['mensaje'];
    if ($resultado['exito']) {
        $tiene_pendiente = true;
    }
}


$title = 'Solicitar ser docente';
$description = 'Solicitud para convertirse en docente o proveedor en Classia.';
$cssPrefix = '..';
$activePage = 'solicitar-docente';
include '../includes/header.php';
?>

<main class="auth-shell">
  <section class="auth-card" aria-labelledby="titulo-solicitud-docente">
    <h1 id="titulo-solicitud-docente">Solicitar ser docente</h1>

    <?php if ($mensaje !== ''): ?>
      <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if ($tiene_pendiente): ?>
      <p>Ya tenes una solicitud pendiente.</p>
    <?php else: ?>
      <form method="POST" action="solicitar-docente.php">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

        <p>
          <label for="motivo">Motivo opcional</label>
          <textarea id="motivo" name="motivo" rows="5"><?php echo htmlspecialchars($_POST['motivo'] ?? ''); ?></textarea>
        </p>

        <button type="submit">Enviar solicitud</button>
      </form>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../php/auth/roles.php';
require_once '../config/database.php';

requerir_rol(ROL_ESTUDIANTE, 'usuario.php');

$usuario = usuario_actual();
$mensaje = '';
$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->prepare(
    "SELECT COUNT(*) FROM solicitudes_docente WHERE id_usuario = :id_usuario AND estado = 'Pendiente'"
);
$stmt->execute(['id_usuario' => $usuario['id_usuario']]);
$tiene_pendiente = (int) $stmt->fetchColumn() > 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $error = 'La sesion del formulario expiro. Recarga la pagina e intenta nuevamente.';
    } elseif ($tiene_pendiente) {
        $error = 'Ya tenes una solicitud pendiente.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "INSERT INTO solicitudes_docente (id_usuario, estado, motivo)
                 VALUES (:id_usuario, 'Pendiente', :motivo)"
            );
            $stmt->execute([
                'id_usuario' => $usuario['id_usuario'],
                'motivo' => $motivo !== '' ? $motivo : null,
            ]);

            $mensaje = 'Solicitud enviada correctamente.';
            $tiene_pendiente = true;
        } catch (PDOException $e) {
            error_log('Error al crear solicitud docente: ' . $e->getMessage());
            $error = 'No se pudo enviar la solicitud. Intenta nuevamente.';
        }
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

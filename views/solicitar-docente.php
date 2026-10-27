<?php

/**
 * Responsabilidad: Postulacion para habilitar el rol docente / proveedor en la cuenta.
 */

require_once '../php/auth/roles.php';
require_once '../php/solicitudes/solicitar_docente.php';
require_once '../php/utils/toast.php';

requerir_rol(ROL_ESTUDIANTE, 'usuario.php');

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$mensaje = '';
$error = '';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$solicitud_actual = obtener_solicitud_docente_usuario($id_usuario);
$estado_actual = $solicitud_actual['estado'] ?? '';
$tiene_pendiente = ($solicitud_actual['estado'] ?? '') === 'Pendiente';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $motivo = trim($_POST['motivo'] ?? '');

    $resultado = crear_solicitud_docente($id_usuario, $motivo, $_FILES['documentacion'] ?? [], $token_recibido, $_SESSION['csrf_token'] ?? '');
    $error = $resultado['error'];
    $mensaje = $resultado['mensaje'];
    if ($resultado['exito']) {
        set_toast('success', 'Solicitud enviada', $mensaje);
        $mensaje = '';
        $tiene_pendiente = true;
        $solicitud_actual = obtener_solicitud_docente_usuario($id_usuario);
    }
}

$title = 'Solicitar ser docente';
$description = 'Solicitud para convertirse en docente o proveedor en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'solicitar-docente';
include '../includes/header.php';
?>

<main id="main-content" class="auth-shell teacher-request-shell">
  <section class="auth-card teacher-request-card" aria-labelledby="titulo-solicitud-docente">
    <header class="teacher-request-card-header">
      <h1 id="titulo-solicitud-docente">Solicitar ser docente</h1>
      <p class="teacher-request-intro">La documentacion permite que un administrador revise tus conocimientos, formacion o experiencia antes de aprobar la solicitud.</p>
    </header>

    <?php if ($error !== ''): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($tiene_pendiente): ?>
      <section class="teacher-request-status teacher-request-status--pending" aria-labelledby="estado-solicitud-docente">
        <span class="teacher-request-status-badge teacher-request-status-badge--pending">Pendiente</span>
        <h2 id="estado-solicitud-docente">Tu solicitud esta siendo revisada</h2>
        <p>No es necesario volver a enviarla mientras permanezca pendiente.</p>
        <?php if (!empty($solicitud_actual['fecha_solicitud'])): ?>
          <dl class="teacher-request-meta">
            <div><dt>Enviada</dt><dd><?= htmlspecialchars(date('d/m/Y H:i', strtotime($solicitud_actual['fecha_solicitud']))) ?></dd></div>
            <div><dt>Documentacion</dt><dd><?= htmlspecialchars($solicitud_actual['documentacion_nombre'] ?? 'Archivo adjunto') ?></dd></div>
          </dl>
        <?php endif; ?>
      </section>
    <?php endif; ?>

    <?php if (!$tiene_pendiente && $estado_actual === 'Aprobada'): ?>
      <section class="teacher-request-status teacher-request-status--approved" aria-labelledby="estado-solicitud-aprobada">
        <span class="teacher-request-status-badge teacher-request-status-badge--approved">Aprobada</span>
        <h2 id="estado-solicitud-aprobada">Tu solicitud fue aprobada</h2>
        <p>Tu cuenta ya puede utilizar las funcionalidades correspondientes al rol docente.</p>
      </section>
    <?php elseif (!$tiene_pendiente): ?>
      <?php if ($estado_actual === 'Rechazada'): ?>
        <section class="teacher-request-status teacher-request-status--rejected" aria-labelledby="estado-solicitud-rechazada">
          <span class="teacher-request-status-badge teacher-request-status-badge--rejected">Rechazada</span>
          <h2 id="estado-solicitud-rechazada">Tu solicitud no fue aprobada</h2>
          <p><?= nl2br(htmlspecialchars($solicitud_actual['motivo'] ?? 'Puedes volver a solicitarlo si la plataforma lo permite.')) ?></p>
        </section>
      <?php endif; ?>
      <form method="POST" action="solicitar-docente.php" enctype="multipart/form-data" class="teacher-request-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

        <div class="teacher-request-field">
          <label for="motivo">Motivo de tu solicitud <span class="form-optional">(opcional)</span></label>
          <textarea id="motivo" name="motivo" rows="5"><?= htmlspecialchars($_POST['motivo'] ?? '') ?></textarea>
        </div>

        <div class="teacher-request-field">
          <label for="documentacion">Documentacion que acredita tus conocimientos <span aria-hidden="true">*</span></label>
          <input id="documentacion" name="documentacion" type="file" accept=".pdf,.jpg,.jpeg,.png" required>
          <small>Formatos aceptados: PDF, JPG, JPEG y PNG. Tamano maximo: 10 MB. El archivo sera revisado por un administrador.</small>
        </div>

        <button type="submit" class="btn btn-primary-action">Enviar solicitud</button>
      </form>
    <?php endif; ?>
  </section>
</main>

<?php include '../includes/footer.php'; ?>

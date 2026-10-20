<?php

/**
 * Responsabilidad: Bandeja administrativa para evaluar solicitudes de rol docente.
 */

require_once '../php/auth/roles.php';
require_once '../php/solicitudes/gestionar_solicitudes_docente.php';
require_once '../php/utils/toast.php';

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
    if ($mensaje !== '') {
        set_toast('success', $mensaje);
        $mensaje = '';
    }
}

$solicitudes = obtener_solicitudes_docente_pendientes();
$solicitudesDialog = [];
foreach ($solicitudes as $solicitud) {
    $solicitudesDialog[(string) (int) $solicitud['id_solicitud_docente']] = [
        'id' => (int) $solicitud['id_solicitud_docente'],
        'nombre' => trim(($solicitud['nombre'] ?? '') . ' ' . ($solicitud['apellido'] ?? '')),
        'email' => (string) ($solicitud['email'] ?? ''),
        'fecha_solicitud' => !empty($solicitud['fecha_solicitud'])
            ? date('d/m/Y H:i', strtotime($solicitud['fecha_solicitud']))
            : '-',
        'estado' => (string) ($solicitud['estado'] ?? 'Pendiente'),
        'motivo' => (string) ($solicitud['motivo'] ?? 'Sin motivo indicado.'),
        'documentacion_nombre' => (string) ($solicitud['documentacion_nombre'] ?? ''),
        'documentacion_mime' => (string) ($solicitud['documentacion_mime'] ?? ''),
        'documentacion_tamano' => number_format(((int) ($solicitud['documentacion_tamano'] ?? 0)) / 1048576, 2, ',', '.'),
        'documentacion_fecha' => (string) ($solicitud['documentacion_fecha'] ?? '-'),
        'tiene_documentacion' => !empty($solicitud['documentacion_ruta']),
    ];
}

$title = 'Solicitudes docentes';
$description = 'Revision de solicitudes para rol docente en Classia.';
$cssPrefix = '..';
$jsPrefix    = '..';

$activePage = 'panel-administrador';
include '../includes/header.php';
?>

<main id="main-content" class="teacher-request-list">
  <header class="teacher-request-list-header">
    <h1 id="titulo-solicitudes-docente">Solicitudes docentes pendientes</h1>
    <p>Selecciona una persona para revisar la documentacion y resolver la solicitud.</p>
  </header>

  <?php if ($error !== ''): ?>
    <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if (empty($solicitudes)): ?>
    <p class="teacher-request-empty">No hay solicitudes pendientes.</p>
  <?php else: ?>
    <section class="teacher-request-table-wrap" aria-labelledby="titulo-solicitudes-docente">
      <table class="teacher-request-table">
        <caption class="sr-only">Solicitudes docentes pendientes</caption>
        <thead>
          <tr>
            <th scope="col">Solicitante</th>
            <th scope="col">Correo</th>
            <th scope="col">Fecha</th>
            <th scope="col">Estado</th>
            <th scope="col">Documentacion</th>
            <th scope="col">Accion</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($solicitudes as $solicitud): ?>
            <?php $idSolicitud = (int) $solicitud['id_solicitud_docente']; ?>
            <tr class="teacher-request-row">
              <td data-label="Solicitante"><?= htmlspecialchars(trim(($solicitud['nombre'] ?? '') . ' ' . ($solicitud['apellido'] ?? ''))) ?></td>
              <td data-label="Correo"><?= htmlspecialchars($solicitud['email'] ?? '') ?></td>
              <td data-label="Fecha"><?= htmlspecialchars($solicitudesDialog[(string) $idSolicitud]['fecha_solicitud']) ?></td>
              <td data-label="Estado"><span class="teacher-request-status-badge teacher-request-status-badge--pending">Pendiente</span></td>
              <td data-label="Documentacion"><?= !empty($solicitud['documentacion_ruta']) ? 'PDF adjunto' : 'Sin archivo' ?></td>
              <td data-label="Accion">
                <button type="button" class="btn btn-secondary teacher-request-details-trigger" data-solicitud-id="<?= $idSolicitud ?>" aria-haspopup="dialog" aria-controls="dialog-solicitud-docente" aria-expanded="false">Ver detalles</button>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </section>
  <?php endif; ?>
</main>

<dialog id="dialog-solicitud-docente" class="course-dialog teacher-request-dialog" aria-labelledby="dialog-solicitud-docente-title" aria-describedby="dialog-solicitud-docente-description" aria-modal="true">
  <header class="course-dialog-header">
    <div>
      <h2 id="dialog-solicitud-docente-title">Solicitud docente</h2>
      <p id="dialog-solicitud-docente-description">Detalle de la solicitud seleccionada.</p>
    </div>
    <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar detalles">&times;</button>
  </header>

  <div class="course-dialog-body teacher-request-dialog-body">
    <dl class="teacher-request-meta">
      <div><dt>Solicitante</dt><dd id="dialog-solicitud-nombre"></dd></div>
      <div><dt>Correo</dt><dd id="dialog-solicitud-email"></dd></div>
      <div><dt>Fecha de solicitud</dt><dd id="dialog-solicitud-fecha"></dd></div>
      <div><dt>Estado</dt><dd><span id="dialog-solicitud-estado" class="teacher-request-status-badge teacher-request-status-badge--pending"></span></dd></div>
    </dl>

    <section class="teacher-request-detail-block">
      <h3>Motivo</h3>
      <p id="dialog-solicitud-motivo"></p>
    </section>

    <section class="teacher-request-detail-block teacher-request-document-actions">
      <h3>Acciones sobre la documentación</h3>
      <div id="dialog-documentacion-actions" class="teacher-request-actions">
        <a id="dialog-documentacion-ver" class="btn btn-secondary" target="_blank" rel="noopener">Ver documento</a>
        <a id="dialog-documentacion-descargar" class="btn btn-secondary">Descargar documento</a>
      </div>
    </section>

    <form method="POST" action="solicitudes-docente.php" class="teacher-request-actions teacher-request-dialog-actions">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="id_solicitud_docente" id="dialog-solicitud-id" value="0">
      <button type="submit" name="accion" value="aprobar" class="btn btn-primary-action">Aprobar</button>
      <button type="submit" name="accion" value="rechazar" class="btn btn-danger-solid">Rechazar</button>
    </form>
  </div>
</dialog>

<script type="application/json" id="teacher-request-dialog-data"><?= json_encode($solicitudesDialog, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>

<?php include '../includes/footer.php'; ?>

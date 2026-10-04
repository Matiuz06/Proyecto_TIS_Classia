<?php   

require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');

require_once '../config/database.php';
require_once '../php/publicaciones/EntregaRepository.php';
require_once '../php/utils/file_upload_helper.php';
require_once '../php/utils/supabase_storage.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$usuario = usuario_actual();
$id_usuario = (int)$usuario['id_usuario'];
$id_entrega = (int)($_GET['id'] ?? $_POST['id_entrega'] ?? 0);

$repo = new EntregaRepository($pdo);
$entrega = $repo->obtenerDetalleParaRevision($id_entrega);

$error = '';
$mensaje = '';

if (!$entrega || (!es_admin() && (int)$entrega['docente_id'] !== $id_usuario)) {
    http_response_code(403);
    $error = 'No tenes permisos para revisar esta entrega.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
            throw new RuntimeException('La sesion del formulario expiro.');
        }
        $tipo = $entrega['tipo_calificacion'];
        $calificacion = trim($_POST['calificacion'] ?? '');
        if ($tipo === 'numerica') {
            if ($calificacion === '' || !is_numeric($calificacion) || (float)$calificacion < 0 || (float)$calificacion > (float)$entrega['puntaje_maximo']) {
                throw new RuntimeException('La calificacion debe estar entre 0 y el puntaje maximo.');
            }
        } elseif ($tipo === 'aprobado_reprobado') {
            if (!in_array($calificacion, ['aprobado', 'reprobado'], true)) throw new RuntimeException('Selecciona aprobado o reprobado.');
        } else {
            $calificacion = null;
        }

        $archivoFeedback = null;
        $archivoFeedbackAnterior = $entrega['archivo_feedback'] ?? null;
        if (!empty($_FILES['archivo_feedback']['name'])) {
            if (empty($entrega['permite_feedback_archivo'])) throw new RuntimeException('Esta tarea no permite archivo de devolucion.');
            $res = guardar_archivo_subido($_FILES['archivo_feedback'], 'feedback', 20);
            if (!$res['ok']) throw new RuntimeException($res['error']);
            $archivoFeedback = $res['ruta'];
        }
        try {
            $repo->guardarCalificacion($id_entrega, $calificacion, trim($_POST['feedback_docente'] ?? '') ?: null, $archivoFeedback, $id_usuario);
        } catch (Throwable $e) {
            if ($archivoFeedback && !eliminar_archivo_storage($archivoFeedback)) {
                error_log('No se pudo eliminar feedback nuevo tras fallo de BD: ' . $archivoFeedback);
            }
            throw $e;
        }
        if ($archivoFeedback && $archivoFeedbackAnterior && $archivoFeedbackAnterior !== $archivoFeedback && !eliminar_archivo_storage($archivoFeedbackAnterior)) {
            error_log('No se pudo eliminar feedback anterior reemplazado: ' . $archivoFeedbackAnterior);
        }
        $mensaje = 'Entrega calificada correctamente.';
        $entrega = $repo->obtenerDetalleParaRevision($id_entrega);
    } catch (Throwable $e) {
        error_log('Revisar entrega: ' . $e->getMessage());
        $error = $e instanceof RuntimeException ? $e->getMessage() : 'No se pudo guardar la calificacion.';
    }
}

$archivos = $entrega ? $repo->listarArchivosIncluyeLegacy($id_entrega) : [];
$title = 'Revisar entrega';
$description = 'Calificacion y feedback docente.';
$cssPrefix = '..';
$jsPrefix = '..';
$bodyClass = 'course-builder-page';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main class="provider-editor course-builder review-delivery-page">
<main id="main-content" class="provider-editor course-builder">
  <?php if ($mensaje): ?><div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if ($entrega): ?>
    <?php
      $entregaCalificada = !empty($entrega['fecha_calificacion']) || (($entrega['estado'] ?? '') === 'Calificada');
      $estadoTexto = $entregaCalificada ? 'Calificada' : 'Pendiente de correccion';
      $estadoBadge = $entregaCalificada ? 'badge-success-soft' : 'badge-info-soft';
    ?>
    <header class="review-delivery-header">
      <div>
        <h1>Revisar entrega</h1>
        <p class="review-delivery-path"><?= htmlspecialchars($entrega['curso_titulo']) ?> &rsaquo; <?= htmlspecialchars($entrega['tarea_titulo']) ?></p>
        <p class="review-delivery-meta"><?= htmlspecialchars($entrega['nombre'] . ' ' . $entrega['apellido']) ?> &middot; <?= date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])) ?></p>
      </div>
      <span class="badge <?= $estadoBadge ?>"><?= htmlspecialchars($estadoTexto) ?></span>
    </header>

    <div class="review-delivery-shell">
      <section class="review-delivery-panel" aria-labelledby="titulo-entrega-alumno">
        <h2 id="titulo-entrega-alumno">Entrega del alumno</h2>

        <?php if (!empty($entrega['texto_entrega'])): ?>
          <div class="review-delivery-field">
            <h3>Texto entregado</h3>
            <p><?= nl2br(htmlspecialchars($entrega['texto_entrega'])) ?></p>
          </div>
        <?php endif; ?>

        <div class="review-delivery-field">
          <h3>Comentario del alumno</h3>
          <p><?= !empty($entrega['comentario_entrega']) ? nl2br(htmlspecialchars($entrega['comentario_entrega'])) : 'Sin comentario.' ?></p>
        </div>

        <?php if (!empty($entrega['enlace_entrega'])): ?>
          <div class="review-delivery-field">
            <h3>Enlace entregado</h3>
            <a class="btn btn-sm btn-outline" href="<?= htmlspecialchars($entrega['enlace_entrega']) ?>" target="_blank" rel="noopener">Abrir enlace</a>
          </div>
        <?php endif; ?>

        <div class="review-delivery-field">
          <h3>Archivo adjunto</h3>
          <?php if (empty($archivos)): ?>
            <p>Sin archivos adjuntos.</p>
          <?php else: ?>
            <div class="review-delivery-files">
              <?php foreach ($archivos as $archivo): ?>
                <?php
                  $nombreArchivo = (string)$archivo['nombre_original'];
                  $hrefArchivo = '../php/descargas/descargar_archivo.php?tipo=' . (!empty($archivo['legacy']) ? 'entrega' : 'entrega_archivo') . '&id=' . (!empty($archivo['legacy']) ? $id_entrega : (int)$archivo['id_archivo']);
                ?>
                <div class="review-delivery-file">
                  <span title="<?= htmlspecialchars($nombreArchivo) ?>"><?= htmlspecialchars($nombreArchivo) ?></span>
                  <a class="btn btn-sm btn-outline" href="<?= htmlspecialchars($hrefArchivo) ?>">Descargar</a>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </div>
      </section>

      <form method="POST" enctype="multipart/form-data" class="review-delivery-panel review-delivery-form" aria-labelledby="titulo-correccion">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
        <input type="hidden" name="id_entrega" value="<?= $id_entrega ?>">
        <h2 id="titulo-correccion">Correccion</h2>

        <?php if ($entrega['tipo_calificacion'] === 'numerica'): ?>
          <label class="review-delivery-grade">Calificacion
            <span>
              <input type="number" step="0.01" min="0" max="<?= htmlspecialchars((string)$entrega['puntaje_maximo']) ?>" name="calificacion" value="<?= htmlspecialchars((string)($entrega['calificacion'] ?? '')) ?>">
              <small>/ <?= htmlspecialchars((string)$entrega['puntaje_maximo']) ?></small>
            </span>
          </label>
        <?php elseif ($entrega['tipo_calificacion'] === 'aprobado_reprobado'): ?>
          <label class="review-delivery-grade">Calificacion
            <select name="calificacion"><option value="aprobado">Aprobado</option><option value="reprobado" <?= ($entrega['calificacion'] ?? '') === 'reprobado' ? 'selected' : '' ?>>Reprobado</option></select>
          </label>
        <?php endif; ?>

        <label>Feedback para el alumno<textarea name="feedback_docente" rows="6"><?= htmlspecialchars($entrega['feedback_docente'] ?? '') ?></textarea></label>

        <?php if (!empty($entrega['permite_feedback_archivo'])): ?>
          <label>Archivo de devolucion<input type="file" name="archivo_feedback"></label>
        <?php endif; ?>

        <?php if (!empty($entrega['archivo_feedback'])): ?>
          <p class="review-delivery-current-feedback"><a class="btn btn-sm btn-outline" href="../php/descargas/descargar_archivo.php?tipo=feedback&id=<?= $id_entrega ?>">Descargar feedback actual</a></p>
        <?php endif; ?>

        <div class="review-delivery-actions">
          <a class="btn btn-outline" href="entregas-tarea.php?id=<?= (int)$entrega['id_tarea'] ?>">Volver</a>
          <button class="btn btn-primary-action" type="submit">Guardar calificacion</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

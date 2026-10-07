<?php

/**
 * Responsabilidad: Gestión de módulos, unidades temáticas y recursos de un curso.
 */

require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');
require_once '../php/publicaciones/contenido_curso.php';
require_once '../php/publicaciones/EntregaRepository.php';
require_once '../php/utils/toast.php';

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
$usuario = usuario_actual();
$id_usuario = (int)$usuario['id_usuario'];
$id_publicacion = (int)($_GET['id'] ?? $_POST['id_publicacion'] ?? 0);
$curso = obtener_curso_del_docente($pdo, $id_publicacion, $id_usuario, es_admin());
$mensaje = '';
$error = '';

if (!$curso) {
    http_response_code(404);
    $error = 'Curso no encontrado o sin permisos.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $r = procesar_contenido_curso($pdo, $_POST, $_FILES, $id_publicacion, $id_usuario, es_admin(), $_SESSION['csrf_token']);
    if ($r['ok']) set_toast('success', 'Contenido actualizado'); else $error = $r['mensaje'];
}

if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'creada') {
    set_toast('success', 'Curso creado', 'Ahora podes cargar su contenido.');
}

$contenido = $curso ? obtener_contenido_curso($pdo, $id_publicacion) : [];
$tareaRepoVista = $curso ? tarea_repo($pdo) : null;
$entregaRepoVista = $curso ? new EntregaRepository($pdo) : null;
$total_clases = array_sum(array_map(fn($m) => count($m['unidades']), $contenido));
$total_recursos = 0;
foreach ($contenido as $modulo) foreach ($modulo['unidades'] as $unidad) $total_recursos += count($unidad['recursos']);

function campo_base_curso(int $id_publicacion): void { ?>
  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
  <input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>">
<?php }

function valor_datetime_local(?string $fecha): string
{
    return $fecha ? date('Y-m-d\TH:i', strtotime($fecha)) : '';
}

function grupos_de_tarea_para_formatos(array $formatos): array
{
    $grupos = [];
    foreach (Tarea::gruposFormatos() as $grupo => $exts) {
        if (array_intersect($exts, $formatos)) $grupos[] = $grupo;
    }
    return $grupos;
}

function campos_tarea(?Tarea $tarea = null): void
{
    $d = $tarea?->toArray() ?? [
        'id_tarea' => null,
        'fecha_apertura' => null,
        'fecha_cierre' => null,
        'permite_archivos' => true,
        'permite_texto' => false,
        'permite_enlace' => false,
        'max_archivos' => 1,
        'max_tamano_mb' => 20,
        'formatos_permitidos' => ['pdf', 'doc', 'docx', 'txt', 'zip'],
        'requisito_entrega' => 'cualquiera',
        'puntaje_maximo' => 10,
        'tipo_calificacion' => 'numerica',
        'permite_feedback_archivo' => false,
    ];
    $gruposSeleccionados = grupos_de_tarea_para_formatos($d['formatos_permitidos']);
    ?>
    <fieldset class="task-config form-grid-full" data-task-config>
      <legend>Configuracion de tarea</legend>
      <input type="hidden" name="id_tarea" value="<?= (int)($d['id_tarea'] ?? 0) ?>">
      <label>Fecha de apertura<input type="datetime-local" name="fecha_apertura" value="<?= htmlspecialchars(valor_datetime_local($d['fecha_apertura'])) ?>"></label>
      <label>Fecha de cierre<input type="datetime-local" name="fecha_cierre" value="<?= htmlspecialchars(valor_datetime_local($d['fecha_cierre'])) ?>"></label>

      <fieldset class="form-grid-full task-options">
        <legend>Tipos de entrega</legend>
        <label class="course-check"><input type="checkbox" name="tipos_entrega[]" value="archivos" <?= $d['permite_archivos'] ? 'checked' : '' ?>> Archivos</label>
        <label class="course-check"><input type="checkbox" name="tipos_entrega[]" value="texto" <?= $d['permite_texto'] ? 'checked' : '' ?>> Texto en linea</label>
        <label class="course-check"><input type="checkbox" name="tipos_entrega[]" value="enlace" <?= $d['permite_enlace'] ? 'checked' : '' ?>> Enlace</label>
        <label>Requisito
          <select name="requisito_entrega">
            <option value="cualquiera" <?= $d['requisito_entrega'] === 'cualquiera' ? 'selected' : '' ?>>Al menos uno</option>
            <option value="todos" <?= $d['requisito_entrega'] === 'todos' ? 'selected' : '' ?>>Todos</option>
          </select>
        </label>
      </fieldset>

      <label>Cantidad maxima de archivos<input type="number" min="1" name="max_archivos" value="<?= (int)$d['max_archivos'] ?>"></label>
      <label>Tamano maximo por archivo (MB)<input type="number" min="1" name="max_tamano_mb" value="<?= (int)$d['max_tamano_mb'] ?>"></label>
      <fieldset class="form-grid-full task-options">
        <legend>Formatos permitidos</legend>
        <?php foreach (Tarea::gruposFormatos() as $grupo => $exts): ?>
          <label class="course-check"><input type="checkbox" name="formatos_grupos[]" value="<?= htmlspecialchars($grupo) ?>" <?= in_array($grupo, $gruposSeleccionados, true) ? 'checked' : '' ?>> <?= htmlspecialchars(ucfirst($grupo)) ?> <small><?= htmlspecialchars(implode(', ', $exts)) ?></small></label>
        <?php endforeach; ?>
      </fieldset>

      <label>Tipo de calificacion
        <select name="tipo_calificacion">
          <option value="numerica" <?= $d['tipo_calificacion'] === 'numerica' ? 'selected' : '' ?>>Numerica</option>
          <option value="aprobado_reprobado" <?= $d['tipo_calificacion'] === 'aprobado_reprobado' ? 'selected' : '' ?>>Aprobado / Reprobado</option>
          <option value="sin_calificacion" <?= $d['tipo_calificacion'] === 'sin_calificacion' ? 'selected' : '' ?>>Sin calificacion</option>
        </select>
      </label>
      <label>Puntaje maximo<input type="number" min="0.01" step="0.01" name="puntaje_maximo" value="<?= htmlspecialchars((string)($d['puntaje_maximo'] ?? '')) ?>"></label>
      <label class="course-check"><input type="checkbox" name="permite_feedback_archivo" value="1" <?= $d['permite_feedback_archivo'] ? 'checked' : '' ?>> Permitir archivo de devolucion</label>
    </fieldset>
<?php }

$title = 'Contenido del curso';
$description = 'Gestión de módulos, clases y recursos.';
$cssPrefix = '..';
$jsPrefix = '..';
$bodyClass = 'course-builder-page';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main id="main-content" class="provider-editor course-editor course-builder">
  <header class="section-heading course-editor-heading">
    <div>
      <p class="course-eyebrow">Constructor de curso</p>
      <h1><?= htmlspecialchars($curso['titulo'] ?? 'Curso') ?></h1>
      <p>Armá el recorrido que verá el alumno: módulos, clases y materiales.</p>
    </div>
    <?php if ($curso): ?>
      <dl class="course-editor-stats" aria-label="Resumen del contenido">
        <div><dt>Módulos</dt><dd><?= count($contenido) ?></dd></div>
        <div><dt>Clases</dt><dd><?= $total_clases ?></dd></div>
        <div><dt>Recursos</dt><dd><?= $total_recursos ?></dd></div>
      </dl>
    <?php endif; ?>
  </header>

  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <?php if ($curso): ?>

  <?php if ($curso && ($curso['estado'] ?? '') === 'Archivado'): ?>
    <div class="alert alert-warning" role="alert">
      <strong>Curso archivado.</strong>
      Este curso fue archivado y conserva su histórico para consulta, pero <strong>no permite agregar ni modificar contenido</strong> (RN-05).
      Si necesés reactivarlo, contactá al administrador.
    </div>
  <?php endif; ?>

  <?php if ($curso): ?>
    <nav class="provider-toolbar">
      <a class="btn" href="editar-publicacion.php?id=<?= $id_publicacion ?>">Editar datos generales</a>
      <a class="btn" href="vista-previa-publicacion.php?id=<?= $id_publicacion ?>">Vista previa</a>
      <a class="btn" href="panel-proveedor.php">Volver al panel</a>
    </nav>

    <section class="course-approval-settings" aria-labelledby="criterio-aprobacion">
      <h2 id="criterio-aprobacion">Criterio de aprobacion</h2>
      <form method="POST" class="course-approval-form">
    <button class="btn btn-secondary course-structure-toggle" type="button" data-course-sidebar-toggle aria-expanded="false">Contenido del curso</button>

    <div class="course-editor-shell">
      <aside class="course-editor-sidebar" data-course-sidebar aria-label="Estructura del curso">
        <div class="course-sidebar-title">Estructura</div>
        <?php if (empty($contenido)): ?>
          <p class="course-empty-note">Este curso todavía no tiene módulos.</p>
        <?php else: ?>
          <?php foreach ($contenido as $modulo): ?>
            <details class="course-nav-module" open>
              <summary>
                <span><?= (int)$modulo['orden'] ?>. <?= htmlspecialchars($modulo['titulo']) ?></span>
                <small><?= count($modulo['unidades']) ?> clases</small>
              </summary>
              <?php if (empty($modulo['unidades'])): ?>
                <p class="course-empty-note">Sin clases.</p>
              <?php else: ?>
                <ol class="course-editor-index">
                  <?php foreach ($modulo['unidades'] as $unidad): ?>
                    <li><a href="#clase-<?= (int)$unidad['id_unidad'] ?>"><?= (int)$modulo['orden'] ?>.<?= (int)$unidad['orden'] ?> <?= htmlspecialchars($unidad['titulo']) ?></a></li>
                  <?php endforeach; ?>
                </ol>
              <?php endif; ?>
            </details>
          <?php endforeach; ?>
        <?php endif; ?>
        <button class="btn btn-primary-action course-full-action" type="button" data-dialog-open="dialog-agregar-modulo">+ Agregar módulo</button>
      </aside>

      <section class="course-editor-main" aria-label="Edicion del contenido">
        <?php if (empty($contenido)): ?>
          <div class="course-empty-state course-empty-state-hero">
            <span class="course-empty-icon" aria-hidden="true">+</span>
            <h2>Este curso todavía no tiene módulos.</h2>
            <p>Empezá creando el primer módulo para organizar las clases y materiales que verá el alumno.</p>
            <button class="btn btn-primary-action" type="button" data-dialog-open="dialog-agregar-modulo">+ Agregar primer módulo</button>
          </div>
        <?php endif; ?>

        <?php foreach ($contenido as $modulo): ?>
          <article class="course-editor-module" id="modulo-<?= (int)$modulo['id_modulo'] ?>">
            <header class="course-editor-module-header">
              <div>
                <span class="course-kicker">Módulo <?= (int)$modulo['orden'] ?></span>
                <h2><?= htmlspecialchars($modulo['titulo']) ?></h2>
                <?php if (!empty($modulo['descripcion'])): ?><p><?= nl2br(htmlspecialchars($modulo['descripcion'])) ?></p><?php endif; ?>
              </div>
              <div class="course-actions" aria-label="Acciones del módulo">
                <?php $es_archivado = ($curso['estado'] ?? '') === 'Archivado'; ?>
                <?php if (!$es_archivado): ?>
                <?php foreach (['subir_modulo' => 'Subir', 'bajar_modulo' => 'Bajar'] as $accion => $texto): ?>
                  <form method="POST"><?php campo_base_curso($id_publicacion); ?><input type="hidden" name="accion" value="<?= $accion ?>"><input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>"><button class="btn btn-sm" type="submit"><?= $texto ?></button></form>
                <?php endforeach; ?>
                <button class="btn btn-sm" type="button" data-dialog-open="dialog-editar-modulo-<?= (int)$modulo['id_modulo'] ?>">Editar</button>
                <form method="POST" onsubmit="return confirm('&iquest;Eliminar este m&oacute;dulo? Tambi&eacute;n se eliminar&aacute;n sus clases y recursos.');">
                  <?php campo_base_curso($id_publicacion); ?>
                  <input type="hidden" name="accion" value="eliminar_modulo">
                  <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
                  <button class="btn-status btn-status-delete" type="submit">Eliminar</button>
                </form>
                <?php endif; ?>
                <!-- REQ-CON-04: Switch de visibilidad para alumnos -->
                <?php $vis_mod = (int)($modulo['visible_alumnos'] ?? 1); ?>
                <form method="POST" class="inline-form">
                  <?php campo_base_curso($id_publicacion); ?>
                  <input type="hidden" name="accion" value="toggle_visible_modulo">
                  <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
                  <button type="submit"
                    class="btn btn-sm <?= $vis_mod ? 'btn-visible-on' : 'btn-visible-off' ?>"
                    title="<?= $vis_mod ? 'Módulo visible para alumnos — clic para ocultar' : 'Módulo oculto para alumnos — clic para mostrar' ?>"
                    aria-label="<?= $vis_mod ? 'Ocultar módulo a alumnos' : 'Mostrar módulo a alumnos' ?>">
                    <?= $vis_mod ? 'Visible' : 'Oculto' ?>
                  </button>
                </form>
              </div>
            </header>

            <div class="course-class-list">
              <?php if (empty($modulo['unidades'])): ?>
                <div class="course-empty-state"><p>Este módulo todavía no tiene clases.</p></div>
              <?php endif; ?>

              <?php foreach ($modulo['unidades'] as $unidad): ?>
                <article class="course-editor-class" id="clase-<?= (int)$unidad['id_unidad'] ?>">
                  <header>
                    <div>
                      <span class="course-kicker">Clase <?= (int)$modulo['orden'] ?>.<?= (int)$unidad['orden'] ?></span>
                      <h3><?= htmlspecialchars($unidad['titulo']) ?></h3>
                      <?php if (!empty($unidad['descripcion'])): ?><p><?= nl2br(htmlspecialchars($unidad['descripcion'])) ?></p><?php endif; ?>
                    </div>
                    <div class="course-actions">
                      <?php foreach (['subir_unidad' => 'Subir', 'bajar_unidad' => 'Bajar'] as $accion => $texto): ?>
                        <form method="POST"><?php campo_base_curso($id_publicacion); ?><input type="hidden" name="accion" value="<?= $accion ?>"><input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>"><button class="btn btn-sm" type="submit"><?= $texto ?></button></form>
                      <?php endforeach; ?>
                      <button class="btn btn-sm" type="button" data-dialog-open="dialog-editar-clase-<?= (int)$unidad['id_unidad'] ?>">Editar</button>
                      <form method="POST" onsubmit="return confirm('¿Eliminar esta clase? También se eliminarán sus recursos asociados.');">
                        <?php campo_base_curso($id_publicacion); ?>
                        <input type="hidden" name="accion" value="eliminar_unidad">
                        <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                        <button class="btn-status btn-status-delete" type="submit">Eliminar</button>
                      </form>
                    </div>
                  </header>

                  <section class="course-resource-editor" aria-label="Recursos de la clase">
                    <div class="course-resource-editor__header">
                      <h4>Recursos</h4>
                      <?php if (!empty($unidad['recursos'])): ?>
                        <button class="btn btn-sm course-add-resource-btn" type="button" data-dialog-open="dialog-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">+ Agregar recurso</button>
                      <?php endif; ?>
                    </div>
                    <?php if (empty($unidad['recursos'])): ?>
                      <div class="course-empty-state course-empty-state-small">
                        <p>Esta clase todavía no tiene recursos.</p>
                        <button class="btn btn-sm" type="button" data-dialog-open="dialog-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">+ Agregar recurso</button>
                      </div>
                    <?php else: ?>
                      <ul class="course-resource-list course-resource-list-editor">
                        <?php foreach ($unidad['recursos'] as $r): ?>
                          <li class="course-resource">
                            <span class="course-resource-icon" aria-hidden="true"><?= htmlspecialchars(icono_recurso_curso($r['tipo'])) ?></span>
                            <div>
                              <strong><?= htmlspecialchars($r['titulo']) ?></strong>
                              <span><?= htmlspecialchars($r['tipo']) ?></span>
                              <?php if (!empty($r['descripcion'])): ?><p><?= nl2br(htmlspecialchars($r['descripcion'])) ?></p><?php endif; ?>
                              <div class="course-resource-actions">
                                <?php if ($r['url']): ?><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener">Abrir</a><?php endif; ?>
                                <?php if ($r['archivo']): ?><a href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$r['id_recurso'] ?>">Descargar</a><?php endif; ?>
                                <?php if ($r['tipo'] === 'Entrega de Tareas'): ?>
                                  <?php $tareaCard = $tareaRepoVista->buscarPorRecurso((int)$r['id_recurso']); ?>
                                  <?php if ($tareaCard): ?>
                                    <?php $resumenTarea = $entregaRepoVista->obtenerResumenPorTarea((int)$tareaCard->getId(), $id_publicacion); ?>
                                    <a href="entregas-tarea.php?id=<?= (int)$tareaCard->getId() ?>">Ver entregas</a>
                                    <span><?= (int)$resumenTarea['total'] ?> estudiantes · <?= (int)$resumenTarea['entregaron'] ?> entregaron · <?= (int)$resumenTarea['calificadas'] ?> calificadas</span>
                                  <?php else: ?>
                                    <span>Tarea pendiente de configuracion</span>
                                  <?php endif; ?>
                                <?php endif; ?>
                              </div>
                            </div>
                            <div class="course-actions course-resource-controls">
                              <?php if (($curso['estado'] ?? '') !== 'Archivado'): ?>
                              <?php foreach (['subir_recurso' => 'Subir', 'bajar_recurso' => 'Bajar'] as $accion => $texto): ?>
                                <form method="POST"><?php campo_base_curso($id_publicacion); ?><input type="hidden" name="accion" value="<?= $accion ?>"><input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>"><button class="btn btn-sm" type="submit"><?= $texto ?></button></form>
                              <?php endforeach; ?>
                              <button class="btn btn-sm" type="button" data-dialog-open="dialog-editar-recurso-<?= (int)$r['id_recurso'] ?>">Editar</button>
                              <form method="POST" class="inline-form" onsubmit="return confirm('&iquest;Eliminar este recurso?');">
                                <?php campo_base_curso($id_publicacion); ?>
                                <input type="hidden" name="accion" value="eliminar_recurso">
                                <input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>">
                                <button class="btn-status btn-status-delete" type="submit">Eliminar</button>
                              </form>
                              <?php endif; ?>
                              <!-- REQ-CON-04: Switch de visibilidad por recurso -->
                              <?php $vis_rec = (int)($r['visible_alumnos'] ?? 1); ?>
                              <form method="POST" class="inline-form">
                                <?php campo_base_curso($id_publicacion); ?>
                                <input type="hidden" name="accion" value="toggle_visible_recurso">
                                <input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>">
                                <button type="submit"
                                  class="btn btn-sm <?= $vis_rec ? 'btn-visible-on' : 'btn-visible-off' ?>"
                                  title="<?= $vis_rec ? 'Recurso visible para alumnos — clic para ocultar' : 'Recurso oculto para alumnos — clic para mostrar' ?>"
                                  aria-label="<?= $vis_rec ? 'Ocultar recurso a alumnos' : 'Mostrar recurso a alumnos' ?>">
                                  <?= $vis_rec ? 'Visible' : 'Oculto' ?>
                                </button>
                              </form>
                            </div>
                            <dialog class="course-dialog" id="dialog-editar-recurso-<?= (int)$r['id_recurso'] ?>" aria-labelledby="titulo-editar-recurso-<?= (int)$r['id_recurso'] ?>">
                              <div class="course-dialog-header">
                                <h2 id="titulo-editar-recurso-<?= (int)$r['id_recurso'] ?>">Editar recurso</h2>
                                <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
                              </div>
                              <form method="POST" enctype="multipart/form-data" class="form-grid compact-form">
                                <?php campo_base_curso($id_publicacion); ?>
                                <input type="hidden" name="accion" value="editar_recurso">
                                <input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>">
                                <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                                <label class="form-grid-full">Tipo de recurso
                                  <select name="tipo_recurso" data-resource-type-select>
                                    <?php foreach (tipos_recurso_curso() as $tipoR): ?>
                                      <option value="<?= $tipoR ?>" <?= $r['tipo'] === $tipoR ? 'selected' : '' ?>>
                                        <?= match($tipoR) {
                                          'Archivo' => 'Archivo (Documentos, PDFs, ZIPs)',
                                          'Foro' => 'Foro (Debate y consultas)',
                                          'Entrega de Tareas' => 'Entrega de Tareas (Consigna y TP)',
                                          'Video' => 'Video (YouTube o subido)',
                                          'PDF' => 'PDF',
                                          'Imagen' => 'Imagen',
                                          'Enlace' => 'Enlace externo',
                                          default => $tipoR
                                        } ?>
                                      </option>
                                    <?php endforeach; ?>
                                  </select>
                                </label>
                                <p class="course-type-hint form-grid-full" data-resource-hint></p>
                                <label class="form-grid-full">Título / Nombre
                                  <input name="titulo_recurso" value="<?= htmlspecialchars($r['titulo']) ?>" required maxlength="180">
                                </label>
                                <label data-field-container="url">URL o Enlace
                                  <input type="url" name="url_recurso" value="<?= htmlspecialchars($r['url'] ?? '') ?>" placeholder="https://...">
                                </label>
                                <label data-field-container="archivo">
                                  <span><?= !empty($r['archivo']) ? 'Reemplazar archivo' : 'Archivo adjunto' ?></span>
                                  <div class="course-file-input-wrapper">
                                    <input type="file" name="archivo_recurso">
                                    <button type="button" class="btn btn-sm btn-outline course-clear-file-btn" data-clear-file-btn hidden title="Quitar archivo seleccionado">✕ Quitar</button>
                                  </div>
                                </label>
                                <label data-field-container="orden">Orden
                                  <input type="number" min="1" name="orden" value="<?= (int)$r['orden'] ?>">
                                </label>
                                <label class="form-grid-full" data-field-container="descripcion">Consigna / Descripción
                                  <textarea name="descripcion_recurso" rows="3"><?= htmlspecialchars($r['descripcion'] ?? '') ?></textarea>
                                </label>
                                <?php campos_tarea($r['tipo'] === 'Entrega de Tareas' ? $tareaRepoVista->buscarPorRecurso((int)$r['id_recurso']) : null); ?>
                                <div class="course-dialog-actions form-grid-full">
                                  <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                                  <button class="btn btn-primary-action" type="submit">Guardar recurso</button>
                                </div>
                              </form>
                            </dialog>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>

                    <dialog class="course-dialog" id="dialog-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>" aria-labelledby="titulo-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">
                      <div class="course-dialog-header">
                        <h2 id="titulo-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">Agregar recurso o actividad</h2>
                        <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
                      </div>
                      <form method="POST" enctype="multipart/form-data" class="form-grid compact-form">
                        <?php campo_base_curso($id_publicacion); ?>
                        <input type="hidden" name="accion" value="agregar_recurso">
                        <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                        <label class="form-grid-full">Tipo de recurso
                          <select name="tipo_recurso" data-resource-type-select>
                            <?php foreach (tipos_recurso_curso() as $tipoR): ?>
                              <option value="<?= $tipoR ?>">
                                <?= match($tipoR) {
                                  'Archivo' => 'Archivo (Documentos, PDFs, ZIPs)',
                                  'Foro' => 'Foro (Debate y consultas)',
                                  'Entrega de Tareas' => 'Entrega de Tareas (Consigna y TP)',
                                  'Video' => 'Video (YouTube o subido)',
                                  'PDF' => 'PDF',
                                  'Imagen' => 'Imagen',
                                  'Enlace' => 'Enlace externo',
                                  default => $tipoR
                                } ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </label>
                        <p class="course-type-hint form-grid-full" data-resource-hint></p>
                        <label class="form-grid-full">Título / Nombre
                          <input name="titulo_recurso" placeholder="Ej: Guía de ejercicios / Consigna TP 1" required maxlength="180">
                        </label>
                        <label data-field-container="url">URL o Enlace
                          <input type="url" name="url_recurso" placeholder="https://...">
                        </label>
                        <label data-field-container="archivo">
                          <span>Archivo adjunto</span>
                          <div class="course-file-input-wrapper">
                            <input type="file" name="archivo_recurso">
                            <button type="button" class="btn btn-sm btn-outline course-clear-file-btn" data-clear-file-btn hidden title="Quitar archivo seleccionado">✕ Quitar</button>
                          </div>
                        </label>
                        <label data-field-container="orden">Orden
                          <input type="number" min="1" name="orden" value="<?= count($unidad['recursos']) + 1 ?>">
                        </label>
                        <label class="form-grid-full" data-field-container="descripcion">Consigna / Descripción
                          <textarea name="descripcion_recurso" rows="3" placeholder="Detallá la descripción, consigna o pautas para los estudiantes..."></textarea>
                        </label>
                        <?php campos_tarea(); ?>
                        <div class="course-dialog-actions form-grid-full">
                          <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                          <button class="btn btn-primary-action" type="submit">Agregar recurso</button>
                        </div>
                      </form>
                    </dialog>
                  </section>

                </article>
              <?php endforeach; ?>
            </div>

            <button class="btn course-add-class-btn" type="button" data-dialog-open="dialog-agregar-clase-<?= (int)$modulo['id_modulo'] ?>">+ Agregar clase</button>

          </article>

          <dialog class="course-dialog" id="dialog-editar-modulo-<?= (int)$modulo['id_modulo'] ?>" aria-labelledby="titulo-editar-modulo-<?= (int)$modulo['id_modulo'] ?>">
            <div class="course-dialog-header">
              <h2 id="titulo-editar-modulo-<?= (int)$modulo['id_modulo'] ?>">Editar módulo</h2>
              <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
            </div>
            <form method="POST" class="form-grid course-dialog-body">
              <?php campo_base_curso($id_publicacion); ?>
              <input type="hidden" name="accion" value="editar_modulo">
              <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
              <label>Nombre del módulo<input name="titulo" value="<?= htmlspecialchars($modulo['titulo']) ?>" required maxlength="180"></label>
              <label>Orden<input type="number" min="1" name="orden" value="<?= (int)$modulo['orden'] ?>"></label>
              <label class="form-grid-full">Descripción<textarea name="descripcion" rows="3"><?= htmlspecialchars($modulo['descripcion'] ?? '') ?></textarea></label>
              <div class="course-dialog-actions form-grid-full">
                <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                <button class="btn btn-primary-action" type="submit">Guardar módulo</button>
              </div>
            </form>
          </dialog>

          <dialog class="course-dialog" id="dialog-agregar-clase-<?= (int)$modulo['id_modulo'] ?>" aria-labelledby="titulo-agregar-clase-<?= (int)$modulo['id_modulo'] ?>">
            <div class="course-dialog-header">
              <h2 id="titulo-agregar-clase-<?= (int)$modulo['id_modulo'] ?>">Agregar clase</h2>
              <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
            </div>
            <form method="POST" class="form-grid course-dialog-body">
              <?php campo_base_curso($id_publicacion); ?>
              <input type="hidden" name="accion" value="agregar_unidad">
              <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
              <p class="course-dialog-context form-grid-full">Módulo: <strong><?= htmlspecialchars($modulo['titulo']) ?></strong></p>
              <label>Título<input name="titulo_unidad" required maxlength="180"></label>
              <label>Orden<input type="number" min="1" name="orden" value="<?= count($modulo['unidades']) + 1 ?>"></label>
              <label class="form-grid-full">Contenido<textarea name="descripcion_unidad" rows="4"></textarea></label>
              <div class="course-dialog-actions form-grid-full">
                <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                <button class="btn btn-primary-action" type="submit">Crear clase</button>
              </div>
            </form>
          </dialog>

          <?php foreach ($modulo['unidades'] as $unidad): ?>
            <dialog class="course-dialog" id="dialog-editar-clase-<?= (int)$unidad['id_unidad'] ?>" aria-labelledby="titulo-editar-clase-<?= (int)$unidad['id_unidad'] ?>">
              <div class="course-dialog-header">
                <h2 id="titulo-editar-clase-<?= (int)$unidad['id_unidad'] ?>">Editar clase</h2>
                <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
              </div>
              <form method="POST" class="form-grid course-dialog-body">
                <?php campo_base_curso($id_publicacion); ?>
                <input type="hidden" name="accion" value="editar_unidad">
                <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                <p class="course-dialog-context form-grid-full">Módulo: <strong><?= htmlspecialchars($modulo['titulo']) ?></strong></p>
                <label>Título<input name="titulo" value="<?= htmlspecialchars($unidad['titulo']) ?>" required maxlength="180"></label>
                <label>Orden<input type="number" min="1" name="orden" value="<?= (int)$unidad['orden'] ?>"></label>
                <label class="form-grid-full">Contenido<textarea name="descripcion" rows="4"><?= htmlspecialchars($unidad['descripcion'] ?? '') ?></textarea></label>
                <div class="course-dialog-actions form-grid-full">
                  <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                  <button class="btn btn-primary-action" type="submit">Guardar clase</button>
                </div>
              </form>
            </dialog>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </section>
    </div>

    <dialog class="course-dialog" id="dialog-agregar-modulo" aria-labelledby="titulo-agregar-modulo">
      <div class="course-dialog-header">
        <h2 id="titulo-agregar-modulo">Agregar módulo</h2>
        <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
      </div>
      <form method="POST" class="form-grid course-dialog-body">
        <?php campo_base_curso($id_publicacion); ?>
        <input type="hidden" name="accion" value="actualizar_aprobacion">
        <label>
          Porcentaje minimo de aprobacion
          <input type="number" name="porcentaje_minimo_aprobacion" min="0" max="100" value="<?= (int)($curso['porcentaje_minimo_aprobacion'] ?? 70) ?>">
        </label>
        <span class="text-muted">El alumno debera alcanzar al menos este porcentaje de progreso para aprobar el curso.</span>
        <button class="btn btn-sm btn-primary-action" type="submit">Guardar</button>
      </form>
    </section>

    <button class="btn btn-secondary course-structure-toggle" type="button" data-course-sidebar-toggle aria-expanded="false">Contenido del curso</button>

    <div class="course-editor-shell">
      <aside class="course-editor-sidebar" data-course-sidebar aria-label="Estructura del curso">
        <div class="course-sidebar-title">Estructura</div>
        <?php if (empty($contenido)): ?>
          <p class="course-empty-note">Este curso todavía no tiene módulos.</p>
        <?php else: ?>
          <?php foreach ($contenido as $modulo): ?>
            <details class="course-nav-module" open>
              <summary>
                <span><?= (int)$modulo['orden'] ?>. <?= htmlspecialchars($modulo['titulo']) ?></span>
                <small><?= count($modulo['unidades']) ?> clases</small>
              </summary>
              <?php if (empty($modulo['unidades'])): ?>
                <p class="course-empty-note">Sin clases.</p>
              <?php else: ?>
                <ol class="course-editor-index">
                  <?php foreach ($modulo['unidades'] as $unidad): ?>
                    <li><a href="#clase-<?= (int)$unidad['id_unidad'] ?>"><?= (int)$modulo['orden'] ?>.<?= (int)$unidad['orden'] ?> <?= htmlspecialchars($unidad['titulo']) ?></a></li>
                  <?php endforeach; ?>
                </ol>
              <?php endif; ?>
            </details>
          <?php endforeach; ?>
        <?php endif; ?>
        <button class="btn btn-primary-action course-full-action" type="button" data-dialog-open="dialog-agregar-modulo">+ Agregar módulo</button>
      </aside>

      <section class="course-editor-main" aria-label="Edicion del contenido">
        <?php if (empty($contenido)): ?>
          <div class="course-empty-state course-empty-state-hero">
            <span class="course-empty-icon" aria-hidden="true">+</span>
            <h2>Este curso todavía no tiene módulos.</h2>
            <p>Empezá creando el primer módulo para organizar las clases y materiales que verá el alumno.</p>
            <button class="btn btn-primary-action" type="button" data-dialog-open="dialog-agregar-modulo">+ Agregar primer módulo</button>
          </div>
        <?php endif; ?>

        <?php foreach ($contenido as $modulo): ?>
          <article class="course-editor-module" id="modulo-<?= (int)$modulo['id_modulo'] ?>">
            <header class="course-editor-module-header">
              <div>
                <span class="course-kicker">Módulo <?= (int)$modulo['orden'] ?></span>
                <h2><?= htmlspecialchars($modulo['titulo']) ?></h2>
                <?php if (!empty($modulo['descripcion'])): ?><p><?= nl2br(htmlspecialchars($modulo['descripcion'])) ?></p><?php endif; ?>
              </div>
              <div class="course-actions" aria-label="Acciones del módulo">
                <?php foreach (['subir_modulo' => 'Subir', 'bajar_modulo' => 'Bajar'] as $accion => $texto): ?>
                  <form method="POST"><?php campo_base_curso($id_publicacion); ?><input type="hidden" name="accion" value="<?= $accion ?>"><input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>"><button class="btn btn-sm" type="submit"><?= $texto ?></button></form>
                <?php endforeach; ?>
                <button class="btn btn-sm" type="button" data-dialog-open="dialog-editar-modulo-<?= (int)$modulo['id_modulo'] ?>">Editar</button>
                <form method="POST" onsubmit="return confirm('¿Eliminar este módulo? También se eliminarán sus clases y recursos.');">
                  <?php campo_base_curso($id_publicacion); ?>
                  <input type="hidden" name="accion" value="eliminar_modulo">
                  <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
                  <button class="btn-status btn-status-delete" type="submit">Eliminar</button>
                </form>
              </div>
            </header>

            <div class="course-class-list">
              <?php if (empty($modulo['unidades'])): ?>
                <div class="course-empty-state"><p>Este módulo todavía no tiene clases.</p></div>
              <?php endif; ?>

              <?php foreach ($modulo['unidades'] as $unidad): ?>
                <article class="course-editor-class" id="clase-<?= (int)$unidad['id_unidad'] ?>">
                  <header>
                    <div>
                      <span class="course-kicker">Clase <?= (int)$modulo['orden'] ?>.<?= (int)$unidad['orden'] ?></span>
                      <h3><?= htmlspecialchars($unidad['titulo']) ?></h3>
                      <?php if (!empty($unidad['descripcion'])): ?><p><?= nl2br(htmlspecialchars($unidad['descripcion'])) ?></p><?php endif; ?>
                    </div>
                    <div class="course-actions">
                      <?php foreach (['subir_unidad' => 'Subir', 'bajar_unidad' => 'Bajar'] as $accion => $texto): ?>
                        <form method="POST"><?php campo_base_curso($id_publicacion); ?><input type="hidden" name="accion" value="<?= $accion ?>"><input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>"><button class="btn btn-sm" type="submit"><?= $texto ?></button></form>
                      <?php endforeach; ?>
                      <button class="btn btn-sm" type="button" data-dialog-open="dialog-editar-clase-<?= (int)$unidad['id_unidad'] ?>">Editar</button>
                      <form method="POST" onsubmit="return confirm('¿Eliminar esta clase? También se eliminarán sus recursos asociados.');">
                        <?php campo_base_curso($id_publicacion); ?>
                        <input type="hidden" name="accion" value="eliminar_unidad">
                        <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                        <button class="btn-status btn-status-delete" type="submit">Eliminar</button>
                      </form>
                    </div>
                  </header>

                  <section class="course-resource-editor" aria-label="Recursos de la clase">
                    <div class="course-resource-editor__header">
                      <h4>Recursos</h4>
                      <?php if (!empty($unidad['recursos'])): ?>
                        <button class="btn btn-sm course-add-resource-btn" type="button" data-dialog-open="dialog-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">+ Agregar recurso</button>
                      <?php endif; ?>
                    </div>
                    <?php if (empty($unidad['recursos'])): ?>
                      <div class="course-empty-state course-empty-state-small">
                        <p>Esta clase todavía no tiene recursos.</p>
                        <button class="btn btn-sm" type="button" data-dialog-open="dialog-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">+ Agregar recurso</button>
                      </div>
                    <?php else: ?>
                      <ul class="course-resource-list course-resource-list-editor">
                        <?php foreach ($unidad['recursos'] as $r): ?>
                          <li class="course-resource">
                            <span class="course-resource-icon" aria-hidden="true"><?= htmlspecialchars(icono_recurso_curso($r['tipo'])) ?></span>
                            <div>
                              <strong><?= htmlspecialchars($r['titulo']) ?></strong>
                              <span><?= htmlspecialchars($r['tipo']) ?></span>
                              <?php if (!empty($r['descripcion'])): ?><p><?= nl2br(htmlspecialchars($r['descripcion'])) ?></p><?php endif; ?>
                              <div class="course-resource-actions">
                                <?php if ($r['url']): ?><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener">Abrir</a><?php endif; ?>
                                <?php if ($r['archivo']): ?><a href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$r['id_recurso'] ?>">Descargar</a><?php endif; ?>
                                <?php if ($r['tipo'] === 'Entrega de Tareas'): ?>
                                  <?php $tareaCard = $tareaRepoVista->buscarPorRecurso((int)$r['id_recurso']); ?>
                                  <?php if ($tareaCard): ?>
                                    <?php $resumenTarea = $entregaRepoVista->obtenerResumenPorTarea((int)$tareaCard->getId(), $id_publicacion); ?>
                                    <a href="entregas-tarea.php?id=<?= (int)$tareaCard->getId() ?>">Ver entregas</a>
                                    <span><?= (int)$resumenTarea['total'] ?> estudiantes · <?= (int)$resumenTarea['entregaron'] ?> entregaron · <?= (int)$resumenTarea['calificadas'] ?> calificadas</span>
                                  <?php else: ?>
                                    <span>Tarea pendiente de configuracion</span>
                                  <?php endif; ?>
                                <?php endif; ?>
                              </div>
                            </div>
                            <div class="course-actions course-resource-controls">
                              <?php foreach (['subir_recurso' => 'Subir', 'bajar_recurso' => 'Bajar'] as $accion => $texto): ?>
                                <form method="POST"><?php campo_base_curso($id_publicacion); ?><input type="hidden" name="accion" value="<?= $accion ?>"><input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>"><button class="btn btn-sm" type="submit"><?= $texto ?></button></form>
                              <?php endforeach; ?>
                              <button class="btn btn-sm" type="button" data-dialog-open="dialog-editar-recurso-<?= (int)$r['id_recurso'] ?>">Editar</button>
                              <form method="POST" class="inline-form" onsubmit="return confirm('¿Eliminar este recurso?');">
                                <?php campo_base_curso($id_publicacion); ?>
                                <input type="hidden" name="accion" value="eliminar_recurso">
                                <input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>">
                                <button class="btn-status btn-status-delete" type="submit">Eliminar</button>
                              </form>
                            </div>
                            <dialog class="course-dialog" id="dialog-editar-recurso-<?= (int)$r['id_recurso'] ?>" aria-labelledby="titulo-editar-recurso-<?= (int)$r['id_recurso'] ?>">
                              <div class="course-dialog-header">
                                <h2 id="titulo-editar-recurso-<?= (int)$r['id_recurso'] ?>">Editar recurso</h2>
                                <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
                              </div>
                              <form method="POST" enctype="multipart/form-data" class="form-grid compact-form">
                                <?php campo_base_curso($id_publicacion); ?>
                                <input type="hidden" name="accion" value="editar_recurso">
                                <input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>">
                                <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                                <label class="form-grid-full">Tipo de recurso
                                  <select name="tipo_recurso" data-resource-type-select>
                                    <?php foreach (tipos_recurso_curso() as $tipoR): ?>
                                      <option value="<?= $tipoR ?>" <?= $r['tipo'] === $tipoR ? 'selected' : '' ?>>
                                        <?= match($tipoR) {
                                          'Archivo' => 'Archivo (Documentos, PDFs, ZIPs)',
                                          'Foro' => 'Foro (Debate y consultas)',
                                          'Entrega de Tareas' => 'Entrega de Tareas (Consigna y TP)',
                                          'Video' => 'Video (YouTube o subido)',
                                          'PDF' => 'PDF',
                                          'Imagen' => 'Imagen',
                                          'Enlace' => 'Enlace externo',
                                          default => $tipoR
                                        } ?>
                                      </option>
                                    <?php endforeach; ?>
                                  </select>
                                </label>
                                <p class="course-type-hint form-grid-full" data-resource-hint></p>
                                <label class="form-grid-full">Título / Nombre
                                  <input name="titulo_recurso" value="<?= htmlspecialchars($r['titulo']) ?>" required maxlength="180">
                                </label>
                                <label data-field-container="url">URL o Enlace
                                  <input type="url" name="url_recurso" value="<?= htmlspecialchars($r['url'] ?? '') ?>" placeholder="https://...">
                                </label>
                                <label data-field-container="archivo">
                                  <span><?= !empty($r['archivo']) ? 'Reemplazar archivo' : 'Archivo adjunto' ?></span>
                                  <div class="course-file-input-wrapper">
                                    <input type="file" name="archivo_recurso">
                                    <button type="button" class="btn btn-sm btn-outline course-clear-file-btn" data-clear-file-btn hidden title="Quitar archivo seleccionado">✕ Quitar</button>
                                  </div>
                                </label>
                                <label data-field-container="orden">Orden
                                  <input type="number" min="1" name="orden" value="<?= (int)$r['orden'] ?>">
                                </label>
                                <label class="form-grid-full" data-field-container="descripcion">Consigna / Descripción
                                  <textarea name="descripcion_recurso" rows="3"><?= htmlspecialchars($r['descripcion'] ?? '') ?></textarea>
                                </label>
                                <?php campos_tarea($r['tipo'] === 'Entrega de Tareas' ? $tareaRepoVista->buscarPorRecurso((int)$r['id_recurso']) : null); ?>
                                <div class="course-dialog-actions form-grid-full">
                                  <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                                  <button class="btn btn-primary-action" type="submit">Guardar recurso</button>
                                </div>
                              </form>
                            </dialog>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>

                    <dialog class="course-dialog" id="dialog-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>" aria-labelledby="titulo-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">
                      <div class="course-dialog-header">
                        <h2 id="titulo-agregar-recurso-<?= (int)$unidad['id_unidad'] ?>">Agregar recurso o actividad</h2>
                        <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
                      </div>
                      <form method="POST" enctype="multipart/form-data" class="form-grid compact-form">
                        <?php campo_base_curso($id_publicacion); ?>
                        <input type="hidden" name="accion" value="agregar_recurso">
                        <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                        <label class="form-grid-full">Tipo de recurso
                          <select name="tipo_recurso" data-resource-type-select>
                            <?php foreach (tipos_recurso_curso() as $tipoR): ?>
                              <option value="<?= $tipoR ?>">
                                <?= match($tipoR) {
                                  'Archivo' => 'Archivo (Documentos, PDFs, ZIPs)',
                                  'Foro' => 'Foro (Debate y consultas)',
                                  'Entrega de Tareas' => 'Entrega de Tareas (Consigna y TP)',
                                  'Video' => 'Video (YouTube o subido)',
                                  'PDF' => 'PDF',
                                  'Imagen' => 'Imagen',
                                  'Enlace' => 'Enlace externo',
                                  default => $tipoR
                                } ?>
                              </option>
                            <?php endforeach; ?>
                          </select>
                        </label>
                        <p class="course-type-hint form-grid-full" data-resource-hint></p>
                        <label class="form-grid-full">Título / Nombre
                          <input name="titulo_recurso" placeholder="Ej: Guía de ejercicios / Consigna TP 1" required maxlength="180">
                        </label>
                        <label data-field-container="url">URL o Enlace
                          <input type="url" name="url_recurso" placeholder="https://...">
                        </label>
                        <label data-field-container="archivo">
                          <span>Archivo adjunto</span>
                          <div class="course-file-input-wrapper">
                            <input type="file" name="archivo_recurso">
                            <button type="button" class="btn btn-sm btn-outline course-clear-file-btn" data-clear-file-btn hidden title="Quitar archivo seleccionado">✕ Quitar</button>
                          </div>
                        </label>
                        <label data-field-container="orden">Orden
                          <input type="number" min="1" name="orden" value="<?= count($unidad['recursos']) + 1 ?>">
                        </label>
                        <label class="form-grid-full" data-field-container="descripcion">Consigna / Descripción
                          <textarea name="descripcion_recurso" rows="3" placeholder="Detallá la descripción, consigna o pautas para los estudiantes..."></textarea>
                        </label>
                        <?php campos_tarea(); ?>
                        <div class="course-dialog-actions form-grid-full">
                          <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                          <button class="btn btn-primary-action" type="submit">Agregar recurso</button>
                        </div>
                      </form>
                    </dialog>
                  </section>

                </article>
              <?php endforeach; ?>
            </div>

            <button class="btn course-add-class-btn" type="button" data-dialog-open="dialog-agregar-clase-<?= (int)$modulo['id_modulo'] ?>">+ Agregar clase</button>

          </article>

          <dialog class="course-dialog" id="dialog-editar-modulo-<?= (int)$modulo['id_modulo'] ?>" aria-labelledby="titulo-editar-modulo-<?= (int)$modulo['id_modulo'] ?>">
            <div class="course-dialog-header">
              <h2 id="titulo-editar-modulo-<?= (int)$modulo['id_modulo'] ?>">Editar módulo</h2>
              <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
            </div>
            <form method="POST" class="form-grid course-dialog-body">
              <?php campo_base_curso($id_publicacion); ?>
              <input type="hidden" name="accion" value="editar_modulo">
              <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
              <label>Nombre del módulo<input name="titulo" value="<?= htmlspecialchars($modulo['titulo']) ?>" required maxlength="180"></label>
              <label>Orden<input type="number" min="1" name="orden" value="<?= (int)$modulo['orden'] ?>"></label>
              <label class="form-grid-full">Descripción<textarea name="descripcion" rows="3"><?= htmlspecialchars($modulo['descripcion'] ?? '') ?></textarea></label>
              <div class="course-dialog-actions form-grid-full">
                <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                <button class="btn btn-primary-action" type="submit">Guardar módulo</button>
              </div>
            </form>
          </dialog>

          <dialog class="course-dialog" id="dialog-agregar-clase-<?= (int)$modulo['id_modulo'] ?>" aria-labelledby="titulo-agregar-clase-<?= (int)$modulo['id_modulo'] ?>">
            <div class="course-dialog-header">
              <h2 id="titulo-agregar-clase-<?= (int)$modulo['id_modulo'] ?>">Agregar clase</h2>
              <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
            </div>
            <form method="POST" class="form-grid course-dialog-body">
              <?php campo_base_curso($id_publicacion); ?>
              <input type="hidden" name="accion" value="agregar_unidad">
              <input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
              <p class="course-dialog-context form-grid-full">Módulo: <strong><?= htmlspecialchars($modulo['titulo']) ?></strong></p>
              <label>Título<input name="titulo_unidad" required maxlength="180"></label>
              <label>Orden<input type="number" min="1" name="orden" value="<?= count($modulo['unidades']) + 1 ?>"></label>
              <label class="form-grid-full">Contenido<textarea name="descripcion_unidad" rows="4"></textarea></label>
              <div class="course-dialog-actions form-grid-full">
                <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                <button class="btn btn-primary-action" type="submit">Crear clase</button>
              </div>
            </form>
          </dialog>

          <?php foreach ($modulo['unidades'] as $unidad): ?>
            <dialog class="course-dialog" id="dialog-editar-clase-<?= (int)$unidad['id_unidad'] ?>" aria-labelledby="titulo-editar-clase-<?= (int)$unidad['id_unidad'] ?>">
              <div class="course-dialog-header">
                <h2 id="titulo-editar-clase-<?= (int)$unidad['id_unidad'] ?>">Editar clase</h2>
                <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
              </div>
              <form method="POST" class="form-grid course-dialog-body">
                <?php campo_base_curso($id_publicacion); ?>
                <input type="hidden" name="accion" value="editar_unidad">
                <input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>">
                <p class="course-dialog-context form-grid-full">Módulo: <strong><?= htmlspecialchars($modulo['titulo']) ?></strong></p>
                <label>Título<input name="titulo" value="<?= htmlspecialchars($unidad['titulo']) ?>" required maxlength="180"></label>
                <label>Orden<input type="number" min="1" name="orden" value="<?= (int)$unidad['orden'] ?>"></label>
                <label class="form-grid-full">Contenido<textarea name="descripcion" rows="4"><?= htmlspecialchars($unidad['descripcion'] ?? '') ?></textarea></label>
                <div class="course-dialog-actions form-grid-full">
                  <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                  <button class="btn btn-primary-action" type="submit">Guardar clase</button>
                </div>
              </form>
            </dialog>
          <?php endforeach; ?>
        <?php endforeach; ?>
      </section>
    </div>

    <dialog class="course-dialog" id="dialog-agregar-modulo" aria-labelledby="titulo-agregar-modulo">
      <div class="course-dialog-header">
        <h2 id="titulo-agregar-modulo">Agregar módulo</h2>
        <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">×</button>
      </div>
      <form method="POST" class="form-grid course-dialog-body">
        <?php campo_base_curso($id_publicacion); ?>
        <input type="hidden" name="accion" value="agregar_modulo">
        <label>Nombre del módulo<input type="text" name="titulo_modulo" required maxlength="180"></label>
        <label>Orden<input type="number" name="orden" min="1" value="<?= count($contenido) + 1 ?>"></label>
        <label class="form-grid-full">Descripción<textarea name="descripcion_modulo" rows="4"></textarea></label>
        <div class="course-dialog-actions form-grid-full">
          <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
          <button class="btn btn-primary-action" type="submit">Crear módulo</button>
        </div>
      </form>
    </dialog>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>


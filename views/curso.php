<?php
require_once '../php/publicaciones/detalle_curso.php';

$title = $curso ? htmlspecialchars($curso['titulo']) : 'Curso no encontrado';
$description = $curso ? htmlspecialchars(mb_strimwidth($curso['descripcion'], 0, 150, '...')) : 'Detalle del curso en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$bodyClass = 'course-page';
$activePage = 'catalogo';

$clases_planas = [];
foreach ($contenido_curso as $modulo) {
    foreach ($modulo['unidades'] as $unidad) {
        $clases_planas[] = ['modulo' => $modulo, 'unidad' => $unidad];
    }
}
$id_unidad_actual = (int)($_GET['unidad'] ?? 0);
$indice_actual = null;
foreach ($clases_planas as $i => $item) {
    if ((int)$item['unidad']['id_unidad'] === $id_unidad_actual) $indice_actual = $i;
}
if ($indice_actual === null && !empty($clases_planas) && $id_unidad_actual > 0) $indice_actual = 0;
$item_actual = $indice_actual !== null ? $clases_planas[$indice_actual] : null;
$prev = $indice_actual !== null && isset($clases_planas[$indice_actual - 1]) ? $clases_planas[$indice_actual - 1]['unidad'] : null;
$next = $indice_actual !== null && isset($clases_planas[$indice_actual + 1]) ? $clases_planas[$indice_actual + 1]['unidad'] : null;
$puedeAgregar = esta_autenticado();

include '../includes/header.php';
?>

<main class="product-detail-container motion-entry">
  <nav aria-label="Ruta de navegación" class="breadcrumb-nav">
    <ol class="breadcrumb-list">
      <li><a href="../index.php">Inicio</a> /</li>
      <li><a href="catalogo.php?tipo=curso">Cursos</a> /</li>
      <li aria-current="page"><?= htmlspecialchars($curso ? $curso['titulo'] : 'Detalle') ?></li>
    </ol>
  </nav>

  <?php 
    $mensaje_exito_curso = $_SESSION['curso_exito'] ?? '';
    unset($_SESSION['curso_exito']);
    $mensaje_error_curso = $_SESSION['curso_error'] ?? '';
    unset($_SESSION['curso_error']);
  ?>
  <?php if (!empty($mensaje_exito_curso)): ?>
    <div class="alert alert-success u-mb-md"><?= htmlspecialchars($mensaje_exito_curso) ?></div>
  <?php endif; ?>
  <?php if (!empty($mensaje_error_curso)): ?>
    <div class="alert alert-error u-mb-md"><?= htmlspecialchars($mensaje_error_curso) ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['completado'])): ?>
    <div class="alert alert-success u-mb-md">🎉 ¡Felicitaciones! Has completado el curso exitosamente.</div>
  <?php endif; ?>

  <?php if (!$curso): ?>
    <section class="empty-state" aria-labelledby="sin-curso">
      <h1 id="sin-curso">Curso no encontrado</h1>
      <p>El curso que buscas no existe o no se encuentra disponible actualmente.</p>
      <p><a href="catalogo.php?tipo=curso" class="btn">Explorar catálogo de cursos</a></p>
    </section>
  <?php elseif ($puede_ver_recursos): ?>
    <?php if (!$item_actual): ?>
      <section class="course-page-hero" aria-labelledby="course-page-title">
        <div class="course-page-hero__body">
          <span class="badge badge-course"><?= htmlspecialchars($curso['nombre_categoria']) ?></span>
          <h1 id="course-page-title"><?= htmlspecialchars($curso['titulo']) ?></h1>
          <p class="course-page-teacher">Curso de <a href="proveedor.php?id=<?= (int)$curso['autor_id'] ?>"><?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?></a></p>
          <p class="course-page-description"><?= htmlspecialchars(mb_strimwidth($curso['descripcion'], 0, 180, '...')) ?></p>
          <dl class="course-page-summary-grid" aria-label="Resumen del curso">
            <div><dt>Módulos</dt><dd><?= count($contenido_curso) ?></dd></div>
            <div><dt>Clases</dt><dd><?= count($clases_planas) ?></dd></div>
            <div><dt>Modalidad</dt><dd><?= htmlspecialchars($curso['modalidad'] ?: 'A definir') ?></dd></div>
            <div><dt>Duración</dt><dd><?= !empty($curso['duracion_horas']) ? ((int)$curso['duracion_horas'] . ' h') : 'A definir' ?></dd></div>
          </dl>
        </div>
        <figure class="course-page-cover">
          <?php if (!empty($curso['imagen'])): ?>
            <img src="../<?= htmlspecialchars($curso['imagen']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>">
          <?php else: ?>
            <div class="course-page-cover__placeholder">Classia</div>
          <?php endif; ?>
        </figure>
      </section>
    <?php endif; ?>
    <button class="btn course-mobile-toggle" type="button" data-course-sidebar-toggle aria-controls="course-sidebar" aria-expanded="false">☰ Contenido del curso</button>
    <div class="course-shell<?= !$item_actual ? ' course-shell--overview' : '' ?>">
      <aside class="course-sidebar" id="course-sidebar" data-course-sidebar aria-label="Contenido del curso">
        <div class="course-sidebar-title">Contenido del curso</div>
        <a class="course-sidebar-general<?= $item_actual ? '' : ' is-active' ?>" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>" <?= $item_actual ? '' : 'aria-current="page"' ?>></a>
        <?php foreach ($contenido_curso as $modulo): ?>
          <details class="course-nav-module" open>
            <summary>
              <span><?= (int)$modulo['orden'] ?>. <?= htmlspecialchars($modulo['titulo']) ?></span>
              <small><?= count($modulo['unidades']) ?> clases</small>
            </summary>
            <?php if (empty($modulo['unidades'])): ?>
              <p class="course-empty-note">Este módulo todavía no tiene clases.</p>
            <?php else: ?>
              <ol class="course-lesson-list">
                <?php foreach ($modulo['unidades'] as $unidad): ?>
                  <?php $activa = $item_actual && (int)$item_actual['unidad']['id_unidad'] === (int)$unidad['id_unidad']; ?>
                  <li class="course-lesson-item<?= $activa ? ' is-active' : '' ?>">
                    <a class="course-lesson-link<?= $activa ? ' is-active' : '' ?>" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$unidad['id_unidad'] ?>" <?= $activa ? 'aria-current="page"' : '' ?>>
                      <span class="course-lesson-code"><?= (int)$modulo['orden'] ?>.<?= (int)$unidad['orden'] ?></span>
                      <span class="course-lesson-title"><?= htmlspecialchars($unidad['titulo']) ?></span>
                    </a>
                    <?php if (!empty($unidad['recursos'])): ?>
                      <ul class="course-subresource-list" aria-label="Recursos de <?= htmlspecialchars($unidad['titulo']) ?>">
                        <?php foreach ($unidad['recursos'] as $idxR => $rec): ?>
                          <?php 
                            $subCodigo = (int)$modulo['orden'] . '.' . (int)$unidad['orden'] . '.' . ($idxR + 1);
                            $emoji = icono_emoji_recurso($rec['tipo']);
                            $hrefRec = $activa ? ('#recurso-' . (int)$rec['id_recurso']) : ('curso.php?id=' . (int)$curso['id_publicacion'] . '&unidad=' . (int)$unidad['id_unidad'] . '#recurso-' . (int)$rec['id_recurso']);
                          ?>
                          <li class="course-subresource-item">
                            <a class="course-subresource-link" href="<?= $hrefRec ?>" title="<?= htmlspecialchars($rec['titulo']) ?> (<?= htmlspecialchars($rec['tipo']) ?>)">
                              <span class="course-subresource-code"><?= $subCodigo ?></span>
                              <span class="course-subresource-icon" aria-hidden="true"><?= $emoji ?></span>
                              <span class="course-subresource-text"><?= htmlspecialchars($rec['titulo']) ?></span>
                            </a>
                          </li>
                        <?php endforeach; ?>
                      </ul>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ol>
            <?php endif; ?>
          </details>
        <?php endforeach; ?>
      </aside>

      <article class="course-content" aria-live="polite">
        <?php if (!$item_actual): ?>
          <section class="course-overview">
            <h2>Información del curso</h2>
            <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>
          </section>
          <?php if (!empty($clases_planas)): ?>
            <nav class="course-lesson-nav" aria-label="Navegación de clases">
              <span></span>
              <a class="btn" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$clases_planas[0]['unidad']['id_unidad'] ?>">Primera clase →</a>
            </nav>
          <?php else: ?>
            <div class="course-empty-state"><p>Este curso todavía no tiene clases cargadas.</p></div>
          <?php endif; ?>
        <?php else: ?>
          <?php $modulo = $item_actual['modulo']; $unidad = $item_actual['unidad']; ?>
          <header class="course-content-header">
            <span class="course-kicker"><?= htmlspecialchars($curso['titulo']) ?></span>
            <p><?= (int)$modulo['orden'] ?>. <?= htmlspecialchars($modulo['titulo']) ?></p>
            <h1><?= htmlspecialchars($unidad['titulo']) ?></h1>

          </header>
          <section class="course-lesson-body">
            <?php 
              $videos_clase = array_filter($unidad['recursos'] ?? [], fn($r) => $r['tipo'] === 'Video');
            ?>
            <?php if (!empty($videos_clase)): ?>
              <?php foreach ($videos_clase as $idxV => $vid): ?>
                <?php 
                  $posRecurso = array_search($vid['id_recurso'], array_column($unidad['recursos'], 'id_recurso'));
                  $codVid = (int)$modulo['orden'] . '.' . (int)$unidad['orden'] . '.' . ($posRecurso !== false ? $posRecurso + 1 : $idxV + 1);
                  $ytEmbed = !empty($vid['url']) ? obtener_youtube_embed_url($vid['url']) : null; 
                ?>
                <div class="course-video-wrapper u-mb-md" id="recurso-<?= (int)$vid['id_recurso'] ?>">
                  <div class="course-video-header u-mb-xs" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                    <div>
                      <span class="course-resource-code"><?= $codVid ?></span>
                      <strong>🎥 <?= htmlspecialchars($vid['titulo']) ?></strong>
                    </div>
                    <?php if (!empty($vid['url'])): ?>
                      <a href="<?= htmlspecialchars($vid['url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                        🔗 Abrir en pestaña nueva ↗
                      </a>
                    <?php elseif (!empty($vid['archivo'])): ?>
                      <a href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$vid['id_recurso'] ?>&modo=inline" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-outline">
                        🔗 Abrir en pestaña nueva ↗
                      </a>
                    <?php endif; ?>
                  </div>
                  <?php if ($embedUrl = obtener_video_embed_url($vid['url'] ?? '')): ?>
                    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:var(--radius-lg);background:#000;box-shadow:var(--shadow-md);">
                      <iframe src="<?= htmlspecialchars($embedUrl) ?>" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen title="<?= htmlspecialchars($vid['titulo']) ?>"></iframe>
                    </div>
                  <?php elseif (!empty($vid['archivo'])): ?>
                    <video controls style="width:100%;border-radius:var(--radius-lg);max-height:640px;background:#000;box-shadow:var(--shadow-md);display:block;">
                      <source src="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$vid['id_recurso'] ?>">
                      Tu navegador no soporta la reproducción directa de video.
                    </video>
                  <?php elseif (!empty($vid['url'])): ?>
                    <div style="position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:var(--radius-lg);background:#000;box-shadow:var(--shadow-md);">
                      <iframe src="<?= htmlspecialchars($vid['url']) ?>" style="position:absolute;top:0;left:0;width:100%;height:100%;border:0;" allowfullscreen title="<?= htmlspecialchars($vid['titulo']) ?>"></iframe>
                    </div>
                  <?php endif; ?>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>

            <?php if (!empty($unidad['descripcion'])): ?>
              <p><?= nl2br(htmlspecialchars($unidad['descripcion'])) ?></p>
            <?php elseif (empty($videos_clase)): ?>
              <div class="course-empty-state"><p>Esta clase todavía no tiene contenido descriptivo.</p></div>
            <?php endif; ?>
          </section>

          <section class="course-materials" aria-labelledby="materiales-clase">
            <h2 id="materiales-clase">Materiales y actividades de la clase</h2>
            <?php if (empty($unidad['recursos'])): ?>
              <div class="course-empty-state"><p>Esta clase todavía no tiene recursos.</p></div>
            <?php else: ?>
              <ul class="course-resource-list">
                <?php foreach ($unidad['recursos'] as $idxRec => $recurso): ?>
                  <?php 
                    $codRec = (int)$modulo['orden'] . '.' . (int)$unidad['orden'] . '.' . ($idxRec + 1);
                    $emoji = icono_emoji_recurso($recurso['tipo']);
                  ?>
                  <li class="course-resource course-resource--<?= strtolower(str_replace(' ', '-', $recurso['tipo'])) ?>" id="recurso-<?= (int)$recurso['id_recurso'] ?>">
                    <span class="course-resource-icon" aria-hidden="true"><?= $emoji ?></span>
                    <div>
                      <div class="course-resource-title-row">
                        <span class="course-resource-code"><?= $codRec ?></span>
                        <strong><?= htmlspecialchars($recurso['titulo']) ?></strong>
                        <span class="course-resource-tag"><?= htmlspecialchars($recurso['tipo']) ?></span>
                      </div>
                      <?php if (!empty($recurso['descripcion'])): ?><p><?= nl2br(htmlspecialchars($recurso['descripcion'])) ?></p><?php endif; ?>
                      <div class="course-resource-actions">
                        <?php if (!empty($recurso['url']) && $recurso['tipo'] !== 'Foro'): ?>
                          <a class="btn btn-sm" href="<?= htmlspecialchars($recurso['url']) ?>" target="_blank" rel="noopener">
                            <?= match($recurso['tipo']) {
                              'Entrega de Tareas' => '🔗 Ver consigna externa',
                              'Video' => '🎥 Ver video',
                              default => '🔗 Abrir enlace'
                            } ?>
                          </a>
                        <?php endif; ?>
                        <?php if (!empty($recurso['archivo'])): ?>
                          <?php $esVisualizable = recurso_es_visualizable_nativamente($recurso['tipo'], $recurso['archivo']); ?>
                          <?php if ($esVisualizable): ?>
                            <button class="btn btn-sm btn-primary-action" type="button" data-dialog-open="dialog-doc-<?= (int)$recurso['id_recurso'] ?>">
                              <?= match($recurso['tipo']) {
                                'PDF' => 'Leer documento PDF',
                                'Imagen' => 'Ver imagen',
                                'Entrega de Tareas' => 'Ver consigna',
                                default => 'Leer documento'
                              } ?>
                            </button>
                            <a class="btn btn-sm btn-outline" href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$recurso['id_recurso'] ?>&modo=descargar">
                              <?= match($recurso['tipo']) {
                                'Entrega de Tareas' => '📥 Descargar consigna',
                                'Video' => '🎥 Descargar video',
                                default => '📥 Descargar'
                              } ?>
                            </a>
                          <?php else: ?>
                            <a class="btn btn-sm" href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$recurso['id_recurso'] ?>&modo=descargar">
                              <?= match($recurso['tipo']) {
                                'Entrega de Tareas' => '📥 Descargar consigna',
                                'Video' => '🎥 Descargar video',
                                default => '📥 Descargar archivo'
                              } ?>
                            </a>
                          <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($recurso['tipo'] === 'Foro'): ?>
                          <?php $msgs = $mensajes_foro[$recurso['id_recurso']] ?? []; ?>
                          <button class="btn btn-sm btn-primary-action" type="button" data-dialog-open="dialog-foro-<?= (int)$recurso['id_recurso'] ?>">
                            💬 Participar en el foro (<?= count($msgs) ?> <?= count($msgs) === 1 ? 'mensaje' : 'mensajes' ?>)
                          </button>
                          <?php if (!empty($recurso['url'])): ?>
                            <a class="btn btn-sm btn-outline" href="<?= htmlspecialchars($recurso['url']) ?>" target="_blank" rel="noopener">🔗 Enlace complementario</a>
                          <?php endif; ?>
                        <?php endif; ?>
                        <?php if ($recurso['tipo'] === 'Entrega de Tareas'): ?>
                          <?php $entrega = $mis_entregas[$recurso['id_recurso']] ?? null; ?>
                          <?php if ($entrega): ?>
                            <button class="btn btn-sm btn-outline" type="button" data-dialog-open="dialog-entrega-<?= (int)$recurso['id_recurso'] ?>">
                              ✓ Ver / Actualizar entrega
                            </button>
                          <?php else: ?>
                            <button class="btn btn-sm btn-primary-action" type="button" data-dialog-open="dialog-entrega-<?= (int)$recurso['id_recurso'] ?>">
                              📤 Entregar tarea
                            </button>
                          <?php endif; ?>
                        <?php endif; ?>
                      </div>
                    </div>

                    <?php if ($recurso['tipo'] === 'Foro'): ?>
                      <?php $msgs = $mensajes_foro[$recurso['id_recurso']] ?? []; ?>
                      <dialog class="course-dialog" id="dialog-foro-<?= (int)$recurso['id_recurso'] ?>" aria-labelledby="titulo-dialog-foro-<?= (int)$recurso['id_recurso'] ?>">
                        <div class="course-dialog-header">
                          <h2 id="titulo-dialog-foro-<?= (int)$recurso['id_recurso'] ?>">💬 Foro de debate: <?= htmlspecialchars($recurso['titulo']) ?></h2>
                          <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">X</button>
                        </div>
                        <div class="course-dialog-body">
                          <?php if (!empty($recurso['descripcion'])): ?>
                            <div class="course-delivery-consigna u-mb-md">
                              <strong>Tema / Pautas del debate:</strong>
                              <p><?= nl2br(htmlspecialchars($recurso['descripcion'])) ?></p>
                            </div>
                          <?php endif; ?>

                          <div class="course-forum-thread u-mb-md">
                            <h3>Comentarios y consultas (<?= count($msgs) ?>)</h3>
                            <?php if (empty($msgs)): ?>
                              <p class="text-muted u-mt-xs">Aún no hay mensajes en este debate. ¡Sé el primero en participar!</p>
                            <?php else: ?>
                              <div class="course-forum-messages-list">
                                <?php foreach ($msgs as $msg): ?>
                                  <article class="course-forum-message">
                                    <div class="course-forum-author">
                                      <strong><?= htmlspecialchars($msg['nombre'] . ' ' . $msg['apellido']) ?></strong>
                                      <span class="badge badge-sm"><?= htmlspecialchars($msg['nombre_rol'] ?? 'Estudiante') ?></span>
                                      <time class="course-forum-time"><?= date('d/m/Y H:i', strtotime($msg['fecha_mensaje'])) ?></time>
                                    </div>
                                    <div class="course-forum-body">
                                      <?= nl2br(htmlspecialchars($msg['mensaje'])) ?>
                                    </div>
                                  </article>
                                <?php endforeach; ?>
                              </div>
                            <?php endif; ?>
                          </div>

                          <form method="POST" class="form-grid course-forum-form">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="accion" value="publicar_mensaje_foro">
                            <input type="hidden" name="id_recurso" value="<?= (int)$recurso['id_recurso'] ?>">
                            <input type="hidden" name="id_unidad" value="<?= (int)($unidad['id_unidad'] ?? 0) ?>">

                            <label class="form-grid-full">
                              Escribir en el debate
                              <textarea name="mensaje_foro" rows="3" required placeholder="Escribí tu consulta, respuesta o comentario para el docente y tus compañeros..."></textarea>
                            </label>

                            <div class="course-dialog-actions form-grid-full">
                              <button class="btn btn-secondary" type="button" data-dialog-close>Cerrar</button>
                              <button class="btn btn-primary-action" type="submit">💬 Publicar en el foro</button>
                            </div>
                          </form>
                        </div>
                      </dialog>
                    <?php endif; ?>

                    <?php if (!empty($recurso['archivo']) && recurso_es_visualizable_nativamente($recurso['tipo'], $recurso['archivo'])): ?>
                      <dialog class="course-dialog course-dialog--doc-viewer" id="dialog-doc-<?= (int)$recurso['id_recurso'] ?>" aria-labelledby="titulo-dialog-doc-<?= (int)$recurso['id_recurso'] ?>">
                        <div class="course-dialog-header">
                          <h2 id="titulo-dialog-doc-<?= (int)$recurso['id_recurso'] ?>">📄 <?= htmlspecialchars($recurso['titulo']) ?></h2>
                          <div class="course-dialog-header-actions">
                            <a class="btn btn-sm btn-outline" href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$recurso['id_recurso'] ?>&modo=inline" target="_blank" rel="noopener" title="Abrir en pestaña completa">⛶ Pantalla completa</a>
                            <a class="btn btn-sm btn-outline" href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$recurso['id_recurso'] ?>&modo=descargar" title="Descargar archivo">📥 Descargar</a>
                            <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">X</button>
                          </div>
                        </div>
                        <div class="course-dialog-body course-dialog-body--viewer">
                          <?php if (!empty($recurso['descripcion'])): ?>
                            <p class="course-doc-viewer__desc"><?= nl2br(htmlspecialchars($recurso['descripcion'])) ?></p>
                          <?php endif; ?>
                          <div class="course-doc-viewer__frame-wrap">
                            <iframe 
                              src="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$recurso['id_recurso'] ?>&modo=inline" 
                              class="course-doc-viewer__frame" 
                              title="<?= htmlspecialchars($recurso['titulo']) ?>"
                              loading="lazy">
                            </iframe>
                          </div>
                        </div>
                      </dialog>
                    <?php endif; ?>

                    <?php if ($recurso['tipo'] === 'Entrega de Tareas'): ?>
                      <?php $entrega = $mis_entregas[$recurso['id_recurso']] ?? null; ?>
                      <dialog class="course-dialog" id="dialog-entrega-<?= (int)$recurso['id_recurso'] ?>" aria-labelledby="titulo-dialog-entrega-<?= (int)$recurso['id_recurso'] ?>">
                        <div class="course-dialog-header">
                          <h2 id="titulo-dialog-entrega-<?= (int)$recurso['id_recurso'] ?>">Entrega de tarea: <?= htmlspecialchars($recurso['titulo']) ?></h2>
                          <button type="button" class="course-dialog-close" data-dialog-close aria-label="Cerrar">X</button>
                        </div>
                        <div class="course-dialog-body">
                          <?php if (!empty($recurso['descripcion'])): ?>
                            <div class="course-delivery-consigna u-mb-md">
                              <strong>Pautas / Consigna:</strong>
                              <p><?= nl2br(htmlspecialchars($recurso['descripcion'])) ?></p>
                            </div>
                          <?php endif; ?>

                          <?php if ($entrega): ?>
                            <div class="alert alert-success u-mb-md">
                              <p><strong>Estado:</strong> Entregada el <?= date('d/m/Y H:i', strtotime($entrega['fecha_entrega'])) ?> hs.</p>
                              <?php if (!empty($entrega['archivo_entrega'])): ?>
                                <?php $entregaVisualizable = recurso_es_visualizable_nativamente('Archivo', $entrega['archivo_entrega']); ?>
                                <div class="u-mt-xs" style="display:flex;gap:0.5rem;flex-wrap:wrap;align-items:center;">
                                  <?php if ($entregaVisualizable): ?>
                                    <a class="btn btn-sm btn-outline" href="../php/descargas/descargar_archivo.php?tipo=entrega&id=<?= (int)$entrega['id_entrega'] ?>&modo=inline" target="_blank" rel="noopener">👁️ Ver mi archivo entregado</a>
                                  <?php endif; ?>
                                  <a class="btn btn-sm" href="../php/descargas/descargar_archivo.php?tipo=entrega&id=<?= (int)$entrega['id_entrega'] ?>&modo=descargar">📥 Descargar mi archivo entregado</a>
                                </div>
                              <?php endif; ?>
                              <?php if (!empty($entrega['comentario_entrega'])): ?>
                                <p class="u-mt-xs"><strong>Tus notas:</strong> <?= nl2br(htmlspecialchars($entrega['comentario_entrega'])) ?></p>
                              <?php endif; ?>
                            </div>
                          <?php endif; ?>

                          <form method="POST" enctype="multipart/form-data" class="form-grid">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <input type="hidden" name="accion" value="entregar_tarea">
                            <input type="hidden" name="id_recurso" value="<?= (int)$recurso['id_recurso'] ?>">
                            <input type="hidden" name="id_unidad" value="<?= (int)($unidad['id_unidad'] ?? 0) ?>">

                            <label class="form-grid-full">
                              <?= $entrega ? 'Reemplazar o adjuntar archivo de entrega' : 'Adjuntar archivo de entrega (PDF, DOCX, ZIP, imágenes)' ?>
                              <input type="file" name="archivo_entrega" <?= !$entrega ? 'required' : '' ?>>
                            </label>

                            <label class="form-grid-full">
                              Comentarios o respuesta escrita (opcional)
                              <textarea name="comentario_entrega" rows="3" placeholder="Escribí aquí tus observaciones, respuestas o enlaces si fuera necesario..."><?= htmlspecialchars($entrega['comentario_entrega'] ?? '') ?></textarea>
                            </label>

                            <div class="course-dialog-actions form-grid-full">
                              <button class="btn btn-secondary" type="button" data-dialog-close>Cancelar</button>
                              <button class="btn btn-primary-action" type="submit"><?= $entrega ? 'Actualizar entrega' : 'Enviar entrega' ?></button>
                            </div>
                          </form>
                        </div>
                      </dialog>
                    <?php endif; ?>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>

          <nav class="course-lesson-nav" aria-label="Navegación de clases">
            <?php if ($prev): ?><a class="btn" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$prev['id_unidad'] ?>">← Clase anterior</a><?php else: ?><span></span><?php endif; ?>
            <?php if ($next): ?>
              <a class="btn" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$next['id_unidad'] ?>">Siguiente clase →</a>
            <?php elseif (!empty($contratacion_curso) && $contratacion_curso['estado'] === 'En Proceso'): ?>
              <form method="POST" class="inline-form" onsubmit="return confirm('¿Deseas marcar este curso como completado?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="accion" value="completar_curso">
                <button type="submit" class="btn btn-primary-action">Finalizar y completar curso</button>
              </form>
            <?php elseif (!empty($contratacion_curso) && $contratacion_curso['estado'] === 'Completada'): ?>
              <span class="badge badge-course">✓ Curso completado</span>
            <?php endif; ?>
          </nav>
        <?php endif; ?>
      </article>
    </div>
  <?php else: ?>
    <div class="product-detail-layout">
      <article class="product-main-content">
        <header class="product-header">
          <span class="badge badge-course"><?= htmlspecialchars($curso['nombre_categoria']) ?></span>
          <h1 class="product-title"><?= htmlspecialchars($curso['titulo']) ?></h1>
          <div class="product-meta-row">
            <div>
              <span>Docente: </span>
              <a href="proveedor.php?id=<?= (int)$curso['autor_id'] ?>" class="provider-link"><?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?></a>
            </div>
            <div>
              <a href="#valoraciones-curso" class="rating-link">
                ★ <strong><?= $promedio_calificacion > 0 ? $promedio_calificacion . ' / 5' : 'Sin calificar' ?></strong>
                (<?= $total_resenas ?> <?= $total_resenas === 1 ? 'valoración' : 'valoraciones' ?>)
              </a>
            </div>
          </div>
          <?php if (!empty($curso['imagen'])): ?>
            <div class="product-media-wrapper"><img src="../<?= htmlspecialchars($curso['imagen']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>"></div>
          <?php endif; ?>
          <div class="product-body">
            <h3>Acerca de este curso</h3>
            <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>
          </div>
        </header>

        <hr class="section-divider">
        <section aria-labelledby="temario-curso">
          <h2 id="temario-curso">Contenido del programa</h2>
          <?php if (empty($contenido_curso)): ?>
            <p class="text-muted">El docente todavía no cargó módulos para este curso.</p>
          <?php else: ?>
            <div class="syllabus-container">
              <?php foreach ($contenido_curso as $indiceModulo => $modulo): ?>
                <div class="syllabus-module-header<?= $indiceModulo > 0 ? ' syllabus-module-header--middle' : '' ?>">
                  <strong>Módulo <?= $indiceModulo + 1 ?>:</strong> <?= htmlspecialchars($modulo['titulo']) ?>
                </div>
                <?php if (!empty($modulo['descripcion'])): ?>
                  <div class="syllabus-module-desc"><?= nl2br(htmlspecialchars($modulo['descripcion'])) ?></div>
                <?php endif; ?>
                <?php if (empty($modulo['unidades'])): ?>
                  <p class="syllabus-empty-note text-muted">Este módulo todavía no tiene clases.</p>
                <?php else: ?>
                  <ul class="syllabus-module-list">
                    <?php foreach ($modulo['unidades'] as $unidad): ?>
                      <li class="syllabus-unit-item">
                        <div class="syllabus-unit-header">
                          <strong class="syllabus-unit-title"><?= htmlspecialchars($unidad['titulo']) ?></strong>
                        </div>
                        <?php if (!empty($unidad['descripcion'])): ?>
                          <div class="syllabus-unit-desc"><?= nl2br(htmlspecialchars($unidad['descripcion'])) ?></div>
                        <?php endif; ?>
                        <?php if (!empty($unidad['recursos'])): ?>
                          <ul class="syllabus-resource-list">
                            <?php foreach ($unidad['recursos'] as $recurso): ?>
                              <li class="syllabus-resource-item">
                                <span class="course-resource-icon syllabus-resource-badge" aria-hidden="true"><?= htmlspecialchars(icono_recurso_curso($recurso['tipo'])) ?></span>
                                <span class="syllabus-resource-title"><?= htmlspecialchars($recurso['titulo']) ?></span>
                                <span class="syllabus-resource-type"><?= htmlspecialchars($recurso['tipo']) ?></span>
                              </li>
                            <?php endforeach; ?>
                          </ul>
                        <?php endif; ?>
                      </li>
                    <?php endforeach; ?>
                  </ul>
                <?php endif; ?>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>

        <hr class="section-divider">
        <section id="valoraciones-curso" aria-labelledby="titulo-resenas-curso">
          <h2 id="titulo-resenas-curso">Opiniones de estudiantes (<?= $total_resenas ?>)</h2>
          <?php if (empty($resenas)): ?>
            <p class="text-muted">Aún no hay reseñas registradas para este curso.</p>
          <?php else: ?>
            <div class="review-list">
              <?php foreach ($resenas as $res): ?>
                <article class="review-card">
                  <div class="review-card__header">
                    <strong><?= htmlspecialchars($res['nombre'] . ' ' . ($res['apellido'] ?? '')) ?></strong>
                    <span class="review-card__stars"><?= str_repeat('★', (int)$res['puntuacion']) . str_repeat('☆', 5 - (int)$res['puntuacion']) ?> (<?= (int)$res['puntuacion'] ?>/5)</span>
                  </div>
                  <p class="review-card__date"><?= date('d/m/Y', strtotime($res['fecha_valoracion'])) ?></p>
                  <p class="review-card__body">"<?= htmlspecialchars($res['comentario'] ?? 'Sin comentario.') ?>"</p>
                </article>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>
        </section>
      </article>

      <aside class="product-sidebar-sticky">
        <div class="product-card-pricing">
          <p class="product-card-pricing__label">Precio del curso</p>
          <div class="product-card-pricing__amount">$<?= number_format($curso['precio'], 2, ',', '.') ?></div>
          <ul class="product-card-pricing__features">
            <?php if (!empty($curso['modalidad'])): ?><li>Modalidad: <?= htmlspecialchars($curso['modalidad']) ?></li><?php endif; ?>
            <?php if (!empty($curso['duracion_horas'])): ?><li>Duración aproximada: <?= (int)$curso['duracion_horas'] ?> h</li><?php endif; ?>
            <?php if (!empty($curso['cupos'])): ?><li>Cupos totales: <?= (int)$curso['cupos'] ?></li><?php endif; ?>
            <?php if (!empty($curso['disponibilidad'])): ?><li>Disponibilidad: <?= htmlspecialchars($curso['disponibilidad']) ?></li><?php endif; ?>
            <li>Acceso a las clases y materiales cargados por el docente.</li>
          </ul>
          <?php if (!empty($es_propietario)): ?>
            <div class="purchase-owner-actions">
              <p class="badge badge-course u-mb-sm">Eres el autor de este curso</p>
              <a class="btn product-card-pricing__btn u-mb-sm" href="gestionar-contenido-curso.php?id=<?= (int)$curso['id_publicacion'] ?>">Gestionar contenido</a>
              <a class="btn btn-secondary product-card-pricing__btn" href="editar-publicacion.php?id=<?= (int)$curso['id_publicacion'] ?>">Editar curso</a>
            </div>
          <?php elseif (es_admin()): ?>
            <div class="purchase-admin-actions">
              <p class="badge u-mb-sm">Modo Administrador</p>
              <a class="btn btn-secondary product-card-pricing__btn" href="editar-publicacion.php?id=<?= (int)$curso['id_publicacion'] ?>">Editar curso</a>
            </div>
          <?php elseif ($puedeAgregar): ?>
            <form action="carrito.php" method="POST">
              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
              <input type="hidden" name="id_publicacion" value="<?= (int)$curso['id_publicacion'] ?>">
              <input type="hidden" name="accion" value="agregar">
              <button type="submit" class="btn product-card-pricing__btn">Inscribirme al curso</button>
            </form>
          <?php else: ?>
            <div class="purchase-auth-actions">
              <a class="btn product-card-pricing__btn" href="login.php">Inicia sesión</a>
              <a href="registro.php">Regístrate</a>
            </div>
          <?php endif; ?>
          <div class="more-courses-box"><a href="catalogo.php?tipo=curso" class="link">← Ver más cursos</a></div>
        </div>
      </aside>
    </div>
  <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

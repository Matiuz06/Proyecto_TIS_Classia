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
        <a class="course-sidebar-general<?= $item_actual ? '' : ' is-active' ?>" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>" <?= $item_actual ? '' : 'aria-current="page"' ?>>
          <span>General</span>
          <small>Información del curso</small>
        </a>
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
                  <li>
                    <a class="course-lesson-link<?= $activa ? ' is-active' : '' ?>" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$unidad['id_unidad'] ?>" <?= $activa ? 'aria-current="page"' : '' ?>>
                      <span><?= (int)$modulo['orden'] ?>.<?= (int)$unidad['orden'] ?></span>
                      <?= htmlspecialchars($unidad['titulo']) ?>
                    </a>
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
            <?php if (!empty($unidad['descripcion'])): ?>
              <p><?= nl2br(htmlspecialchars($unidad['descripcion'])) ?></p>
            <?php else: ?>
              <div class="course-empty-state"><p>Esta clase todavía no tiene contenido descriptivo.</p></div>
            <?php endif; ?>
          </section>

          <section class="course-materials" aria-labelledby="materiales-clase">
            <h2 id="materiales-clase">Materiales de la clase</h2>
            <?php if (empty($unidad['recursos'])): ?>
              <div class="course-empty-state"><p>Esta clase todavía no tiene recursos.</p></div>
            <?php else: ?>
              <ul class="course-resource-list">
                <?php foreach ($unidad['recursos'] as $recurso): ?>
                  <li class="course-resource">
                    <span class="course-resource-icon" aria-hidden="true"><?= htmlspecialchars(icono_recurso_curso($recurso['tipo'])) ?></span>
                    <div>
                      <strong><?= htmlspecialchars($recurso['titulo']) ?></strong>
                      <span><?= htmlspecialchars($recurso['tipo']) ?></span>
                      <?php if (!empty($recurso['descripcion'])): ?><p><?= nl2br(htmlspecialchars($recurso['descripcion'])) ?></p><?php endif; ?>
                      <div class="course-resource-actions">
                        <?php if (!empty($recurso['url'])): ?><a class="btn btn-sm" href="<?= htmlspecialchars($recurso['url']) ?>" target="_blank" rel="noopener">Abrir</a><?php endif; ?>
                        <?php if (!empty($recurso['archivo'])): ?><a class="btn btn-sm" href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$recurso['id_recurso'] ?>">Descargar</a><?php endif; ?>
                      </div>
                    </div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </section>

          <nav class="course-lesson-nav" aria-label="Navegación de clases">
            <?php if ($prev): ?><a class="btn" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$prev['id_unidad'] ?>">← Clase anterior</a><?php else: ?><span></span><?php endif; ?>
            <?php if ($next): ?><a class="btn" href="curso.php?id=<?= (int)$curso['id_publicacion'] ?>&unidad=<?= (int)$next['id_unidad'] ?>">Siguiente clase →</a><?php endif; ?>
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
                <div class="syllabus-module-header<?= $indiceModulo > 0 ? ' syllabus-module-header--middle' : '' ?>">Módulo <?= $indiceModulo + 1 ?>: <?= htmlspecialchars($modulo['titulo']) ?></div>
                <?php if (!empty($modulo['descripcion'])): ?><p><?= nl2br(htmlspecialchars($modulo['descripcion'])) ?></p><?php endif; ?>
                <?php if (empty($modulo['unidades'])): ?>
                  <p class="text-muted">Este módulo todavía no tiene clases.</p>
                <?php else: ?>
                  <ul class="syllabus-module-list">
                    <?php foreach ($modulo['unidades'] as $unidad): ?>
                      <li><strong><?= htmlspecialchars($unidad['titulo']) ?></strong><?php if (!empty($unidad['descripcion'])): ?><div><?= nl2br(htmlspecialchars($unidad['descripcion'])) ?></div><?php endif; ?></li>
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
          <?php if ($puedeAgregar): ?>
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

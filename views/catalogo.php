<?php

/**
 * Responsabilidad: Catálogo general de cursos y servicios con filtros por categoría y modalidad.
 */

require_once __DIR__ . '/../php/publicaciones/catalogo.php';

$title       = 'Catálogo de cursos y servicios';
$description = 'Catálogo de cursos y servicios educativos disponibles en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$bodyClass   = 'catalog-page';

$activePage  = 'catalogo';
include '../includes/header.php';
$puedeAgregar = esta_autenticado();
?>

    <main>
      <nav aria-label="Ruta de navegación" class="breadcrumb-nav">
        <ol class="breadcrumb-list">
          <li><a href="../index.php"><?= __t('nav_inicio', 'Inicio') ?></a> /</li>
          <li aria-current="page"><?= __t('nav_catalogo', 'Catálogo') ?></li>
        </ol>
      </nav>

      <header>
        <p><?= __t('catalog_title') ?></p>
        <h1><?= __t('catalog_heading') ?></h1>
        <p><?= __t('catalog_sub') ?></p>
        <form
          action="catalogo.php"
          method="get"
          role="search"
          class="catalog-tools">
          <?php if (!empty($tipo_filtro)): ?>
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($tipo_filtro) ?>" />
          <?php endif; ?>
          <?php if ($categoria_filtro > 0): ?>
            <input type="hidden" name="categoria" value="<?= (int) $categoria_filtro ?>" />
          <?php endif; ?>
          <p>
            <label for="busqueda"><?= __t('btn_search') ?></label>
            <input
              id="Barrabusqueda"
              type="search"
              name="busqueda"
              value="<?= htmlspecialchars($busqueda) ?>"
              placeholder="<?= __t('catalog_search_ph') ?>" />
          </p>
          <p class="catalog-sort-group">
            <label for="sort-order"><?= __t('sort_label', 'Ordenar por') ?></label>
            <select
              id="sort-order"
              name="orden"
              class="catalog-sort-select"
              aria-label="<?= __t('sort_label', 'Ordenar por') ?>">
              <option value="reciente"<?= $orden === 'reciente' ? ' selected' : '' ?>>
                <?= __t('sort_reciente', 'Más reciente') ?>
              </option>
              <option value="valoracion"<?= $orden === 'valoracion' ? ' selected' : '' ?>>
                <?= __t('sort_valoracion', 'Mejor valoración') ?>
              </option>
              <option value="popularidad"<?= $orden === 'popularidad' ? ' selected' : '' ?>>
                <?= __t('sort_popularidad', 'Más popular') ?>
              </option>
              <option value="precio_asc"<?= $orden === 'precio_asc' ? ' selected' : '' ?>>
                <?= __t('sort_precio_asc', 'Precio: menor a mayor') ?>
              </option>
              <option value="precio_desc"<?= $orden === 'precio_desc' ? ' selected' : '' ?>>
                <?= __t('sort_precio_desc', 'Precio: mayor a menor') ?>
              </option>
            </select>
          </p>
          <button type="submit" class="btnAlignIzq"><?= __t('btn_search') ?></button>
        </form>
      </header>

      <div class="catalog-layout">
        <aside class="catalog-filters" aria-labelledby="titulo-filtros">
          <h2 id="titulo-filtros"><?= __t('catalog_filters') ?></h2>
          <form action="catalogo.php" method="get">
            <?php if (!empty($busqueda)): ?>
              <input type="hidden" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" />
            <?php endif; ?>
            <?php if (!empty($orden) && $orden !== 'reciente'): ?>
              <input type="hidden" name="orden" value="<?= htmlspecialchars($orden) ?>" />
            <?php endif; ?>
            
            <fieldset>
              <legend><?= __t('filter_type', 'Tipo') ?></legend>
              <label for="tipo-todos">
                <input
                  type="radio"
                  id="tipo-todos"
                  name="tipo"
                  value=""
                  <?= empty($tipo_filtro) ? 'checked' : '' ?> />
                <?= __t('filter_all', 'Todos') ?>
              </label>
              <label for="tipo-curso">
                <input
                  type="radio"
                  id="tipo-curso"
                  name="tipo"
                  value="curso"
                  <?= $tipo_filtro === 'curso' ? 'checked' : '' ?> />
                <?= __t('catalog_courses', 'Cursos') ?>
              </label>
              <label for="tipo-servicio">
                <input
                  type="radio"
                  id="tipo-servicio"
                  name="tipo"
                  value="servicio"
                  <?= $tipo_filtro === 'servicio' ? 'checked' : '' ?> />
                <?= __t('catalog_services', 'Servicios') ?>
              </label>
            </fieldset>

            <fieldset class="filter-category-fieldset">
              <legend><?= __t('filter_discipline', 'Disciplina / Orientación') ?></legend>
              <select name="categoria" id="filtro-categoria" class="filter-category-select">
                <option value="0"><?= __t('cat_all_disciplines', 'Todas las disciplinas') ?></option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= (int) $cat['id_categoria'] ?>" <?= ($categoria_filtro === (int)$cat['id_categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars(__t_db($cat['nombre_categoria'])) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </fieldset>

            <div class="filter-buttons-wrapper">
              <button type="submit" class="btn"><?= __t('btn_apply_filters', 'Aplicar filtros') ?></button>
              <a href="catalogo.php" class="btn-ghost botonLimpiar"><?= __t('btn_clear', 'Limpiar') ?></a>
            </div>
          </form>
        </aside>

        <section id="catalogo-contenido" aria-labelledby="titulo-contenido">
          <?php if (!empty($recomendaciones)): ?>
            <section class="catalog-recommendations" aria-labelledby="titulo-recomendaciones">
              <header>
                <p><?= __t('catalog_preferences', 'Según tus preferencias') ?></p>
                <h2 id="titulo-recomendaciones"><?= __t('catalog_recommended', 'Recomendado para vos') ?></h2>
                <p><?= __t('catalog_recommended_sub', 'Propuestas relacionadas con lo que elegiste en tus primeros pasos.') ?></p>
              </header>

              <div class="catalog-grid">
                <?php foreach ($recomendaciones as $recomendacion): ?>
                  <?php $esCurso = $recomendacion['tipo'] === 'Curso'; ?>
                  <article class="catalog-card" aria-labelledby="recomendacion-<?= (int) $recomendacion['id_publicacion'] ?>">
                    <?php if (!empty($recomendacion['imagen'])): ?>
                      <div class="catalog-card-media">
                        <img class="catalog-card-image" src="../<?= htmlspecialchars($recomendacion['imagen']) ?>" alt="<?= htmlspecialchars($recomendacion['titulo']) ?>" loading="lazy" />
                      </div>
                    <?php else: ?>
                      <div class="placeholder-visual" aria-hidden="true"><?= $esCurso ? __t('label_course') : __t('label_service') ?></div>
                    <?php endif; ?>
                    <div>
                      <h3 id="recomendacion-<?= (int) $recomendacion['id_publicacion'] ?>"><?= htmlspecialchars($recomendacion['titulo']) ?></h3>
                      <p><?= htmlspecialchars($recomendacion['descripcion']) ?></p>
                      <p><strong><?= htmlspecialchars(__t_db($recomendacion['nombre_categoria'])) ?></strong> · $<?= number_format($recomendacion['precio'], 2, ',', '.') ?></p>
                      <p class="catalog-actions">
                        <a class="btn" href="<?= $esCurso ? 'curso.php' : 'servicio-detalle.php' ?>?id=<?= (int) $recomendacion['id_publicacion'] ?>">
                          <?= $esCurso ? __t('btn_view_course') : __t('btn_view_service') ?>
                        </a>
                      </p>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <?php if (empty($publicaciones)): ?>
            <section class="empty-state catalog-empty-state" aria-labelledby="sin-mas-cursos">
              <div class="empty-state-icon" aria-hidden="true">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                  <circle cx="11" cy="11" r="8"></circle>
                  <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                  <line x1="8" y1="11" x2="14" y2="11"></line>
                </svg>
              </div>
              <h3 id="sin-mas-cursos"><?= __t('catalog_empty') ?></h3>
              <p>
                <?= __t('catalog_empty_sub') ?>
              </p>
              <div class="empty-state-actions">
                <a href="catalogo.php" class="btn"><?= __t('btn_view_catalog') ?></a>
              </div>
            </section>
          <?php else: ?>

            <?php if (empty($tipo_filtro) || $tipo_filtro === 'curso'): ?>
              <?php if (!empty($cursos)): ?>
                <header>
                  <br>
                  <h2 id="titulo-cursos"><?= __t('catalog_courses') ?> (<?= count($cursos) ?>)</h2>
                  <p><?= __t('catalog_courses_sub') ?></p>
                </header>

                <div class="catalog-grid catalog-grid--courses">
                  <?php foreach ($cursos as $curso): ?>
                    <article class="catalog-card" aria-labelledby="curso-<?= $curso['id_publicacion'] ?>">
                      <?php if (!empty($curso['imagen'])): ?>
                        <div class="catalog-card-media">
                          <img class="catalog-card-image" src="../<?= htmlspecialchars($curso['imagen']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>" loading="lazy" />
                        </div>
                      <?php else: ?>
                        <div class="placeholder-visual" aria-hidden="true"><?= __t('label_course') ?></div>
                      <?php endif; ?>
                      <div>
                        <h3 id="curso-<?= $curso['id_publicacion'] ?>"><?= htmlspecialchars($curso['titulo']) ?></h3>
                        <p><?= htmlspecialchars($curso['descripcion']) ?></p>
                        <dl>
                          <div>
                            <dt><?= __t('label_category', 'Categoría') ?></dt>
                            <dd><?= htmlspecialchars(__t_db($curso['nombre_categoria'])) ?></dd>
                          </div>
                          <div>
                            <dt><?= __t('label_instructor', 'Docente') ?></dt>
                            <dd>
                              <a href="proveedor.php?id=<?= (int)$curso['id_usuario'] ?>" class="provider-name-link">
                                <?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?>
                              </a>
                            </dd>
                          </div>
                          <div>
                            <dt><?= __t('label_price', 'Precio') ?></dt>
                            <dd><strong>$<?= number_format($curso['precio'], 2, ',', '.') ?></strong></dd>
                          </div>
                        </dl>
                        <?php if ((float)$curso['promedio_valoracion'] > 0): ?>
                          <p class="catalog-rating" aria-label="Valoración: <?= number_format((float)$curso['promedio_valoracion'], 1) ?> de 5">
                            <span class="catalog-rating__stars" aria-hidden="true">
                              <?php
                                $prom = round((float)$curso['promedio_valoracion']);
                                for ($i = 1; $i <= 5; $i++): ?>
                                  <span class="catalog-rating__star<?= $i <= $prom ? ' catalog-rating__star--filled' : '' ?>" aria-hidden="true">★</span>
                              <?php endfor; ?>
                            </span>
                            <span class="catalog-rating__value"><?= number_format((float)$curso['promedio_valoracion'], 1) ?></span>
                            <?php if ((int)$curso['total_contrataciones'] > 0): ?>
                              <span class="catalog-rating__count">(<?= (int)$curso['total_contrataciones'] ?>)</span>
                            <?php endif; ?>
                          </p>
                        <?php endif; ?>
                        <p class="catalog-actions">
                          <a class="btn" href="curso.php?id=<?= $curso['id_publicacion'] ?>"><?= __t('btn_view_course') ?></a>
                          <?php if ($puedeAgregar): ?>
                            <form action="../php/contrataciones/carrito.php" method="post">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                              <input type="hidden" name="id_publicacion" value="<?= (int) $curso['id_publicacion'] ?>">
                              <button type="submit" name="accion" value="agregar"><?= __t('btn_add_cart') ?></button>
                            </form>
                          <?php else: ?>
                            <a class="btn-ghost" href="login.php"><?= __t('catalog_login_hint') ?></a>
                          <?php endif; ?>
                        </p>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <?php if (empty($tipo_filtro) || $tipo_filtro === 'servicio'): ?>
              <?php if (!empty($servicios)): ?>
                <header>
                  <br>
                  <h2 id="titulo-servicios"><?= __t('catalog_services') ?> (<?= count($servicios) ?>)</h2>
                  <p><?= __t('catalog_services_sub') ?></p>
                </header>

                <div class="catalog-grid">
                  <?php foreach ($servicios as $serv): ?>
                    <article class="catalog-card" aria-labelledby="servicio-<?= $serv['id_publicacion'] ?>">
                      <?php if (!empty($serv['imagen'])): ?>
                        <div class="catalog-card-media">
                          <img class="catalog-card-image" src="../<?= htmlspecialchars($serv['imagen']) ?>" alt="<?= htmlspecialchars($serv['titulo']) ?>" loading="lazy" />
                        </div>
                      <?php else: ?>
                        <div class="placeholder-visual" aria-hidden="true"><?= __t('label_service') ?></div>
                      <?php endif; ?>
                      <div>
                        <h3 id="servicio-<?= $serv['id_publicacion'] ?>"><?= htmlspecialchars($serv['titulo']) ?></h3>
                        <p><?= htmlspecialchars($serv['descripcion']) ?></p>
                        <dl>
                          <div>
                            <dt><?= __t('label_category', 'Categoría') ?></dt>
                            <dd><?= htmlspecialchars(__t_db($serv['nombre_categoria'])) ?></dd>
                          </div>
                          <div>
                            <dt><?= __t('label_provider', 'Proveedor') ?></dt>
                            <dd>
                              <a href="proveedor.php?id=<?= (int)$serv['id_usuario'] ?>" class="provider-name-link">
                                <?= htmlspecialchars($serv['autor_nombre'] . ' ' . $serv['autor_apellido']) ?>
                              </a>
                            </dd>
                          </div>
                          <div>
                            <dt><?= __t('label_price', 'Precio') ?></dt>
                            <dd><strong>$<?= number_format($serv['precio'], 2, ',', '.') ?></strong></dd>
                          </div>
                        </dl>
                        <?php if ((float)$serv['promedio_valoracion'] > 0): ?>
                          <p class="catalog-rating" aria-label="Valoración: <?= number_format((float)$serv['promedio_valoracion'], 1) ?> de 5">
                            <span class="catalog-rating__stars" aria-hidden="true">
                              <?php
                                $prom = round((float)$serv['promedio_valoracion']);
                                for ($i = 1; $i <= 5; $i++): ?>
                                  <span class="catalog-rating__star<?= $i <= $prom ? ' catalog-rating__star--filled' : '' ?>" aria-hidden="true">★</span>
                              <?php endfor; ?>
                            </span>
                            <span class="catalog-rating__value"><?= number_format((float)$serv['promedio_valoracion'], 1) ?></span>
                            <?php if ((int)$serv['total_contrataciones'] > 0): ?>
                              <span class="catalog-rating__count">(<?= (int)$serv['total_contrataciones'] ?>)</span>
                            <?php endif; ?>
                          </p>
                        <?php endif; ?>
                        <p class="catalog-actions">
                          <a class="btn" href="servicio-detalle.php?id=<?= $serv['id_publicacion'] ?>"><?= __t('btn_request_service') ?></a>
                          <?php if ($puedeAgregar): ?>
                            <form action="../php/contrataciones/carrito.php" method="post">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                              <input type="hidden" name="id_publicacion" value="<?= (int) $serv['id_publicacion'] ?>">
                              <button type="submit" name="accion" value="agregar"><?= __t('btn_add_cart') ?></button>
                            </form>
                          <?php else: ?>
                            <a class="btn-ghost" href="login.php"><?= __t('catalog_login_hint') ?></a>
                          <?php endif; ?>
                        </p>
                      </div>
                    </article>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>
            <?php endif; ?>

          <?php endif; ?>
        </section>
      </div>
    </main>

<?php include '../includes/footer.php'; ?>

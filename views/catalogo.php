<?php
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
      <header>
        <p>Catálogo</p>
        <h1>Cursos y servicios educativos</h1>
        <p>
          Explorá las propuestas educativas y técnicas de nuestros docentes y especialistas en Classia.
        </p>
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
            <label for="busqueda">Buscar en Classia</label>
            <input
              id="Barrabusqueda"
              type="search"
              name="busqueda"
              value="<?= htmlspecialchars($busqueda) ?>"
              placeholder="Curso, docente, servicio o temática" />
          </p>
          <button type="submit" class="btnAlignIzq">Buscar</button>
        </form>
      </header>

      <div class="catalog-layout">
        <aside class="catalog-filters" aria-labelledby="titulo-filtros">
          <h2 id="titulo-filtros">Filtros</h2>
          <form action="catalogo.php" method="get">
            <?php if (!empty($busqueda)): ?>
              <input type="hidden" name="busqueda" value="<?= htmlspecialchars($busqueda) ?>" />
            <?php endif; ?>
            
            <fieldset>
              <legend>Tipo</legend>
              <label for="tipo-todos">
                <input
                  type="radio"
                  id="tipo-todos"
                  name="tipo"
                  value=""
                  <?= empty($tipo_filtro) ? 'checked' : '' ?> />
                Todos
              </label>
              <label for="tipo-curso">
                <input
                  type="radio"
                  id="tipo-curso"
                  name="tipo"
                  value="curso"
                  <?= $tipo_filtro === 'curso' ? 'checked' : '' ?> />
                Cursos
              </label>
              <label for="tipo-servicio">
                <input
                  type="radio"
                  id="tipo-servicio"
                  name="tipo"
                  value="servicio"
                  <?= $tipo_filtro === 'servicio' ? 'checked' : '' ?> />
                Servicios
              </label>
            </fieldset>

            <fieldset class="filter-category-fieldset">
              <legend>Disciplina / Orientación</legend>
              <select name="categoria" id="filtro-categoria" class="filter-category-select">
                <option value="0">Todas las disciplinas</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= (int) $cat['id_categoria'] ?>" <?= ($categoria_filtro === (int)$cat['id_categoria']) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat['nombre_categoria']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </fieldset>

            <div class="filter-buttons-wrapper">
              <button type="submit" class="btn">Aplicar filtros</button>
              <a href="catalogo.php" class="btn-ghost botonLimpiar">Limpiar</a>
            </div>
          </form>
        </aside>

        <section id="catalogo-contenido" aria-labelledby="titulo-contenido">
          <?php if (!empty($recomendaciones)): ?>
            <section class="catalog-recommendations" aria-labelledby="titulo-recomendaciones">
              <header>
                <p>Según tus preferencias</p>
                <h2 id="titulo-recomendaciones">Recomendado para vos</h2>
                <p>Propuestas relacionadas con lo que elegiste en tus primeros pasos.</p>
              </header>

              <div class="catalog-grid">
                <?php foreach ($recomendaciones as $recomendacion): ?>
                  <?php $esCurso = $recomendacion['tipo'] === 'Curso'; ?>
                  <article class="catalog-card" aria-labelledby="recomendacion-<?= (int) $recomendacion['id_publicacion'] ?>">
                    <?php if (!empty($recomendacion['imagen'])): ?>
                      <div class="catalog-card-media">
                        <img class="catalog-card-image" src="../<?= htmlspecialchars($recomendacion['imagen']) ?>" alt="<?= htmlspecialchars($recomendacion['titulo']) ?>" />
                      </div>
                    <?php else: ?>
                      <div class="placeholder-visual" aria-hidden="true"><?= $esCurso ? 'Curso' : 'Servicio' ?></div>
                    <?php endif; ?>
                    <div>
                      <h3 id="recomendacion-<?= (int) $recomendacion['id_publicacion'] ?>"><?= htmlspecialchars($recomendacion['titulo']) ?></h3>
                      <p><?= htmlspecialchars($recomendacion['descripcion']) ?></p>
                      <p><strong><?= htmlspecialchars($recomendacion['nombre_categoria']) ?></strong> · $<?= number_format($recomendacion['precio'], 2, ',', '.') ?></p>
                      <p class="catalog-actions">
                        <a class="btn" href="<?= $esCurso ? 'curso.php' : 'servicio-detalle.php' ?>?id=<?= (int) $recomendacion['id_publicacion'] ?>">
                          <?= $esCurso ? 'Ver curso' : 'Ver servicio' ?>
                        </a>
                      </p>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>

          <?php if (empty($publicaciones)): ?>
            <section class="empty-state" aria-labelledby="sin-mas-cursos">
              <h3 id="sin-mas-cursos">No se encontraron cursos ni servicios</h3>
              <p>
                No encontramos publicaciones que coincidan con tu búsqueda<?= !empty($busqueda) ? ' ("' . htmlspecialchars($busqueda) . '")' : '' ?>.
                Intentá con otras palabras clave o limpiá los filtros.
              </p>
              <p><a href="catalogo.php" class="btn">Ver todo el catálogo</a></p>
            </section>
          <?php else: ?>

            <?php if (empty($tipo_filtro) || $tipo_filtro === 'curso'): ?>
              <?php if (!empty($cursos)): ?>
                <header>
                  <p>Contenido disponible</p>
                  <h2 id="titulo-cursos">Cursos (<?= count($cursos) ?>)</h2>
                  <p>Cursos diversos para un aprendizaje profundo.</p>
                </header>

                <div class="catalog-grid catalog-grid--courses">
                  <?php foreach ($cursos as $curso): ?>
                    <article class="catalog-card" aria-labelledby="curso-<?= $curso['id_publicacion'] ?>">
                      <?php if (!empty($curso['imagen'])): ?>
                        <div class="catalog-card-media">
                          <img class="catalog-card-image" src="../<?= htmlspecialchars($curso['imagen']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>" />
                        </div>
                      <?php else: ?>
                        <div class="placeholder-visual" aria-hidden="true">Curso</div>
                      <?php endif; ?>
                      <div>
                        <h3 id="curso-<?= $curso['id_publicacion'] ?>"><?= htmlspecialchars($curso['titulo']) ?></h3>
                        <p><?= htmlspecialchars($curso['descripcion']) ?></p>
                        <dl>
                          <div>
                            <dt>Categoría</dt>
                            <dd><?= htmlspecialchars($curso['nombre_categoria']) ?></dd>
                          </div>
                          <div>
                            <dt>Docente</dt>
                            <dd>
                              <a href="proveedor.php?id=<?= (int)$curso['id_usuario'] ?>" class="provider-name-link">
                                <?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?>
                              </a>
                            </dd>
                          </div>
                          <div>
                            <dt>Precio</dt>
                            <dd><strong>$<?= number_format($curso['precio'], 2, ',', '.') ?></strong></dd>
                          </div>
                        </dl>
                        <p class="catalog-actions">
                          <a class="btn" href="curso.php?id=<?= $curso['id_publicacion'] ?>">Ver curso</a>
                          <?php if ($puedeAgregar): ?>
                            <form action="../php/contrataciones/carrito.php" method="post">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                              <input type="hidden" name="id_publicacion" value="<?= (int) $curso['id_publicacion'] ?>">
                              <button type="submit" name="accion" value="agregar">Agregar al carrito</button>
                            </form>
                          <?php else: ?>
                            <a class="btn-ghost" href="login.php">Inicia sesión</a>
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
                  <h2 id="titulo-servicios">Servicios (<?= count($servicios) ?>)</h2>
                  <p>Distintos servicios según tu necesidad.</p>
                </header>

                <div class="catalog-grid">
                  <?php foreach ($servicios as $serv): ?>
                    <article class="catalog-card" aria-labelledby="servicio-<?= $serv['id_publicacion'] ?>">
                      <?php if (!empty($serv['imagen'])): ?>
                        <div class="catalog-card-media">
                          <img class="catalog-card-image" src="../<?= htmlspecialchars($serv['imagen']) ?>" alt="<?= htmlspecialchars($serv['titulo']) ?>" />
                        </div>
                      <?php else: ?>
                        <div class="placeholder-visual" aria-hidden="true">Servicio</div>
                      <?php endif; ?>
                      <div>
                        <h3 id="servicio-<?= $serv['id_publicacion'] ?>"><?= htmlspecialchars($serv['titulo']) ?></h3>
                        <p><?= htmlspecialchars($serv['descripcion']) ?></p>
                        <dl>
                          <div>
                            <dt>Categoría</dt>
                            <dd><?= htmlspecialchars($serv['nombre_categoria']) ?></dd>
                          </div>
                          <div>
                            <dt>Proveedor</dt>
                            <dd>
                              <a href="proveedor.php?id=<?= (int)$serv['id_usuario'] ?>" class="provider-name-link">
                                <?= htmlspecialchars($serv['autor_nombre'] . ' ' . $serv['autor_apellido']) ?>
                              </a>
                            </dd>
                          </div>
                          <div>
                            <dt>Precio</dt>
                            <dd><strong>$<?= number_format($serv['precio'], 2, ',', '.') ?></strong></dd>
                          </div>
                        </dl>
                        <p class="catalog-actions">
                          <a class="btn" href="servicio-detalle.php?id=<?= $serv['id_publicacion'] ?>">Solicitar servicio</a>
                          <?php if ($puedeAgregar): ?>
                            <form action="../php/contrataciones/carrito.php" method="post">
                              <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                              <input type="hidden" name="id_publicacion" value="<?= (int) $serv['id_publicacion'] ?>">
                              <button type="submit" name="accion" value="agregar">Agregar al carrito</button>
                            </form>
                          <?php else: ?>
                            <a class="btn-ghost" href="login.php">Inicia sesión</a>
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

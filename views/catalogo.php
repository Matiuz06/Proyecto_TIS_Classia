<?php
require_once '../php/publicaciones/catalogo.php';

$title      = 'Catálogo de cursos y servicios';
$description = 'Catálogo de cursos y servicios educativos disponibles en Classia.';
$cssPrefix  = '..';
$jsPrefix    = '..';

$activePage = 'catalogo';
include '../includes/header.php';
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
            <button type="submit">Aplicar filtros</button>
            <a href="catalogo.php" class="btn-ghost botonLimpiar">Limpiar</a>
          </form>
        </aside>

        <section id="catalogo-contenido" aria-labelledby="titulo-contenido">
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
                            <dd><?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?></dd>
                          </div>
                          <div>
                            <dt>Precio</dt>
                            <dd><strong>$<?= number_format($curso['precio'], 2, ',', '.') ?></strong></dd>
                          </div>
                        </dl>
                        <p class="catalog-actions">
                          <a class="btn" href="curso.php?id=<?= $curso['id_publicacion'] ?>">Ver curso</a>
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
                  <p>Soluciones personalizadas</p>
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
                            <dd><?= htmlspecialchars($serv['autor_nombre'] . ' ' . $serv['autor_apellido']) ?></dd>
                          </div>
                          <div>
                            <dt>Precio</dt>
                            <dd><strong>$<?= number_format($serv['precio'], 2, ',', '.') ?></strong></dd>
                          </div>
                        </dl>
                        <p class="catalog-actions">
                          <a class="btn" href="servicio-detalle.php?id=<?= $serv['id_publicacion'] ?>">Solicitar servicio</a>
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

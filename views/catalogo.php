<?php
require_once __DIR__ . '/../config/database.php';
$sql = "SELECT id_publicacion, titulo, descripcion, precio, tipo 
        FROM publicaciones 
        WHERE estado = 'Activo'";

$stmt = $pdo->query($sql);
$publicaciones = $stmt->fetchAll();

$cursos = [];
$servicios = [];

foreach ($publicaciones as $publicacion) {
    if ($publicacion['tipo'] === 'Curso') {
        $cursos[] = $publicacion;
    } elseif ($publicacion['tipo'] === 'Servicio') {
        $servicios[] = $publicacion;
    }
}

$title = 'Catálogo de cursos y servicios';
$description = 'Catálogo de cursos y servicios educativos disponibles en Classia.';
$cssPrefix = '..';
$activePage = 'catalogo';
include '../includes/header.php';
?>

    <main>
      <header>
        <p>Catálogo</p>
        <h1>Cursos y servicios educativos</h1>
        <p>
          Classia está preparando su catálogo público. Mientras se publican
          nuevas propuestas, podés revisar un curso de demostración y solicitar
          servicios educativos personalizados.
        </p>
        <form
          action="catalogo.php"
          method="get"
          role="search"
          class="catalog-tools">
          <p>
            <label for="busqueda">Buscar en Classia</label>
            <input
              id="Barrabusqueda"
              type="search"
              name="busqueda"
              placeholder="Curso, docente, servicio o temática" />
          </p>
          <button type="submit" class="btnAlignIzq">Buscar</button>
        </form>
      </header>

      <div class="catalog-layout">
        <aside class="catalog-filters" aria-labelledby="titulo-filtros">
          <h2 id="titulo-filtros">Filtros</h2>
          <form action="catalogo.php" method="get">
            <fieldset>
              <legend>Tipo</legend>
              <label for="tipo-curso"
                ><input
                  type="radio"
                  id="tipo-curso"
                  name="tipo"
                  value="curso"
                  checked />
                Cursos</label
              >
              <label for="tipo-servicio"
                ><input
                  type="radio"
                  id="tipo-servicio"
                  name="tipo"
                  value="servicio" />
                Servicios</label
              >
            </fieldset>
            <fieldset>
              <legend>Modalidad</legend>
              <label for="modalidad-virtual"
                ><input
                  type="checkbox"
                  id="modalidad-virtual"
                  name="modalidad"
                  value="virtual" />
                Virtual</label
              >
              <label for="modalidad-presencial"
                ><input
                  type="checkbox"
                  id="modalidad-presencial"
                  name="modalidad"
                  value="presencial" />
                Presencial</label
              >
              <label for="modalidad-hibrida"
                ><input
                  type="checkbox"
                  id="modalidad-hibrida"
                  name="modalidad"
                  value="hibrida" />
                Híbrida</label
              >
            </fieldset>
            <button type="submit">Aplicar filtros</button>
            <button class="botonLimpiar" type="reset">Limpiar</button>
          </form>
        </aside>

        <section id="cursos" aria-labelledby="titulo-cursos">
          <header>
            <p>Contenido disponible</p>
            <h2 id="titulo-cursos">Cursos</h2>
            <p>Cursos diversos para una aprendizaje profundo.</p>
          </header>

          <div class="catalog-grid">
            <?php foreach ($cursos as $curso): ?>

            <article class="catalog-card">

              <div class="placeholder-visual" aria-hidden="true">
                Curso img
              </div>

              <div>

                <h3>
                  <?php echo htmlspecialchars($curso['titulo']); ?>
                </h3>

                <p>
                  <?php echo htmlspecialchars($curso['descripcion']); ?>
                </p>

                <dl>

                  <div>

                    <dt>Precio</dt>

                    <dd>
                      $<?php echo number_format($curso['precio'], 2, ',', '.'); ?>
                    </dd>

                  </div>

                  <div>

                    <dt>Tipo</dt>

                    <dd>
                      <?php echo htmlspecialchars($curso['tipo']); ?>
                    </dd>

                  </div>

                </dl>

                <p class="catalog-actions">

                  <a class="btn" href="curso.php">
                    Ver curso
                  </a>

                </p>

              </div>

            </article>

            <?php endforeach; ?>

            <?php foreach ($servicios as $servicio): ?>

              <article class="catalog-card">

                <div class="placeholder-visual" aria-hidden="true">
                  Servicio img
                </div>

                <div>

                  <h3>
                    <?php echo htmlspecialchars($servicio['titulo']); ?>
                  </h3>

                  <p>
                    <?php echo htmlspecialchars($servicio['descripcion']); ?>
                  </p>

                  <dl>

                    <div>

                      <dt>Precio</dt>

                      <dd>
                        $<?php echo number_format($servicio['precio'], 2, ',', '.'); ?>
                      </dd>

                    </div>

                    <div>

                      <dt>Tipo</dt>

                      <dd>
                        <?php echo htmlspecialchars($servicio['tipo']); ?>
                      </dd>

                    </div>

                  </dl>

                  <p class="catalog-actions">

                    <a class="btn" href="servicio-detalle.php">
                      Solicitar servicio
                    </a>

                  </p>

                </div>

              </article>

            <?php endforeach; ?>

            <section class="empty-state" aria-labelledby="sin-mas-cursos">
              <h3 id="sin-mas-cursos">Aún no hay más cursos publicados</h3>
              <p>
                Cuando se carguen nuevas propuestas, aparecerán en este
                catálogo.
              </p>
            </section>
          </div>
        </section>
      </div>
    </main>

    <footer class="site-footer">
      <p>
        &copy; 2026 Classia. Plataforma educativa para estudiantes, docentes y
        administradores.
      </p>
    </footer>
</body>
</html>

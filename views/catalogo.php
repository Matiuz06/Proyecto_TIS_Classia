<?php
require_once __DIR__ . '/../php/auth/session.php';
require_once __DIR__ . '/../config/database.php';

iniciar_sesion();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$stmt = $pdo->query(
    "SELECT id_publicacion, titulo, descripcion, precio, tipo
     FROM publicaciones
     WHERE estado = 'Activo'
     ORDER BY fecha_creacion DESC"
);
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

$title = 'Catalogo de cursos y servicios';
$description = 'Catalogo de cursos y servicios educativos disponibles en Classia.';
$cssPrefix = '..';
$activePage = 'catalogo';
include '../includes/header.php';
?>

    <main>
      <header>
        <p>Catalogo</p>
        <h1>Cursos y servicios educativos</h1>
        <p>Explora publicaciones activas y agrega cursos o servicios al carrito.</p>
        <?php if (isset($_GET['carrito']) && $_GET['carrito'] === 'agregado'): ?>
          <div class="alert alert-success">Publicacion agregada al carrito.</div>
        <?php endif; ?>
        <form action="catalogo.php" method="get" role="search" class="catalog-tools">
          <p>
            <label for="busqueda">Buscar en Classia</label>
            <input
              id="busqueda"
              type="search"
              name="busqueda"
              placeholder="Curso, docente, servicio o tematica" />
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
              <label for="tipo-curso">
                <input type="radio" id="tipo-curso" name="tipo" value="curso" checked />
                Cursos
              </label>
              <label for="tipo-servicio">
                <input type="radio" id="tipo-servicio" name="tipo" value="servicio" />
                Servicios
              </label>
            </fieldset>
            <button type="submit">Aplicar filtros</button>
            <button class="botonLimpiar" type="reset">Limpiar</button>
          </form>
        </aside>

        <section id="cursos" aria-labelledby="titulo-cursos">
          <header>
            <p>Contenido disponible</p>
            <h2 id="titulo-cursos">Cursos</h2>
          </header>

          <div class="catalog-grid">
            <?php foreach ($cursos as $curso): ?>
              <article class="catalog-card">
                <div class="placeholder-visual" aria-hidden="true">Curso img</div>
                <div>
                  <h3><?php echo htmlspecialchars($curso['titulo']); ?></h3>
                  <p><?php echo htmlspecialchars($curso['descripcion']); ?></p>
                  <dl>
                    <div>
                      <dt>Precio</dt>
                      <dd>$<?php echo number_format((float) $curso['precio'], 2, ',', '.'); ?></dd>
                    </div>
                    <div>
                      <dt>Tipo</dt>
                      <dd><?php echo htmlspecialchars($curso['tipo']); ?></dd>
                    </div>
                  </dl>
                  <div class="catalog-actions">
                    <a class="btn" href="curso.php">Ver curso</a>
                    <form action="carrito.php" method="post">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="id_publicacion" value="<?php echo (int) $curso['id_publicacion']; ?>">
                      <button type="submit" name="accion" value="agregar">Agregar al carrito</button>
                    </form>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>

            <?php foreach ($servicios as $servicio): ?>
              <article class="catalog-card">
                <div class="placeholder-visual" aria-hidden="true">Servicio img</div>
                <div>
                  <h3><?php echo htmlspecialchars($servicio['titulo']); ?></h3>
                  <p><?php echo htmlspecialchars($servicio['descripcion']); ?></p>
                  <dl>
                    <div>
                      <dt>Precio</dt>
                      <dd>$<?php echo number_format((float) $servicio['precio'], 2, ',', '.'); ?></dd>
                    </div>
                    <div>
                      <dt>Tipo</dt>
                      <dd><?php echo htmlspecialchars($servicio['tipo']); ?></dd>
                    </div>
                  </dl>
                  <div class="catalog-actions">
                    <a class="btn" href="servicio-detalle.php">Ver servicio</a>
                    <form action="carrito.php" method="post">
                      <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                      <input type="hidden" name="id_publicacion" value="<?php echo (int) $servicio['id_publicacion']; ?>">
                      <button type="submit" name="accion" value="agregar">Agregar al carrito</button>
                    </form>
                  </div>
                </div>
              </article>
            <?php endforeach; ?>

            <?php if (empty($publicaciones)): ?>
              <section class="empty-state" aria-labelledby="sin-publicaciones">
                <h3 id="sin-publicaciones">No hay publicaciones activas</h3>
                <p>Cuando se carguen nuevas propuestas, apareceran en este catalogo.</p>
              </section>
            <?php endif; ?>
          </div>
        </section>
      </div>
    </main>

    <footer class="site-footer">
      <p>&copy; 2026 Classia. Plataforma educativa para estudiantes, docentes y administradores.</p>
    </footer>
</body>
</html>

<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      http-equiv="Content-Security-Policy"
      content="default-src 'self'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; script-src 'self';" />
    <title>Classia</title>
    <meta
      name="description"
      content="Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada." />
    <link rel="stylesheet" href="css/animation.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="icon" type="image/png" href="assets/favicon.png" />
  </head>
  <body>
    <header class="site-header">
      <div class="site-header__inner">
        <a class="site-brand" href="index.php">
          <img src="assets/logoC.png" alt="Classia" />
        </a>
        <nav class="site-nav" aria-label="Navegaci&oacute;n principal">
          <a href="index.php" aria-current="page">Inicio</a>
          <a href="catalogo.php">Cat&aacute;logo</a>
          <a href="carrito.php">Carrito</a>
          <a href="login.php">Mi cuenta</a>
        </nav>
      </div>
    </header>

    <main>
      <header>
        <p>Plataforma educativa</p>
        <h1>Classia conecta aprendizaje y servicios</h1>
        <p>
          Un espacio para explorar cursos, contratar servicios educativos,
          publicar propuestas y administrar la actividad de la plataforma.
        </p>
        <p class="action-row">
          <a class="btn" href="catalogo.php">Explorar cat&aacute;logo</a>
          <a href="registro.php">Crear cuenta</a>
        </p>
      </header>

      <section aria-labelledby="roles-classia">
        <header>
          <p>Gesti&oacute;n clara</p>
          <h2 id="roles-classia">Una misma plataforma para cada rol</h2>
        </header>
        <div class="role-grid">
          <a
            class="content-card role-card-link motion-card"
            href="catalogo.php">
            <span class="badge role-card-meta">Estudiante</span>
            <h3>Cliente</h3>
            <p class="role-card-text">
              Explora cursos y servicios, consulta publicaciones, contrata
              propuestas educativas y gestiona su actividad.
            </p>
            <span class="role-card-action">Acceder</span>
          </a>

          <a
            class="content-card role-card-link motion-card"
            href="panelProveedor.php">
            <span class="badge role-card-meta">Publicador</span>
            <h3>Docente / Proveedor</h3>
            <p class="role-card-text">
              Publica cursos, ofrece servicios, administra publicaciones y
              gestiona solicitudes desde su panel.
            </p>
            <span class="role-card-action">Acceder</span>
          </a>

          <a
            class="content-card role-card-link motion-card"
            href="panelAdministrador.php">
            <span class="badge role-card-meta">Supervisi&oacute;n</span>
            <h3>Administrador</h3>
            <p class="role-card-text">
              Gestiona usuarios, revisa cursos, habilita o rechaza publicaciones
              y supervisa la plataforma.
            </p>
            <span class="role-card-action">Acceder</span>
          </a>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <p>&copy; 2026 Classia. Todos los derechos reservados.</p>
    </footer>

    <script src="js/script.js"></script>
  </body>
</html>

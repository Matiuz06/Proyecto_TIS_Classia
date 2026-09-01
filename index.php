<?php
$title      = 'Classia';
$description = 'Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada.';
$cssPrefix  = '.';
$jsPrefix   = '.';
$activePage = 'inicio';
include 'includes/header.php';
?>

    <main>
      <header>
        <p>Plataforma educativa</p>
        <h1>Classia conecta aprendizaje y servicios</h1>
        <p>
          Un espacio para explorar cursos, contratar servicios educativos,
          publicar propuestas y administrar la actividad de la plataforma.
        </p>
        <p class="action-row">
          <a class="btn" href="views/catalogo.php">Explorar catálogo</a>
          <a href="views/registro.php">Crear cuenta</a>
        </p>
      </header>

      <section aria-labelledby="roles-classia">
        <header>
          <p>Gestión clara</p>
          <h2 id="roles-classia">Una misma plataforma para cada rol</h2>
        </header>
        <div class="role-grid">
          <a
            class="content-card role-card-link motion-card"
            href="views/usuario.php">
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
            href="views/panel-proveedor.php">
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
            href="views/panel-administrador.php">
            <span class="badge role-card-meta">Supervisión</span>
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

<?php
include 'includes/footer.php';
?>

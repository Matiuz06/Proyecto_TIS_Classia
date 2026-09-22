<?php
require_once '../php/auth/roles.php';

requerir_rol(ROL_ADMIN, 'usuario.php');

$title       = 'Panel administrador - Classia';
$description = 'Panel de supervisión y gestión integral de la plataforma Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'panel-administrador';

include '../includes/header.php';
?>

<main class="admin-panel">

  <header class="admin-panel-header">
    <p class="admin-panel-kicker">Plataforma educativa</p>

    <h1>Panel de administración y control</h1>

    <p>
      Espacio centralizado para supervisar usuarios, gestionar solicitudes docentes,
      revisar publicaciones y coordinar la actividad global de Classia.
    </p>
  </header>


  <section class="admin-panel-section" aria-labelledby="apartados-admin">

    <h2 id="apartados-admin">Apartados del panel</h2>

    <div class="admin-panel-dropdown">

      <button type="button" class="admin-panel-trigger">
        <span>Seleccionar apartado</span>
        <span class="admin-panel-caret">▾</span>
      </button>

      <div class="admin-panel-menu">

        <a href="#" class="admin-panel-item">
          Usuarios
        </a>

        <a href="#" class="admin-panel-item">
          Solicitudes docentes
        </a>

        <a href="#" class="admin-panel-item">
          Publicaciones
        </a>

        <a href="#" class="admin-panel-item">
          Actividad
        </a>

      </div>

    </div>


    <div class="admin-panel-general">

      <div class="admin-panel-general-header">

        <h3>Estado de la plataforma</h3>

        <p>
          Consulta rápida de los principales elementos que requieren
          supervisión dentro de Classia.
        </p>
        <p class="action-row">
          <a class="btn" href="catalogo.php">Explorar catálogo</a>
          <a class="btn" href="solicitudes-docente.php">Gestionar solicitudes docentes</a>
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
            href="usuario.php">
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
            href="panel-proveedor.php">
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
            href="eventos.php">
            <span class="badge role-card-meta">Comunidad</span>
            <h3>Eventos &amp; Webinars</h3>
            <p class="role-card-text">
              Supervisa, crea, aprueba o rechaza eventos propuestos por docentes y la institución.
            </p>
            <span class="role-card-action">Gestionar Eventos</span>
          </a>

          <a
            class="content-card role-card-link motion-card"
            href="noticias.php">
            <span class="badge role-card-meta">Prensa</span>
            <h3>Noticias &amp; Novedades</h3>
            <p class="role-card-text">
              Publica y modera artículos, comunicados institucionales y noticias académicas.
            </p>
            <span class="role-card-action">Gestionar Noticias</span>
          </a>
        </div>

      </div>

    </div>

  </section>

</main>

<?php include '../includes/footer.php'; ?>
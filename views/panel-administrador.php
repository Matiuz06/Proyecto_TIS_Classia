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

    <main>
      <header>
        <p>Plataforma educativa</p>
        <h1>Panel de Administración y Control</h1>
        <p>
          Espacio centralizado para supervisar usuarios, gestionar solicitudes docentes,
          revisar publicaciones y coordinar la actividad global de Classia.
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
            href="solicitudes-docente.php">
            <span class="badge role-card-meta">Supervisión</span>
            <h3>Solicitudes Docentes</h3>
            <p class="role-card-text">
              Evalúa y aprueba o rechaza solicitudes de usuarios que desean ser
              docentes en la plataforma.
            </p>
            <span class="role-card-action">Administrar</span>
          </a>
        </div>
      </section>
    </main>

<?php include '../includes/footer.php'; ?>

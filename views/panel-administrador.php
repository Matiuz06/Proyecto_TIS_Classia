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

    <p class="admin-panel-kicker">Gestión clara</p>

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
        <p class="admin-panel-kicker">Resumen general</p>

        <h3>Estado de la plataforma</h3>

        <p>
          Consulta rápida de los principales elementos que requieren
          supervisión dentro de Classia.
        </p>
      </div>


      <div class="admin-panel-stats">

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Usuarios</span>

          <strong class="admin-panel-stat-value">0</strong>

          <span class="admin-panel-stat-description">
            Usuarios registrados
          </span>
        </article>


        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Solicitudes docentes</span>

          <strong class="admin-panel-stat-value">0</strong>

          <span class="admin-panel-stat-description">
            Solicitudes pendientes
          </span>
        </article>


        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Publicaciones</span>

          <strong class="admin-panel-stat-value">0</strong>

          <span class="admin-panel-stat-description">
            Publicaciones disponibles
          </span>
        </article>


        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Actividad</span>

          <strong class="admin-panel-stat-value">0</strong>

          <span class="admin-panel-stat-description">
            Actividades recientes
          </span>
        </article>

      </div>


      <div class="admin-panel-general-info">

        <div class="admin-panel-info-card">

          <h4>Supervisión de la plataforma</h4>

          <p>
            Desde este panel podés consultar y supervisar los principales
            movimientos de Classia, manteniendo organizada la gestión
            administrativa de la plataforma.
          </p>

        </div>


        <div class="admin-panel-info-card">

          <h4>Gestión administrativa</h4>

          <p>
            Utilizá los apartados del panel para acceder a la información
            correspondiente a usuarios, publicaciones, solicitudes docentes
            y actividad general.
          </p>

        </div>

      </div>

    </div>

  </section>

</main>

<?php include '../includes/footer.php'; ?>
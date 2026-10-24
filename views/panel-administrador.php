<?php

/**
 * Responsabilidad: Panel de administración, métricas, gestión de usuarios, cursos y moderación.
 */

require_once '../php/auth/roles.php';

requerir_rol(ROL_ADMIN, 'usuario.php');

$usuario = usuario_actual();
$uid     = (int) ($usuario['id_usuario'] ?? 0);

require_once '../config/database.php';
require_once '../php/admin/acciones_admin.php';
require_once '../php/admin/estadisticas_admin.php';
require_once '../php/solicitudes/gestionar_solicitudes_docente.php';
require_once '../php/utils/toast.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Procesamiento de acciones POST

$mensaje_admin = '';
$error_admin   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion       = $_POST['accion']      ?? '';
    $token_post   = $_POST['csrf_token']  ?? '';
    $csrf_session = $_SESSION['csrf_token'] ?? '';

    switch ($accion) {

        case 'cambiar_rol':
            $resultado     = cambiar_rol_usuario($pdo, (int)($_POST['id_usuario'] ?? 0), (int)($_POST['nuevo_rol'] ?? 0), $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'bloquear_usuario':
            $motivo        = trim($_POST['motivo_bloqueo'] ?? '');
            $resultado     = bloquear_usuario($pdo, (int)($_POST['id_usuario'] ?? 0), $motivo, $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'desbloquear_usuario':
            $resultado     = desbloquear_usuario($pdo, (int)($_POST['id_usuario'] ?? 0), $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'cambiar_estado_pub':
            $resultado     = cambiar_estado_publicacion_admin($pdo, (int)($_POST['id_publicacion'] ?? 0), $_POST['nuevo_estado'] ?? '', $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'archivar_curso':
            $resultado     = archivar_curso_admin($pdo, (int)($_POST['id_publicacion'] ?? 0), $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'eliminar_pub':
            $resultado     = eliminar_publicacion_admin($pdo, (int)($_POST['id_publicacion'] ?? 0), $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'decision_solicitud':
            $resultado     = procesar_decision_solicitud_docente((int)($_POST['id_solicitud_docente'] ?? 0), $_POST['decision'] ?? '', $token_post, $csrf_session, $pdo);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

    }
}

if ($mensaje_admin !== '') {
    $toastData = is_array($resultado ?? null) ? $resultado : [];
    set_toast(
        $toastData['toast_tipo'] ?? 'success',
        $mensaje_admin,
        $toastData['toast_descripcion'] ?? null
    );
    $mensaje_admin = '';
}

// Carga de datos del panel

$stats               = obtener_stats_plataforma($pdo);
$todos_usuarios      = obtener_todos_usuarios($pdo, $uid);
$todas_publicaciones = obtener_todas_publicaciones($pdo);
$solicitudes_docente = obtener_solicitudes_docente_pendientes($pdo);

// Carga de analíticas y estadísticas
$filtro_periodo      = $_GET['periodo'] ?? '30d';
$filtro_desde        = $_GET['desde'] ?? null;
$filtro_hasta        = $_GET['hasta'] ?? null;
$filtro_pub          = (int)($_GET['publicacion'] ?? 0);

$info_rango          = obtener_rango_fechas_analiticas($filtro_periodo, $filtro_desde, $filtro_hasta);
$kpis_analiticas     = obtener_kpis_analiticas($pdo, $info_rango['desde'], $info_rango['hasta'], $filtro_pub);
$series_graficos     = obtener_series_graficos($pdo, $info_rango['desde'], $info_rango['hasta'], $filtro_pub);
$dist_valoraciones   = obtener_distribucion_valoraciones($pdo, $info_rango['desde'], $info_rango['hasta'], $filtro_pub);
$top_servicios       = obtener_top_servicios_ingresos($pdo, $info_rango['desde'], $info_rango['hasta'], 5);
$desglose_servicios  = obtener_desglose_servicios_periodo($pdo, $info_rango['desde'], $info_rango['hasta'], $filtro_pub);

// Configuración de página

$title       = 'Panel de administración — Classia';
$description = 'Panel de supervisión y gestión integral de la plataforma Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'panel-administrador';

include '../includes/header.php';
?>

<main id="main-content" class="admin-panel">

  <?php if ($error_admin !== ''): ?>
    <div class="alert alert-danger motion-entry" role="alert">
      <?= htmlspecialchars($error_admin) ?>
    </div>
  <?php endif; ?>

  <!-- CABECERA -->
  <header class="admin-panel-header provider-dashboard__header">
    <h1>Panel de administración y control</h1>

    <p>Gestioná usuarios, solicitudes docentes, publicaciones y estadísticas de Classia.</p>
  </header>


  <!-- APARTADOS DEL PANEL -->
  <div class="admin-dashboard-layout">
  <section class="admin-panel-section admin-panel-navigation" aria-labelledby="apartados-admin">

    <p class="admin-panel-kicker">Gestión clara</p>

    <h2 id="apartados-admin">Apartados del panel</h2>

    <div class="admin-panel-dropdown">

      <button type="button" class="admin-panel-trigger" id="admin-panel-trigger" aria-expanded="false" aria-controls="admin-panel-menu">
        <span>Seleccionar apartado</span>
        <span class="admin-panel-caret" aria-hidden="true">▾</span>
      </button>

      <div class="admin-panel-menu" id="admin-panel-menu" role="menu">

        <a href="#dashboard" class="admin-panel-item" role="menuitem">
          Estado general
        </a>

        <a href="#analiticas" class="admin-panel-item" role="menuitem">
          Analíticas y Métricas
        </a>

        <a href="#usuarios" class="admin-panel-item" role="menuitem">
          Usuarios
          <span class="admin-badge-muted"><?= $stats['total_usuarios'] ?></span>
        </a>

        <a href="#solicitudes" class="admin-panel-item" role="menuitem">
          Solicitudes docentes
          <?php if ($stats['solicitudes_docente'] > 0): ?>
            <span class="admin-badge-count"><?= $stats['solicitudes_docente'] ?></span>
          <?php endif; ?>
        </a>

        <a href="#publicaciones" class="admin-panel-item" role="menuitem">
          Publicaciones
          <span class="admin-badge-muted"><?= $stats['publicaciones_total'] ?></span>
        </a>

      </div>

    </div>


  </section>

  <!-- RESUMEN GENERAL / DASHBOARD -->
  <section class="admin-panel-general admin-tab-panel" id="dashboard" data-admin-section="dashboard" aria-labelledby="titulo-dashboard">

      <div class="admin-panel-general-header">
        <p class="admin-panel-kicker">Resumen general</p>

        <h3 id="titulo-dashboard">Estado de la plataforma</h3>

        <p>
          Consulta rápida de los principales elementos que requieren
          supervisión dentro de Classia.
        </p>
      </div>


      <div class="admin-panel-stats admin-summary-section">

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Usuarios totales</span>
          <strong class="admin-panel-stat-value"><?= $stats['total_usuarios'] ?></strong>
          <span class="admin-panel-stat-description">Registrados en la plataforma</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Estudiantes</span>
          <strong class="admin-panel-stat-value"><?= $stats['total_estudiantes'] ?></strong>
          <span class="admin-panel-stat-description">Usuarios con rol estudiante</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Docentes / Proveedores</span>
          <strong class="admin-panel-stat-value"><?= $stats['total_docentes'] ?></strong>
          <span class="admin-panel-stat-description">Usuarios con rol docente</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Publicaciones activas</span>
          <strong class="admin-panel-stat-value"><?= $stats['publicaciones_activas'] ?></strong>
          <span class="admin-panel-stat-description">De <?= $stats['publicaciones_total'] ?> en total</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Solicitudes docentes</span>
          <strong class="admin-panel-stat-value"><?= $stats['solicitudes_docente'] ?></strong>
          <span class="admin-panel-stat-description">Pendientes de revisión</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Noticias pendientes</span>
          <strong class="admin-panel-stat-value"><?= $stats['noticias_pendientes'] ?></strong>
          <span class="admin-panel-stat-description">Esperan moderación</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Eventos pendientes</span>
          <strong class="admin-panel-stat-value"><?= $stats['eventos_pendientes'] ?></strong>
          <span class="admin-panel-stat-description">Esperan aprobación</span>
        </article>

        <article class="admin-panel-stat">
          <span class="admin-panel-stat-label">Servicios en proceso</span>
          <strong class="admin-panel-stat-value"><?= $stats['servicios_en_proceso'] ?></strong>
          <span class="admin-panel-stat-description">Contrataciones activas</span>
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

  </section>


  <!--ANALÍTICAS Y RENDIMIENTO. Métricas de visitas, contrataciones, ingresos y valoraciones-->
  <section id="analiticas" data-admin-section="analiticas" class="admin-tab-panel admin-analytics-section" aria-labelledby="titulo-analiticas">
    <header class="admin-analytics-header">
      <div class="admin-analytics-title-wrap">
        <p class="admin-panel-kicker">Métricas y Rendimiento</p>
        <h2 id="titulo-analiticas">Panel de analíticas institucionales</h2>
        <p>Estadísticas de visitas, contrataciones, ingresos generados y satisfacción consolidada.</p>
      </div>

      <!-- FILTRO POR PERÍODO Y SERVICIO (REQ-ADM-02) -->
      <form method="GET" action="panel-administrador.php#analiticas" class="admin-analytics-filter-form" role="search" aria-label="Filtro de analíticas">
        <div class="admin-filter-group">
          <label for="analytics-periodo-select" class="admin-filter-label">Período:</label>
          <select id="analytics-periodo-select" name="periodo" class="admin-filter-select" aria-label="Seleccionar período de análisis">
            <option value="7d" <?= $filtro_periodo === '7d' ? 'selected' : '' ?>>Últimos 7 días</option>
            <option value="30d" <?= $filtro_periodo === '30d' ? 'selected' : '' ?>>Últimos 30 días</option>
            <option value="mes" <?= $filtro_periodo === 'mes' ? 'selected' : '' ?>>Este mes</option>
            <option value="anio" <?= $filtro_periodo === 'anio' ? 'selected' : '' ?>>Este año</option>
            <option value="todo" <?= $filtro_periodo === 'todo' ? 'selected' : '' ?>>Todo el histórico</option>
            <option value="custom" <?= $filtro_periodo === 'custom' ? 'selected' : '' ?>>Personalizado...</option>
          </select>
        </div>

        <div id="analytics-custom-dates" class="admin-filter-group admin-custom-dates" style="<?= $filtro_periodo === 'custom' ? 'display: flex;' : 'display: none;' ?>">
          <label for="analytics-desde" class="admin-filter-label">Desde:</label>
          <input type="date" id="analytics-desde" name="desde" value="<?= htmlspecialchars($info_rango['desde']) ?>" class="admin-filter-input">
          <label for="analytics-hasta" class="admin-filter-label">Hasta:</label>
          <input type="date" id="analytics-hasta" name="hasta" value="<?= htmlspecialchars($info_rango['hasta']) ?>" class="admin-filter-input">
        </div>

        <div class="admin-filter-group">
          <label for="analytics-pub-select" class="admin-filter-label">Servicio / Curso:</label>
          <select id="analytics-pub-select" name="publicacion" class="admin-filter-select" aria-label="Filtrar por publicación específica">
            <option value="0">Todos los servicios y cursos</option>
            <?php foreach ($todas_publicaciones as $pub): ?>
              <?php if ($pub['estado'] !== 'Eliminado'): ?>
                <option value="<?= (int)$pub['id_publicacion'] ?>" <?= $filtro_pub === (int)$pub['id_publicacion'] ? 'selected' : '' ?>>
                  [<?= htmlspecialchars($pub['tipo']) ?>] <?= htmlspecialchars(mb_strimwidth($pub['titulo'], 0, 40, '…')) ?>
                </option>
              <?php endif; ?>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="admin-filter-actions">
          <button type="submit" class="btn btn-sm">Aplicar filtros</button>
          <?php if ($filtro_periodo !== '30d' || $filtro_pub > 0): ?>
            <a href="panel-administrador.php#analiticas" class="btn btn-ghost btn-sm">Restablecer</a>
          <?php endif; ?>
        </div>
      </form>
    </header>

    <!-- Rango activo badge -->
    <div class="admin-analytics-active-range">
      <span>Rango consultado: <strong><?= htmlspecialchars($info_rango['etiqueta']) ?></strong></span>
      <?php if ($filtro_pub > 0): ?>
        <?php 
          $pub_sel_nombre = 'Publicación #' . $filtro_pub;
          foreach ($todas_publicaciones as $p) {
              if ((int)$p['id_publicacion'] === $filtro_pub) { $pub_sel_nombre = $p['titulo']; break; }
          }
        ?>
        <span class="admin-analytics-active-tag">Filtrado por: <em><?= htmlspecialchars($pub_sel_nombre) ?></em></span>
      <?php endif; ?>
    </div>

    <!-- TARJETAS KPIS DINÁMICAS (REQ-ADM-04) -->
    <div class="admin-analytics-kpi-grid">
      <article class="admin-kpi-card">
        <span class="admin-kpi-label">Ingresos del período</span>
        <strong class="admin-kpi-value admin-kpi-value--accent">$<?= number_format($kpis_analiticas['ingresos_totales'], 2, ',', '.') ?></strong>
        <span class="admin-kpi-sub">Monto total contratado</span>
      </article>

      <article class="admin-kpi-card">
        <span class="admin-kpi-label">Contrataciones</span>
        <strong class="admin-kpi-value"><?= number_format($kpis_analiticas['contrataciones_totales']) ?></strong>
        <span class="admin-kpi-sub">Órdenes concretadas</span>
      </article>

      <article class="admin-kpi-card">
        <span class="admin-kpi-label">Visitas registradas</span>
        <strong class="admin-kpi-value"><?= number_format($kpis_analiticas['visitas_totales']) ?></strong>
        <span class="admin-kpi-sub">Visualizaciones en detalle</span>
      </article>

      <article class="admin-kpi-card">
        <span class="admin-kpi-label">Tasa de conversión</span>
        <strong class="admin-kpi-value admin-kpi-value--success"><?= number_format($kpis_analiticas['tasa_conversion'], 2, ',', '.') ?>%</strong>
        <span class="admin-kpi-sub">Contrataciones / Visitas</span>
      </article>

      <article class="admin-kpi-card">
        <span class="admin-kpi-label">Satisfacción media</span>
        <strong class="admin-kpi-value admin-kpi-value--warning">
          <?php if ($kpis_analiticas['promedio_valoracion'] > 0): ?>
            ★ <?= number_format($kpis_analiticas['promedio_valoracion'], 1) ?> <span class="admin-kpi-sub-text">/ 5.0</span>
          <?php else: ?>
            —
          <?php endif; ?>
        </strong>
        <span class="admin-kpi-sub"><?= $kpis_analiticas['total_valoraciones'] ?> valoracion<?= $kpis_analiticas['total_valoraciones'] !== 1 ? 'es' : '' ?></span>
      </article>
    </div>

    <!-- GRÁFICOS INTERACTIVOS (REQ-ADM-04) -->
    <div class="admin-charts-grid">
      <!-- Gráfico 1: Evolución Ingresos y Contrataciones -->
      <div class="admin-chart-card admin-chart-card--wide">
        <div class="admin-chart-header">
          <h3>Evolución de Ingresos y Contrataciones</h3>
          <span class="admin-chart-legend-badge">Ingresos ($) vs Cantidad</span>
        </div>
        <div class="admin-chart-container">
          <canvas id="chart-ingresos-contrataciones" aria-label="Gráfico de evolución de ingresos y contrataciones"></canvas>
        </div>
      </div>

      <!-- Gráfico 2: Visitas vs Conversión -->
      <div class="admin-chart-card">
        <div class="admin-chart-header">
          <h3>Visitas vs. Contrataciones</h3>
          <span class="admin-chart-legend-badge">Embudo de demanda</span>
        </div>
        <div class="admin-chart-container">
          <canvas id="chart-visitas-conversion" aria-label="Gráfico de visitas versus contrataciones"></canvas>
        </div>
      </div>

      <!-- Gráfico 3: Distribución de Valoraciones -->
      <div class="admin-chart-card">
        <div class="admin-chart-header">
          <h3>Distribución de Valoraciones</h3>
          <span class="admin-chart-legend-badge">Calificaciones de clientes</span>
        </div>
        <div class="admin-chart-container">
          <canvas id="chart-valoraciones-distribucion" aria-label="Gráfico de distribución de calificaciones de 1 a 5 estrellas"></canvas>
        </div>
      </div>

      <!-- Gráfico 4: Top Servicios por Ingresos -->
      <?php if (!empty($top_servicios)): ?>
        <div class="admin-chart-card admin-chart-card--wide">
          <div class="admin-chart-header">
            <h3>Publicaciones con mayor volumen de ingresos</h3>
            <span class="admin-chart-legend-badge">Top 5 más demandados</span>
          </div>
          <div class="admin-chart-container">
            <canvas id="chart-top-servicios" aria-label="Gráfico de publicaciones más rentables"></canvas>
          </div>
        </div>
      <?php endif; ?>
    </div>

    <!-- TABLA DE DESGLOSE POR SERVICIO Y PERÍODO (REQ-ADM-02) -->
    <div class="admin-services-breakdown-section">
      <header class="admin-breakdown-header">
        <div>
          <h3>Desglose de rendimiento por servicio y curso</h3>
          <p>Métricas consolidadas de visitas, contrataciones, tasa de conversión e ingresos en el período seleccionado.</p>
        </div>
      </header>

      <?php if (empty($desglose_servicios)): ?>
        <p class="muted">No hay publicaciones con actividad en el período consultado.</p>
      <?php else: ?>
        <div class="admin-users-table-wrap">
          <table class="admin-users-table admin-breakdown-table" aria-label="Estadísticas de visitas y contrataciones por servicio y período">
            <thead>
              <tr>
                <th scope="col">Publicación</th>
                <th scope="col">Tipo</th>
                <th scope="col">Categoría</th>
                <th scope="col">Docente / Proveedor</th>
                <th scope="col">Visitas</th>
                <th scope="col">Contrataciones</th>
                <th scope="col">Conversión</th>
                <th scope="col">Ingresos</th>
                <th scope="col">Valoración</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($desglose_servicios as $ds): ?>
                <?php
                  $tipo_class = ($ds['tipo'] === 'Curso') ? 'badge-course' : 'badge-service';
                  $conv = (float)$ds['conversion_pct'];
                ?>
                <tr>
                  <td>
                    <strong><?= htmlspecialchars($ds['titulo']) ?></strong>
                    <div class="text-muted" style="font-size: var(--text-xs);">Precio unitario: $<?= number_format($ds['precio'], 2, ',', '.') ?></div>
                  </td>
                  <td>
                    <span class="badge <?= $tipo_class ?>"><?= htmlspecialchars($ds['tipo']) ?></span>
                  </td>
                  <td><?= htmlspecialchars($ds['nombre_categoria']) ?></td>
                  <td><?= htmlspecialchars($ds['docente_nombre']) ?></td>
                  <td><strong><?= number_format($ds['visitas_periodo']) ?></strong></td>
                  <td><strong><?= number_format($ds['contrataciones_periodo']) ?></strong></td>
                  <td>
                    <div class="admin-conversion-indicator" title="Tasa de conversión: <?= $conv ?>%">
                      <span><?= $conv ?>%</span>
                      <div class="admin-conversion-bar-bg">
                        <div class="admin-conversion-bar-fill" style="width: <?= min(100, $conv * 4) ?>%;"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <strong class="admin-table-money">$<?= number_format($ds['ingresos_periodo'], 2, ',', '.') ?></strong>
                  </td>
                  <td>
                    <?php if ((float)$ds['promedio_valoracion'] > 0): ?>
                      <span class="admin-table-rating" title="<?= $ds['total_valoraciones'] ?> valoraciones">
                        ★ <?= number_format((float)$ds['promedio_valoracion'], 1) ?>
                        <small>(<?= (int)$ds['total_valoraciones'] ?>)</small>
                      </span>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </section>


  <!-- USUARIOS -->
  <section id="usuarios" data-admin-section="usuarios" class="admin-tab-panel admin-users-section" aria-labelledby="titulo-usuarios">
    <header>
      <h2 id="titulo-usuarios">Usuarios</h2>
      <p>Consultá, gestioná roles y moderá el acceso de los usuarios registrados en la plataforma.</p>
    </header>

    <?php if (empty($todos_usuarios)): ?>
      <p class="muted">No hay usuarios registrados.</p>
    <?php else: ?>
      <div class="admin-users-table-wrap">
        <table class="admin-users-table" aria-label="Lista de usuarios">
          <thead>
            <tr>
              <th scope="col">Nombre</th>
              <th scope="col">Email</th>
              <th scope="col">Rol actual</th>
              <th scope="col">Estado</th>
              <th scope="col">Fecha de registro</th>
              <th scope="col">Acciones</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($todos_usuarios as $usr): ?>
              <?php
                $id_rol_usr   = (int) $usr['id_rol'];
                $esta_activo  = (int) ($usr['activo'] ?? 1) === 1;
                $motivo_usr   = trim((string) ($usr['motivo_bloqueo'] ?? ''));
                $etiqueta_rol = match ($id_rol_usr) {
                    ROL_ESTUDIANTE => 'Estudiante',
                    ROL_DOCENTE    => 'Docente',
                    ROL_ADMIN      => 'Admin',
                    default        => 'Desconocido',
                };
                $clase_rol = match ($id_rol_usr) {
                    ROL_ESTUDIANTE => 'role-badge--estudiante',
                    ROL_DOCENTE    => 'role-badge--docente',
                    ROL_ADMIN      => 'role-badge--admin',
                    default        => '',
                };
              ?>
              <tr>
                <td><strong><?= htmlspecialchars(trim($usr['nombre'] . ' ' . $usr['apellido'])) ?></strong></td>
                <td><?= htmlspecialchars($usr['email']) ?></td>
                <td>
                  <span class="role-badge <?= $clase_rol ?>">
                    <?= htmlspecialchars($etiqueta_rol) ?>
                  </span>
                </td>
                <td>
                  <?php if ($esta_activo): ?>
                    <span class="badge badge-course">Activo</span>
                  <?php else: ?>
                    <span class="badge badge-error" title="<?= htmlspecialchars($motivo_usr ?: 'Sin justificación especificada') ?>">Bloqueado</span>
                    <?php if ($motivo_usr !== ''): ?>
                      <div class="admin-user-reason text-muted" title="<?= htmlspecialchars($motivo_usr) ?>">
                        <em>Motivo: <?= htmlspecialchars(mb_strimwidth($motivo_usr, 0, 45, '…')) ?></em>
                      </div>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($usr['fecha_registro'])): ?>
                    <time datetime="<?= htmlspecialchars($usr['fecha_registro']) ?>">
                      <?= date('d/m/Y', strtotime($usr['fecha_registro'])) ?>
                    </time>
                  <?php else: ?>—<?php endif; ?>
                </td>
                <td>
                  <?php if ($id_rol_usr !== ROL_ADMIN): ?>
                    <div class="admin-actions-cell">
                      <!-- Cambio de rol -->
                      <form method="POST" action="panel-administrador.php#usuarios" class="admin-inline-form">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                        <input type="hidden" name="accion"     value="cambiar_rol">
                        <input type="hidden" name="id_usuario" value="<?= (int) $usr['id_usuario'] ?>">
                        <?php if ($id_rol_usr === ROL_ESTUDIANTE): ?>
                          <input type="hidden" name="nuevo_rol" value="<?= ROL_DOCENTE ?>">
                          <button type="submit" class="btn-status btn-status-activate" title="Promover a Docente"
                            onclick="return confirm('¿Promover a Docente a <?= htmlspecialchars(trim($usr['nombre'] . ' ' . $usr['apellido'])) ?>?')">
                            → Docente
                          </button>
                        <?php elseif ($id_rol_usr === ROL_DOCENTE): ?>
                          <input type="hidden" name="nuevo_rol" value="<?= ROL_ESTUDIANTE ?>">
                          <button type="submit" class="btn-status btn-status-pause" title="Rebajar a Estudiante"
                            onclick="return confirm('¿Rebajar a Estudiante a <?= htmlspecialchars(trim($usr['nombre'] . ' ' . $usr['apellido'])) ?>?')">
                            → Estudiante
                          </button>
                        <?php endif; ?>
                      </form>

                      <!-- Bloqueo / Desbloqueo con justificación -->
                      <?php if ($esta_activo): ?>
                        <button type="button" class="btn-status btn-status-delete"
                          onclick="abrirModalBloqueo(<?= (int)$usr['id_usuario'] ?>, '<?= htmlspecialchars(trim($usr['nombre'] . ' ' . $usr['apellido']), ENT_QUOTES) ?>')">
                          Bloquear
                        </button>
                      <?php else: ?>
                        <form method="POST" action="panel-administrador.php#usuarios" class="admin-inline-form">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                          <input type="hidden" name="accion"     value="desbloquear_usuario">
                          <input type="hidden" name="id_usuario" value="<?= (int) $usr['id_usuario'] ?>">
                          <button type="submit" class="btn-status btn-status-activate"
                            onclick="return confirm('¿Reactivar y desbloquear a <?= htmlspecialchars(trim($usr['nombre'] . ' ' . $usr['apellido'])) ?>?')">
                            Desbloquear
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  <?php else: ?>
                    <span class="muted">—</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </section>


  <!-- SOLICITUDES DOCENTES -->
  <section id="solicitudes" data-admin-section="solicitudes" class="admin-tab-panel admin-requests-section" aria-labelledby="titulo-solicitudes">
    <header>
      <h2 id="titulo-solicitudes">
        Solicitudes docentes pendientes
        <?php if ($stats['solicitudes_docente'] > 0): ?>
          <span class="admin-badge-count"><?= $stats['solicitudes_docente'] ?></span>
        <?php endif; ?>
      </h2>
      <p>Aprobá o rechazá solicitudes de usuarios que quieren ser docentes / proveedores.</p>
      <a href="solicitudes-docente.php" class="btn btn-secondary-action">Ver página completa</a>
    </header>

    <?php if (empty($solicitudes_docente)): ?>
      <br>  
      <p class="muted">No hay solicitudes docentes pendientes.</p>
    <?php else: ?>
      <?php foreach ($solicitudes_docente as $sol): ?>
        <details class="course-deliveries-task teacher-request-embedded-details">
          <summary aria-controls="detalle-solicitud-panel-<?= (int) $sol['id_solicitud_docente'] ?>" aria-expanded="false">
            <span class="teacher-request-embedded-summary">
              <strong><?= htmlspecialchars($sol['nombre'] . ' ' . $sol['apellido']) ?></strong>
              <span><?= htmlspecialchars($sol['email']) ?></span>
            </span>
            <span class="course-deliveries-chevron" aria-hidden="true"></span>
          </summary>
          <div id="detalle-solicitud-panel-<?= (int) $sol['id_solicitud_docente'] ?>" class="accordion-panel">
        <article>
          <header>
            <h3><?= htmlspecialchars($sol['nombre'] . ' ' . $sol['apellido']) ?></h3>
            <p>Estado: <strong><?= htmlspecialchars($sol['estado']) ?></strong></p>
          </header>
          <dl>
            <div><dt>Email</dt><dd><?= htmlspecialchars($sol['email']) ?></dd></div>
            <div><dt>Fecha de solicitud</dt><dd><?= date('d/m/Y', strtotime($sol['fecha_solicitud'])) ?></dd></div>
          </dl>
          <?php if (!empty($sol['motivo'])): ?>
            <p><?= htmlspecialchars(mb_strimwidth($sol['motivo'], 0, 260, '…')) ?></p>
          <?php endif; ?>

          <?php if (!empty($sol['documentacion_ruta'])): ?>
            <p>
              <strong>Documentación adjunta:</strong>
              <?= htmlspecialchars($sol['documentacion_nombre'] ?? 'Archivo') ?>
              (<?= htmlspecialchars($sol['documentacion_mime'] ?? 'tipo desconocido') ?>,
              <?= number_format(((int) ($sol['documentacion_tamano'] ?? 0)) / 1048576, 2, ',', '.') ?> MB,
              <?= htmlspecialchars($sol['documentacion_fecha'] ?? '') ?>)
            </p>
            <div class="teacher-request-actions">
              <a class="btn btn-secondary" href="../php/solicitudes/descargar_documentacion_docente.php?id=<?= (int) $sol['id_solicitud_docente'] ?>&modo=inline" target="_blank" rel="noopener">Ver documentación</a>
              <a class="btn btn-secondary" href="../php/solicitudes/descargar_documentacion_docente.php?id=<?= (int) $sol['id_solicitud_docente'] ?>">Descargar documentación</a>
            </div>
          <?php endif; ?>

          <form method="POST" action="panel-administrador.php#solicitudes" class="admin-decision-form">
            <input type="hidden" name="csrf_token"           value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="accion"               value="decision_solicitud">
            <input type="hidden" name="id_solicitud_docente" value="<?= (int) $sol['id_solicitud_docente'] ?>">
            <button type="submit" name="decision" value="aprobar"  class="btn-status btn-status-activate">Aprobar</button>
            <button type="submit" name="decision" value="rechazar" class="btn-status btn-status-delete"
              onclick="return confirm('¿Rechazar la solicitud de <?= htmlspecialchars($sol['nombre'] . ' ' . $sol['apellido']) ?>?')">
              Rechazar
            </button>
          </form>
        </article>
          </div>
        </details>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>


  <!-- PUBLICACIONES Y MODERACIÓN DE CURSOS -->
  <section id="publicaciones" data-admin-section="publicaciones" class="admin-tab-panel admin-publications-section" aria-labelledby="titulo-publicaciones">
    <header>
      <h2 id="titulo-publicaciones">Publicaciones y Cursos</h2>
      <p>Revisá, moderá contenidos, cambiá el estado o eliminá publicaciones de la plataforma. Los cursos archivados conservan su histórico pero no admiten nuevas actividades.</p>
      <a href="crear-publicacion.php" class="btn btn-primary-action">+ Crear publicación</a>
      <br>
    </header>

    <?php if (empty($todas_publicaciones)): ?>
      <br>
      <p class="muted">No hay publicaciones en la plataforma.</p>
    <?php else: ?>
      <div class="publicaciones-lista">
        <?php foreach ($todas_publicaciones as $pub): ?>
          <article class="pub-card">
            <header>
              <h4>
                <?php $detalle_url = $pub['tipo'] === 'Curso' ? 'curso.php' : 'servicio-detalle.php'; ?>
                <a href="<?= $detalle_url ?>?id=<?= (int) $pub['id_publicacion'] ?>">
                  <?= htmlspecialchars($pub['titulo']) ?>
                </a>
              </h4>
              <span class="pub-badge">
                <?= htmlspecialchars($pub['tipo']) ?>
                <?php if (!empty($pub['nombre_categoria'])): ?> — <?= htmlspecialchars($pub['nombre_categoria']) ?><?php endif; ?>
              </span>
            </header>

            <dl class="pub-details">
              <div>
                <dt><strong>Estado:</strong></dt>
                <dd>
                  <?php
                    $badge_estado = match($pub['estado']) {
                        'Activo'    => 'badge-course',
                        'Pausado'   => 'badge-warning',
                        'Inactivo'  => 'badge-muted',
                        'Archivado' => 'badge-archived',
                        'Eliminado' => 'badge-error',
                        default     => ''
                    };
                  ?>
                  <span class="badge <?= $badge_estado ?>"><?= htmlspecialchars($pub['estado']) ?></span>
                </dd>
              </div>
              <div>
                <dt><strong>Autor:</strong></dt>
                <dd><?= htmlspecialchars(trim($pub['autor_nombre'] . ' ' . $pub['autor_apellido'])) ?></dd>
              </div>
              <div>
                <dt><strong>Precio:</strong></dt>
                <dd>$<?= number_format((float) $pub['precio'], 2, ',', '.') ?></dd>
              </div>
              <div>
                <dt><strong>Fecha:</strong></dt>
                <dd><?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?></dd>
              </div>
            </dl>

            <nav aria-label="Acciones de <?= htmlspecialchars($pub['titulo']) ?>">
              <ul class="pub-actions">
                <?php if ($pub['tipo'] === 'Curso'): ?>
                  <li><a href="gestionar-contenido-curso.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link" title="Moderar módulos, clases y recursos">Moderar temario</a></li>
                  <li><a href="curso.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link" title="Supervisar aula y foro">Ver aula</a></li>
                <?php endif; ?>
                <li><a href="editar-publicacion.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link">Editar</a></li>
                <li><a href="vista-previa-publicacion.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link">Vista previa</a></li>
                <?php if ($pub['estado'] !== 'Eliminado'): ?>
                <li>
                  <form method="POST" action="panel-administrador.php#publicaciones" class="admin-inline-form">
                    <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"         value="cambiar_estado_pub">
                    <input type="hidden" name="id_publicacion" value="<?= (int) $pub['id_publicacion'] ?>">
                    <?php if ($pub['estado'] === 'Activo'): ?>
                      <input type="hidden" name="nuevo_estado" value="Pausado">
                      <button type="submit" class="btn-status btn-status-pause">Pausar</button>
                    <?php elseif ($pub['estado'] === 'Archivado'): ?>
                      <input type="hidden" name="nuevo_estado" value="Activo">
                      <button type="submit" class="btn-status btn-status-activate" title="Restaurar curso archivado">Restaurar</button>
                    <?php else: ?>
                      <input type="hidden" name="nuevo_estado" value="Activo">
                      <button type="submit" class="btn-status btn-status-activate">Activar</button>
                    <?php endif; ?>
                  </form>
                </li>
                <?php endif; ?>
                <?php if ($pub['tipo'] === 'Curso' && !in_array($pub['estado'], ['Archivado', 'Eliminado'], true)): ?>
                <li>
                  <form method="POST" action="panel-administrador.php#publicaciones" class="admin-inline-form">
                    <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"         value="archivar_curso">
                    <input type="hidden" name="id_publicacion" value="<?= (int) $pub['id_publicacion'] ?>">
                    <button type="submit" class="btn-status btn-status-archive"
                      onclick="return confirm('¿Archivar el curso «<?= htmlspecialchars($pub['titulo'], ENT_QUOTES) ?>»? Conservará su histórico pero no admitirá nuevas actividades.')"
                      title="Archivar: bloquea nuevas actividades, conserva histórico">
                      Archivar
                    </button>
                  </form>
                </li>
                <?php endif; ?>
                <li>
                  <form method="POST" action="panel-administrador.php#publicaciones" class="admin-inline-form">
                    <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"         value="eliminar_pub">
                    <input type="hidden" name="id_publicacion" value="<?= (int) $pub['id_publicacion'] ?>">
                    <button type="submit" class="btn-status btn-status-delete"
                      onclick="return confirm('¿Eliminar la publicación «<?= htmlspecialchars($pub['titulo']) ?>»?')">
                      Eliminar
                    </button>
                  </form>
                </li>
              </ul>
            </nav>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>


    </div>

</main>

<!-- Modal / Diálogo para bloqueo con justificación -->
<dialog id="dialog-bloqueo-admin" class="course-dialog admin-dialog-bloqueo">
  <form method="POST" action="panel-administrador.php#usuarios">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
    <input type="hidden" name="accion" value="bloquear_usuario">
    <input type="hidden" name="id_usuario" id="bloqueo_id_usuario" value="0">

    <h3>Bloquear usuario</h3>
    <p>Estás por suspender el acceso de: <strong id="bloqueo_nombre_usuario"></strong></p>

    <label class="admin-dialog-label">
      <strong>Motivo o justificación del bloqueo (obligatorio):</strong>
      <textarea name="motivo_bloqueo" id="bloqueo_motivo" rows="3" required placeholder="Indicá la razón por la cual se restringe la cuenta..." class="admin-dialog-textarea"></textarea>
    </label>

    <div class="admin-dialog-actions">
      <button type="button" class="btn btn-secondary" onclick="document.getElementById('dialog-bloqueo-admin').close();">Cancelar</button>
      <button type="submit" class="btn btn-status-delete">Confirmar bloqueo</button>
    </div>
  </form>
</dialog>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
  window.classiaAnalyticsData = {
    periodo: <?= json_encode($info_rango) ?>,
    kpis: <?= json_encode($kpis_analiticas) ?>,
    series: <?= json_encode($series_graficos) ?>,
    distribucionValoraciones: <?= json_encode($dist_valoraciones) ?>,
    topServicios: <?= json_encode($top_servicios) ?>
  };
</script>
<script src="<?= $jsPrefix ?>/js/admin.js?v=<?= filemtime(__DIR__ . '/../js/admin.js') ?>" defer></script>
<script src="<?= $jsPrefix ?>/js/admin-charts.js" defer></script>

<?php include '../includes/footer.php'; ?>

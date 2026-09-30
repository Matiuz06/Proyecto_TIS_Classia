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
require_once '../php/noticias/gestionar_noticias.php';
require_once '../php/eventos/gestionar_eventos.php';

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

        case 'inscribir_usuario_curso':
            $id_u          = (int)($_POST['id_usuario_inscribir'] ?? 0);
            $id_p          = (int)($_POST['id_curso_inscribir'] ?? 0);
            $resultado     = inscribir_usuario_en_curso($pdo, $id_u, $id_p, $token_post, $csrf_session);
            $error_admin   = $resultado['error'];
            $mensaje_admin = $resultado['mensaje'];
            break;

        case 'cambiar_estado_pub':
            $resultado     = cambiar_estado_publicacion_admin($pdo, (int)($_POST['id_publicacion'] ?? 0), $_POST['nuevo_estado'] ?? '', $token_post, $csrf_session);
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

        case 'aprobar_noticia':
            if (!empty($csrf_session) && hash_equals($csrf_session, $token_post)) {
                $id_n = (int)($_POST['id_noticia'] ?? 0);
                if ($id_n > 0) { aprobar_noticia($pdo, $id_n); $mensaje_admin = 'Noticia aprobada correctamente.'; }
            } else { $error_admin = 'Sesión de formulario inválida.'; }
            break;

        case 'rechazar_noticia':
            if (!empty($csrf_session) && hash_equals($csrf_session, $token_post)) {
                $id_n = (int)($_POST['id_noticia'] ?? 0);
                if ($id_n > 0) { rechazar_noticia($pdo, $id_n); $mensaje_admin = 'Noticia rechazada correctamente.'; }
            } else { $error_admin = 'Sesión de formulario inválida.'; }
            break;

        case 'eliminar_noticia':
            if (!empty($csrf_session) && hash_equals($csrf_session, $token_post)) {
                $id_n = (int)($_POST['id_noticia'] ?? 0);
                if ($id_n > 0) { eliminar_noticia($pdo, $id_n, null, true); $mensaje_admin = 'Noticia eliminada correctamente.'; }
            } else { $error_admin = 'Sesión de formulario inválida.'; }
            break;

        case 'aprobar_evento':
            if (!empty($csrf_session) && hash_equals($csrf_session, $token_post)) {
                $id_e = (int)($_POST['id_evento'] ?? 0);
                if ($id_e > 0) { aprobar_evento($pdo, $id_e); $mensaje_admin = 'Evento aprobado correctamente.'; }
            } else { $error_admin = 'Sesión de formulario inválida.'; }
            break;

        case 'rechazar_evento':
            if (!empty($csrf_session) && hash_equals($csrf_session, $token_post)) {
                $id_e = (int)($_POST['id_evento'] ?? 0);
                if ($id_e > 0) { rechazar_evento($pdo, $id_e); $mensaje_admin = 'Evento rechazado correctamente.'; }
            } else { $error_admin = 'Sesión de formulario inválida.'; }
            break;

        case 'eliminar_evento':
            if (!empty($csrf_session) && hash_equals($csrf_session, $token_post)) {
                $id_e = (int)($_POST['id_evento'] ?? 0);
                if ($id_e > 0) { eliminar_evento($pdo, $id_e, null, true); $mensaje_admin = 'Evento eliminado correctamente.'; }
            } else { $error_admin = 'Sesión de formulario inválida.'; }
            break;
    }
}

// Carga de datos del panel

$stats               = obtener_stats_plataforma($pdo);
$todos_usuarios      = obtener_todos_usuarios($pdo, $uid);
$todos_cursos        = obtener_todos_cursos($pdo);
$todas_publicaciones = obtener_todas_publicaciones($pdo);
$solicitudes_docente = obtener_solicitudes_docente_pendientes($pdo);
$noticias_moderacion = obtener_noticias($pdo, 30, 'Pendiente', null, true);
$eventos_pendientes  = obtener_eventos($pdo, 5, 'Pendiente', null, true);
$total_eventos_pend  = $stats['eventos_pendientes'];

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

<main class="admin-panel">

  <?php if ($mensaje_admin !== ''): ?>
    <div class="alert alert-success motion-entry" role="alert">
      <?= htmlspecialchars($mensaje_admin) ?>
    </div>
  <?php endif; ?>

  <?php if ($error_admin !== ''): ?>
    <div class="alert alert-danger motion-entry" role="alert">
      <?= htmlspecialchars($error_admin) ?>
    </div>
  <?php endif; ?>

  <!-- CABECERA -->
  <header class="admin-panel-header provider-dashboard__header">
    <p class="admin-panel-kicker">Plataforma educativa</p>

    <h1>Panel de administración y control</h1>

    <p>
      Espacio centralizado para supervisar usuarios, gestionar solicitudes docentes,
      revisar publicaciones, matricular estudiantes y moderar la actividad global de Classia.
    </p>

    <p>
      <span>Sesión iniciada como:</span>
      <strong><?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))) ?></strong>
    </p>
  </header>


  <!-- APARTADOS DEL PANEL -->
  <section class="admin-panel-section" aria-labelledby="apartados-admin">

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
          <span class="admin-badge-count">KPIs</span>
        </a>

        <a href="#usuarios" class="admin-panel-item" role="menuitem">
          Usuarios
          <span class="admin-badge-muted"><?= $stats['total_usuarios'] ?></span>
        </a>

        <a href="#matriculacion" class="admin-panel-item" role="menuitem">
          Matriculación a cursos
          <span class="admin-badge-muted"><?= count($todos_cursos) ?></span>
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

        <a href="#noticias" class="admin-panel-item" role="menuitem">
          Noticias
          <?php if ($stats['noticias_pendientes'] > 0): ?>
            <span class="admin-badge-count"><?= $stats['noticias_pendientes'] ?></span>
          <?php endif; ?>
        </a>

        <a href="#eventos" class="admin-panel-item" role="menuitem">
          Eventos
          <?php if ($stats['eventos_pendientes'] > 0): ?>
            <span class="admin-badge-count"><?= $stats['eventos_pendientes'] ?></span>
          <?php endif; ?>
        </a>

        <a href="#servicios" class="admin-panel-item" role="menuitem">
          Servicios en proceso
          <?php if ($stats['servicios_en_proceso'] > 0): ?>
            <span class="admin-badge-muted"><?= $stats['servicios_en_proceso'] ?></span>
          <?php endif; ?>
        </a>

      </div>

    </div>


    <!-- RESUMEN GENERAL / DASHBOARD -->
    <div class="admin-panel-general" id="dashboard">

      <div class="admin-panel-general-header">
        <p class="admin-panel-kicker">Resumen general</p>

        <h3>Estado de la plataforma</h3>

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

    </div>

  </section>


  <!--ANALÍTICAS Y RENDIMIENTO. Métricas de visitas, contrataciones, ingresos y valoraciones-->
  <section id="analiticas" class="admin-analytics-section" aria-labelledby="titulo-analiticas">
    <header class="admin-analytics-header">
      <div class="admin-analytics-title-wrap">
        <p class="admin-panel-kicker">Métricas y Rendimiento</p>
        <h2 id="titulo-analiticas">Panel de analíticas institucionales</h2>
        <p>Estadísticas de visitas, contrataciones, ingresos generados y satisfacción consolidada.</p>
      </div>

      <!-- FILTRO POR PERÍODO Y SERVICIO (REQ-ADM-02) -->
      <form method="GET" action="panel-administrador.php" class="admin-analytics-filter-form" role="search" aria-label="Filtro de analíticas">
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
  <section id="usuarios" class="admin-users-section" aria-labelledby="titulo-usuarios">
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


  <!-- MATRICULACIÓN MANUAL A CURSOS -->
  <section id="matriculacion" class="admin-panel-section admin-enroll-section" aria-labelledby="titulo-matriculacion">
    <header>
      <p class="admin-panel-kicker">Gestión académica</p>
      <h2 id="titulo-matriculacion">Agregar y matricular usuarios a cursos</h2>
      <p>Asigná acceso directo e inmediato a cualquier curso disponible para los estudiantes o usuarios registrados.</p>
    </header>

    <form method="POST" action="panel-administrador.php#matriculacion" class="form-grid u-mt-md admin-enroll-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
      <input type="hidden" name="accion" value="inscribir_usuario_curso">

      <label class="form-grid-full">
        <strong>Seleccionar usuario a matricular:</strong>
        <select name="id_usuario_inscribir" required class="admin-enroll-select">
          <option value="">-- Seleccionar usuario --</option>
          <?php foreach ($todos_usuarios as $u_item): ?>
            <option value="<?= (int)$u_item['id_usuario'] ?>">
              <?= htmlspecialchars(trim($u_item['nombre'] . ' ' . $u_item['apellido'])) ?> (<?= htmlspecialchars($u_item['email']) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <label class="form-grid-full">
        <strong>Seleccionar curso:</strong>
        <select name="id_curso_inscribir" required class="admin-enroll-select">
          <option value="">-- Seleccionar curso --</option>
          <?php foreach ($todos_cursos as $c_item): ?>
            <option value="<?= (int)$c_item['id_publicacion'] ?>">
              <?= htmlspecialchars($c_item['titulo']) ?> (Docente: <?= htmlspecialchars(trim($c_item['docente_nombre'] . ' ' . $c_item['docente_apellido'])) ?>)
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <div class="form-grid-full u-mt-sm">
        <button type="submit" class="btn btn-primary-action">
          ✓ Matricular usuario en el curso
        </button>
      </div>
    </form>
  </section>


  <!-- SOLICITUDES DOCENTES -->
  <section id="solicitudes" class="admin-requests-section" aria-labelledby="titulo-solicitudes">
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
      <?php endforeach; ?>
    <?php endif; ?>
  </section>


  <!-- PUBLICACIONES Y MODERACIÓN DE CURSOS -->
  <section id="publicaciones" class="admin-publications-section" aria-labelledby="titulo-publicaciones">
    <header>
      <h2 id="titulo-publicaciones">Publicaciones y Cursos</h2>
      <p>Revisá, moderá contenidos, cambiá el estado o eliminá publicaciones de la plataforma.</p>
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
                <dd><span class="status-<?= strtolower(htmlspecialchars($pub['estado'])) ?>"><?= htmlspecialchars($pub['estado']) ?></span></dd>
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
                  <li><a href="contenido-curso.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link" title="Moderar módulos, clases y recursos">Moderar temario</a></li>
                  <li><a href="curso.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link" title="Supervisar aula y foro">Ver aula</a></li>
                <?php endif; ?>
                <li><a href="editar-publicacion.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link">Editar</a></li>
                <li><a href="vista-previa-publicacion.php?id=<?= (int) $pub['id_publicacion'] ?>" class="pub-actions-link">Vista previa</a></li>
                <li>
                  <form method="POST" action="panel-administrador.php#publicaciones" class="admin-inline-form">
                    <input type="hidden" name="csrf_token"     value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"         value="cambiar_estado_pub">
                    <input type="hidden" name="id_publicacion" value="<?= (int) $pub['id_publicacion'] ?>">
                    <?php if ($pub['estado'] === 'Activo'): ?>
                      <input type="hidden" name="nuevo_estado" value="Pausado">
                      <button type="submit" class="btn-status btn-status-pause">Pausar</button>
                    <?php else: ?>
                      <input type="hidden" name="nuevo_estado" value="Activo">
                      <button type="submit" class="btn-status btn-status-activate">Activar</button>
                    <?php endif; ?>
                  </form>
                </li>
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


  <!-- NOTICIAS -->
  <section id="noticias" class="admin-news-section" aria-labelledby="titulo-noticias">
    <header>
      <h2 id="titulo-noticias">
        Moderación de noticias
        <?php if ($stats['noticias_pendientes'] > 0): ?>
          <span class="admin-badge-count"><?= $stats['noticias_pendientes'] ?></span>
        <?php endif; ?>
      </h2>
      <p>Aprobá, rechazá o eliminá noticias propuestas por docentes y la institución.</p>
      <a href="noticias.php" class="btn btn-secondary-action">Ver todas las noticias</a>
    </header>

    <?php if (empty($noticias_moderacion)): ?>
      <br>
      <p class="muted">No hay noticias pendientes de moderación.</p>
    <?php else: ?>
      <?php foreach ($noticias_moderacion as $noticia): ?>
        <article>
          <header>
            <h3><?= htmlspecialchars($noticia['titulo']) ?></h3>
            <p>
              Estado: <strong><?= htmlspecialchars($noticia['estado']) ?></strong>
              <?php if (!empty($noticia['categoria'])): ?> · <?= htmlspecialchars($noticia['categoria']) ?><?php endif; ?>
            </p>
          </header>
          <?php if (!empty($noticia['subtitulo'])): ?>
            <p><?= htmlspecialchars(mb_strimwidth($noticia['subtitulo'], 0, 200, '…')) ?></p>
          <?php endif; ?>
          <dl>
            <div><dt>Autor</dt><dd><?= htmlspecialchars($noticia['autor'] ?? '—') ?></dd></div>
            <div><dt>Publicación</dt><dd><?= !empty($noticia['fecha_publicacion']) ? date('d/m/Y', strtotime($noticia['fecha_publicacion'])) : '—' ?></dd></div>
          </dl>

          <nav aria-label="Acciones de la noticia <?= htmlspecialchars($noticia['titulo']) ?>">
            <ul class="pub-actions">
              <li><a href="noticia-detalle.php?id=<?= (int) $noticia['id_noticia'] ?>" class="pub-actions-link">Ver detalle</a></li>
              <li>
                <form method="POST" action="panel-administrador.php#noticias" class="admin-inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="accion"     value="aprobar_noticia">
                  <input type="hidden" name="id_noticia" value="<?= (int) $noticia['id_noticia'] ?>">
                  <button type="submit" class="btn-status btn-status-activate">Aprobar</button>
                </form>
              </li>
              <li>
                <form method="POST" action="panel-administrador.php#noticias" class="admin-inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="accion"     value="rechazar_noticia">
                  <input type="hidden" name="id_noticia" value="<?= (int) $noticia['id_noticia'] ?>">
                  <button type="submit" class="btn-status btn-status-pause">Rechazar</button>
                </form>
              </li>
              <li>
                <form method="POST" action="panel-administrador.php#noticias" class="admin-inline-form">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                  <input type="hidden" name="accion"     value="eliminar_noticia">
                  <input type="hidden" name="id_noticia" value="<?= (int) $noticia['id_noticia'] ?>">
                  <button type="submit" class="btn-status btn-status-delete"
                    onclick="return confirm('¿Eliminar definitivamente esta noticia?')">Eliminar</button>
                </form>
              </li>
            </ul>
          </nav>
        </article>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>


  <!-- EVENTOS -->
  <section id="eventos" class="admin-events-section" aria-labelledby="titulo-eventos">
    <header>
      <h2 id="titulo-eventos">
        Eventos
        <?php if ($total_eventos_pend > 0): ?>
          <span class="admin-badge-count"><?= $total_eventos_pend ?></span>
        <?php endif; ?>
      </h2>
      <p>Administrá, aprobá o eliminá los eventos propuestos por docentes e institución.</p>
    </header>

    <div class="admin-events-actions">
      <a href="eventos.php" class="btn">Ver todos los eventos</a>
      <br><br>
      <?php if ($total_eventos_pend > 0): ?>
        <span class="admin-alert-pill">
          <?= $total_eventos_pend ?> evento<?= $total_eventos_pend !== 1 ? 's' : '' ?> pendiente<?= $total_eventos_pend !== 1 ? 's' : '' ?> de aprobación
        </span>
      <?php else: ?>
        <span class="muted">No hay eventos pendientes de moderación.</span>
      <?php endif; ?>
    </div>

    <?php if (!empty($eventos_pendientes)): ?>
      <div class="admin-eventos-lista">
        <?php foreach ($eventos_pendientes as $ev): ?>
          <article>
            <header>
              <h3><?= htmlspecialchars($ev['titulo']) ?></h3>
              <p>
                <strong><?= htmlspecialchars($ev['tipo'] ?? '') ?></strong>
                <?php if (!empty($ev['modalidad'])): ?> · <?= htmlspecialchars($ev['modalidad']) ?><?php endif; ?>
                <?php if (!empty($ev['fecha_evento'])): ?> · <?= date('d/m/Y H:i', strtotime($ev['fecha_evento'])) ?><?php endif; ?>
              </p>
            </header>
            <?php if (!empty($ev['descripcion'])): ?>
              <p><?= htmlspecialchars(mb_strimwidth($ev['descripcion'], 0, 200, '…')) ?></p>
            <?php endif; ?>

            <nav aria-label="Acciones del evento <?= htmlspecialchars($ev['titulo']) ?>">
              <ul class="pub-actions">
                <li><a href="evento-detalle.php?id=<?= (int) $ev['id_evento'] ?>" class="pub-actions-link">Ver detalle</a></li>
                <li>
                  <form method="POST" action="panel-administrador.php#eventos" class="admin-inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"    value="aprobar_evento">
                    <input type="hidden" name="id_evento" value="<?= (int) $ev['id_evento'] ?>">
                    <button type="submit" class="btn-status btn-status-activate">Aprobar</button>
                  </form>
                </li>
                <li>
                  <form method="POST" action="panel-administrador.php#eventos" class="admin-inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"    value="rechazar_evento">
                    <input type="hidden" name="id_evento" value="<?= (int) $ev['id_evento'] ?>">
                    <button type="submit" class="btn-status btn-status-pause">Rechazar</button>
                  </form>
                </li>
                <li>
                  <form method="POST" action="panel-administrador.php#eventos" class="admin-inline-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="accion"    value="eliminar_evento">
                    <input type="hidden" name="id_evento" value="<?= (int) $ev['id_evento'] ?>">
                    <button type="submit" class="btn-status btn-status-delete"
                      onclick="return confirm('¿Eliminar definitivamente este evento?')">Eliminar</button>
                  </form>
                </li>
              </ul>
            </nav>
          </article>
        <?php endforeach; ?>

        <?php if ($total_eventos_pend > count($eventos_pendientes)): ?>
          <p class="admin-eventos-ver-mas">
            <a href="eventos.php" class="btn btn-secondary-action">
              Ver <?= $total_eventos_pend - count($eventos_pendientes) ?> evento<?= ($total_eventos_pend - count($eventos_pendientes)) !== 1 ? 's' : '' ?> más pendientes
            </a>
          </p>
        <?php endif; ?>
      </div>
    <?php endif; ?>


    <!-- Sección servicios enlazada desde el dropdown -->
    <div id="servicios" class="admin-events-actions admin-servicios-section">
      <h2 class="admin-servicios-title">Servicios en proceso</h2>
      <a href="solicitudes-servicios.php" class="btn">Ver solicitudes de servicios</a>
      <br><br>
      <?php if ($stats['servicios_en_proceso'] > 0): ?>
        <span class="admin-alert-pill">
          <?= $stats['servicios_en_proceso'] ?> servicio<?= $stats['servicios_en_proceso'] !== 1 ? 's' : '' ?> en proceso
        </span>
      <?php else: ?>
        <span class="muted">No hay servicios en proceso actualmente.</span>
      <?php endif; ?>
    </div>
  </section>

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
<script src="<?= $jsPrefix ?>/js/admin.js" defer></script>
<script src="<?= $jsPrefix ?>/js/admin-charts.js" defer></script>

<?php include '../includes/footer.php'; ?>
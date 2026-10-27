<?php

/**
 * Responsabilidad: Detalle informativo, fecha, modalidad y agenda de un evento.
 *
 * Funcionalidades:
 * - Valida la existencia del evento y controla permisos de visualización.
 * - Procesa acciones de moderación (aprobar, rechazar, eliminar) con protección CSRF.
 * - Calcula y formatea fechas, cupos, enlaces a Google Calendar y opciones para compartir.
 * - Renderiza la vista con diseño accesible, alertas dinámicas y panel de administración contextual.
 */

// Dependencias de autenticación, control de roles, conexión a base de datos y lógica de eventos
require_once __DIR__ . '/../php/auth/sesion.php';
require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/eventos/gestionar_eventos.php';

// Inicialización de la sesión del usuario
iniciar_sesion();

// Validación del identificador numérico de evento recibido por parámetro GET
$id_evento = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_evento || $id_evento <= 0) {
    header('Location: eventos.php');
    exit;
}

// Obtención de la información del evento desde la base de datos
$evento = obtener_evento_por_id($pdo, $id_evento);
if (!$evento) {
    header('Location: eventos.php');
    exit;
}

// Identificación del usuario en sesión y verificación de privilegios
$usuario = usuario_actual();
$esAdmin = es_admin();
$esDocente = es_docente();
$idUsuarioActual = $usuario ? (int)$usuario['id_usuario'] : 0;

// Regla de acceso: si el evento está en revisión (Pendiente), solo administradores o su docente autor pueden verlo
if ($evento['estado'] === 'Pendiente' && !$esAdmin && ($evento['id_usuario'] === null || (int)$evento['id_usuario'] !== $idUsuarioActual)) {
    header('Location: eventos.php');
    exit;
}

// Variables para mensajes de retroalimentación al usuario
$msg = '';
$tipo_msg = 'info';

// Procesamiento de acciones de moderación (Aprobar, Rechazar, Eliminar) mediante método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $accion = $_POST['accion'] ?? '';
        if ($esAdmin && $accion === 'aprobar_evento') {
            aprobar_evento($pdo, $id_evento);
            $evento['estado'] = 'Abierto';
            $msg = 'El evento fue aprobado y ya se encuentra visible públicamente.';
            $tipo_msg = 'success';
        } elseif ($esAdmin && $accion === 'rechazar_evento') {
            rechazar_evento($pdo, $id_evento);
            $evento['estado'] = 'Rechazado';
            $msg = 'El evento fue marcado como rechazado.';
            $tipo_msg = 'warning';
        } elseif (($esAdmin || ($esDocente && (int)$evento['id_usuario'] === $idUsuarioActual)) && $accion === 'eliminar_evento') {
            eliminar_evento($pdo, $id_evento, $idUsuarioActual, $esAdmin);
            header('Location: eventos.php?msg=eliminado');
            exit;
        }
    }
}

// Construcción de la URL canónica actual para compartir
$urlActual = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

// Normalización de la ruta de la imagen con fallback a imagen por defecto
$imgSrc = htmlspecialchars($evento['imagen'] ?: 'assets/images/event_webinar.jpg');
if (strpos($imgSrc, 'http') !== 0 && strpos($imgSrc, '..') !== 0) {
    $imgSrc = '../' . ltrim($imgSrc, '/');
}

// Formateo legible de la fecha y hora del evento
$fechaObj = new DateTime($evento['fecha_evento']);
$fechaFormateada = $fechaObj->format('d/m/Y \a \l\a\s H:i') . ' hs';

// Cálculo de fechas de inicio y finalización (+2 horas estimadas) en formato ISO para Google Calendar
$fechaCalInicio = $fechaObj->format('Ymd\THis');
$fechaFinObj = clone $fechaObj;
$fechaFinObj->modify('+2 hours');
$fechaCalFin = $fechaFinObj->format('Ymd\THis');

// Enlaces codificados para compartir en redes sociales y agregar a calendario
$shareText = urlencode('¡Te invito al evento "' . $evento['titulo'] . '" en Classia! ' . $fechaFormateada);
$shareUrl = urlencode($urlActual);
$gCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE&text=" . urlencode($evento['titulo']) . "&dates={$fechaCalInicio}/{$fechaCalFin}&details=" . urlencode($evento['descripcion']) . "&location=" . urlencode($evento['ubicacion_enlace'] ?? '');

// Configuración de metadatos de cabecera e inclusión del encabezado común
$title       = htmlspecialchars($evento['titulo']) . ' — Eventos Classia';
$description = htmlspecialchars(substr(strip_tags($evento['descripcion']), 0, 150));
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'eventos';
include '../includes/header.php';
?>

  <main id="main-content" class="events-page-container motion-entry">
    <!-- Migas de pan para navegación contextual -->
    <nav aria-label="Ruta de navegación" class="breadcrumb u-mb-4">
      <ol class="breadcrumb__list">
        <li><a href="../index.php">Inicio</a> /</li>
        <li><a href="eventos.php">Eventos</a> /</li>
        <li aria-current="page" class="breadcrumb__current"><?= htmlspecialchars($evento['titulo']) ?></li>
      </ol>
    </nav>

    <!-- Alertas de estado y resultado de acciones del usuario -->
    <?php if ($msg !== ''): ?>
      <div class="alert alert-<?= $tipo_msg ?> u-mb-4">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <!-- Aviso visible si el evento se encuentra en estado de revisión -->
    <?php if ($evento['estado'] === 'Pendiente'): ?>
      <div class="alert alert-warning u-mb-4">
        <strong>Evento en revisión:</strong> Este evento está pendiente de aprobación por el equipo de administración antes de mostrarse en la agenda pública.
      </div>
    <?php endif; ?>

    <article class="news-detail-card">
      <header class="news-detail-header">
        <div class="news-detail-meta">
          <span class="news-detail-tag"><?= htmlspecialchars($evento['tipo']) ?></span>
          <span class="event-badge--mode"><?= htmlspecialchars($evento['modalidad']) ?></span>
          <?php if ($evento['estado'] === 'Pendiente'): ?>
            <span class="badge badge-warning">Pendiente de Aprobación</span>
          <?php elseif ($evento['estado'] === 'Rechazado'): ?>
            <span class="badge badge-danger">Rechazado</span>
          <?php endif; ?>
        </div>
        <h1 class="news-detail-title"><?= htmlspecialchars($evento['titulo']) ?></h1>
      </header>

      <div class="news-detail-media">
        <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($evento['titulo']) ?>" />
      </div>

      <!-- Ficha de Datos Clave del Evento -->
      <section class="service-req-summary u-mb-6">
        <div>
          <div class="u-text-xs u-text-muted u-mb-1">Fecha y Horario:</div>
          <strong class="u-text-md u-text-brand-primary">📅 <?= $fechaFormateada ?></strong>
        </div>

        <div>
          <div class="u-text-xs u-text-muted u-mb-1">Modalidad / Ubicación:</div>
          <?php if (!empty($evento['ubicacion_enlace']) && filter_var($evento['ubicacion_enlace'], FILTER_VALIDATE_URL)): ?>
            <a href="<?= htmlspecialchars($evento['ubicacion_enlace']) ?>" target="_blank" rel="noopener noreferrer" class="link u-font-bold">
              🔗 Ingresar al enlace del evento →
            </a>
          <?php elseif (!empty($evento['ubicacion_enlace'])): ?>
            <strong class="u-text-sm">📍 <?= htmlspecialchars($evento['ubicacion_enlace']) ?></strong>
          <?php else: ?>
            <span class="u-text-sm u-text-muted"><?= htmlspecialchars($evento['modalidad']) ?></span>
          <?php endif; ?>
        </div>

        <?php if ((int)$evento['cupos'] > 0): ?>
          <div>
            <div class="u-text-xs u-text-muted u-mb-1">Cupos disponibles:</div>
            <strong class="u-text-sm u-text-success">👥 <?= (int)$evento['cupos'] ?> lugares</strong>
          </div>
        <?php endif; ?>
      </section>

      <!-- Descripción Detallada -->
      <div class="news-detail-body">
        <h2 class="u-text-lg u-font-bold u-mb-3">Acerca de este evento</h2>
        <p><?= nl2br(htmlspecialchars($evento['descripcion'])) ?></p>
      </div>

      <!-- Sección de Invitación y Compartir -->
      <section class="news-detail-footer">
        <div>
          <strong class="u-block u-text-sm u-mb-2">📣 Invitar amigos y compartir:</strong>
          <div class="u-flex u-gap-xs" style="flex-wrap: wrap;">
            <a href="https://api.whatsapp.com/send?text=<?= $shareText ?>%20<?= $shareUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-whatsapp">
              WhatsApp
            </a>
            <a href="https://twitter.com/intent/tweet?text=<?= $shareText ?>&url=<?= $shareUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-twitter">
              Twitter / X
            </a>
            <a href="<?= $gCalUrl ?>" target="_blank" rel="noopener noreferrer" class="btn btn-sm btn-secondary">
             Añadir a Google Calendar
            </a>
            <button type="button" class="btn btn-sm btn-secondary" onclick="copiarEnlaceEvento('<?= htmlspecialchars($urlActual) ?>', this)">
              🔗 Copiar Enlace
            </button>
          </div>
        </div>

        <div>
          <a href="eventos.php" class="btn btn-secondary">← Ver todos los eventos</a>
        </div>
      </section>

      <!-- Panel de Moderación para Administradores y Docente Autor -->
      <?php if ($esAdmin || ($esDocente && (int)$evento['id_usuario'] === $idUsuarioActual)): ?>
        <footer class="news-card__admin u-mt-6">
          <div class="u-flex u-gap-xs" style="align-items: center; justify-content: space-between; flex-wrap: wrap; width: 100%;">
            <div class="u-text-xs u-text-muted">
              Estado actual: <strong><?= htmlspecialchars($evento['estado']) ?></strong>
            </div>
            <div class="u-flex u-gap-xs">
              <?php if ($esAdmin && $evento['estado'] === 'Pendiente'): ?>
                <form action="evento-detalle.php?id=<?= $id_evento ?>" method="post" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="accion" value="aprobar_evento">
                  <button type="submit" class="btn btn-sm btn-primary">✓ Aprobar y Publicar</button>
                </form>
                <form action="evento-detalle.php?id=<?= $id_evento ?>" method="post" style="display:inline;">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                  <input type="hidden" name="accion" value="rechazar_evento">
                  <button type="submit" class="btn btn-sm btn-secondary">✗ Rechazar</button>
                </form>
              <?php endif; ?>

              <form action="evento-detalle.php?id=<?= $id_evento ?>" method="post" style="display:inline;" onsubmit="return confirm('¿Confirmas que deseas eliminar este evento?');">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                <input type="hidden" name="accion" value="eliminar_evento">
                <button type="submit" class="btn btn-sm btn-danger-outline">Eliminar Evento</button>
              </form>
            </div>
          </div>
        </footer>
      <?php endif; ?>
    </article>
  </main>

<?php include '../includes/footer.php'; ?>


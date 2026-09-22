<?php
require_once __DIR__ . '/../php/auth/sesion.php';
require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/eventos/gestionar_eventos.php';

iniciar_sesion();
$usuario = usuario_actual();
$esAdmin = es_admin();
$esDocente = es_docente();
$puedeCrear = $esAdmin || $esDocente;
$idUsuarioActual = $usuario ? (int)$usuario['id_usuario'] : 0;

$msg = '';
$tipo_msg = 'info';

if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $msg = 'El evento fue eliminado correctamente.';
    $tipo_msg = 'success';
}

// Procesamiento de acciones (Crear, Aprobar, Rechazar, Eliminar)
if ($puedeCrear && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear_evento') {
            $titulo = trim($_POST['titulo'] ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');
            $tipo = trim($_POST['tipo'] ?? 'Webinar');
            $modalidad = trim($_POST['modalidad'] ?? 'Online');
            $fecha_evento = trim($_POST['fecha_evento'] ?? '');
            $ubicacion_enlace = trim($_POST['ubicacion_enlace'] ?? '');
            $cupos = (int)($_POST['cupos'] ?? 0);
            $imagen = '';

            if (!empty($_FILES['imagen_archivo']['name'])) {
                $ext = strtolower(pathinfo($_FILES['imagen_archivo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $nombreImg = 'event_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destino = __DIR__ . '/../assets/images/' . $nombreImg;
                    if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $destino)) {
                        $imagen = 'assets/images/' . $nombreImg;
                    }
                }
            }
            if ($imagen === '' && !empty($_POST['imagen_url'])) {
                $imagen = trim($_POST['imagen_url']);
            }

            if ($titulo !== '' && $fecha_evento !== '') {
                crear_evento($pdo, [
                    'titulo'           => $titulo,
                    'descripcion'      => $descripcion,
                    'tipo'             => $tipo,
                    'modalidad'        => $modalidad,
                    'fecha_evento'     => str_replace('T', ' ', $fecha_evento),
                    'ubicacion_enlace' => $ubicacion_enlace,
                    'cupos'            => $cupos,
                    'imagen'           => $imagen !== '' ? $imagen : 'assets/images/event_webinar.jpg',
                ], $idUsuarioActual, $esAdmin);

                if ($esAdmin) {
                    $msg = 'Evento creado y publicado con éxito.';
                } else {
                    $msg = 'Tu evento ha sido enviado y se encuentra en revisión. Será visible públicamente una vez aprobado por los administradores.';
                }
                $tipo_msg = 'success';
            } else {
                $msg = 'El título y la fecha del evento son obligatorios.';
                $tipo_msg = 'danger';
            }
        } elseif ($esAdmin && $accion === 'aprobar_evento') {
            $id_evento = (int)($_POST['id_evento'] ?? 0);
            if ($id_evento > 0) {
                aprobar_evento($pdo, $id_evento);
                $msg = 'Evento aprobado y publicado con éxito.';
                $tipo_msg = 'success';
            }
        } elseif ($esAdmin && $accion === 'rechazar_evento') {
            $id_evento = (int)($_POST['id_evento'] ?? 0);
            if ($id_evento > 0) {
                rechazar_evento($pdo, $id_evento);
                $msg = 'Evento rechazado.';
                $tipo_msg = 'warning';
            }
        } elseif ($accion === 'eliminar_evento') {
            $id_evento = (int)($_POST['id_evento'] ?? 0);
            if ($id_evento > 0) {
                eliminar_evento($pdo, $id_evento, $idUsuarioActual, $esAdmin);
                $msg = 'Evento eliminado correctamente.';
                $tipo_msg = 'success';
            }
        }
    }
}

// Obtener eventos: si es admin ve todos, si es docente ve los públicos y los suyos propios, si es visitante ve solo los abiertos
$eventos = obtener_eventos($pdo, 0, '', $idUsuarioActual, $esAdmin);

$title       = 'Próximos Eventos — Classia';
$description = 'Participa en webinars, talleres y simposios de tecnología, robótica, impresión 3D y ciberseguridad.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'eventos';
include '../includes/header.php';
?>

  <main class="events-page-container motion-entry">
    <header class="news-page-header">
      <div class="news-page-header__inner">
        <div>
          <span class="news-eyebrow">Agenda Académica &amp; Profesional</span>
          <h1 class="news-page-title">Próximos Eventos</h1>
          <p class="news-page-desc">
            Descubrí charlas, webinars en vivo, simposios y jornadas prácticas organizadas por AniTech y nuestra comunidad docente.
          </p>
        </div>
        <?php if ($puedeCrear): ?>
          <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-crear-evento').showModal();">
            + Proponer Nuevo Evento
          </button>
        <?php endif; ?>
      </div>
    </header>

    <?php if ($msg !== ''): ?>
      <div class="alert alert-<?= $tipo_msg ?> u-mb-4">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($eventos)): ?>
      <div class="news-empty-state">
        <p>No hay eventos disponibles en este momento. ¡Volvé a consultar pronto!</p>
      </div>
    <?php else: ?>
      <div class="events-grid">
        <?php foreach ($eventos as $ev): ?>
          <?php 
            $fechaTimestamp = strtotime($ev['fecha_evento']);
            $fechaLegible = date('d/m/Y \a \l\a\s H:i', $fechaTimestamp) . ' hs';
            $imgSrc = htmlspecialchars($ev['imagen'] ?: 'assets/images/event_webinar.jpg');
            if (strpos($imgSrc, 'http') !== 0 && strpos($imgSrc, '..') !== 0) {
                $imgSrc = '../' . ltrim($imgSrc, '/');
            }
          ?>
          <article class="event-card">
            <div class="event-card__media">
              <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($ev['titulo']) ?>" class="event-card__img" loading="lazy" />
              <div class="event-badges-wrapper">
                <span class="event-badge--primary"><?= htmlspecialchars($ev['tipo']) ?></span>
                <span class="event-badge--mode"><?= htmlspecialchars($ev['modalidad']) ?></span>
                <?php if ($ev['estado'] === 'Pendiente'): ?>
                  <span class="badge badge-warning">En Revisión</span>
                <?php elseif ($ev['estado'] === 'Rechazado'): ?>
                  <span class="badge badge-danger">Rechazado</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="event-card__body">
              <div class="event-card__date-pill">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <?= $fechaLegible ?>
              </div>

              <h2 class="event-card__title"><?= htmlspecialchars($ev['titulo']) ?></h2>
              <p class="event-card__desc"><?= htmlspecialchars(substr($ev['descripcion'], 0, 120) . (strlen($ev['descripcion']) > 120 ? '...' : '')) ?></p>

              <?php if (!empty($ev['ubicacion_enlace'])): ?>
                <div class="event-card__location">
                  📍 <?= htmlspecialchars(substr($ev['ubicacion_enlace'], 0, 45) . (strlen($ev['ubicacion_enlace']) > 45 ? '...' : '')) ?>
                </div>
              <?php endif; ?>

              <div class="event-card__footer">
                <?php if ((int)$ev['cupos'] > 0): ?>
                  <span class="event-card__spots">👥 <?= (int)$ev['cupos'] ?> cupos</span>
                <?php else: ?>
                  <span class="u-text-xs u-text-muted">Acceso libre</span>
                <?php endif; ?>

                <a href="evento-detalle.php?id=<?= (int)$ev['id_evento'] ?>" class="btn btn-sm btn-primary">
                  Ver evento →
                </a>
              </div>

              <?php if ($esAdmin || ($esDocente && (int)($ev['id_usuario'] ?? 0) === $idUsuarioActual)): ?>
                <div class="event-card__admin">
                  <div class="u-flex u-gap-xs" style="justify-content: flex-end; align-items: center; flex-wrap: wrap;">
                    <?php if ($esAdmin && $ev['estado'] === 'Pendiente'): ?>
                      <form action="eventos.php" method="post" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="accion" value="aprobar_evento">
                        <input type="hidden" name="id_evento" value="<?= (int)$ev['id_evento'] ?>">
                        <button type="submit" class="btn btn-sm btn-primary">✓ Aprobar</button>
                      </form>
                      <form action="eventos.php" method="post" style="display:inline;">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="accion" value="rechazar_evento">
                        <input type="hidden" name="id_evento" value="<?= (int)$ev['id_evento'] ?>">
                        <button type="submit" class="btn btn-sm btn-secondary">✗ Rechazar</button>
                      </form>
                    <?php endif; ?>

                    <form action="eventos.php" method="post" style="display:inline;" onsubmit="return confirm('¿Deseas eliminar este evento?');">
                      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                      <input type="hidden" name="accion" value="eliminar_evento">
                      <input type="hidden" name="id_evento" value="<?= (int)$ev['id_evento'] ?>">
                      <button type="submit" class="btn btn-sm btn-danger-outline">Eliminar</button>
                    </form>
                  </div>
                </div>
              <?php endif; ?>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Modal para Crear/Proponer Evento -->
  <?php if ($puedeCrear): ?>
    <dialog id="modal-crear-evento" class="modal">
      <div class="modal__card" style="max-width: 600px;">
        <header class="modal__header">
          <h2 class="u-text-lg u-font-bold"><?= $esAdmin ? 'Publicar Nuevo Evento' : 'Proponer Nuevo Evento' ?></h2>
          <button type="button" class="btn-close" onclick="document.getElementById('modal-crear-evento').close();" aria-label="Cerrar modal">&times;</button>
        </header>

        <form action="eventos.php" method="post" enctype="multipart/form-data" class="service-req-stack u-mt-4">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="accion" value="crear_evento">

          <div>
            <label for="titulo" class="service-req-label">Título del evento *</label>
            <input type="text" id="titulo" name="titulo" class="service-req-input" required placeholder="Ej: Simposio de Robótica y Educación Activa" />
          </div>

          <div class="service-req-grid--2col">
            <div>
              <label for="tipo" class="service-req-label">Tipo de evento</label>
              <select id="tipo" name="tipo" class="service-req-select">
                <option value="Webinar">Webinar</option>
                <option value="Taller">Taller Práctico</option>
                <option value="Conferencia">Conferencia / Charla</option>
                <option value="Simposio">Simposio</option>
                <option value="Jornada">Jornada Académica</option>
              </select>
            </div>

            <div>
              <label for="modalidad" class="service-req-label">Modalidad</label>
              <select id="modalidad" name="modalidad" class="service-req-select">
                <option value="Online">Online / Virtual</option>
                <option value="Presencial">Presencial</option>
                <option value="Híbrido">Híbrido</option>
              </select>
            </div>
          </div>

          <div class="service-req-grid--2col">
            <div>
              <label for="fecha_evento" class="service-req-label">Fecha y hora *</label>
              <input type="datetime-local" id="fecha_evento" name="fecha_evento" class="service-req-input" required />
            </div>

            <div>
              <label for="cupos" class="service-req-label">Cupos (0 para ilimitados)</label>
              <input type="number" id="cupos" name="cupos" class="service-req-input" value="0" min="0" />
            </div>
          </div>

          <div>
            <label for="ubicacion_enlace" class="service-req-label">Ubicación física o Enlace de reunión</label>
            <input type="text" id="ubicacion_enlace" name="ubicacion_enlace" class="service-req-input" placeholder="Ej: https://meet.google.com/xyz o Laboratorio Central" />
          </div>

          <div>
            <label for="descripcion" class="service-req-label">Descripción detallada *</label>
            <textarea id="descripcion" name="descripcion" class="service-req-textarea" rows="4" required placeholder="Explicá el objetivo del evento, disertantes y temario..."></textarea>
          </div>

          <div>
            <label for="imagen_archivo" class="service-req-label">Imagen o Flyer del evento</label>
            <input type="file" id="imagen_archivo" name="imagen_archivo" class="service-req-file" accept="image/*" />
          </div>

          <footer class="service-req-actions u-mt-4">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-crear-evento').close();">Cancelar</button>
            <button type="submit" class="btn btn-primary"><?= $esAdmin ? 'Publicar Evento' : 'Enviar a Revisión' ?></button>
          </footer>
        </form>
      </div>
    </dialog>
  <?php endif; ?>

<?php include '../includes/footer.php'; ?>

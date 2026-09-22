<?php
require_once __DIR__ . '/../php/auth/sesion.php';
require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/noticias/gestionar_noticias.php';

iniciar_sesion();
$usuario = usuario_actual();
$esAdmin = es_admin();
$esDocente = es_docente();
$puedeCrear = $esAdmin || $esDocente;
$idUsuarioActual = $usuario ? (int)$usuario['id_usuario'] : 0;

$msg = '';
$tipo_msg = 'info';

// Procesamiento de acciones (Crear, Aprobar, Rechazar, Eliminar, Reordenar)
if ($puedeCrear && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $accion = $_POST['accion'] ?? '';

        if ($accion === 'crear_noticia') {
            $titulo = trim($_POST['titulo'] ?? '');
            $subtitulo = trim($_POST['subtitulo'] ?? '');
            $cuerpo = trim($_POST['cuerpo'] ?? '');
            $categoria = trim($_POST['categoria'] ?? 'Institucional');
            $autor = trim($_POST['autor'] ?? '');
            if ($autor === '') {
                $autor = $usuario ? trim($usuario['nombre'] . ' ' . $usuario['apellido']) : 'Equipo AniTech';
            }
            $orden = (int)($_POST['orden'] ?? 0);
            $imagen = '';

            // Manejo de carga de imagen opcional
            if (!empty($_FILES['imagen_archivo']['name'])) {
                $ext = strtolower(pathinfo($_FILES['imagen_archivo']['name'], PATHINFO_EXTENSION));
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                    $nombreImg = 'news_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                    $destino = __DIR__ . '/../assets/images/' . $nombreImg;
                    if (move_uploaded_file($_FILES['imagen_archivo']['tmp_name'], $destino)) {
                        $imagen = 'assets/images/' . $nombreImg;
                    }
                }
            }
            if ($imagen === '' && !empty($_POST['imagen_url'])) {
                $imagen = trim($_POST['imagen_url']);
            }

            if ($titulo !== '' && $cuerpo !== '') {
                crear_noticia($pdo, [
                    'titulo'    => $titulo,
                    'subtitulo' => $subtitulo,
                    'cuerpo'    => $cuerpo,
                    'imagen'    => $imagen !== '' ? $imagen : 'assets/images/cybersecurity_lab.jpg',
                    'categoria' => $categoria,
                    'autor'     => $autor,
                    'orden'     => $orden,
                ], $idUsuarioActual, $esAdmin);

                if ($esAdmin) {
                    $msg = 'Noticia publicada con éxito.';
                } else {
                    $msg = 'Tu noticia ha sido enviada y se encuentra en revisión. Será visible públicamente una vez aprobada por los administradores.';
                }
                $tipo_msg = 'success';
            } else {
                $msg = 'El título y el contenido son obligatorios.';
                $tipo_msg = 'danger';
            }
        } elseif ($esAdmin && $accion === 'aprobar_noticia') {
            $id_noticia = (int)($_POST['id_noticia'] ?? 0);
            if ($id_noticia > 0) {
                aprobar_noticia($pdo, $id_noticia);
                $msg = 'Noticia aprobada y publicada con éxito.';
                $tipo_msg = 'success';
            }
        } elseif ($esAdmin && $accion === 'rechazar_noticia') {
            $id_noticia = (int)($_POST['id_noticia'] ?? 0);
            if ($id_noticia > 0) {
                rechazar_noticia($pdo, $id_noticia);
                $msg = 'Noticia rechazada.';
                $tipo_msg = 'warning';
            }
        } elseif ($accion === 'eliminar_noticia') {
            $id_noticia = (int)($_POST['id_noticia'] ?? 0);
            if ($id_noticia > 0) {
                eliminar_noticia($pdo, $id_noticia, $idUsuarioActual, $esAdmin);
                $msg = 'Noticia eliminada correctamente.';
                $tipo_msg = 'success';
            }
        } elseif ($esAdmin && $accion === 'reordenar_noticia') {
            $id_noticia = (int)($_POST['id_noticia'] ?? 0);
            $nuevo_orden = (int)($_POST['nuevo_orden'] ?? 0);
            if ($id_noticia > 0) {
                reordenar_noticia($pdo, $id_noticia, $nuevo_orden);
                $msg = 'Orden actualizado.';
                $tipo_msg = 'success';
            }
        }
    }
}

// Obtener noticias según rol
$noticias = obtener_noticias($pdo, 0, '', $idUsuarioActual, $esAdmin);

$title       = 'Noticias y Novedades Institucionales — Classia';
$description = 'Últimas novedades, anuncios académicos y avances tecnológicos de Classia y AniTech.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'noticias';
include '../includes/header.php';
?>

  <main class="news-page-container motion-entry">
    <header class="news-page-header">
      <div class="news-page-header__inner">
        <div>
          <span class="news-eyebrow">Prensa &amp; Comunidad</span>
          <h1 class="news-page-title">Noticias y Novedades</h1>
          <p class="news-page-desc">
            Seguí el pulso de la plataforma: nuevas funcionalidades, eventos académicos, talleres y proyectos tecnológicos de la comunidad.
          </p>
        </div>
        <?php if ($puedeCrear): ?>
          <button type="button" class="btn btn-primary" onclick="document.getElementById('modal-crear-noticia').showModal();">
            + Proponer Noticia
          </button>
        <?php endif; ?>
      </div>
    </header>

    <?php if ($msg !== ''): ?>
      <div class="alert alert-<?= $tipo_msg ?> u-mb-4">
        <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <?php if (empty($noticias)): ?>
      <div class="news-empty-state">
        <p>No hay noticias publicadas en este momento. ¡Volvé a consultar pronto!</p>
      </div>
    <?php else: ?>
      <div class="news-grid">
        <?php foreach ($noticias as $item): ?>
          <?php 
            $fechaTimestamp = strtotime($item['fecha_publicacion']);
            $fechaLegible = date('d/m/Y', $fechaTimestamp);
            $imgSrc = htmlspecialchars($item['imagen'] ?: 'assets/images/cybersecurity_lab.jpg');
            if (strpos($imgSrc, 'http') !== 0 && strpos($imgSrc, '..') !== 0) {
                $imgSrc = '../' . ltrim($imgSrc, '/');
            }
          ?>
          <article class="news-card">
            <div class="news-card__media">
              <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($item['titulo']) ?>" class="news-card__img" loading="lazy" />
              <div class="news-badges-wrapper">
                <span class="news-tag"><?= htmlspecialchars($item['categoria']) ?></span>
                <?php if ($item['estado'] === 'Pendiente'): ?>
                  <span class="badge badge-warning">En Revisión</span>
                <?php elseif ($item['estado'] === 'Rechazada'): ?>
                  <span class="badge badge-danger">Rechazada</span>
                <?php endif; ?>
              </div>
            </div>

            <div class="news-card__body">
              <div class="news-card__meta">
                <span class="news-card__author">Por <?= htmlspecialchars($item['autor']) ?></span>
                <time datetime="<?= date('Y-m-d', $fechaTimestamp) ?>"><?= $fechaLegible ?></time>
              </div>

              <h2 class="news-card__title">
                <a href="noticia-detalle.php?id=<?= (int)$item['id_noticia'] ?>">
                  <?= htmlspecialchars($item['titulo']) ?>
                </a>
              </h2>

              <p class="news-card__lead">
                <?= htmlspecialchars($item['subtitulo'] ?: substr(strip_tags($item['cuerpo']), 0, 110) . '...') ?>
              </p>

              <div class="news-card__footer">
                <a href="noticia-detalle.php?id=<?= (int)$item['id_noticia'] ?>" class="link u-text-xs u-font-bold">
                  Leer noticia completa →
                </a>

                <?php if ($esAdmin || ($esDocente && (int)($item['id_usuario'] ?? 0) === $idUsuarioActual)): ?>
                  <div class="news-card__admin">
                    <div class="u-flex u-gap-xs" style="align-items: center; justify-content: flex-end; flex-wrap: wrap;">
                      <?php if ($esAdmin && $item['estado'] === 'Pendiente'): ?>
                        <form action="noticias.php" method="post" style="display:inline;">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                          <input type="hidden" name="accion" value="aprobar_noticia">
                          <input type="hidden" name="id_noticia" value="<?= (int)$item['id_noticia'] ?>">
                          <button type="submit" class="btn btn-sm btn-primary">✓ Aprobar</button>
                        </form>
                        <form action="noticias.php" method="post" style="display:inline;">
                          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                          <input type="hidden" name="accion" value="rechazar_noticia">
                          <input type="hidden" name="id_noticia" value="<?= (int)$item['id_noticia'] ?>">
                          <button type="submit" class="btn btn-sm btn-secondary">✗ Rechazar</button>
                        </form>
                      <?php endif; ?>

                      <form action="noticias.php" method="post" style="display:inline;" onsubmit="return confirm('¿Deseas eliminar esta noticia?');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <input type="hidden" name="accion" value="eliminar_noticia">
                        <input type="hidden" name="id_noticia" value="<?= (int)$item['id_noticia'] ?>">
                        <button type="submit" class="btn btn-sm btn-danger-outline">Eliminar</button>
                      </form>
                    </div>
                  </div>
                <?php endif; ?>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </main>

  <!-- Modal para Crear Noticia -->
  <?php if ($puedeCrear): ?>
    <dialog id="modal-crear-noticia" class="modal">
      <div class="modal__card" style="max-width: 600px;">
        <header class="modal__header">
          <h2 class="u-text-lg u-font-bold"><?= $esAdmin ? 'Publicar Nueva Noticia' : 'Proponer Nueva Noticia' ?></h2>
          <button type="button" class="btn-close" onclick="document.getElementById('modal-crear-noticia').close();" aria-label="Cerrar modal">&times;</button>
        </header>

        <form action="noticias.php" method="post" enctype="multipart/form-data" class="service-req-stack u-mt-4">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <input type="hidden" name="accion" value="crear_noticia">

          <div>
            <label for="titulo_noticia" class="service-req-label">Título principal *</label>
            <input type="text" id="titulo_noticia" name="titulo" class="service-req-input" required placeholder="Ej: Nuevo laboratorio de robótica en funcionamiento" />
          </div>

          <div>
            <label for="subtitulo_noticia" class="service-req-label">Bajada / Subtítulo breve</label>
            <input type="text" id="subtitulo_noticia" name="subtitulo" class="service-req-input" placeholder="Resumen introductorio que acompañará al título" />
          </div>

          <div class="service-req-grid--2col">
            <div>
              <label for="categoria_noticia" class="service-req-label">Categoría</label>
              <select id="categoria_noticia" name="categoria" class="service-req-select">
                <option value="Institucional">Institucional</option>
                <option value="Ciberseguridad">Ciberseguridad</option>
                <option value="Académico">Académico</option>
                <option value="Tecnología">Tecnología</option>
                <option value="EdTech">EdTech</option>
                <option value="Robótica">Robótica</option>
              </select>
            </div>

            <div>
              <label for="autor_noticia" class="service-req-label">Autor</label>
              <input type="text" id="autor_noticia" name="autor" class="service-req-input" value="<?= htmlspecialchars($usuario ? $usuario['nombre'] . ' ' . $usuario['apellido'] : 'Equipo AniTech') ?>" />
            </div>
          </div>

          <div>
            <label for="cuerpo_noticia" class="service-req-label">Contenido de la noticia *</label>
            <textarea id="cuerpo_noticia" name="cuerpo" class="service-req-textarea" rows="6" required placeholder="Escribí aquí el cuerpo completo de la noticia..."></textarea>
          </div>

          <div>
            <label for="imagen_archivo" class="service-req-label">Imagen de portada</label>
            <input type="file" id="imagen_archivo" name="imagen_archivo" class="service-req-file" accept="image/*" />
          </div>

          <footer class="service-req-actions u-mt-4">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modal-crear-noticia').close();">Cancelar</button>
            <button type="submit" class="btn btn-primary"><?= $esAdmin ? 'Publicar Noticia' : 'Enviar a Revisión' ?></button>
          </footer>
        </form>
      </div>
    </dialog>
  <?php endif; ?>

<?php include '../includes/footer.php'; ?>

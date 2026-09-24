<?php

/**
 * Responsabilidad: Lectura completa de noticias y comunicados institucionales.
 */

require_once __DIR__ . '/../php/auth/sesion.php';
require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/noticias/gestionar_noticias.php';

iniciar_sesion();

$id_noticia = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id_noticia || $id_noticia <= 0) {
    header('Location: noticias.php');
    exit;
}

$noticia = obtener_noticia_por_id($pdo, $id_noticia);
if (!$noticia) {
    header('Location: noticias.php');
    exit;
}

$urlActual = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$imgSrc = htmlspecialchars($noticia['imagen'] ?: 'assets/images/cybersecurity_lab.jpg');
if (strpos($imgSrc, 'http') !== 0 && strpos($imgSrc, '..') !== 0) {
    $imgSrc = '../' . ltrim($imgSrc, '/');
}

$title       = htmlspecialchars($noticia['titulo']) . ' — Classia';
$description = htmlspecialchars($noticia['subtitulo'] ?: substr(strip_tags($noticia['cuerpo']), 0, 150));
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'noticias';
include '../includes/header.php';
?>

  <main class="news-detail-container motion-entry">
    <div class="u-mb-4">
      <a href="noticias.php" class="btn btn-secondary">← Volver a Noticias</a>
    </div>

    <article class="news-detail-card">
      
      <!-- Encabezado de la noticia -->
      <header class="news-detail-header">
        <div class="news-detail-meta">
          <span class="news-detail-tag">
            <?= htmlspecialchars($noticia['categoria']) ?>
          </span>
          <span>
            Publicado el <?= date('d/m/Y \a \l\a\s H:i', strtotime($noticia['fecha_publicacion'])) ?> hs.
          </span>
          <span>· Por <strong><?= htmlspecialchars($noticia['autor']) ?></strong></span>
        </div>

        <h1 class="news-detail-title">
          <?= htmlspecialchars($noticia['titulo']) ?>
        </h1>

        <?php if (!empty($noticia['subtitulo'])): ?>
          <p class="news-detail-lead">
            <?= htmlspecialchars($noticia['subtitulo']) ?>
          </p>
        <?php endif; ?>
      </header>

      <!-- Imagen principal -->
      <?php if (!empty($noticia['imagen'])): ?>
        <div class="news-detail-media">
          <img src="<?= $imgSrc ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>" />
        </div>
      <?php endif; ?>

      <!-- Cuerpo de la noticia -->
      <div class="news-detail-body">
        <?= nl2br(htmlspecialchars($noticia['cuerpo'])) ?>
      </div>

      <!-- Barra para compartir -->
      <footer class="news-detail-footer">
        <div>
          <strong class="u-text-sm">Compartir esta noticia:</strong>
        </div>
        <div class="u-flex u-gap-2 u-flex-wrap">
          <button type="button" class="btn btn-sm" onclick="copiarAlPortapapeles('<?= $urlActual ?>', this)">
            🔗 Copiar enlace
          </button>
          <a href="https://api.whatsapp.com/send?text=<?= urlencode($noticia['titulo'] . ' - ' . $urlActual) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-whatsapp">
            WhatsApp
          </a>
          <a href="https://twitter.com/intent/tweet?text=<?= urlencode($noticia['titulo']) ?>&url=<?= urlencode($urlActual) ?>" target="_blank" rel="noopener" class="btn btn-sm btn-twitter">
            Twitter / X
          </a>
        </div>
      </footer>
    </article>
  </main>

<?php include '../includes/footer.php'; ?>


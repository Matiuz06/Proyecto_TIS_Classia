<?php
$publicaciones_docente = [];
$resenas = [];
$promedio_calificacion = 0.0;
$total_resenas = 0;
require_once '../php/usuarios/perfil_proveedor.php';

$nombreCompleto = $proveedor ? htmlspecialchars($proveedor['nombre'] . ' ' . ($proveedor['apellido'] ?? '')) : 'Proveedor no encontrado';
$title       = 'Perfil de ' . $nombreCompleto;
$description = 'Perfil profesional, cursos, servicios y reseñas en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';

$activePage  = 'catalogo';
include '../includes/header.php';
?>

  <main class="product-detail-container motion-entry">
    <?php if (!$proveedor): ?>
      <section class="empty-state" aria-labelledby="sin-proveedor">
        <h1 id="sin-proveedor">Proveedor no encontrado</h1>
        <p>El perfil que estás buscando no existe o no se encuentra disponible.</p>
        <p><a href="catalogo.php" class="btn">Volver al catálogo</a></p>
      </section>
    <?php else: ?>
      <!-- Hero del proveedor -->
      <section class="provider-hero-card" aria-labelledby="nombre-proveedor">
        <div class="provider-hero-inner">
          <div class="profile-avatar-wrapper">
            <?php 
              $fotoProveedor = (string) ($proveedor['foto_perfil'] ?? '');
              $fotoSrc = $fotoProveedor !== ''
                ? (preg_match('#^https?://#i', $fotoProveedor) ? $fotoProveedor : $cssPrefix . '/' . ltrim($fotoProveedor, '/'))
                : ($cssPrefix . '/assets/images/default-avatar.svg');
            ?>
            <img src="<?= htmlspecialchars($fotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= $nombreCompleto ?>" class="provider-hero-avatar" />
          </div>

          <div class="profile-hero-text">
            <span class="badge badge-provider">
              <?= htmlspecialchars($proveedor['nombre_rol']) ?>
            </span>
            <h1 id="nombre-proveedor"><?= $nombreCompleto ?></h1>
            <p class="provider-meta">
              Miembro desde <?= date('F Y', strtotime($proveedor['fecha_registro'])) ?>
            </p>
            <div class="provider-stats-row">
              <div>
                <strong><?= $promedio_calificacion > 0 ? $promedio_calificacion . ' / 5' : 'Sin calificar' ?></strong>
                <span class="text-muted">(<?= $total_resenas ?> <?= $total_resenas === 1 ? 'reseña' : 'reseñas' ?>)</span>
              </div>
              <div>
                <strong><?= count($publicaciones_docente) ?></strong>
                <span class="text-muted">trabajos y publicaciones activas</span>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Grid de Cursos y Servicios Publicados -->
      <section aria-labelledby="trabajos-publicados" class="provider-section">
        <h2 id="trabajos-publicados">Cursos y Servicios publicados</h2>
        <?php if (empty($publicaciones_docente)): ?>
          <p class="text-muted">Este proveedor aún no tiene publicaciones activas.</p>
        <?php else: ?>
          <div class="catalog-grid">
            <?php foreach ($publicaciones_docente as $pub): ?>
              <article class="catalog-card">
                <span class="badge <?= $pub['tipo'] === 'Curso' ? 'badge-course' : 'badge-service' ?>">
                  <?= htmlspecialchars($pub['tipo']) ?>
                </span>
                <h3>
                  <?= htmlspecialchars($pub['titulo']) ?>
                </h3>
                <p>
                  <?= htmlspecialchars(mb_strimwidth($pub['descripcion'], 0, 100, '...')) ?>
                </p>
                <div class="catalog-card-footer">
                  <strong>$<?= number_format($pub['precio'], 2, ',', '.') ?></strong>
                  <a href="<?= $pub['tipo'] === 'Curso' ? 'curso.php?id=' . $pub['id_publicacion'] : 'servicio-detalle.php?id=' . $pub['id_publicacion'] ?>" class="btn">
                    Ver <?= strtolower($pub['tipo']) ?>
                  </a>
                </div>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <!-- Reseñas del Proveedor -->
      <section aria-labelledby="opiniones-estudiantes" class="provider-section">
        <h2 id="opiniones-estudiantes">Reseñas y valoraciones de estudiantes</h2>
        <?php if (empty($resenas)): ?>
          <p class="text-muted">Aún no se han publicado reseñas para este docente.</p>
        <?php else: ?>
          <div class="review-list">
            <?php foreach ($resenas as $res): ?>
              <article class="review-card">
                <div class="review-card__header">
                  <strong><?= htmlspecialchars($res['autor_resena_nombre'] . ' ' . $res['autor_resena_apellido']) ?></strong>
                  <span class="review-card__stars">
                    <?= str_repeat('★', (int)$res['puntuacion']) . str_repeat('☆', 5 - (int)$res['puntuacion']) ?>
                    (<?= (int)$res['puntuacion'] ?>/5)
                  </span>
                </div>
                <p class="review-card__date">
                  Sobre: <em><?= htmlspecialchars($res['publicacion_titulo']) ?> (<?= htmlspecialchars($res['publicacion_tipo']) ?>)</em> · <?= date('d/m/Y', strtotime($res['fecha_valoracion'])) ?>
                </p>
                <p class="review-card__body">
                  "<?= htmlspecialchars($res['comentario'] ?? 'Sin comentario escrito.') ?>"
                </p>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <p class="back-link-container">
        <a href="catalogo.php" class="link">← Volver al catálogo</a>
      </p>
    <?php endif; ?>
  </main>

<?php include '../includes/footer.php'; ?>

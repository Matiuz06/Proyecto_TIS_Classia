<?php
require_once '../php/publicaciones/detalle_servicio.php';

$title       = $servicio ? htmlspecialchars($servicio['titulo']) : 'Servicio no encontrado';
$description = $servicio ? htmlspecialchars(mb_strimwidth($servicio['descripcion'], 0, 150, '...')) : 'Detalle del servicio en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';

$activePage  = 'catalogo';
include '../includes/header.php';
$puedeAgregar = esta_autenticado();
?>

  <main class="product-detail-container motion-entry">
    <nav aria-label="Ruta de navegación" class="breadcrumb-nav">
      <ol class="breadcrumb-list">
        <li><a href="../index.php">Inicio</a> /</li>
        <li><a href="catalogo.php?tipo=servicio">Servicios</a> /</li>
        <li aria-current="page">
          <?= htmlspecialchars($servicio ? $servicio['titulo'] : 'Detalle') ?>
        </li>
      </ol>
    </nav>

    <?php if (!$servicio): ?>
      <section class="empty-state" aria-labelledby="sin-servicio">
        <h1 id="sin-servicio">Servicio no encontrado</h1>
        <p>El servicio solicitado no está disponible o ha sido dado de baja.</p>
        <p><a href="catalogo.php?tipo=servicio" class="btn">Explorar otros servicios</a></p>
      </section>
    <?php else: ?>
      <div class="product-detail-layout">
        
        <!-- Columna Izquierda: Detalle principal -->
        <article class="product-main-content">
          <header class="product-header">
            <span class="badge badge-service">
              <?= htmlspecialchars($servicio['nombre_categoria']) ?>
            </span>
            <h1 class="product-title"><?= htmlspecialchars($servicio['titulo']) ?></h1>

            <div class="product-meta-row">
              <div>
                <span>Proveedor: </span>
                <a href="proveedor.php?id=<?= (int)$servicio['autor_id'] ?>" class="provider-link">
                  <?= htmlspecialchars($servicio['autor_nombre'] . ' ' . $servicio['autor_apellido']) ?>
                </a>
              </div>
              <div>
                <a href="#valoraciones" class="rating-link">
                  ⭐ <strong><?= $promedio_calificacion > 0 ? $promedio_calificacion . ' / 5' : 'Sin calificar' ?></strong>
                  (<?= $total_resenas ?> <?= $total_resenas === 1 ? 'valoración' : 'valoraciones' ?>)
                </a>
              </div>
            </div>

            <?php if (!empty($servicio['imagen'])): ?>
              <div class="product-media-wrapper">
                <img src="../<?= htmlspecialchars($servicio['imagen']) ?>" alt="<?= htmlspecialchars($servicio['titulo']) ?>" />
              </div>
            <?php endif; ?>

            <div class="product-body">
              <h3>Descripción del servicio</h3>
              <p><?= nl2br(htmlspecialchars($servicio['descripcion'])) ?></p>
            </div>
          </header>

          <hr class="section-divider" />

          <!-- Reseñas Reales de la BD -->
          <section id="valoraciones" aria-labelledby="titulo-valoraciones">
            <h2 id="titulo-valoraciones">
              Valoraciones de los clientes (<?= $total_resenas ?>)
            </h2>

            <?php if (empty($resenas)): ?>
              <p class="text-muted">Aún no hay valoraciones para este servicio. ¡Sé el primero en contratarlo y opinar!</p>
            <?php else: ?>
              <div class="review-list">
                <?php foreach ($resenas as $res): ?>
                  <article class="review-card">
                    <div class="review-card__header">
                      <strong><?= htmlspecialchars($res['nombre'] . ' ' . ($res['apellido'] ?? '')) ?></strong>
                      <span class="review-card__stars">
                        <?= str_repeat('★', (int)$res['puntuacion']) . str_repeat('☆', 5 - (int)$res['puntuacion']) ?>
                        (<?= (int)$res['puntuacion'] ?>/5)
                      </span>
                    </div>
                    <p class="review-card__date">
                      <?= date('d/m/Y', strtotime($res['fecha_valoracion'])) ?>
                    </p>
                    <p class="review-card__body">
                      "<?= htmlspecialchars($res['comentario'] ?? 'Sin comentario.') ?>"
                    </p>
                  </article>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </section>

          <!-- Servicios Relacionados Reales de la BD -->
          <?php if (!empty($servicios_relacionados)): ?>
            <hr class="section-divider" />
            <section aria-labelledby="servicios-relacionados">
              <h2 id="servicios-relacionados">Servicios relacionados</h2>
              <div class="related-grid">
                <?php foreach ($servicios_relacionados as $rel): ?>
                  <article class="related-card">
                    <span class="badge badge-service badge-sm">
                      <?= htmlspecialchars($rel['nombre_categoria']) ?>
                    </span>
                    <h4>
                      <a href="servicio-detalle.php?id=<?= $rel['id_publicacion'] ?>">
                        <?= htmlspecialchars($rel['titulo']) ?>
                      </a>
                    </h4>
                    <p class="related-author">
                      Por: <a href="proveedor.php?id=<?= (int)$rel['id_usuario'] ?>"><?= htmlspecialchars($rel['autor_nombre'] . ' ' . $rel['autor_apellido']) ?></a>
                    </p>
                    <div class="related-footer">
                      <strong>$<?= number_format($rel['precio'], 2, ',', '.') ?></strong>
                      <a href="servicio-detalle.php?id=<?= $rel['id_publicacion'] ?>" class="btn btn-sm">Ver</a>
                    </div>
                  </article>
                <?php endforeach; ?>
              </div>
            </section>
          <?php endif; ?>
        </article>

        <!-- Columna Derecha: Tarjeta de Contratación -->
        <aside class="product-sidebar-sticky">
          <div class="product-card-pricing">
            <p class="product-card-pricing__label">Precio total del servicio</p>
            <div class="product-card-pricing__amount">
              $<?= number_format($servicio['precio'], 2, ',', '.') ?>
            </div>

            <ul class="product-card-pricing__features">
              <li>✓ Contacto directo con el proveedor</li>
              <li>✓ Seguimiento paso a paso</li>
              <li>✓ Garantía de satisfacción Classia</li>
            </ul>

            <!-- Enviar al carrito-->
            <?php if ($puedeAgregar): ?>
              <form action="carrito.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
                <input type="hidden" name="id_publicacion" value="<?= (int)$servicio['id_publicacion'] ?>" />
                <input type="hidden" name="accion" value="agregar" />
                <button type="submit" class="btn product-card-pricing__btn">
                  Continuar con la contratación
                </button>
              </form>
            <?php else: ?>
              <div class="purchase-auth-actions">
                <a class="btn product-card-pricing__btn" href="login.php">Inicia sesión</a>
                <a href="registro.php">Regístrate</a>
              </div>
            <?php endif; ?>

            <div class="custom-request-box">
              <p>¿Tienes requerimientos especiales?</p>
              <a href="form-solicitar-servicio.php" class="link">Enviar solicitud personalizada</a>
            </div>
          </div>
        </aside>

      </div>
    <?php endif; ?>
  </main>

<?php include '../includes/footer.php'; ?>

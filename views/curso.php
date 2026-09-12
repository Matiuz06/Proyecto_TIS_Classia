<?php
require_once '../php/publicaciones/detalle_curso.php';

$title       = $curso ? htmlspecialchars($curso['titulo']) : 'Curso no encontrado';
$description = $curso ? htmlspecialchars(mb_strimwidth($curso['descripcion'], 0, 150, '...')) : 'Detalle del curso en Classia.';
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
        <li><a href="catalogo.php?tipo=curso">Cursos</a> /</li>
        <li aria-current="page">
          <?= htmlspecialchars($curso ? $curso['titulo'] : 'Detalle') ?>
        </li>
      </ol>
    </nav>

    <?php if (!$curso): ?>
      <section class="empty-state" aria-labelledby="sin-curso">
        <h1 id="sin-curso">Curso no encontrado</h1>
        <p>El curso que buscas no existe o no se encuentra disponible actualmente.</p>
        <p><a href="catalogo.php?tipo=curso" class="btn">Explorar catálogo de cursos</a></p>
      </section>
    <?php else: ?>
      <div class="product-detail-layout">
        
        <!-- Columna Izquierda: Información del Curso -->
        <article class="product-main-content">
          <header class="product-header">
            <span class="badge badge-course">
              <?= htmlspecialchars($curso['nombre_categoria']) ?>
            </span>
            <h1 class="product-title"><?= htmlspecialchars($curso['titulo']) ?></h1>

            <div class="product-meta-row">
              <div>
                <span>Docente: </span>
                <a href="proveedor.php?id=<?= (int)$curso['autor_id'] ?>" class="provider-link">
                  <?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?>
                </a>
              </div>
              <div>
                <a href="#valoraciones-curso" class="rating-link">
                  ⭐ <strong><?= $promedio_calificacion > 0 ? $promedio_calificacion . ' / 5' : 'Sin calificar' ?></strong>
                  (<?= $total_resenas ?> <?= $total_resenas === 1 ? 'valoración' : 'valoraciones' ?>)
                </a>
              </div>
            </div>

            <?php if (!empty($curso['imagen'])): ?>
              <div class="product-media-wrapper">
                <img src="../<?= htmlspecialchars($curso['imagen']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>" />
              </div>
            <?php else: ?>
              <div class="course-video-wrapper">
                <video controls aria-label="Video de vista previa" class="course-demo-video">
                  Tu navegador no admite video HTML5.
                </video>
              </div>
            <?php endif; ?>

            <div class="product-body">
              <h3>Acerca de este curso</h3>
              <p><?= nl2br(htmlspecialchars($curso['descripcion'])) ?></p>
            </div>
          </header>

          <hr class="section-divider" />

          <!-- Temario y Módulos -->
          <section aria-labelledby="temario-curso">
            <h2 id="temario-curso">Contenido del programa</h2>
            <div class="syllabus-container">
              <div class="syllabus-module-header">
                Módulo 1: Fundamentos y Conceptos Clave (3 lecciones · 45 min)
              </div>
              <ul class="syllabus-module-list">
                <li>Introducción y objetivos de aprendizaje</li>
                <li>Configuración del entorno de trabajo</li>
                <li>Primeros pasos y conceptos teóricos esenciales</li>
              </ul>

              <div class="syllabus-module-header syllabus-module-header--middle">
                Módulo 2: Desarrollo Práctico y Casos Reales (5 lecciones · 2h 15m)
              </div>
              <ul class="syllabus-module-list">
                <li>Arquitectura y aplicación de buenas prácticas</li>
                <li>Resolución guiada de ejercicios prácticos</li>
                <li>Taller interactivo paso a paso</li>
              </ul>

              <div class="syllabus-module-header syllabus-module-header--middle">
                Módulo 3: Proyecto Final y Certificación (2 lecciones · 1h)
              </div>
              <ul class="syllabus-module-list">
                <li>Integración integral de los conocimientos</li>
                <li>Evaluación final y entrega de certificado</li>
              </ul>
            </div>
          </section>

          <hr class="section-divider" />

          <!-- Reseñas del Curso -->
          <section id="valoraciones-curso" aria-labelledby="titulo-resenas-curso">
            <h2 id="titulo-resenas-curso">
              Opiniones de estudiantes (<?= $total_resenas ?>)
            </h2>

            <?php if (empty($resenas)): ?>
              <p class="text-muted">Aún no hay reseñas registradas para este curso.</p>
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
        </article>

        <!-- Columna Derecha: Tarjeta de Compra / Inscripción -->
        <aside class="product-sidebar-sticky">
          <div class="product-card-pricing">
            <p class="product-card-pricing__label">Precio del curso</p>
            <div class="product-card-pricing__amount">
              $<?= number_format($curso['precio'], 2, ',', '.') ?>
            </div>

            <ul class="product-card-pricing__features">
              <li>✓ Acceso completo e ilimitado de por vida</li>
              <li>✓ Materiales y recursos descargables</li>
              <li>✓ Certificado digital de finalización</li>
              <li>✓ Asesoría directa con el docente</li>
            </ul>

            <?php if ($comprado): ?>
              <div class="alert alert-success alert-enrolled">
                <strong>¡Ya estás inscripto en este curso!</strong>
              </div>
              <a href="usuario.php" class="btn btn-full">
                Ver en Mi Perfil
              </a>
            <?php else: ?>
              <?php if ($puedeAgregar): ?>
                <form action="carrito.php" method="POST">
                  <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
                  <input type="hidden" name="id_publicacion" value="<?= (int)$curso['id_publicacion'] ?>" />
                  <input type="hidden" name="accion" value="agregar" />
                  <button type="submit" class="btn product-card-pricing__btn">
                    Inscribirme al curso
                  </button>
                </form>
              <?php else: ?>
                <div class="purchase-auth-actions">
                  <a class="btn product-card-pricing__btn" href="login.php">Inicia sesión</a>
                  <a href="registro.php">Regístrate</a>
                </div>
              <?php endif; ?>
            <?php endif; ?>

            <div class="more-courses-box">
              <a href="catalogo.php?tipo=curso" class="link">← Ver más cursos</a>
            </div>
          </div>
        </aside>

      </div>
    <?php endif; ?>
  </main>

<?php include '../includes/footer.php'; ?>

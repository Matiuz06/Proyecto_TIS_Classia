<?php
$title       = 'Classia — Plataforma Educativa y Servicios';
$description = 'Classia conecta aprendizaje, cursos prácticos, servicios y administración en una plataforma educativa moderna.';
$cssPrefix   = '.';
$jsPrefix    = '.';
$bodyClass   = 'home-page';
$activePage  = 'inicio';

require_once 'php/inicio/obtener_datos_home.php';
include 'includes/header.php';
?>

  <main>
    <section class="home-hero-centered motion-entry" aria-labelledby="hero-heading">
      <div class="home-hero-content">
        <h1 id="hero-heading" class="home-hero-title">
          <?= __t('hero_title') ?>
        </h1>
        <p class="home-hero-subtitle">
          <?= __t('hero_subtitle') ?>
        </p>
        <div class="home-hero-actions">
          <a href="views/catalogo.php" class="btn btn-lg">
            <?= __t('hero_btn_explore') ?>
          </a>
          <?php if (!$isAuth): ?>
            <a href="views/registro.php" class="btn btn-ghost btn-lg">
              <?= __t('hero_btn_start') ?>
            </a>
          <?php else: ?>
            <a href="views/usuario.php" class="btn btn-ghost btn-lg">
              <?= __t('hero_btn_account') ?> &rarr;
            </a>
          <?php endif; ?>
        </div>
        <?php if (!$isAuth): ?>
          <p class="home-hero-login-hint">
            <?= __t('hero_login_prompt') ?> <a href="views/login.php" class="link u-font-semibold"><?= __t('nav_iniciar_sesion') ?> &rarr;</a>
          </p>
        <?php endif; ?>
      </div>
    </section>

    <section class="home-section" aria-labelledby="categorias-heading">
      <div class="home-section-header">
        <div>
          <h2 id="categorias-heading"><?= __t('sec_categories') ?></h2>
          <p><?= __t('sec_categories_sub') ?></p>
        </div>
        <a href="views/catalogo.php" class="link u-font-semibold u-text-xs"><?= __t('sec_view_all') ?></a>
      </div>

      <?php if (!empty($categorias_home)): ?>
        <nav class="home-category-bar" aria-label="Categorías destacadas">
          <?php foreach ($categorias_home as $categoria): ?>
            <?php $nombre_categoria = (string)$categoria['nombre_categoria']; ?>
            <a href="views/catalogo.php?categoria=<?= rawurlencode($nombre_categoria) ?>" class="home-category-item">
              <span aria-hidden="true"></span>
              <?= htmlspecialchars(__t_db($nombre_categoria), ENT_QUOTES, 'UTF-8') ?>
            </a>
          <?php endforeach; ?>
        </nav>
      <?php else: ?>
        <nav class="home-category-bar" aria-label="Categorías destacadas">
          <a href="views/catalogo.php" class="home-category-item">
            <span aria-hidden="true"></span>
            <?= __t('sec_view_all') ?>
          </a>
        </nav>
      <?php endif; ?>
      <?php if (false): ?>
      <div class="home-carousel-container">
        <div class="home-carousel-track" id="categories-carousel" tabindex="0" role="region" aria-label="Carrusel de categorías">
          <a href="views/catalogo.php?categoria=Programacion" class="home-category-card">
            <div class="home-category-icon-box" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
            </div>
            <h3 class="home-category-title"><?= __t('cat_programming') ?></h3>
            <p class="home-category-desc"><?= __t('cat_desc_programming') ?></p>
          </a>

          <a href="views/catalogo.php?categoria=Ciberseguridad" class="home-category-card">
            <div class="home-category-icon-box" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
            <h3 class="home-category-title"><?= __t('cat_cybersecurity') ?></h3>
            <p class="home-category-desc"><?= __t('cat_desc_cybersecurity') ?></p>
          </a>

          <a href="views/catalogo.php?categoria=Impresion3D" class="home-category-card">
            <div class="home-category-icon-box" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
            </div>
            <h3 class="home-category-title"><?= __t('cat_3d_prototypes') ?></h3>
            <p class="home-category-desc"><?= __t('cat_desc_3d_prototypes') ?></p>
          </a>

          <a href="views/catalogo.php?categoria=InteligenciaArtificial" class="home-category-card">
            <div class="home-category-icon-box" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="4" width="16" height="16" rx="2" ry="2"></rect><rect x="9" y="9" width="6" height="6"></rect><line x1="9" y1="1" x2="9" y2="4"></line><line x1="15" y1="1" x2="15" y2="4"></line><line x1="9" y1="20" x2="9" y2="23"></line><line x1="15" y1="20" x2="15" y2="23"></line><line x1="20" y1="9" x2="23" y2="9"></line><line x1="20" y1="14" x2="23" y2="14"></line><line x1="1" y1="9" x2="4" y2="9"></line><line x1="1" y1="14" x2="4" y2="14"></line></svg>
            </div>
            <h3 class="home-category-title"><?= __t('cat_ai') ?></h3>
            <p class="home-category-desc"><?= __t('cat_desc_ai') ?></p>
          </a>

          <a href="views/catalogo.php?categoria=Diseno" class="home-category-card">
            <div class="home-category-icon-box" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="13.5" cy="6.5" r=".5"></circle><circle cx="17.5" cy="10.5" r=".5"></circle><circle cx="8.5" cy="7.5" r=".5"></circle><circle cx="6.5" cy="12.5" r=".5"></circle><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125a1.64 1.64 0 0 1 1.668-1.668h1.996c3.051 0 5.563-2.512 5.563-5.563C22 6.5 17.5 2 12 2z"></path></svg>
            </div>
            <h3 class="home-category-title"><?= __t('cat_ux_ui') ?></h3>
            <p class="home-category-desc"><?= __t('cat_desc_ux_ui') ?></p>
          </a>

          <a href="views/catalogo.php?categoria=Negocios" class="home-category-card">
            <div class="home-category-icon-box" aria-hidden="true">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
            </div>
            <h3 class="home-category-title"><?= __t('cat_management_consulting') ?></h3>
            <p class="home-category-desc"><?= __t('cat_desc_management_consulting') ?></p>
          </a>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <section class="home-section" aria-labelledby="recientes-heading">
      <div class="home-section-header">
        <div>
          <h2 id="recientes-heading"><?= __t('sec_recent_courses') ?></h2>
          <p><?= __t('sec_recent_sub') ?></p>
        </div>
        <a href="views/catalogo.php" class="link u-font-semibold u-text-xs"><?= __t('btn_view_catalog') ?> &rarr;</a>
      </div>

      <div class="home-course-grid">
        <?php if (!empty($publicaciones_recientes)): ?>
          <?php 
            $default_covers = [
              'assets/images/cybersecurity_lab.jpg',
              'assets/images/printing3d_lab.jpg',
              'assets/images/tech_conference.jpg'
            ];
            $idx = 0;
          ?>
          <?php foreach ($publicaciones_recientes as $pub): ?>
            <?php 
              $cover_src = !empty($pub['imagen']) ? $pub['imagen'] : ($default_covers[$idx % count($default_covers)]);
              $idx++;
            ?>
            <article class="content-card motion-card content-card--column home-course-card">
              <div>
                <div class="card-media-wrapper">
                  <img src="<?= htmlspecialchars($cover_src) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>" class="card-media-img" loading="lazy" />
                </div>
                <div class="u-flex-between u-mb-md">
                  <span class="badge"><?= htmlspecialchars(__t_db($pub['tipo'])) ?></span>
                  <span class="u-text-xs u-text-muted u-font-semibold"><?= htmlspecialchars(__t_db($pub['nombre_categoria'])) ?></span>
                </div>
                <h3 class="u-mt-0 u-text-lg"><?= htmlspecialchars($pub['titulo']) ?></h3>
                <p class="u-text-base u-text-muted u-line-height-base">
                  <?= htmlspecialchars(mb_strimwidth($pub['descripcion'], 0, 110, '...')) ?>
                </p>
              </div>

              <div class="content-card-footer">
                <div>
                  <span class="u-text-xs u-text-muted display-block"><?= __t('label_instructor') ?></span>
                  <span class="u-text-sm u-font-semibold"><?= htmlspecialchars($pub['docente_nombre'] . ' ' . $pub['docente_apellido']) ?></span>
                </div>
                <div class="u-right">
                  <span class="u-text-lg u-font-extrabold u-text-primary">
                    $<?= number_format((float)$pub['precio'], 2, ',', '.') ?>
                  </span>
                </div>
              </div>

              <div class="home-card-action-row">
                <?php if ($pub['tipo'] === 'Curso'): ?>
                  <a href="views/curso.php?id=<?= (int)$pub['id_publicacion'] ?>" class="btn btn-full btn-card-action">
                    <?= __t('btn_view_details') ?>
                  </a>
                <?php else: ?>
                  <a href="views/servicio-detalle.php?id=<?= (int)$pub['id_publicacion'] ?>" class="btn btn-full btn-card-action">
                    <?= __t('btn_view_details') ?>
                  </a>
                <?php endif; ?>
              </div>
            </article>
          <?php endforeach; ?>
        <?php else: ?>

          <article class="content-card motion-card home-course-card">
            <div class="card-media-wrapper">
              <img src="assets/images/cybersecurity_lab.jpg" alt="Seguridad Web" class="card-media-img" loading="lazy" />
            </div>
            <span class="badge"><?= __t('label_course') ?></span>
            <h3><?= __t('fallback_c1_title') ?></h3>
            <p><?= __t('fallback_c1_desc') ?></p>
            <p class="role-card-action"><a href="views/catalogo.php" class="link"><?= __t('btn_view_catalog') ?> &rarr;</a></p>
          </article>

          <article class="content-card motion-card home-course-card">
            <div class="card-media-wrapper">
              <img src="assets/images/printing3d_lab.jpg" alt="Impresión 3D" class="card-media-img" loading="lazy" />
            </div>
            <span class="badge"><?= __t('label_course') ?></span>
            <h3><?= __t('fallback_c2_title') ?></h3>
            <p><?= __t('fallback_c2_desc') ?></p>
            <p class="role-card-action"><a href="views/catalogo.php" class="link"><?= __t('btn_view_catalog') ?> &rarr;</a></p>
          </article>

          <article class="content-card motion-card home-course-card">
            <div class="card-media-wrapper">
              <img src="assets/images/tech_conference.jpg" alt="Auditoría de Código" class="card-media-img" loading="lazy" />
            </div>
            <span class="badge"><?= __t('label_service') ?></span>
            <h3><?= __t('fallback_c3_title') ?></h3>
            <p><?= __t('fallback_c3_desc') ?></p>
            <p class="role-card-action"><a href="views/catalogo.php" class="link"><?= __t('btn_view_catalog') ?> &rarr;</a></p>
          </article>
        <?php endif; ?>
      </div>
    </section>

    <section class="home-section home-provider-cta" aria-labelledby="proveedores-heading">
      <div>
        <p class="section-title-tag-sm">Docentes y proveedores</p>
        <h2 id="proveedores-heading">Enseña o brinda servicios desde Classia</h2>
        <p>Publicá cursos, mentorías o servicios educativos y tecnológicos en el mismo entorno donde los estudiantes descubren nuevas oportunidades.</p>
      </div>
      <a href="views/solicitar-docente.php" class="btn">Postularme como docente</a>
    </section>

    <section class="home-section" aria-labelledby="noticias-heading">
      <div class="home-section-header">
        <div>
          <h2 id="noticias-heading"><?= __t('sec_news') ?></h2>
          <p><?= __t('sec_news_sub') ?></p>
        </div>
      </div>

      <div class="home-news-grid">
        <article class="home-news-card">
          <div class="card-media-wrapper">
            <img src="assets/images/cybersecurity_lab.jpg" alt="Ciberseguridad y 2FA" class="card-media-img" loading="lazy" />
          </div>
          <div>
            <div class="news-meta">
              <span class="badge badge-brand-soft"><?= __t('news_tag_institutional') ?></span>
              <time datetime="2026-09-10"><?= __t('news_date_1') ?></time>
            </div>
            <h3><?= __t('news_title_1') ?></h3>
            <p><?= __t('news_desc_1') ?></p>
          </div>
          <a href="views/institucional.php" class="link u-text-xs u-font-semibold"><?= __t('news_link_1') ?></a>
        </article>

        <article class="home-news-card">
          <div class="card-media-wrapper">
            <img src="assets/images/hero_education.jpg" alt="Integración OAuth" class="card-media-img" loading="lazy" />
          </div>
          <div>
            <div class="news-meta">
              <span class="badge badge-brand-soft"><?= __t('news_tag_edtech') ?></span>
              <time datetime="2026-09-04"><?= __t('news_date_2') ?></time>
            </div>
            <h3><?= __t('news_title_2') ?></h3>
            <p><?= __t('news_desc_2') ?></p>
          </div>
          <a href="views/login.php" class="link u-text-xs u-font-semibold"><?= __t('news_link_2') ?></a>
        </article>

        <article class="home-news-card">
          <div class="card-media-wrapper">
            <img src="assets/images/tech_conference.jpg" alt="Convocatoria Docente" class="card-media-img" loading="lazy" />
          </div>
          <div>
            <div class="news-meta">
              <span class="badge badge-brand-soft"><?= __t('news_tag_academic') ?></span>
              <time datetime="2026-08-28"><?= __t('news_date_3') ?></time>
            </div>
            <h3><?= __t('news_title_3') ?></h3>
            <p><?= __t('news_desc_3') ?></p>
          </div>
          <a href="views/solicitar-docente.php" class="link u-text-xs u-font-semibold"><?= __t('news_link_3') ?></a>
        </article>
      </div>
    </section>

    <section class="home-section" aria-labelledby="eventos-heading">
      <div class="home-section-header">
        <div>
          <h2 id="eventos-heading"><?= __t('sec_events') ?></h2>
          <p><?= __t('sec_events_sub') ?></p>
        </div>
      </div>

      <div class="home-events-grid">
        <article class="home-event-card">
          <div class="card-media-wrapper">
            <img src="assets/images/event_webinar.jpg" alt="Webinar de Seguridad" class="card-media-img" loading="lazy" />
          </div>
          <div>
            <div class="event-meta">
              <span class="event-tag event-tag--own"><?= __t('event_own') ?></span>
              <span class="u-inline-flex-center u-gap-xs">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <?= __t('event_date_1') ?>
              </span>
            </div>
            <h3><?= __t('event_title_1') ?></h3>
            <p><?= __t('event_desc_1') ?></p>
          </div>
          <div class="event-footer-strip">
            <span class="u-text-xs u-font-bold u-text-success"><?= __t('event_status_1') ?></span>
            <a href="views/contacto.php" class="btn btn-sm"><?= __t('event_btn_1') ?></a>
          </div>
        </article>

        <article class="home-event-card">
          <div class="card-media-wrapper">
            <img src="assets/images/event_symposium.jpg" alt="Simposio EdTech y Fabricación 3D" class="card-media-img" loading="lazy" />
          </div>
          <div>
            <div class="event-meta">
              <span class="event-tag event-tag--sector"><?= __t('event_sector') ?></span>
              <span class="u-inline-flex-center u-gap-xs">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                <?= __t('event_date_2') ?>
              </span>
            </div>
            <h3><?= __t('event_title_2') ?></h3>
            <p><?= __t('event_desc_2') ?></p>
          </div>
          <div class="event-footer-strip">
            <span class="u-text-xs u-text-muted"><?= __t('event_status_2') ?></span>
            <a href="views/contacto.php" class="btn btn-sm btn-ghost"><?= __t('event_btn_2') ?></a>
          </div>
        </article>

        <article class="home-event-card">
          <div class="card-media-wrapper">
            <img src="assets/images/event_national.jpg" alt="Jornadas Nacionales de Informática" class="card-media-img" loading="lazy" />
          </div>
          <div>
            <div class="event-meta">
              <span class="event-tag event-tag--national"><?= __t('event_national') ?></span>
              <span class="u-inline-flex-center u-gap-xs">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                <?= __t('event_date_3') ?>
              </span>
            </div>
            <h3><?= __t('event_title_3') ?></h3>
            <p><?= __t('event_desc_3') ?></p>
          </div>
          <div class="event-footer-strip">
            <span class="u-text-xs u-text-muted"><?= __t('event_status_3') ?></span>
            <a href="views/contacto.php" class="btn btn-sm btn-ghost"><?= __t('event_btn_3') ?></a>
          </div>
        </article>
      </div>
    </section>

    <section class="home-section" aria-labelledby="testimonios-heading">
      <div class="home-section-header">
        <div>
          <h2 id="testimonios-heading"><?= __t('sec_testimonials') ?></h2>
          <p><?= __t('sec_testimonials_sub') ?></p>
        </div>
      </div>

      <div class="home-testimonials-grid">
        <div class="testimonial-card">
          <div>
            <div class="rating-stars" hidden aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <p class="testimonial-quote">
              <?= __t('testimonial_quote_1') ?>
            </p>
          </div>
          <div class="testimonial-author">
            <img src="assets/images/default-avatar.svg" alt="Avatar" class="testimonial-avatar" />
            <div>
              <p class="testimonial-name"><?= __t('testimonial_name_1') ?></p>
              <p class="testimonial-role"><?= __t('testimonial_role_1') ?></p>
            </div>
          </div>
        </div>

        <div class="testimonial-card">
          <div>
            <div class="rating-stars" hidden aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <p class="testimonial-quote">
              <?= __t('testimonial_quote_2') ?>
            </p>
          </div>
          <div class="testimonial-author">
            <img src="assets/images/default-avatar.svg" alt="Avatar" class="testimonial-avatar" />
            <div>
              <p class="testimonial-name"><?= __t('testimonial_name_2') ?></p>
              <p class="testimonial-role"><?= __t('testimonial_role_2') ?></p>
            </div>
          </div>
        </div>

        <div class="testimonial-card">
          <div>
            <div class="rating-stars" hidden aria-hidden="true">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
              <svg width="16" height="16" viewBox="0 0 24 24" fill="#f59e0b"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
            </div>
            <p class="testimonial-quote">
              <?= __t('testimonial_quote_3') ?>
            </p>
          </div>
          <div class="testimonial-author">
            <img src="assets/images/default-avatar.svg" alt="Avatar" class="testimonial-avatar" />
            <div>
              <p class="testimonial-name"><?= __t('testimonial_name_3') ?></p>
              <p class="testimonial-role"><?= __t('testimonial_role_3') ?></p>
            </div>
          </div>
        </div>
      </div>
    </section>

    <section class="home-section" aria-labelledby="confian-heading">
      <div class="home-trusted-strip">
        <p class="trusted-label"><?= __t('sec_trusted_by') ?></p>
        <div class="home-trusted-grid">

          <div class="trusted-badge">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <rect x="3" y="3" width="18" height="18" rx="3"/>
              <path d="M12 8v4l3 3"/>
            </svg>
            <span><?= __t('trusted_org_1') ?></span>
          </div>

          <div class="trusted-badge">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <circle cx="12" cy="12" r="9"/>
              <path d="M12 7v5l3 2"/>
            </svg>
            <span><?= __t('trusted_org_2') ?></span>
          </div>

          <div class="trusted-badge">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
              <circle cx="9" cy="7" r="4"/>
              <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
              <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
            </svg>
            <span><?= __t('trusted_org_3') ?></span>
          </div>

          <div class="trusted-badge">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            </svg>
            <span><?= __t('trusted_org_4') ?></span>
          </div>

          <div class="trusted-badge">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
              <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>
            </svg>
            <span><?= __t('trusted_org_5') ?></span>
          </div>

        </div>
      </div>
    </section>

    <section class="home-section" aria-labelledby="tech-heading">
      <div class="home-tech-strip">
        <p class="trusted-label"><?= __t('sec_tech_stack') ?></p>
        <div class="home-tech-grid">

          <div class="trusted-badge" title="PHP 8.2 Engine">
            <svg class="trusted-badge-icon" viewBox="0 0 128 128" aria-hidden="true"><path fill="currentColor" d="M64 0C28.7 0 0 28.7 0 64s28.7 64 64 64 64-28.7 64-64S99.3 0 64 0zm-7.6 92.5H43.9l6.5-32.9h12.5c8.6 0 13.9 4.3 12.3 12.3-1.6 8.3-8.8 20.6-18.8 20.6zm42.7 0H86.6l6.5-32.9h12.5c8.6 0 13.9 4.3 12.3 12.3-1.6 8.3-8.8 20.6-18.8 20.6zM28.4 59.6H15.9l6.5-32.9h12.5c8.6 0 13.9 4.3 12.3 12.3-1.6 8.3-8.8 20.6-18.8 20.6z"/></svg>
            <span>PHP 8.2</span>
          </div>

          <div class="trusted-badge" title="MySQL Database">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><ellipse cx="12" cy="7" rx="9" ry="3"/><path d="M3 7v5c0 1.66 4.03 3 9 3s9-1.34 9-3V7"/><path d="M3 12v5c0 1.66 4.03 3 9 3s9-1.34 9-3v-5"/></svg>
            <span>MySQL 8.0</span>
          </div>

          <div class="trusted-badge" title="Docker Containers">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="2" y="10" width="4" height="4" rx="1"/><rect x="7" y="10" width="4" height="4" rx="1"/><rect x="12" y="10" width="4" height="4" rx="1"/><rect x="7" y="5" width="4" height="4" rx="1"/><path d="M22 12.5c-.7-.5-2-.5-3-.3-.2-1.5-1-2.8-2.5-3.5l-.5-.2-.3.5c-.7 1.2-1.9 1.9-3.2 1.9H2l-.2.8C1.1 14 1.5 16.2 3 18c1.6 1.8 4 2.8 6.5 2.8 7 0 12.3-4 13.5-10.7.3.1.6.1 1 .1.7 0 1.4-.1 2-.4"/></svg>
            <span>Docker</span>
          </div>

          <div class="trusted-badge" title="JavaScript Moderno">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M20 4H4v16h16V4z"/><path d="M10 16c0 1.1.7 2 2 2s2-.9 2-2v-6"/><path d="M14 10h2"/></svg>
            <span>JavaScript ES6+</span>
          </div>

          <div class="trusted-badge" title="HTML5 &amp; CSS3 Standards">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 3l1.5 17L12 22l6.5-2L20 3H4z"/><path d="M16 8H8l.5 5h7l-.5 4.5L12 19l-3-.5L8.75 16"/></svg>
            <span>HTML5 &amp; CSS3</span>
          </div>

          <div class="trusted-badge" title="OAuth 2.0 (Google &amp; GitHub)">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <span>OAuth 2.0</span>
          </div>

          <div class="trusted-badge" title="Google reCAPTCHA">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span>reCAPTCHA</span>
          </div>

          <div class="trusted-badge" title="TOTP 2FA (RFC 6238)">
            <svg class="trusted-badge-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
            <span>TOTP 2FA</span>
          </div>

        </div>
      </div>
    </section>

  </main>

<?php
include 'includes/footer.php';
?>

<?php
require_once '../php/auth/roles.php';
require_once '../php/usuarios/perfil_profesional.php';

$actual = usuario_actual();
$id_docente = (int)($_GET['id'] ?? 0);

if ($id_docente <= 0 && $actual) {
    $id_docente = (int)$actual['id_usuario'];
}

if ($id_docente <= 0) {
    $id_docente = obtener_primer_docente_id($pdo);
}

$perfil = $id_docente > 0 ? obtener_perfil_profesional($pdo, $id_docente) : null;

$permitido = false;
$es_propio = false;

if ($perfil) {
    $es_propio = $actual && ((int)$actual['id_usuario'] === $id_docente);
    $visibilidad = $perfil['visibilidad'] ?? 'Registrados';
    
    if (es_admin() || $es_propio) {
        $permitido = true;
    } elseif ($visibilidad === 'Registrados' && esta_autenticado()) {
        $permitido = true;
    } elseif ($actual && usuario_relacionado_con_docente($pdo, (int)$actual['id_usuario'], $id_docente)) {
        $permitido = true;
    } elseif (!esta_autenticado() && $visibilidad === 'Registrados') {
        $permitido = false;
    }
}

// Obtener publicaciones activas del docente
$publicaciones_docente = [];
$resenas = [];
$promedio_calificacion = 0.0;
$total_resenas = 0;

if ($perfil && $permitido) {
    try {
        $stmtPubs = $pdo->prepare(
            "SELECT p.*, c.nombre_categoria 
             FROM publicaciones p 
             JOIN categorias c ON p.id_categoria = c.id_categoria 
             WHERE p.id_usuario = :u AND p.estado = 'Activo' 
             ORDER BY p.fecha_creacion DESC"
        );
        $stmtPubs->execute(['u' => $id_docente]);
        $publicaciones_docente = $stmtPubs->fetchAll();

        $stmtResenas = $pdo->prepare(
            "SELECT v.*, u.nombre AS autor_nombre, u.apellido AS autor_apellido, p.titulo AS pub_titulo, p.tipo AS pub_tipo 
             FROM valoraciones v 
             JOIN usuarios u ON u.id_usuario = v.id_usuario 
             JOIN publicaciones p ON p.id_publicacion = v.id_publicacion 
             WHERE p.id_usuario = :u 
             ORDER BY v.fecha_valoracion DESC 
             LIMIT 6"
        );
        $stmtResenas->execute(['u' => $id_docente]);
        $resenas = $stmtResenas->fetchAll();

        $stmtStats = $pdo->prepare(
            "SELECT COUNT(v.id_valoracion) AS total, AVG(v.puntuacion) AS promedio 
             FROM valoraciones v 
             JOIN publicaciones p ON p.id_publicacion = v.id_publicacion 
             WHERE p.id_usuario = :u"
        );
        $stmtStats->execute(['u' => $id_docente]);
        $stats = $stmtStats->fetch();
        if ($stats) {
            $total_resenas = (int)($stats['total'] ?? 0);
            $promedio_calificacion = round((float)($stats['promedio'] ?? 0), 1);
        }
    } catch (PDOException $e) {
        error_log('Perfil profesional queries: ' . $e->getMessage());
    }
}

$nombreCompleto = $perfil ? trim($perfil['nombre'] . ' ' . ($perfil['apellido'] ?? '')) : 'Perfil profesional';
$title = $nombreCompleto !== '' ? $nombreCompleto . ' — Perfil Profesional' : 'Perfil profesional';
$description = 'Perfil profesional y trayectoria en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'catalogo';

include '../includes/header.php';
?>

<main class="product-detail-container motion-entry">
    <?php if (!$perfil): ?>
        <section class="empty-state" aria-labelledby="perfil-no-encontrado">
            <h1 id="perfil-no-encontrado">Perfil profesional no disponible</h1>
            <p>No se encontró el perfil profesional solicitado en la plataforma.</p>
            <div class="empty-state-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="catalogo.php" class="btn btn-primary-action">Explorar catálogo</a>
                <?php if (esta_autenticado() && (es_docente() || es_admin())): ?>
                    <a href="editar-perfil-profesional.php" class="btn">Configurar mi perfil profesional</a>
                <?php else: ?>
                    <a href="usuario.php" class="btn">Ir a mi cuenta</a>
                <?php endif; ?>
            </div>
        </section>
    <?php elseif (!esta_autenticado() && ($perfil['visibilidad'] ?? 'Registrados') === 'Registrados'): ?>
        <section class="empty-state" aria-labelledby="perfil-requiere-sesion">
            <h1 id="perfil-requiere-sesion">Acceso para usuarios registrados</h1>
            <p>Este profesional configuró su perfil para que sea visible por miembros registrados de la comunidad Classia.</p>
            <div class="empty-state-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="login.php" class="btn btn-primary-action">Iniciar sesión</a>
                <a href="registro.php" class="btn">Crear cuenta gratis</a>
            </div>
        </section>
    <?php elseif (!$permitido): ?>
        <section class="empty-state" aria-labelledby="perfil-privado">
            <h1 id="perfil-privado">Perfil profesional privado</h1>
            <p>Este profesional eligió compartir los detalles avanzados de su perfil únicamente con usuarios que tengan cursos o servicios contratados con él.</p>
            <div class="empty-state-actions" style="margin-top: 1.5rem; display: flex; gap: 1rem; justify-content: center;">
                <a href="proveedor.php?id=<?= $id_docente ?>" class="btn">Ver ficha pública del proveedor</a>
                <a href="catalogo.php" class="btn btn-primary-action">Volver al catálogo</a>
            </div>
        </section>
    <?php else: ?>
        <!-- Hero del Perfil Profesional -->
        <section class="provider-hero-card" aria-labelledby="titulo-perfil-profesional">
            <div class="provider-hero-inner">
                <div class="profile-avatar-wrapper">
                    <?php 
                        $fotoPerfil = (string)($perfil['foto_perfil'] ?? '');
                        $fotoSrc = $fotoPerfil !== ''
                            ? (preg_match('#^https?://#i', $fotoPerfil) ? $fotoPerfil : $cssPrefix . '/' . ltrim($fotoPerfil, '/'))
                            : ($cssPrefix . '/assets/images/default-avatar.svg');
                    ?>
                    <img src="<?= htmlspecialchars($fotoSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($nombreCompleto) ?>" class="provider-hero-avatar" />
                </div>

                <div class="profile-hero-text">
                    <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap; margin-bottom: 0.5rem;">
                        <span class="badge badge-provider">
                            <?= htmlspecialchars($perfil['nombre_rol'] ?? 'Docente / Proveedor') ?>
                        </span>
                        <?php if (!empty($perfil['modalidad_trabajo'])): ?>
                            <span class="badge" style="background: var(--color-brand-primary-soft); color: var(--color-brand-primary); font-weight: 600;">
                                <?= htmlspecialchars($perfil['modalidad_trabajo']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <h1 id="titulo-perfil-profesional"><?= htmlspecialchars($nombreCompleto) ?></h1>
                    
                    <p class="lead" style="font-size: 1.15rem; color: var(--color-brand-primary); font-weight: 600; margin-bottom: 0.5rem;">
                        <?= htmlspecialchars($perfil['titulo_profesional'] ?: 'Docente y Profesional Especialista') ?>
                    </p>

                    <?php if (!empty($perfil['ubicacion'])): ?>
                        <p class="provider-meta" style="margin-bottom: 0.75rem;">
                            📍 <?= htmlspecialchars($perfil['ubicacion']) ?> · Miembro desde <?= date('F Y', strtotime($perfil['fecha_registro'])) ?>
                        </p>
                    <?php endif; ?>

                    <div class="provider-stats-row">
                        <div>
                            <strong><?= $promedio_calificacion > 0 ? $promedio_calificacion . ' / 5' : 'Sin calificar' ?></strong>
                            <span class="text-muted">(<?= $total_resenas ?> <?= $total_resenas === 1 ? 'reseña' : 'reseñas' ?>)</span>
                        </div>
                        <div>
                            <strong><?= count($publicaciones_docente) ?></strong>
                            <span class="text-muted">cursos y servicios activos</span>
                        </div>
                        <?php if (!empty($perfil['tiempo_respuesta'])): ?>
                            <div>
                                <strong>⚡ <?= htmlspecialchars($perfil['tiempo_respuesta']) ?></strong>
                                <span class="text-muted">tiempo de respuesta</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="provider-hero-actions" style="margin-top: 1.25rem; display: flex; gap: 0.75rem; flex-wrap: wrap;">
                        <?php if ($es_propio || es_admin()): ?>
                            <a href="editar-perfil-profesional.php" class="btn btn-primary-action">✏️ Editar mi perfil</a>
                            <a href="panel-proveedor.php" class="btn">Panel del proveedor</a>
                        <?php endif; ?>
                        
                        <?php if (!empty($perfil['portfolio_url'])): ?>
                            <a href="<?= htmlspecialchars($perfil['portfolio_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ghost">
                                🌐 Portfolio externo
                            </a>
                        <?php endif; ?>

                        <?php if (!empty($perfil['linkedin_url'])): ?>
                            <a href="<?= htmlspecialchars($perfil['linkedin_url']) ?>" target="_blank" rel="noopener noreferrer" class="btn btn-ghost">
                                💼 LinkedIn
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>

        <!-- Presentación y Bio -->
        <?php if (!empty($perfil['presentacion'])): ?>
            <section class="provider-section" aria-labelledby="presentacion-titulo">
                <h2 id="presentacion-titulo">Presentación profesional</h2>
                <div class="professional-bio-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm); line-height: 1.7;">
                    <p><?= nl2br(htmlspecialchars($perfil['presentacion'])) ?></p>
                </div>
            </section>
        <?php endif; ?>

        <!-- Grilla de Experiencia, Formación, Habilidades y Certificaciones -->
        <section class="provider-section" aria-labelledby="trayectoria-titulo">
            <h2 id="trayectoria-titulo">Trayectoria y competencias</h2>
            <div class="profile-columns">
                <?php if (!empty($perfil['experiencia'])): ?>
                    <article class="info-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            💼 <span>Experiencia</span>
                        </h3>
                        <p style="line-height: 1.6;"><?= nl2br(htmlspecialchars($perfil['experiencia'])) ?></p>
                    </article>
                <?php endif; ?>

                <?php if (!empty($perfil['formacion'])): ?>
                    <article class="info-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            🎓 <span>Formación académica</span>
                        </h3>
                        <p style="line-height: 1.6;"><?= nl2br(htmlspecialchars($perfil['formacion'])) ?></p>
                    </article>
                <?php endif; ?>

                <?php if (!empty($perfil['habilidades'])): ?>
                    <article class="info-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            ⚡ <span>Habilidades técnicas</span>
                        </h3>
                        <p style="line-height: 1.6;"><?= nl2br(htmlspecialchars($perfil['habilidades'])) ?></p>
                    </article>
                <?php endif; ?>

                <?php if (!empty($perfil['especialidades'])): ?>
                    <article class="info-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            🎯 <span>Especialidades</span>
                        </h3>
                        <p style="line-height: 1.6;"><?= nl2br(htmlspecialchars($perfil['especialidades'])) ?></p>
                    </article>
                <?php endif; ?>

                <?php if (!empty($perfil['certificaciones'])): ?>
                    <article class="info-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            📜 <span>Certificaciones</span>
                        </h3>
                        <p style="line-height: 1.6;"><?= nl2br(htmlspecialchars($perfil['certificaciones'])) ?></p>
                    </article>
                <?php endif; ?>

                <?php if (!empty($perfil['idiomas'])): ?>
                    <article class="info-card" style="padding: var(--space-4); background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-sm);">
                        <h3 style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                            🗣️ <span>Idiomas</span>
                        </h3>
                        <p style="line-height: 1.6;"><?= htmlspecialchars($perfil['idiomas']) ?></p>
                    </article>
                <?php endif; ?>
            </div>
        </section>

        <!-- Propuestas y Publicaciones Activas -->
        <section class="provider-section" aria-labelledby="cursos-servicios-docente">
            <h2 id="cursos-servicios-docente">Cursos y Servicios disponibles de este profesional</h2>
            <?php if (empty($publicaciones_docente)): ?>
                <p class="text-muted">Este docente no tiene publicaciones activas en este momento.</p>
            <?php else: ?>
                <div class="catalog-grid">
                    <?php foreach ($publicaciones_docente as $pub): ?>
                        <article class="catalog-card">
                            <span class="badge <?= $pub['tipo'] === 'Curso' ? 'badge-course' : 'badge-service' ?>">
                                <?= htmlspecialchars($pub['tipo']) ?>
                            </span>
                            <h3><?= htmlspecialchars($pub['titulo']) ?></h3>
                            <p><?= htmlspecialchars(mb_strimwidth($pub['descripcion'], 0, 110, '...')) ?></p>
                            <div class="catalog-card-footer">
                                <strong>$<?= number_format((float)$pub['precio'], 2, ',', '.') ?></strong>
                                <a href="<?= $pub['tipo'] === 'Curso' ? 'curso.php?id=' . (int)$pub['id_publicacion'] : 'servicio-detalle.php?id=' . (int)$pub['id_publicacion'] ?>" class="btn">
                                    Ver <?= strtolower(htmlspecialchars($pub['tipo'])) ?>
                                </a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Reseñas y Testimonios -->
        <?php if (!empty($resenas)): ?>
            <section class="provider-section" aria-labelledby="opiniones-estudiantes">
                <h2 id="opiniones-estudiantes">Reseñas y valoraciones de estudiantes</h2>
                <div class="review-list">
                    <?php foreach ($resenas as $res): ?>
                        <article class="review-card">
                            <div class="review-card__header">
                                <strong><?= htmlspecialchars($res['autor_nombre'] . ' ' . ($res['autor_apellido'] ?? '')) ?></strong>
                                <span class="review-card__stars">
                                    <?= str_repeat('★', (int)$res['puntuacion']) . str_repeat('☆', 5 - (int)$res['puntuacion']) ?>
                                    (<?= (int)$res['puntuacion'] ?>/5)
                                </span>
                            </div>
                            <p class="review-card__date">
                                Sobre: <em><?= htmlspecialchars($res['pub_titulo']) ?> (<?= htmlspecialchars($res['pub_tipo']) ?>)</em> · <?= date('d/m/Y', strtotime($res['fecha_valoracion'])) ?>
                            </p>
                            <p class="review-card__body">
                                "<?= htmlspecialchars($res['comentario'] ?? 'Sin comentario.') ?>"
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <p class="back-link-container" style="margin-top: 2rem;">
            <a href="catalogo.php" class="link">← Volver al catálogo general</a>
        </p>
    <?php endif; ?>
</main>

<?php include '../includes/footer.php'; ?>

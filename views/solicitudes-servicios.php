<?php
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE, 'usuario.php');
require_once '../php/solicitudes/solicitudes_servicio.php';

$uid = (int)usuario_actual()['id_usuario'];
$estado = trim($_GET['estado'] ?? '');
$solicitudes = obtener_solicitudes_proveedor($pdo, $uid, $estado !== '' ? $estado : null);
$estados = ['Pendiente', 'Aceptada', 'Contraoferta', 'En Proceso', 'Realizada', 'Rechazada'];

$title = 'Solicitudes de servicios';
$description = 'Gestión de solicitudes recibidas por el proveedor.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main class="provider-editor provider-workspace requests-management-page">
    <header class="section-heading provider-page-heading">
        <p class="course-eyebrow">Servicios</p>
        <h1>Solicitudes de servicios</h1>
        <p>Revisá cada solicitud particular, respondé al usuario y administrá su estado.</p>
    </header>

    <nav class="provider-toolbar request-filter" aria-label="Filtrar solicitudes por estado">
        <a class="request-filter-btn<?= $estado === '' ? ' is-active' : '' ?>" href="solicitudes-servicios.php"<?= $estado === '' ? ' aria-current="page"' : '' ?>>Todas</a>
        <?php foreach ($estados as $e): ?>
            <?php $slug = strtolower(str_replace(' ', '-', $e)); ?>
            <a class="request-filter-btn request-filter-btn--<?= $slug ?><?= $estado === $e ? ' is-active' : '' ?>" href="?estado=<?= urlencode($e) ?>"<?= $estado === $e ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($e) ?></a>
        <?php endforeach; ?>
        <a class="request-filter-btn request-filter-btn--panel" href="panel-proveedor.php">Volver al Panel</a>
    </nav>

    <?php if (!$solicitudes): ?>
        <div class="course-empty-state">
            <h2>No hay solicitudes para este filtro.</h2>
            <p>Cuando llegue una solicitud compatible, va a aparecer en este listado.</p>
        </div>
    <?php else: ?>
        <section class="request-list provider-card-grid" aria-label="Solicitudes recibidas">
            <?php foreach ($solicitudes as $s): ?>
                <?php $statusSlug = strtolower(str_replace(' ', '-', $s['estado'])); ?>
                <article class="content-card request-card">
                    <header class="request-card-header">
                        <div class="request-card-title-group">
                            <h2 class="request-card-title"><?= htmlspecialchars($s['servicio']) ?></h2>
                            <p class="request-card-user"><?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?> &middot; <span class="request-card-email"><?= htmlspecialchars($s['email']) ?></span></p>
                        </div>
                        <span class="status-chip status-chip--<?= $statusSlug ?>"><?= htmlspecialchars($s['estado']) ?></span>
                    </header>
                    <p class="request-card-desc"><?= nl2br(htmlspecialchars($s['descripcion'])) ?></p>
                    <dl class="request-details">
                        <div class="request-detail-item">
                            <dt>Recibida</dt>
                            <dd><?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></dd>
                        </div>
                        <?php if (!empty($s['precio_propuesto'])): ?>
                            <div class="request-detail-item">
                                <dt>Precio propuesto</dt>
                                <dd class="request-detail-price">$<?= number_format((float)$s['precio_propuesto'], 2, ',', '.') ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <div class="request-card-actions">
                        <a class="btn btn-primary-action request-card-action" href="solicitud-servicio-detalle.php?id=<?= (int)$s['id_solicitud'] ?>">Abrir y responder</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

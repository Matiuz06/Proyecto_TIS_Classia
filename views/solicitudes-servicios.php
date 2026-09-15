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
<main class="provider-editor provider-workspace">
    <header class="section-heading provider-page-heading">
        <p class="course-eyebrow">Servicios</p>
        <h1>Solicitudes de servicios</h1>
        <p>Revisá cada solicitud particular, respondé al usuario y administrá su estado.</p>
    </header>

    <nav class="provider-toolbar request-filter" aria-label="Filtrar solicitudes por estado">
        <a class="btn<?= $estado === '' ? ' btn-primary-action' : '' ?>" href="solicitudes-servicios.php"<?= $estado === '' ? ' aria-current="page"' : '' ?>>Todas</a>
        <?php foreach ($estados as $e): ?>
            <a class="btn<?= $estado === $e ? ' btn-primary-action' : '' ?>" href="?estado=<?= urlencode($e) ?>"<?= $estado === $e ? ' aria-current="page"' : '' ?>><?= htmlspecialchars($e) ?></a>
        <?php endforeach; ?>
        <a class="btn" href="panel-proveedor.php">Panel</a>
    </nav>

    <?php if (!$solicitudes): ?>
        <div class="course-empty-state">
            <h2>No hay solicitudes para este filtro.</h2>
            <p>Cuando llegue una solicitud compatible, va a aparecer en este listado.</p>
        </div>
    <?php else: ?>
        <section class="request-list provider-card-grid" aria-label="Solicitudes recibidas">
            <?php foreach ($solicitudes as $s): ?>
                <article class="content-card request-card">
                    <header class="request-card-header">
                        <div>
                            <h2><?= htmlspecialchars($s['servicio']) ?></h2>
                            <p><?= htmlspecialchars($s['nombre'] . ' ' . $s['apellido']) ?> &middot; <?= htmlspecialchars($s['email']) ?></p>
                        </div>
                        <span class="status-chip"><?= htmlspecialchars($s['estado']) ?></span>
                    </header>
                    <p><?= nl2br(htmlspecialchars($s['descripcion'])) ?></p>
                    <dl class="request-details">
                        <div>
                            <dt>Recibida</dt>
                            <dd><?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></dd>
                        </div>
                        <?php if (!empty($s['precio_propuesto'])): ?>
                            <div>
                                <dt>Precio propuesto</dt>
                                <dd>$<?= number_format((float)$s['precio_propuesto'], 2, ',', '.') ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <div class="form-actions">
                        <a class="btn btn-primary-action" href="solicitud-servicio-detalle.php?id=<?= (int)$s['id_solicitud'] ?>">Abrir y responder</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

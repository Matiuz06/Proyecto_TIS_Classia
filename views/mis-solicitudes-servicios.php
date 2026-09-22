<?php
require_once '../php/auth/roles.php';
requerir_rol(ROL_ESTUDIANTE, 'usuario.php');
require_once '../php/solicitudes/solicitudes_servicio.php';

$uid = (int)usuario_actual()['id_usuario'];
$solicitudes = obtener_solicitudes_cliente($pdo, $uid);

$title = 'Mis solicitudes';
$description = 'Seguimiento de solicitudes de servicios.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>
<main class="provider-editor provider-workspace requests-management-page">
    <header class="section-heading provider-page-heading">
        <h1>Mis solicitudes de servicios</h1>
        <p>Consultá el estado, las propuestas del proveedor y la conversación de cada solicitud.</p>
    </header>

    <?php if (!$solicitudes): ?>
        <div class="course-empty-state">
            <h2>No tenés solicitudes de servicios todavía.</h2>
            <p>Podés explorar el catálogo y solicitar servicios técnicos o pedagógicos a medida.</p>
            <a class="btn btn-primary-action" href="catalogo.php" style="margin-top: 1rem; color: #ffffff !important;">Explorar catálogo</a>
        </div>
    <?php else: ?>
        <section class="request-list provider-card-grid" aria-label="Mis solicitudes enviadas">
            <?php foreach ($solicitudes as $s): ?>
                <?php $statusSlug = strtolower(str_replace(' ', '-', $s['estado'])); ?>
                <article class="content-card request-card">
                    <header class="request-card-header">
                        <div class="request-card-title-group">
                            <h2 class="request-card-title"><?= htmlspecialchars($s['servicio']) ?></h2>
                            <p class="request-card-user">Proveedor: <strong><?= htmlspecialchars($s['proveedor_nombre'] . ' ' . $s['proveedor_apellido']) ?></strong></p>
                        </div>
                        <span class="status-chip status-chip--<?= $statusSlug ?>"><?= htmlspecialchars($s['estado']) ?></span>
                    </header>
                    <dl class="request-details">
                        <div class="request-detail-item">
                            <dt>Fecha</dt>
                            <dd><?= date('d/m/Y H:i', strtotime($s['fecha_solicitud'])) ?></dd>
                        </div>
                        <?php if ($s['precio_propuesto'] !== null): ?>
                            <div class="request-detail-item">
                                <dt>Precio propuesto</dt>
                                <dd class="request-detail-price">$<?= number_format((float)$s['precio_propuesto'], 2, ',', '.') ?></dd>
                            </div>
                        <?php endif; ?>
                    </dl>
                    <div class="request-card-actions">
                        <a class="btn btn-primary-action request-card-action" href="mi-solicitud-servicio.php?id=<?= (int)$s['id_solicitud'] ?>">Ver detalle y conversación</a>
                    </div>
                </article>
            <?php endforeach; ?>
        </section>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

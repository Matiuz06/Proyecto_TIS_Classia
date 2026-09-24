<?php

/**
 * Responsabilidad: Gestión de solicitud de servicio recibida por el docente/proveedor.
 */

require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE, 'usuario.php');
require_once '../php/solicitudes/solicitudes_servicio.php';

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$uid = (int)usuario_actual()['id_usuario'];
$id = (int)($_GET['id'] ?? $_POST['id_solicitud'] ?? 0);
$mensaje = '';
$error = '';
$solicitud = obtener_solicitud_para_proveedor($pdo, $id, $uid);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $solicitud) {
    $r = procesar_solicitud_proveedor($pdo, $id, $uid, $uid, $_POST, $_SESSION['csrf_token']);
    if ($r['ok']) $mensaje = $r['mensaje'];
    else $error = $r['mensaje'];
    $solicitud = obtener_solicitud_para_proveedor($pdo, $id, $uid);
}

$mensajes = $solicitud ? obtener_mensajes_solicitud($pdo, $id) : [];
$detalles = $solicitud ? json_decode($solicitud['detalles_json'] ?? '{}', true) : [];
$plantilla = $solicitud ? obtener_plantilla_servicio($solicitud['tipo_servicio'] ?? null) : null;

$title = 'Detalle de solicitud';
$description = 'Gestión de una solicitud de servicio.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main class="provider-editor provider-workspace">
    <?php if (!$solicitud): ?>
        <div class="alert alert-danger">Solicitud inexistente o sin permisos.</div>
    <?php else: ?>
        <header class="section-heading provider-page-heading">
            <p class="course-eyebrow">Solicitud de servicio</p>
            <h1><?= htmlspecialchars($solicitud['servicio']) ?></h1>
            <p>Solicitud de <?= htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['apellido']) ?> &middot; Estado: <strong><?= htmlspecialchars($solicitud['estado']) ?></strong></p>
        </header>

        <?php if ($mensaje): ?><div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="request-detail-layout">
            <section class="content-card request-detail-card">
                <div class="provider-section-head">
                    <h2>Información solicitada</h2>
                    <p>Datos enviados por el usuario para definir el servicio.</p>
                </div>
                <dl class="request-details">
                    <?php foreach ($detalles as $k => $v): ?>
                        <?php $label = $plantilla['campos'][$k]['label'] ?? str_replace('_', ' ', ucfirst($k)); ?>
                        <div>
                            <dt><?= htmlspecialchars($label) ?></dt>
                            <dd><?= nl2br(htmlspecialchars((string)$v)) ?></dd>
                        </div>
                    <?php endforeach; ?>
                </dl>
                <p><strong>Descripción general:</strong><br><?= nl2br(htmlspecialchars($solicitud['descripcion'])) ?></p>
                <?php if ($solicitud['archivo_adjunto']): ?>
                    <p><a class="btn" href="../php/descargas/descargar_archivo.php?tipo=solicitud&id=<?= (int)$solicitud['id_solicitud'] ?>">Descargar archivo adjunto</a></p>
                <?php endif; ?>
            </section>

            <section class="content-card request-state-card">
                <div class="provider-section-head">
                    <h2>Gestionar propuesta</h2>
                    <p>Actualizá precio, fecha o respuesta según el estado actual.</p>
                </div>
                <form method="POST" class="provider-form">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                    <input type="hidden" name="id_solicitud" value="<?= $id ?>">
                    <div class="form-grid">
                        <label>Precio acordado / propuesto<input type="number" name="precio_propuesto" min="0.01" step="0.01" value="<?= htmlspecialchars($solicitud['precio_propuesto'] ?? $solicitud['precio_publicado']) ?>"></label>
                        <label>Fecha y horario<input type="datetime-local" name="fecha_hora_propuesta" value="<?= $solicitud['fecha_hora_propuesta'] ? date('Y-m-d\TH:i', strtotime($solicitud['fecha_hora_propuesta'])) : '' ?>"></label>
                        <label class="form-grid-full">Respuesta<textarea name="respuesta_proveedor" rows="4"><?= htmlspecialchars($solicitud['respuesta_proveedor'] ?? '') ?></textarea></label>
                    </div>
                    <div class="form-actions request-action-row">
                        <?php if ($solicitud['estado'] === 'Pendiente'): ?>
                            <button type="submit" name="accion" value="aceptar" class="btn btn-primary-action">Aceptar propuesta base</button>
                            <button type="submit" name="accion" value="contraoferta" class="btn">Proponer cambios</button>
                            <button type="submit" name="accion" value="rechazar" class="link-danger">Rechazar</button>
                        <?php elseif (in_array($solicitud['estado'], ['Aceptada', 'Contraoferta'], true)): ?>
                            <button type="submit" name="accion" value="contraoferta" class="btn btn-primary-action">Actualizar propuesta</button>
                            <button type="submit" name="accion" value="rechazar" class="link-danger">Rechazar</button>
                            <p class="muted">Esperando que el cliente confirme y realice el pago.</p>
                        <?php elseif ($solicitud['estado'] === 'En Proceso'): ?>
                            <button type="submit" name="accion" value="realizada" class="btn btn-primary-action">Marcar como realizada</button>
                        <?php else: ?>
                            <p class="muted">Esta solicitud está cerrada y ya no admite cambios de estado.</p>
                        <?php endif; ?>
                    </div>
                </form>
            </section>
        </div>

        <section class="content-card">
            <div class="provider-section-head">
                <h2>Conversación</h2>
                <p>Mensajes asociados a esta solicitud.</p>
            </div>
            <div class="message-thread">
                <?php foreach ($mensajes as $m): ?>
                    <article>
                        <strong><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></strong>
                        <time><?= date('d/m/Y H:i', strtotime($m['fecha_mensaje'])) ?></time>
                        <p><?= nl2br(htmlspecialchars($m['mensaje'])) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
            <form method="POST" class="provider-message-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id_solicitud" value="<?= $id ?>">
                <input type="hidden" name="accion" value="mensaje">
                <label>Nuevo mensaje<textarea name="mensaje" rows="3" required placeholder="Escribí un mensaje"></textarea></label>
                <button class="btn" type="submit">Enviar mensaje</button>
            </form>
        </section>

        <p><a class="btn" href="solicitudes-servicios.php">Volver a solicitudes</a></p>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

<?php

/**
 * Responsabilidad: Gestión de solicitud de servicio recibida por el docente/proveedor.
 */

require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE, 'usuario.php');
require_once '../php/solicitudes/solicitudes_servicio.php';
require_once '../php/utils/toast.php';

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$uid = (int)usuario_actual()['id_usuario'];
$id = (int)($_GET['id'] ?? $_POST['id_solicitud'] ?? 0);
$mensaje = '';
$error = '';
$solicitud = obtener_solicitud_para_proveedor($pdo, $id, $uid);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $solicitud) {
    $r = procesar_solicitud_proveedor($pdo, $id, $uid, $uid, $_POST, $_SESSION['csrf_token']);
    if ($r['ok']) {
        set_toast('success', $r['mensaje']);
        if (($_POST['accion'] ?? '') === 'mensaje') {
            header('Location: solicitud-servicio-detalle.php?id=' . $id);
            exit;
        }
    }
    else $error = $r['mensaje'];
    $solicitud = obtener_solicitud_para_proveedor($pdo, $id, $uid);
}

$mensajes = $solicitud ? obtener_mensajes_solicitud($pdo, $id, $uid) : [];
$detalles = $solicitud ? json_decode($solicitud['detalles_json'] ?? '{}', true) : [];
$plantilla = $solicitud ? obtener_plantilla_servicio($solicitud['tipo_servicio'] ?? null) : null;
$camposContacto = [
    'nombre_contacto' => ['label' => 'Nombre completo', 'value' => trim(($solicitud['nombre'] ?? '') . ' ' . ($solicitud['apellido'] ?? ''))],
    'correo_contacto' => ['label' => 'Correo de contacto', 'value' => $solicitud['email'] ?? ''],
    'telefono' => ['label' => 'Teléfono', 'value' => $detalles['telefono'] ?? ''],
    'medio_contacto' => ['label' => 'Medio de contacto preferido', 'value' => $detalles['medio_contacto'] ?? ''],
];
$detallesProyecto = array_diff_key($detalles, array_flip(array_keys($camposContacto)));

$title = 'Detalle de solicitud';
$description = 'Gestión de una solicitud de servicio.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main id="main-content" class="provider-editor provider-workspace service-request-provider-page">
    <?php if (!$solicitud): ?>
        <div class="alert alert-danger">Solicitud inexistente o sin permisos.</div>
    <?php else: ?>
        <header class="section-heading provider-page-heading request-page-heading">
            <p class="course-eyebrow">Solicitud de servicio</p>
            <h1><?= htmlspecialchars($solicitud['servicio']) ?></h1>
            <div class="request-heading-meta">
                <p>Solicitante: <?= htmlspecialchars($solicitud['nombre'] . ' ' . $solicitud['apellido']) ?></p>
                <span class="request-status-badge"><?= htmlspecialchars($solicitud['estado']) ?></span>
            </div>
            <?php if ($solicitud['estado'] === 'Pendiente'): ?>
                <p class="request-status-help">Revisá los requisitos y respondé con una propuesta para continuar.</p>
            <?php elseif (in_array($solicitud['estado'], ['Aceptada', 'Contraoferta'], true)): ?>
                <p class="request-status-help">La propuesta está pendiente de confirmación y pago por parte del cliente.</p>
            <?php elseif ($solicitud['estado'] === 'En Proceso'): ?>
                <p class="request-status-help">El servicio está en ejecución. Podés marcarlo como realizado cuando finalice.</p>
            <?php endif; ?>
        </header>

        <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

        <div class="request-detail-layout">
            <section class="content-card request-detail-card request-info-card">
                <details class="request-info-disclosure">
                    <summary>Información de la solicitud</summary>
                    <div class="request-info-content">
                        <section class="request-info-group">
                            <h2>Datos del solicitante</h2>
                            <dl class="request-details request-contact-details">
                                <?php foreach ($camposContacto as $campo): ?>
                                    <div>
                                        <dt><?= htmlspecialchars($campo['label']) ?></dt>
                                        <dd><?= trim((string)$campo['value']) !== '' ? nl2br(htmlspecialchars((string)$campo['value'])) : '<span class="request-value-empty">No especificado</span>' ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        </section>

                        <section class="request-info-group">
                            <h2>Detalles del proyecto</h2>
                            <dl class="request-details request-project-details">
                                <?php foreach ($detallesProyecto as $k => $v): ?>
                                    <?php $label = $plantilla['campos'][$k]['label'] ?? str_replace('_', ' ', ucfirst($k)); ?>
                                    <div>
                                        <dt><?= htmlspecialchars($label) ?></dt>
                                        <dd><?= trim((string)$v) !== '' ? nl2br(htmlspecialchars((string)$v)) : '<span class="request-value-empty">No especificado</span>' ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                            <div class="request-description">
                                <h3>Descripción general</h3>
                                <p><?= trim((string)$solicitud['descripcion']) !== '' ? nl2br(htmlspecialchars($solicitud['descripcion'])) : '<span class="request-value-empty">No especificado</span>' ?></p>
                            </div>
                        </section>

                        <?php if ($solicitud['archivo_adjunto']): ?>
                            <p class="request-info-attachment"><a class="btn" href="../php/descargas/descargar_archivo.php?tipo=solicitud&id=<?= (int)$solicitud['id_solicitud'] ?>">Descargar archivo adjunto</a></p>
                        <?php endif; ?>
                    </div>
                </details>
            </section>

            <section class="content-card request-state-card request-proposal-card">
                <details class="request-info-disclosure request-proposal-disclosure"<?= in_array($solicitud['estado'], ['Pendiente', 'Aceptada', 'Contraoferta', 'En Proceso'], true) ? ' open' : '' ?>>
                    <summary>Gestionar propuesta</summary>
                    <div class="request-info-content request-proposal-content">
                        <div class="provider-section-head">
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
                    </div>
                </details>
            </section>
        </div>

        <section class="content-card request-conversation-card">
            <div class="provider-section-head request-section-heading">
                <div><h2>Conversación</h2><p>Mensajes asociados a esta solicitud.</p></div>
            </div>
            <div class="message-thread">
                <?php if (!$mensajes): ?>
                    <p class="request-empty-state">Todavía no hay mensajes. Podés escribirle al cliente para coordinar el trabajo.</p>
                <?php else: ?>
                    <?php foreach ($mensajes as $m): ?>
                        <article class="<?= (int)$m['id_usuario'] === $uid ? 'message-own' : 'message-client' ?>">
                            <div class="message-meta"><strong><?= htmlspecialchars($m['nombre'] . ' ' . $m['apellido']) ?></strong><time><?= date('d/m/Y H:i', strtotime($m['fecha_mensaje'])) ?></time></div>
                            <p><?= nl2br(htmlspecialchars($m['mensaje'])) ?></p>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <form method="POST" class="provider-message-form request-message-form">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                <input type="hidden" name="id_solicitud" value="<?= $id ?>">
                <input type="hidden" name="accion" value="mensaje">
                <label>Nuevo mensaje<textarea name="mensaje" rows="3" required placeholder="Escribí un mensaje"></textarea></label>
                <button class="btn" type="submit">Enviar mensaje</button>
            </form>
        </section>

        <p><a class="btn request-back-link" href="solicitudes-servicios.php">Volver a solicitudes</a></p>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

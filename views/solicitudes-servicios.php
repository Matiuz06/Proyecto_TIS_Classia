<?php
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE,'usuario.php');
require_once '../php/solicitudes/solicitudes_servicio.php';
$uid=(int)usuario_actual()['id_usuario']; $estado=trim($_GET['estado'] ?? ''); $solicitudes=obtener_solicitudes_proveedor($pdo,$uid,$estado!==''?$estado:null);
$title='Solicitudes de servicios'; $description='Gestión de solicitudes recibidas por el proveedor.'; $cssPrefix='..'; $jsPrefix='..'; $activePage='panel-proveedor'; include '../includes/header.php';
?>
<main>
<header class="section-heading"><h1>Solicitudes de servicios</h1><p>Revisá cada solicitud particular, respondé al usuario y administrá su estado.</p></header>
<nav class="provider-toolbar"><a class="btn" href="solicitudes-servicios.php">Todas</a><?php foreach(['Pendiente','Aceptada','Contraoferta','En Proceso','Realizada','Rechazada'] as $e): ?><a class="btn" href="?estado=<?= urlencode($e) ?>"><?= htmlspecialchars($e) ?></a><?php endforeach; ?><a class="btn" href="panel-proveedor.php">Panel</a></nav>
<?php if(!$solicitudes): ?><p>No hay solicitudes para este filtro.</p><?php endif; ?>
<div class="request-list"><?php foreach($solicitudes as $s): ?><article class="content-card"><header><h2><?= htmlspecialchars($s['servicio']) ?></h2><span class="status-chip"><?= htmlspecialchars($s['estado']) ?></span></header><p><strong>Solicitante:</strong> <?= htmlspecialchars($s['nombre'].' '.$s['apellido']) ?> · <?= htmlspecialchars($s['email']) ?></p><p><?= htmlspecialchars($s['descripcion']) ?></p><p><strong>Recibida:</strong> <?= date('d/m/Y H:i',strtotime($s['fecha_solicitud'])) ?></p><a class="btn btn-primary-action" href="solicitud-servicio-detalle.php?id=<?= (int)$s['id_solicitud'] ?>">Abrir y responder</a></article><?php endforeach; ?></div>
</main>
<?php include '../includes/footer.php'; ?>

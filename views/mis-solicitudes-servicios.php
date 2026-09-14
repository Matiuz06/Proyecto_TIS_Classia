<?php
require_once '../php/auth/roles.php'; requerir_rol(ROL_ESTUDIANTE,'usuario.php'); require_once '../php/solicitudes/solicitudes_servicio.php';
$uid=(int)usuario_actual()['id_usuario'];$solicitudes=obtener_solicitudes_cliente($pdo,$uid);
$title='Mis solicitudes';$description='Seguimiento de solicitudes de servicios.';$cssPrefix='..';$jsPrefix='..';$activePage='cuenta';include '../includes/header.php';
?>
<main><header class="section-heading"><h1>Mis solicitudes de servicios</h1><p>Consultá el estado, las propuestas del proveedor y la conversación de cada solicitud.</p></header><?php if(!$solicitudes): ?><p>No tenés solicitudes de servicios todavía.</p><?php endif; ?><div class="request-list"><?php foreach($solicitudes as $s): ?><article class="content-card"><h2><?= htmlspecialchars($s['servicio']) ?></h2><p>Proveedor: <?= htmlspecialchars($s['proveedor_nombre'].' '.$s['proveedor_apellido']) ?></p><p>Estado: <strong><?= htmlspecialchars($s['estado']) ?></strong></p><?php if($s['precio_propuesto']!==null): ?><p>Precio propuesto: $<?= number_format((float)$s['precio_propuesto'],2,',','.') ?></p><?php endif; ?><a class="btn" href="mi-solicitud-servicio.php?id=<?= (int)$s['id_solicitud'] ?>">Ver detalle y conversación</a></article><?php endforeach; ?></div></main>
<?php include '../includes/footer.php'; ?>

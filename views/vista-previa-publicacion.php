<?php
require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');
require_once '../config/database.php';
require_once '../php/publicaciones/contenido_curso.php';
require_once '../php/solicitudes/plantillas_servicio.php';

$uid = (int)usuario_actual()['id_usuario'];
$id = (int)($_GET['id'] ?? 0);
$sql = "SELECT p.*,c.nombre_categoria,u.nombre autor_nombre,u.apellido autor_apellido FROM publicaciones p JOIN categorias c ON c.id_categoria=p.id_categoria JOIN usuarios u ON u.id_usuario=p.id_usuario WHERE p.id_publicacion=:id" . (es_admin() ? '' : ' AND p.id_usuario=:u');
$st = $pdo->prepare($sql);
$params = ['id' => $id];
if (!es_admin()) $params['u'] = $uid;
$st->execute($params);
$p = $st->fetch() ?: null;
$contenido = $p && $p['tipo'] === 'Curso' ? obtener_contenido_curso($pdo, $id) : [];
$plantilla = $p && $p['tipo'] === 'Servicio' ? obtener_plantilla_servicio($p['tipo_servicio'] ?? null) : null;

$title = 'Vista previa';
$description = 'Vista previa de publicación.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main class="provider-editor provider-workspace">
    <?php if (!$p): ?>
        <div class="alert alert-danger">Publicación no encontrada.</div>
    <?php else: ?>
        <div class="alert alert-warning">Vista previa del proveedor · Estado actual: <?= htmlspecialchars($p['estado']) ?></div>
        <article class="publication-detail">
            <header>
                <p><?= htmlspecialchars($p['nombre_categoria']) ?> · <?= htmlspecialchars($p['tipo']) ?></p>
                <h1><?= htmlspecialchars($p['titulo']) ?></h1>
                <?php if ($p['imagen']): ?><img class="service-banner-img" src="../<?= htmlspecialchars($p['imagen']) ?>" alt="Portada"><?php endif; ?>
                <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
            </header>
            <dl class="pub-details">
                <div><dt>Precio</dt><dd>$<?= number_format((float)$p['precio'], 2, ',', '.') ?></dd></div>
                <div><dt>Modalidad</dt><dd><?= htmlspecialchars($p['modalidad'] ?? 'A definir') ?></dd></div>
                <div><dt>Duración</dt><dd><?= !empty($p['duracion_horas']) ? ((int)$p['duracion_horas'] . ' h') : 'A definir' ?></dd></div>
                <div><dt>Cupos</dt><dd><?= $p['cupos'] !== null ? (int)$p['cupos'] : 'No definido' ?></dd></div>
                <div><dt>Disponibilidad</dt><dd><?= htmlspecialchars($p['disponibilidad'] ?: 'A coordinar') ?></dd></div>
            </dl>
            <?php if ($p['tipo'] === 'Curso'): ?>
                <section>
                    <h2>Programa</h2>
                    <?php foreach ($contenido as $m): ?>
                        <details class="course-module">
                            <summary><?= htmlspecialchars($m['titulo']) ?></summary>
                            <div class="course-module-body">
                                <?php foreach ($m['unidades'] as $u): ?><h3><?= htmlspecialchars($u['titulo']) ?></h3><?php endforeach; ?>
                            </div>
                        </details>
                    <?php endforeach; ?>
                </section>
            <?php else: ?>
                <section>
                    <h2>Plantilla de solicitud</h2>
                    <p><?= htmlspecialchars($plantilla['nombre'] ?? 'Sin configurar') ?></p>
                    <?php if ($plantilla): ?>
                        <ul><?php foreach ($plantilla['campos'] as $c): ?><li><?= htmlspecialchars($c['label']) ?></li><?php endforeach; ?></ul>
                    <?php endif; ?>
                </section>
            <?php endif; ?>
            <p><a class="btn" href="editar-publicacion.php?id=<?= $id ?>">Volver a editar</a></p>
        </article>
    <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

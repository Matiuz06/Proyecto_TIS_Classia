<?php
require_once '../php/auth/roles.php';

requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');

$usuario = usuario_actual();
$uid = (int)($usuario['id_usuario'] ?? 0);

require_once '../config/database.php';
require_once '../php/publicaciones/obtener_publicaciones.php';
require_once '../php/usuarios/perfil_profesional.php';
require_once '../php/solicitudes/solicitudes_servicio.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!function_exists('proveedor_escalar')) {
    function proveedor_escalar(PDO $pdo, string $sql, array $params = [])
    {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }
}

$perfil_profesional = obtener_perfil_profesional($pdo, $uid);
$solicitudes_todas = obtener_solicitudes_proveedor($pdo, $uid);
$solicitudes_recientes = array_slice($solicitudes_todas, 0, 5);

$activas = (int) proveedor_escalar(
    $pdo,
    "SELECT COUNT(*) FROM publicaciones WHERE id_usuario = :u AND estado = 'Activo'",
    ['u' => $uid]
);

$pendientes = (int) proveedor_escalar(
    $pdo,
    "SELECT COUNT(*)
     FROM solicitudes s
     JOIN publicaciones p ON p.id_publicacion = s.id_publicacion
     WHERE p.id_usuario = :u
       AND p.tipo = 'Servicio'
       AND s.estado = 'Pendiente'",
    ['u' => $uid]
);

$enProceso = (int) proveedor_escalar(
    $pdo,
    "SELECT COUNT(*)
     FROM solicitudes s
     JOIN publicaciones p ON p.id_publicacion = s.id_publicacion
     WHERE p.id_usuario = :u
       AND p.tipo = 'Servicio'
       AND s.estado = 'En Proceso'",
    ['u' => $uid]
);

$estudiantes = (int) proveedor_escalar(
    $pdo,
    "SELECT COUNT(DISTINCT c.id_usuario)
     FROM contrataciones c
     JOIN detalles_contratacion dc ON dc.id_contratacion = c.id_contratacion
     JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
     WHERE p.id_usuario = :u
       AND p.tipo = 'Curso'
       AND c.estado IN ('En Proceso', 'Completada')",
    ['u' => $uid]
);

$valoracion = proveedor_escalar(
    $pdo,
    "SELECT ROUND(AVG(v.puntuacion), 1)
     FROM valoraciones v
     JOIN publicaciones p ON p.id_publicacion = v.id_publicacion
     WHERE p.id_usuario = :u",
    ['u' => $uid]
);

$ingresos = (float) proveedor_escalar(
    $pdo,
    "SELECT COALESCE(SUM(dc.subtotal), 0)
     FROM detalles_contratacion dc
     JOIN contrataciones c ON c.id_contratacion = dc.id_contratacion
     JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
     WHERE p.id_usuario = :u
       AND EXISTS (
           SELECT 1
           FROM pagos pg
           WHERE pg.id_contratacion = c.id_contratacion
             AND pg.estado_pago = 'Aprobado'
       )",
    ['u' => $uid]
);

$stmtCursos = $pdo->prepare(
    "SELECT p.id_publicacion,
            p.titulo,
            COUNT(DISTINCT CASE WHEN c.estado IN ('En Proceso', 'Completada') THEN c.id_usuario END) AS inscriptos,
            COUNT(DISTINCT CASE WHEN c.estado = 'En Proceso' THEN c.id_usuario END) AS activos,
            COUNT(DISTINCT CASE WHEN c.estado = 'Completada' THEN c.id_usuario END) AS completaron
     FROM publicaciones p
     LEFT JOIN detalles_contratacion dc ON dc.id_publicacion = p.id_publicacion
     LEFT JOIN contrataciones c ON c.id_contratacion = dc.id_contratacion
     WHERE p.id_usuario = :u
       AND p.tipo = 'Curso'
       AND p.estado <> 'Eliminado'
     GROUP BY p.id_publicacion, p.titulo
     ORDER BY p.fecha_creacion DESC"
);
$stmtCursos->execute(['u' => $uid]);
$cursos_estudiantes = $stmtCursos->fetchAll();

$stmtValoraciones = $pdo->prepare(
    "SELECT v.*, u.nombre, u.apellido, p.titulo AS publicacion
     FROM valoraciones v
     JOIN usuarios u ON u.id_usuario = v.id_usuario
     JOIN publicaciones p ON p.id_publicacion = v.id_publicacion
     WHERE p.id_usuario = :u
     ORDER BY v.fecha_valoracion DESC
     LIMIT 5"
);
$stmtValoraciones->execute(['u' => $uid]);
$valoraciones_recientes = $stmtValoraciones->fetchAll();

$trabajos_completados = (int) proveedor_escalar(
    $pdo,
    "SELECT COUNT(DISTINCT c.id_contratacion)
     FROM contrataciones c
     JOIN detalles_contratacion dc ON dc.id_contratacion = c.id_contratacion
     JOIN publicaciones p ON p.id_publicacion = dc.id_publicacion
     WHERE p.id_usuario = :u
       AND c.estado = 'Completada'",
    ['u' => $uid]
);

$notificaciones = [];
foreach (array_slice($solicitudes_recientes, 0, 3) as $sol) {
    $notificaciones[] = [
        'titulo' => 'Solicitud de ' . ($sol['servicio'] ?? 'servicio'),
        'texto' => trim(($sol['nombre'] ?? '') . ' ' . ($sol['apellido'] ?? '')) . ' · Estado: ' . ($sol['estado'] ?? 'Pendiente'),
        'href' => 'solicitud-servicio-detalle.php?id=' . (int)$sol['id_solicitud'],
    ];
}
foreach (array_slice($valoraciones_recientes, 0, 2) as $v) {
    $notificaciones[] = [
        'titulo' => 'Nueva valoración recibida',
        'texto' => ($v['nombre'] ?? '') . ' ' . ($v['apellido'] ?? '') . ' valoró ' . ($v['publicacion'] ?? 'una publicación') . ' con ' . (int)$v['puntuacion'] . ' / 5.',
        'href' => '#valoraciones',
    ];
}

$title = 'Panel proveedor';
$description = 'Panel de gestión para proveedores de cursos y servicios en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>

<main class="provider-dashboard">
    <?php if (isset($_GET['mensaje'])): ?>
        <div class="alert alert-success">
            <?php
            $mensaje = $_GET['mensaje'];
            if ($mensaje === 'creada') echo 'Publicación creada exitosamente.';
            elseif ($mensaje === 'actualizada') echo 'Publicación actualizada correctamente.';
            elseif ($mensaje === 'estado_actualizado') echo 'El estado de la publicación se actualizó correctamente.';
            else echo 'Operación realizada correctamente.';
            ?>
        </div>
    <?php endif; ?>

    <header>
        <h1>Panel del proveedor</h1>
        <p>Gestiona tus cursos, servicios, solicitudes, estudiantes y datos profesionales desde un único lugar.</p>
        <p>Sesión iniciada como <strong><?= htmlspecialchars(trim(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? ''))) ?></strong></p>

        <nav aria-label="Acciones rápidas del proveedor">
            <ul>
                <li><a href="crear-publicacion.php">Publicar un curso o servicio</a></li>
                <li><a href="#publicaciones">Gestionar publicaciones</a></li>
                <?php if (es_docente()): ?>
                    <li><a href="solicitudes-servicios.php">Ver solicitudes</a></li>
                    <li><a href="editar-perfil-profesional.php">Editar perfil profesional</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <section aria-labelledby="titulo-resumen">
        <h2 id="titulo-resumen">Resumen de actividad</h2>

        <article><h3>Publicaciones activas</h3><p><strong><?= $activas ?></strong></p><a href="#publicaciones">Ver publicaciones</a></article>
        <article><h3>Estudiantes inscriptos</h3><p><strong><?= $estudiantes ?></strong></p><a href="#estudiantes">Ver estudiantes</a></article>
        <article><h3>Solicitudes pendientes</h3><p><strong><?= $pendientes ?></strong></p><a href="#solicitudes">Revisar solicitudes</a></article>
        <article><h3>Servicios en proceso</h3><p><strong><?= $enProceso ?></strong></p><a href="#solicitudes">Ver servicios</a></article>
        <article><h3>Valoración promedio</h3><p><strong><?= $valoracion !== false && $valoracion !== null ? htmlspecialchars((string)$valoracion) . ' de 5' : 'Sin valoraciones' ?></strong></p><a href="#valoraciones">Ver valoraciones</a></article>
        <article><h3>Ingresos registrados</h3><p><strong>$<?= number_format($ingresos, 2, ',', '.') ?></strong></p><p>Total correspondiente a pagos aprobados de tus contrataciones.</p></article>
    </section>

    <section aria-labelledby="titulo-notificaciones">
        <header>
            <h2 id="titulo-notificaciones">Notificaciones</h2>
            <p><?= count($notificaciones) ?> novedades recientes.</p>
        </header>

        <?php if (!$notificaciones): ?>
            <p class="muted">No hay novedades recientes.</p>
        <?php else: ?>
            <?php foreach ($notificaciones as $notificacion): ?>
                <article>
                    <header><h3><?= htmlspecialchars($notificacion['titulo']) ?></h3></header>
                    <p><?= htmlspecialchars($notificacion['texto']) ?></p>
                    <a href="<?= htmlspecialchars($notificacion['href']) ?>">Ver detalle</a>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section id="publicaciones" aria-labelledby="titulo-publicaciones">
        <header>
            <h2 id="titulo-publicaciones">Mis cursos y servicios</h2>
            <p>Consulta y administra las publicaciones asociadas a tu perfil.</p>
            <a href="crear-publicacion.php" class="btn btn-primary-action">+ Crear nueva publicación</a>
        </header>
        <br>

        <?php if (empty($publicaciones_proveedor)): ?>
            <p>No tenés publicaciones registradas aún. <a href="crear-publicacion.php">Creá tu primera publicación</a>.</p>
        <?php else: ?>
            <div class="publicaciones-lista">
                <?php foreach ($publicaciones_proveedor as $pub): ?>
                    <article class="pub-card<?= $pub['estado'] === 'Eliminado' ? ' publication-deleted' : '' ?>">
                        <?php if (!empty($pub['imagen'])): ?>
                            <div class="pub-card-thumb"><img src="../<?= htmlspecialchars($pub['imagen']) ?>" alt="<?= htmlspecialchars($pub['titulo']) ?>"></div>
                        <?php endif; ?>

                        <header>
                            <h4>
                                <?php $detalle = $pub['tipo'] === 'Curso' ? 'curso.php' : 'servicio-detalle.php'; ?>
                                <?php if ($pub['estado'] === 'Activo'): ?>
                                    <a href="<?= $detalle ?>?id=<?= (int)$pub['id_publicacion'] ?>"><?= htmlspecialchars($pub['titulo']) ?></a>
                                <?php else: ?>
                                    <a href="vista-previa-publicacion.php?id=<?= (int)$pub['id_publicacion'] ?>"><?= htmlspecialchars($pub['titulo']) ?></a>
                                <?php endif; ?>
                            </h4>
                            <span class="pub-badge"><?= htmlspecialchars($pub['tipo']) ?> — <?= htmlspecialchars($pub['nombre_categoria']) ?></span>
                        </header>

                        <p class="pub-desc"><?= htmlspecialchars($pub['descripcion']) ?></p>

                        <dl class="pub-details">
                            <div><dt><strong>Estado:</strong></dt><dd><span class="status-<?= strtolower(htmlspecialchars($pub['estado'])) ?>"><?= htmlspecialchars($pub['estado']) ?></span></dd></div>
                            <div><dt><strong>Precio:</strong></dt><dd>$<?= number_format((float)$pub['precio'], 2, ',', '.') ?></dd></div>
                            <?php if (!empty($pub['modalidad'])): ?><div><dt><strong>Modalidad:</strong></dt><dd><?= htmlspecialchars($pub['modalidad']) ?></dd></div><?php endif; ?>
                            <?php if (!empty($pub['duracion_horas'])): ?><div><dt><strong>Duración:</strong></dt><dd><?= ((int)$pub['duracion_horas'] . ' h') ?></dd></div><?php endif; ?>
                            <?php if (!empty($pub['cupos']) && $pub['tipo'] === 'Curso'): ?><div><dt><strong>Cupos:</strong></dt><dd><?= (int)$pub['cupos'] ?></dd></div><?php endif; ?>
                            <div><dt><strong>Fecha de creación:</strong></dt><dd><?= date('d/m/Y', strtotime($pub['fecha_creacion'])) ?></dd></div>
                        </dl>

                        <nav aria-label="Acciones de la publicación <?= htmlspecialchars($pub['titulo']) ?>">
                            <ul class="pub-actions">
                                <li><a href="vista-previa-publicacion.php?id=<?= (int)$pub['id_publicacion'] ?>" class="pub-actions-link">Vista previa</a></li>
                                <li><a href="editar-publicacion.php?id=<?= (int)$pub['id_publicacion'] ?>" class="pub-actions-link">Editar</a></li>
                                <?php if ($pub['tipo'] === 'Curso'): ?>
                                    <li><a href="gestionar-contenido-curso.php?id=<?= (int)$pub['id_publicacion'] ?>" class="pub-actions-link">Módulos y contenido</a></li>
                                <?php endif; ?>
                                <li>
                                    <form action="editar-publicacion.php?id=<?= (int)$pub['id_publicacion'] ?>" method="POST">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                        <input type="hidden" name="id_publicacion" value="<?= (int)$pub['id_publicacion'] ?>">
                                        <?php if ($pub['estado'] === 'Eliminado'): ?>
                                            <button type="submit" name="cambiar_estado" value="Inactivo" class="btn-status btn-status-activate">Restaurar</button>
                                        <?php elseif ($pub['estado'] === 'Activo'): ?>
                                            <button type="submit" name="cambiar_estado" value="Pausado" class="btn-status btn-status-pause">Pausar</button>
                                        <?php else: ?>
                                            <button type="submit" name="cambiar_estado" value="Activo" class="btn-status btn-status-activate">Activar</button>
                                        <?php endif; ?>
                                    </form>
                                </li>
                                <?php if ($pub['estado'] !== 'Eliminado'): ?>
                                    <li>
                                        <form action="editar-publicacion.php?id=<?= (int)$pub['id_publicacion'] ?>" method="POST">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
                                            <input type="hidden" name="id_publicacion" value="<?= (int)$pub['id_publicacion'] ?>">
                                            <button type="submit" name="cambiar_estado" value="Eliminado" onclick="return confirm('¿Confirmás que querés eliminar lógicamente esta publicación? Podrás restaurarla más adelante.');" class="btn-status btn-status-delete">Eliminar</button>
                                        </form>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <section id="solicitudes" aria-labelledby="titulo-solicitudes">
        <header>
            <h2 id="titulo-solicitudes">Solicitudes recibidas</h2>
            <p>Gestiona las solicitudes de servicios de tus clientes.</p>
        </header>

        <?php if (!$solicitudes_recientes): ?>
            <p class="muted">Todavía no recibiste solicitudes.</p>
        <?php else: ?>
            <?php foreach ($solicitudes_recientes as $sol): ?>
                <article>
                    <header>
                        <h3><?= htmlspecialchars($sol['servicio']) ?></h3>
                        <p>Estado: <strong><?= htmlspecialchars($sol['estado']) ?></strong></p>
                    </header>
                    <dl>
                        <div><dt>Cliente</dt><dd><?= htmlspecialchars(trim($sol['nombre'] . ' ' . $sol['apellido'])) ?></dd></div>
                        <?php if (!empty($sol['precio_propuesto'])): ?><div><dt>Precio acordado/propuesto</dt><dd>$<?= number_format((float)$sol['precio_propuesto'], 2, ',', '.') ?></dd></div><?php endif; ?>
                        <?php if (!empty($sol['fecha_hora_propuesta'])): ?><div><dt>Fecha propuesta</dt><dd><?= htmlspecialchars($sol['fecha_hora_propuesta']) ?></dd></div><?php endif; ?>
                    </dl>
                    <?php if (!empty($sol['descripcion'])): ?><p><?= htmlspecialchars(mb_strimwidth($sol['descripcion'], 0, 220, '…')) ?></p><?php endif; ?>
                    <?php if (es_docente()): ?><a href="solicitud-servicio-detalle.php?id=<?= (int)$sol['id_solicitud'] ?>">Ver y responder</a><?php endif; ?>
                </article>
            <?php endforeach; ?>
            <?php if (es_docente()): ?><p><a href="solicitudes-servicios.php">Ver todas las solicitudes</a></p><?php endif; ?>
        <?php endif; ?>
    </section>

    <section id="estudiantes" aria-labelledby="titulo-estudiantes">
        <header><h2 id="titulo-estudiantes">Estudiantes</h2><p>Consulta la cantidad de estudiantes inscriptos en tus cursos.</p></header>
        <?php if (!$cursos_estudiantes): ?>
            <p class="muted">Todavía no tenés cursos con estudiantes.</p>
        <?php else: ?>
            <?php foreach ($cursos_estudiantes as $curso): ?>
                <article>
                    <h3><?= htmlspecialchars($curso['titulo']) ?></h3>
                    <dl>
                        <div><dt>Estudiantes inscriptos</dt><dd><?= (int)$curso['inscriptos'] ?></dd></div>
                        <div><dt>Estudiantes activos</dt><dd><?= (int)$curso['activos'] ?></dd></div>
                        <div><dt>Finalizaron el curso</dt><dd><?= (int)$curso['completaron'] ?></dd></div>
                    </dl>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <section aria-labelledby="titulo-habilidades">
        <header><h2 id="titulo-habilidades">Habilidades y especialidades</h2><p>Información profesional que ayuda a los usuarios a conocer tu experiencia.</p></header>
        <?php if (!empty($perfil_profesional['habilidades'])): ?><p><strong>Habilidades:</strong> <?= nl2br(htmlspecialchars($perfil_profesional['habilidades'])) ?></p><?php else: ?><p class="muted">Todavía no cargaste habilidades.</p><?php endif; ?>
        <?php if (!empty($perfil_profesional['especialidades'])): ?><p><strong>Especialidades:</strong> <?= nl2br(htmlspecialchars($perfil_profesional['especialidades'])) ?></p><?php endif; ?>
        <?php if (es_docente()): ?><a href="editar-perfil-profesional.php">Editar habilidades</a><?php endif; ?>
    </section>

    <section id="perfil" aria-labelledby="titulo-perfil-profesional">
        <br>
        <header><h2 id="titulo-perfil-profesional">Perfil profesional</h2><p>Información visible para posibles clientes y estudiantes según la privacidad elegida.</p></header>
        <?php if (!$perfil_profesional || empty($perfil_profesional['titulo_profesional'])): ?>
            <div class="alert alert-warning">Tu perfil profesional está incompleto.</div>
        <?php else: ?>
            <h3><?= htmlspecialchars($perfil_profesional['titulo_profesional']) ?></h3>
            <p><?= nl2br(htmlspecialchars($perfil_profesional['presentacion'] ?? '')) ?></p>
            <dl>
                <?php if (!empty($perfil_profesional['ubicacion'])): ?><div><dt>Ubicación</dt><dd><?= htmlspecialchars($perfil_profesional['ubicacion']) ?></dd></div><?php endif; ?>
                <?php if (!empty($perfil_profesional['tiempo_respuesta'])): ?><div><dt>Tiempo de respuesta</dt><dd><?= htmlspecialchars($perfil_profesional['tiempo_respuesta']) ?></dd></div><?php endif; ?>
                <?php if (!empty($perfil_profesional['idiomas'])): ?><div><dt>Idiomas</dt><dd><?= htmlspecialchars($perfil_profesional['idiomas']) ?></dd></div><?php endif; ?>
                <div><dt>Valoración promedio</dt><dd><?= $valoracion !== false && $valoracion !== null ? htmlspecialchars((string)$valoracion) . ' de 5' : 'Sin valoraciones' ?></dd></div>
                <div><dt>Trabajos completados</dt><dd><?= $trabajos_completados ?></dd></div>
                <div><dt>Visibilidad</dt><dd><?= htmlspecialchars($perfil_profesional['visibilidad'] ?? 'Registrados') ?></dd></div>
            </dl>
        <?php endif; ?>
        <?php if (es_docente()): ?><a href="perfil-profesional.php?id=<?= $uid ?>">Ver perfil público</a> <a href="editar-perfil-profesional.php">Editar información</a><?php endif; ?>
    </section>

    <section id="valoraciones" aria-labelledby="titulo-valoraciones">
        <header><h2 id="titulo-valoraciones">Valoraciones recientes</h2><p>Opiniones recibidas en cursos y servicios.</p></header>
        <?php if (!$valoraciones_recientes): ?>
            <p class="muted">Todavía no recibiste valoraciones.</p>
        <?php else: ?>
            <?php foreach ($valoraciones_recientes as $v): ?>
                <article>
                    <header>
                        <h3><?= htmlspecialchars(trim($v['nombre'] . ' ' . $v['apellido'])) ?></h3>
                        <p><?= (int)$v['puntuacion'] ?> de 5</p>
                        <?php if (!empty($v['fecha_valoracion'])): ?><time datetime="<?= htmlspecialchars($v['fecha_valoracion']) ?>"><?= date('d/m/Y', strtotime($v['fecha_valoracion'])) ?></time><?php endif; ?>
                    </header>
                    <?php if (!empty($v['comentario'])): ?><p><?= nl2br(htmlspecialchars($v['comentario'])) ?></p><?php endif; ?>
                    <p>Publicación: <?= htmlspecialchars($v['publicacion']) ?></p>
                </article>
            <?php endforeach; ?>
        <?php endif; ?>
    </section>

    <br>
    <section aria-labelledby="titulo-configuracion">
        <h2 id="titulo-configuracion">Configuración del espacio de proveedor</h2>
        <br>
        <nav aria-label="Configuración del proveedor">
            <ul>
                <?php if (es_docente()): ?>
                    <li><a href="editar-perfil-profesional.php">Datos profesionales</a></li>
                    <li><a href="solicitudes-servicios.php">Gestión de solicitudes</a></li>
                <?php endif; ?>
                <li><a href="usuario.php">Seguridad y datos de la cuenta</a></li>
            </ul>
        </nav>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

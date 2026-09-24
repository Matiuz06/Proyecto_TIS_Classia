<?php

/**
 * Responsabilidad: Vista previa completa de una publicación.
 */

require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');
require_once '../config/database.php';
require_once '../php/publicaciones/contenido_curso.php';
require_once '../php/solicitudes/plantillas_servicio.php';

$uid = (int)usuario_actual()['id_usuario'];
$id = (int)($_GET['id'] ?? 0);
$sql = "SELECT p.*,c.nombre_categoria,u.id_usuario autor_id,u.nombre autor_nombre,u.apellido autor_apellido FROM publicaciones p JOIN categorias c ON c.id_categoria=p.id_categoria JOIN usuarios u ON u.id_usuario=p.id_usuario WHERE p.id_publicacion=:id" . (es_admin() ? '' : ' AND p.id_usuario=:u');
$st = $pdo->prepare($sql);
$params = ['id' => $id];
if (!es_admin()) $params['u'] = $uid;
$st->execute($params);
$p = $st->fetch() ?: null;
$contenido = $p && $p['tipo'] === 'Curso' ? obtener_contenido_curso($pdo, $id) : [];
$plantilla = $p && $p['tipo'] === 'Servicio' ? obtener_plantilla_servicio($p['tipo_servicio'] ?? null) : null;

$total_clases = 0;
$total_recursos = 0;
foreach ($contenido as $modulo) {
    $total_clases += count($modulo['unidades']);
    foreach ($modulo['unidades'] as $unidad) {
        $total_recursos += count($unidad['recursos']);
    }
}

$title = 'Vista previa';
$description = 'Vista previa de publicacion.';
$cssPrefix = '..';
$jsPrefix = '..';
$bodyClass = 'publication-preview-page';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main class="publication-preview" aria-labelledby="publication-preview-title">
  <?php if (!$p): ?>
    <div class="alert alert-danger">Publicacion no encontrada.</div>
  <?php else: ?>
    <section class="publication-preview__toolbar" aria-label="Modo vista previa">
      <div>
        <strong>Vista previa del proveedor</strong>
        <span>Estado actual: <?= htmlspecialchars($p['estado']) ?></span>
      </div>
      <nav class="publication-preview__actions" aria-label="Acciones de la vista previa">
        <a class="btn btn-secondary" href="editar-publicacion.php?id=<?= $id ?>">Volver a editar</a>
        <?php if ($p['tipo'] === 'Curso'): ?>
          <a class="btn btn-secondary" href="gestionar-contenido-curso.php?id=<?= $id ?>">Modulos y contenido</a>
        <?php endif; ?>
        <a class="btn btn-secondary" href="panel-proveedor.php">Volver al panel</a>
      </nav>
    </section>

    <article class="publication-preview__detail">
      <header class="publication-preview__hero">
        <div class="publication-preview__intro">
          <p class="publication-preview__kicker"><?= htmlspecialchars($p['nombre_categoria']) ?> &middot; <?= htmlspecialchars($p['tipo']) ?></p>
          <h1 id="publication-preview-title"><?= htmlspecialchars($p['titulo']) ?></h1>
          <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
          <div class="publication-preview__provider">
            <span>Impartido por</span>
            <strong><?= htmlspecialchars(trim(($p['autor_nombre'] ?? '') . ' ' . ($p['autor_apellido'] ?? ''))) ?></strong>
            <?php if (!empty($p['autor_id'])): ?>
              <a href="proveedor.php?id=<?= (int)$p['autor_id'] ?>">Ver perfil profesional</a>
            <?php endif; ?>
          </div>
        </div>

        <aside class="publication-preview__summary" aria-label="Resumen de la publicacion">
          <figure class="publication-preview__cover">
            <?php if (!empty($p['imagen'])): ?>
              <img src="../<?= htmlspecialchars($p['imagen']) ?>" alt="Portada de <?= htmlspecialchars($p['titulo']) ?>">
            <?php else: ?>
              <div class="publication-preview__cover-placeholder" aria-label="Sin imagen de portada">Classia</div>
            <?php endif; ?>
          </figure>
          <dl class="publication-preview__meta">
            <div><dt>Precio</dt><dd>$<?= number_format((float)$p['precio'], 2, ',', '.') ?></dd></div>
            <div><dt>Modalidad</dt><dd><?= htmlspecialchars($p['modalidad'] ?: 'A definir') ?></dd></div>
            <div><dt>Duracion</dt><dd><?= !empty($p['duracion_horas']) ? ((int)$p['duracion_horas'] . ' h') : 'A definir' ?></dd></div>
            <div><dt>Cupos</dt><dd><?= $p['cupos'] !== null ? (int)$p['cupos'] : 'Sin limite' ?></dd></div>
            <?php if (!empty($p['nivel_experiencia'])): ?><div><dt>Nivel</dt><dd><?= htmlspecialchars($p['nivel_experiencia']) ?></dd></div><?php endif; ?>
            <div><dt>Estado</dt><dd><?= htmlspecialchars($p['estado']) ?></dd></div>
            <div class="publication-preview__meta-wide"><dt>Disponibilidad</dt><dd><?= htmlspecialchars($p['disponibilidad'] ?: 'A coordinar') ?></dd></div>
          </dl>
        </aside>
      </header>

      <div class="publication-preview__content">
        <div class="publication-preview__main">
          <section class="publication-preview__section">
            <h2>Sobre este <?= $p['tipo'] === 'Curso' ? 'curso' : 'servicio' ?></h2>
            <p><?= nl2br(htmlspecialchars($p['descripcion'])) ?></p>
          </section>

          <?php if ($p['tipo'] === 'Curso'): ?>
            <section class="publication-preview__section publication-preview__program">
              <div class="publication-preview__section-head">
                <div>
                  <h2>Programa del curso</h2>
                  <p><?= count($contenido) ?> modulos &middot; <?= $total_clases ?> clases &middot; <?= $total_recursos ?> recursos</p>
                </div>
              </div>

              <?php if (empty($contenido)): ?>
                <div class="publication-preview__empty">Este curso todavia no tiene modulos cargados.</div>
              <?php else: ?>
                <?php foreach ($contenido as $m): ?>
                  <?php
                    $moduleResources = 0;
                    foreach ($m['unidades'] as $unidad) $moduleResources += count($unidad['recursos']);
                  ?>
                  <details class="publication-preview__module" open>
                    <summary>
                      <span><?= (int)$m['orden'] ?>. <?= htmlspecialchars($m['titulo']) ?></span>
                      <small><?= count($m['unidades']) ?> clases &middot; <?= $moduleResources ?> recursos</small>
                    </summary>
                    <?php if (!empty($m['descripcion'])): ?><p><?= nl2br(htmlspecialchars($m['descripcion'])) ?></p><?php endif; ?>
                    <?php if (empty($m['unidades'])): ?>
                      <div class="publication-preview__empty">Este modulo todavia no tiene clases.</div>
                    <?php else: ?>
                      <ol class="publication-preview__lesson-list">
                        <?php foreach ($m['unidades'] as $u): ?>
                          <li>
                            <span><?= (int)$m['orden'] ?>.<?= (int)$u['orden'] ?> <?= htmlspecialchars($u['titulo']) ?></span>
                            <small><?= count($u['recursos']) ?> recursos</small>
                          </li>
                        <?php endforeach; ?>
                      </ol>
                    <?php endif; ?>
                  </details>
                <?php endforeach; ?>
              <?php endif; ?>
            </section>
          <?php else: ?>
            <section class="publication-preview__section publication-preview__service">
              <h2>Informacion del servicio</h2>
              <dl class="publication-preview__service-list">
                <div><dt>Plantilla de solicitud</dt><dd><?= htmlspecialchars($plantilla['nombre'] ?? 'Sin configurar') ?></dd></div>
                <div><dt>Modalidad</dt><dd><?= htmlspecialchars($p['modalidad'] ?: 'A definir') ?></dd></div>
                <div><dt>Disponibilidad</dt><dd><?= htmlspecialchars($p['disponibilidad'] ?: 'A coordinar') ?></dd></div>
              </dl>
              <?php if ($plantilla && !empty($plantilla['campos'])): ?>
                <h3>Datos que solicitara el cliente</h3>
                <ul class="publication-preview__field-list">
                  <?php foreach ($plantilla['campos'] as $c): ?><li><?= htmlspecialchars($c['label']) ?></li><?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </section>
          <?php endif; ?>
        </div>

        <aside class="publication-preview__side">
          <section class="publication-preview__section">
            <h2>Resumen</h2>
            <dl class="publication-preview__side-list">
              <div><dt>Tipo</dt><dd><?= htmlspecialchars($p['tipo']) ?></dd></div>
              <div><dt>Categoria</dt><dd><?= htmlspecialchars($p['nombre_categoria']) ?></dd></div>
              <div><dt>Estado</dt><dd><?= htmlspecialchars($p['estado']) ?></dd></div>
              <?php if ($p['tipo'] === 'Curso'): ?><div><dt>Contenido</dt><dd><?= count($contenido) ?> modulos</dd></div><?php endif; ?>
            </dl>
          </section>
        </aside>
      </div>
    </article>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

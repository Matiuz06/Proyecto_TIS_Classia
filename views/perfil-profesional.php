<?php
require_once '../php/auth/roles.php';
requerir_autenticacion('login.php');
require_once '../php/usuarios/perfil_profesional.php';
$id_docente=(int)($_GET['id'] ?? 0); $perfil=obtener_perfil_profesional($pdo,$id_docente); $actual=usuario_actual();
$permitido=false;
if($perfil){
  if(es_admin() || (int)$actual['id_usuario']===$id_docente || ($perfil['visibilidad'] ?? 'Registrados')==='Registrados') $permitido=true;
  elseif(usuario_relacionado_con_docente($pdo,(int)$actual['id_usuario'],$id_docente)) $permitido=true;
}
$title=$perfil?trim($perfil['nombre'].' '.$perfil['apellido']):'Perfil profesional'; $description='Perfil profesional de proveedor en Classia.'; $cssPrefix='..'; $jsPrefix='..'; $activePage='catalogo'; include '../includes/header.php';
?>
<main>
<?php if(!$perfil): ?><div class="alert alert-danger">El perfil profesional no existe.</div>
<?php elseif(!$permitido): ?><div class="alert alert-danger"><h1>Perfil privado</h1><p>Este profesional eligió mostrar su perfil únicamente a usuarios que ya tienen una relación de curso o servicio con él.</p></div>
<?php else: ?>
<article class="professional-profile">
<header class="professional-profile-header">
<?php if($perfil['foto_perfil']): ?><img class="profile-avatar-large" src="../<?= htmlspecialchars($perfil['foto_perfil']) ?>" alt="Foto de <?= htmlspecialchars($perfil['nombre']) ?>"><?php endif; ?>
<div><h1><?= htmlspecialchars($perfil['nombre'].' '.$perfil['apellido']) ?></h1><p class="lead"><?= htmlspecialchars($perfil['titulo_profesional'] ?: 'Docente / Proveedor') ?></p><?php if($perfil['ubicacion']): ?><p><?= htmlspecialchars($perfil['ubicacion']) ?></p><?php endif; ?></div>
</header>
<?php if($perfil['presentacion']): ?><section><h2>Presentación</h2><p><?= nl2br(htmlspecialchars($perfil['presentacion'])) ?></p></section><?php endif; ?>
<div class="profile-columns">
<?php if($perfil['experiencia']): ?><section><h2>Experiencia</h2><p><?= nl2br(htmlspecialchars($perfil['experiencia'])) ?></p></section><?php endif; ?>
<?php if($perfil['formacion']): ?><section><h2>Formación</h2><p><?= nl2br(htmlspecialchars($perfil['formacion'])) ?></p></section><?php endif; ?>
<?php if($perfil['certificaciones']): ?><section><h2>Certificaciones</h2><p><?= nl2br(htmlspecialchars($perfil['certificaciones'])) ?></p></section><?php endif; ?>
<?php if($perfil['especialidades']): ?><section><h2>Especialidades</h2><p><?= nl2br(htmlspecialchars($perfil['especialidades'])) ?></p></section><?php endif; ?>
<?php if($perfil['habilidades']): ?><section><h2>Habilidades</h2><p><?= nl2br(htmlspecialchars($perfil['habilidades'])) ?></p></section><?php endif; ?>
</div>
<dl class="profile-meta"><?php if($perfil['idiomas']): ?><div><dt>Idiomas</dt><dd><?= htmlspecialchars($perfil['idiomas']) ?></dd></div><?php endif; ?><?php if($perfil['modalidad_trabajo']): ?><div><dt>Modalidad</dt><dd><?= htmlspecialchars($perfil['modalidad_trabajo']) ?></dd></div><?php endif; ?><?php if($perfil['tiempo_respuesta']): ?><div><dt>Tiempo de respuesta</dt><dd><?= htmlspecialchars($perfil['tiempo_respuesta']) ?></dd></div><?php endif; ?><div><dt>Miembro desde</dt><dd><?= date('m/Y',strtotime($perfil['fecha_registro'])) ?></dd></div></dl>
<p><?php if($perfil['portfolio_url']): ?><a class="btn" target="_blank" rel="noopener" href="<?= htmlspecialchars($perfil['portfolio_url']) ?>">Portfolio</a><?php endif; ?> <?php if($perfil['linkedin_url']): ?><a class="btn" target="_blank" rel="noopener" href="<?= htmlspecialchars($perfil['linkedin_url']) ?>">LinkedIn</a><?php endif; ?></p>
<?php if((int)$actual['id_usuario']===$id_docente): ?><a class="btn btn-primary-action" href="editar-perfil-profesional.php">Editar perfil</a><?php endif; ?>
</article>
<?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

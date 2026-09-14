<?php
$publicacion=null; $categorias=[];
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE,'usuario.php');
require_once '../php/publicaciones/editar_publicacion.php';
$title='Editar publicación'; $description='Modificación de cursos o servicios en Classia.'; $cssPrefix='..'; $jsPrefix='..'; $bodyClass='auth-page'; $activePage='cuenta';
include '../includes/header.php';
?>
<main class="auth-shell">
  <section class="auth-intro"><h1>Modificá tu publicación</h1><p>Actualizá la propuesta sin perder su historial, contrataciones ni contenido asociado.</p></section>
  <section class="auth-card provider-editor">
    <h2>Editar propuesta</h2>
    <?php if(!empty($errores)): ?><div class="alert alert-danger"><ul><?php foreach($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <?php if($publicacion): ?>
    <div class="provider-toolbar"><a class="btn" href="vista-previa-publicacion.php?id=<?= (int)$publicacion['id_publicacion'] ?>">Vista previa</a><?php if($publicacion['tipo']==='Curso'): ?><a class="btn" href="gestionar-contenido-curso.php?id=<?= (int)$publicacion['id_publicacion'] ?>">Módulos y contenido</a><?php endif; ?></div>
    <form action="editar-publicacion.php?id=<?= (int)$publicacion['id_publicacion'] ?>" method="POST" enctype="multipart/form-data" class="provider-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>"><input type="hidden" name="id_publicacion" value="<?= (int)$publicacion['id_publicacion'] ?>"><input type="hidden" name="tipo" value="<?= htmlspecialchars($publicacion['tipo']) ?>">
      <details open><summary>Información principal</summary><div class="form-grid">
        <label>Título<input name="titulo" maxlength="200" required value="<?= htmlspecialchars($_POST['titulo'] ?? $publicacion['titulo']) ?>"></label>
        <label>Tipo<input value="<?= htmlspecialchars($publicacion['tipo']) ?>" readonly></label>
        <label>Precio ($ UYU)<input type="number" step="0.01" min="1" name="precio" required value="<?= htmlspecialchars($_POST['precio'] ?? $publicacion['precio']) ?>"></label>
        <?php $cm=$_POST['modalidad'] ?? ($publicacion['modalidad'] ?? ''); ?><label>Modalidad<select name="modalidad"><option value="">Sin especificar</option><?php foreach(['virtual'=>'Virtual','presencial'=>'Presencial','hibrida'=>'Híbrida','producto-entregable'=>'Producto entregable'] as $v=>$e): ?><option value="<?= $v ?>" <?= $cm===$v?'selected':'' ?>><?= $e ?></option><?php endforeach; ?></select></label>
        <?php $cn=$_POST['nivel_experiencia'] ?? ($publicacion['nivel_experiencia'] ?? ''); ?><label>Nivel de experiencia<select name="nivel_experiencia"><option value="">Sin especificar</option><?php foreach(['inicial'=>'Inicial','intermedio'=>'Intermedio','avanzado'=>'Avanzado','depende-categoria'=>'Depende de la categoría'] as $v=>$e): ?><option value="<?= $v ?>" <?= $cn===$v?'selected':'' ?>><?= $e ?></option><?php endforeach; ?></select></label>
        <label>Duración aproximada (horas)<input type="number" min="1" max="10000" name="duracion_horas" value="<?= htmlspecialchars($_POST['duracion_horas'] ?? ($publicacion['duracion_horas'] ?? '')) ?>"></label>
        <label>Cupos<input type="number" min="1" name="cupos" value="<?= htmlspecialchars($_POST['cupos'] ?? ($publicacion['cupos'] ?? '')) ?>"></label>
        <label>Disponibilidad<input name="disponibilidad" value="<?= htmlspecialchars($_POST['disponibilidad'] ?? ($publicacion['disponibilidad'] ?? '')) ?>"></label>
        <label>Estado<select name="estado"><?php $ce=$_POST['estado'] ?? $publicacion['estado']; foreach(['Activo','Pausado','Inactivo','Eliminado'] as $e): ?><option value="<?= $e ?>" <?= $ce===$e?'selected':'' ?>><?= $e ?></option><?php endforeach; ?></select></label>
        <label class="form-grid-full">Descripción<textarea name="descripcion" rows="5" required><?= htmlspecialchars($_POST['descripcion'] ?? $publicacion['descripcion']) ?></textarea></label>
      </div></details>
      <details open><summary>Categoría</summary><div class="form-grid">
        <?php $cc=(int)($_POST['id_categoria'] ?? $publicacion['id_categoria']); ?><label>Categoría existente<select name="id_categoria"><option value="">-- Seleccioná --</option><?php foreach($categorias as $cat): ?><option value="<?= (int)$cat['id_categoria'] ?>" <?= $cc===(int)$cat['id_categoria']?'selected':'' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option><?php endforeach; ?></select></label>
        <label>Nueva categoría<input name="nueva_categoria" maxlength="100" value="<?= htmlspecialchars($_POST['nueva_categoria'] ?? '') ?>"></label>
        <label class="form-grid-full">Descripción de la nueva categoría<textarea name="descripcion_categoria" rows="2"><?= htmlspecialchars($_POST['descripcion_categoria'] ?? '') ?></textarea></label>
      </div></details>
      <details open><summary>Servicio y portada</summary><div class="form-grid">
        <?php $cts=$_POST['tipo_servicio'] ?? ($publicacion['tipo_servicio'] ?? ''); ?><label>Plantilla de servicio<select name="tipo_servicio" <?= $publicacion['tipo']!=='Servicio'?'disabled':'' ?>><option value="">Sin plantilla / contratación directa</option><?php foreach($plantillas_servicios as $clave=>$plantilla): ?><option value="<?= htmlspecialchars($clave) ?>" <?= $cts===$clave?'selected':'' ?>><?= htmlspecialchars($plantilla['nombre']) ?></option><?php endforeach; ?></select></label>
        <label>Imagen<?php if(!empty($publicacion['imagen'])): ?><img src="../<?= htmlspecialchars($publicacion['imagen']) ?>" class="pub-preview-img" alt="Imagen actual"><span><input type="checkbox" name="eliminar_imagen" value="1"> Quitar imagen actual</span><?php endif; ?><input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif"></label>
      </div></details>
      <button type="submit">Actualizar cambios</button>
    </form>
    <?php endif; ?>
    <p class="auth-links"><a href="panel-proveedor.php">Volver al panel del proveedor</a></p>
  </section>
</main>
<?php include '../includes/footer.php'; ?>

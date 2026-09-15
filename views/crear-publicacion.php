<?php
$categorias = [];
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE, 'usuario.php');
require_once '../php/publicaciones/crear_publicacion.php';

$title='Crear publicación';
$description='Publicación de nuevos cursos o servicios en Classia.';
$cssPrefix='..'; $jsPrefix='..'; $bodyClass='auth-page'; $activePage='cuenta';
include '../includes/header.php';
?>
<main class="auth-shell provider-form-shell">
  <section class="auth-intro" aria-labelledby="intro-crear">
    <h1 id="intro-crear">Publicá un curso o servicio</h1>
    <p>Creá y compartí tus propuestas formativas o servicios profesionales con la comunidad de Classia.</p>
  </section>
  <section class="auth-card provider-editor" aria-labelledby="titulo-crear">
    <h2 id="titulo-crear">Nueva publicación</h2>
    <p class="muted">Ingresá los detalles de tu propuesta educativa.</p>
    <?php if (!empty($errores)): ?><div class="alert alert-danger"><ul><?php foreach($errores as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form action="crear-publicacion.php" method="POST" enctype="multipart/form-data" class="provider-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
      <details open><summary>Información principal</summary><div class="form-grid">
        <label>Título<input type="text" name="titulo" maxlength="200" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>"></label>
        <label>Tipo<select name="tipo" required><option value="">-- Seleccioná --</option><option value="Curso" <?= ($_POST['tipo']??'')==='Curso'?'selected':'' ?>>Curso</option><option value="Servicio" <?= ($_POST['tipo']??'')==='Servicio'?'selected':'' ?>>Servicio</option></select></label>
        <label>Precio ($ UYU)<input type="number" step="0.01" min="1" name="precio" required value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>"></label>
        <label>Modalidad<select name="modalidad"><option value="">Sin especificar</option><?php foreach(['virtual'=>'Virtual','presencial'=>'Presencial','hibrida'=>'Híbrida','producto-entregable'=>'Producto entregable'] as $v=>$e): ?><option value="<?= $v ?>" <?= ($_POST['modalidad']??'')===$v?'selected':'' ?>><?= $e ?></option><?php endforeach; ?></select></label>
        <label>Nivel de experiencia<select name="nivel_experiencia"><option value="">Sin especificar</option><?php foreach(['inicial'=>'Inicial','intermedio'=>'Intermedio','avanzado'=>'Avanzado','depende-categoria'=>'Depende de la categoría'] as $v=>$e): ?><option value="<?= $v ?>" <?= ($_POST['nivel_experiencia']??'')===$v?'selected':'' ?>><?= $e ?></option><?php endforeach; ?></select></label>
        <label>Duración aproximada (horas)<input type="number" min="1" max="10000" name="duracion_horas" value="<?= htmlspecialchars($_POST['duracion_horas'] ?? '') ?>"></label>
        <label>Cupos (opcional)<input type="number" min="1" name="cupos" value="<?= htmlspecialchars($_POST['cupos'] ?? '') ?>"></label>
        <label>Disponibilidad<input type="text" name="disponibilidad" value="<?= htmlspecialchars($_POST['disponibilidad'] ?? '') ?>" placeholder="Ej: lunes y miércoles de 18 a 21"></label>
        <label class="form-grid-full">Descripción<textarea name="descripcion" rows="5" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea></label>
      </div></details>
      <details open><summary>Categoría</summary><div class="form-grid">
        <label>Categoría existente<select name="id_categoria"><option value="">-- Seleccioná --</option><?php foreach($categorias as $cat): ?><option value="<?= (int)$cat['id_categoria'] ?>" <?= (int)($_POST['id_categoria']??0)===(int)$cat['id_categoria']?'selected':'' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option><?php endforeach; ?></select></label>
        <label>Nueva categoría<input name="nueva_categoria" maxlength="100" value="<?= htmlspecialchars($_POST['nueva_categoria'] ?? '') ?>" placeholder="Solo si ninguna categoría aplica"></label>
        <label class="form-grid-full">Descripción de la nueva categoría<textarea name="descripcion_categoria" rows="2"><?= htmlspecialchars($_POST['descripcion_categoria'] ?? '') ?></textarea></label>
      </div></details>
      <details open><summary>Servicio y portada</summary><div class="form-grid">
        <label>Plantilla de servicio<select name="tipo_servicio"><option value="">Sin plantilla / contratación directa</option><?php foreach($plantillas_servicios as $clave=>$plantilla): ?><option value="<?= htmlspecialchars($clave) ?>" <?= ($_POST['tipo_servicio']??'')===$clave?'selected':'' ?>><?= htmlspecialchars($plantilla['nombre']) ?></option><?php endforeach; ?></select></label>
        <label>Imagen de portada<input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif"><small>JPG, PNG, WEBP o GIF. Máximo 5MB.</small></label>
      </div></details>
      <div class="form-actions">
        <button class="btn btn-primary-action" type="submit">Guardar publicación</button>
      </div>
    </form>
    <p class="auth-links"><a href="panel-proveedor.php">Volver al panel del proveedor</a></p>
  </section>
</main>
<?php include '../includes/footer.php'; ?>

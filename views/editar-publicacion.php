<?php
<<<<<<< HEAD
$publicacion=null; $categorias=[];
=======
$publicacion = null;
$categorias = [];
>>>>>>> 187b1cca0ceb4e5a72ed2c4a005b042108a51e1e
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE,'usuario.php');
require_once '../php/publicaciones/editar_publicacion.php';
$title='Editar publicación'; $description='Modificación de cursos o servicios en Classia.'; $cssPrefix='..'; $jsPrefix='..'; $bodyClass='auth-page'; $activePage='cuenta';
include '../includes/header.php';
?>
<<<<<<< HEAD
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
=======

  <main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-editar">
      <h1 id="intro-editar">Modificá tu publicación</h1>
      <p>
        Actualizá los datos de tus cursos o servicios, o gestioná su estado de visibilidad.
      </p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-editar">
      <h2 id="titulo-editar">Editar propuesta</h2>

      <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($publicacion): ?>
        <form action="editar-publicacion.php?id=<?php echo (int)$publicacion['id_publicacion']; ?>" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
          <input type="hidden" name="id_publicacion" value="<?php echo (int)$publicacion['id_publicacion']; ?>">

          <p>
            <label for="titulo">Título de la publicación</label>
            <input
              type="text"
              id="titulo"
              name="titulo"
              required
              value="<?php echo htmlspecialchars($_POST['titulo'] ?? $publicacion['titulo']); ?>" />
          </p>

          <p>
            <label for="tipo">Tipo de propuesta</label>
            <select id="tipo" name="tipo" class="form-select-full" required>
              <?php $current_tipo = $_POST['tipo'] ?? $publicacion['tipo']; ?>
              <option value="Curso" <?php echo ($current_tipo === 'Curso') ? 'selected' : ''; ?>>Curso</option>
              <option value="Servicio" <?php echo ($current_tipo === 'Servicio') ? 'selected' : ''; ?>>Servicio</option>
            </select>
          </p>

          <p>
            <label for="id_categoria">Categoría</label>
            <select id="id_categoria" name="id_categoria" class="form-select-full" required>
              <?php $current_cat = (int)($_POST['id_categoria'] ?? $publicacion['id_categoria']); ?>
              <?php foreach ($categorias as $cat): ?>
                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($current_cat === (int)$cat['id_categoria']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </p>

          <p>
            <label for="precio">Precio ($ UYU)</label>
            <input
              type="number"
              step="0.01"
              min="1"
              id="precio"
              name="precio"
              required
              value="<?php echo htmlspecialchars($_POST['precio'] ?? $publicacion['precio']); ?>" />
          </p>

          <?php $current_modalidad = $_POST['modalidad'] ?? ($publicacion['modalidad'] ?? ''); ?>
          <p>
            <label for="modalidad">Modalidad</label>
            <select id="modalidad" name="modalidad" class="form-select-full">
              <option value="">Sin especificar</option>
              <?php foreach (['virtual' => 'Virtual', 'presencial' => 'Presencial', 'hibrida' => 'Híbrida', 'producto-entregable' => 'Producto entregable'] as $valor => $etiqueta): ?>
                <option value="<?= $valor ?>" <?= $current_modalidad === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
              <?php endforeach; ?>
            </select>
          </p>

          <?php $current_nivel = $_POST['nivel_experiencia'] ?? ($publicacion['nivel_experiencia'] ?? ''); ?>
          <p>
            <label for="nivel_experiencia">Nivel de experiencia</label>
            <select id="nivel_experiencia" name="nivel_experiencia" class="form-select-full">
              <option value="">Sin especificar</option>
              <?php foreach (['inicial' => 'Inicial', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado', 'depende-categoria' => 'Depende de la categoría'] as $valor => $etiqueta): ?>
                <option value="<?= $valor ?>" <?= $current_nivel === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
              <?php endforeach; ?>
            </select>
          </p>

          <p>
            <label for="duracion_horas">Duración aproximada (horas)</label>
            <input type="number" min="1" max="10000" id="duracion_horas" name="duracion_horas" value="<?= htmlspecialchars($_POST['duracion_horas'] ?? ($publicacion['duracion_horas'] ?? '')) ?>" placeholder="Opcional" />
          </p>

          <p>
            <label for="imagen">Imagen de portada / vista previa</label>
            <?php if (!empty($publicacion['imagen'])): ?>
              <div class="current-image-preview">
                <img src="../<?php echo htmlspecialchars($publicacion['imagen']); ?>" alt="Imagen actual" class="pub-preview-img" />
                <label class="checkbox-label">
                  <input type="checkbox" name="eliminar_imagen" value="1" />
                  Quitar imagen actual
                </label>
              </div>
            <?php endif; ?>
            <input
              type="file"
              id="imagen"
              name="imagen"
              accept="image/jpeg,image/png,image/webp,image/gif"
              class="form-file-input" />
            <small class="muted">Subir una nueva imagen reemplazará la actual. Formatos: JPG, PNG, WEBP, GIF (máx 5MB).</small>
          </p>

          <p>
            <label for="estado">Estado de la publicación (Baja Lógica / Estado)</label>
            <select id="estado" name="estado" class="form-select-full" required>
              <?php $current_estado = $_POST['estado'] ?? $publicacion['estado']; ?>
              <option value="Activo" <?php echo ($current_estado === 'Activo') ? 'selected' : ''; ?>>Activo</option>
              <option value="Pausado" <?php echo ($current_estado === 'Pausado') ? 'selected' : ''; ?>>Pausado</option>
              <option value="Inactivo" <?php echo ($current_estado === 'Inactivo') ? 'selected' : ''; ?>>Inactivo (Baja Lógica)</option>
            </select>
          </p>

          <p>
            <label for="descripcion">Descripción detallada</label>
            <textarea
              id="descripcion"
              name="descripcion"
              rows="5"
              class="form-textarea-full"
              required><?php echo htmlspecialchars($_POST['descripcion'] ?? $publicacion['descripcion']); ?></textarea>
          </p>

          <button type="submit">Actualizar cambios</button>
        </form>
      <?php endif; ?>

      <p class="auth-links">
        <a href="panel-proveedor.php">Volver al panel del proveedor</a>
      </p>
    </section>
  </main>

>>>>>>> 187b1cca0ceb4e5a72ed2c4a005b042108a51e1e
<?php include '../includes/footer.php'; ?>

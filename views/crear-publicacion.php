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
<<<<<<< HEAD
<main class="auth-shell">
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
      <button type="submit">Guardar publicación</button>
    </form>
    <p class="auth-links"><a href="panel-proveedor.php">Volver al panel del proveedor</a></p>
  </section>
</main>
=======

  <main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-crear">
      <h1 id="intro-crear">Publicá un curso o servicio</h1>
      <p>
        Creá y compartí tus propuestas formativas o servicios profesionales con la comunidad de Classia.
      </p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-crear">
      <h2 id="titulo-crear">Nueva publicación</h2>
      <p class="muted">Ingresá los detalles de tu propuesta educativa.</p>

      <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="crear-publicacion.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">

        <p>
          <label for="titulo">Título de la publicación</label>
          <input
            type="text"
            id="titulo"
            name="titulo"
            required
            value="<?php echo htmlspecialchars($_POST['titulo'] ?? ''); ?>"
            placeholder="Ej: Curso Completo de PHP y MySQL" />
        </p>

        <p>
          <label for="tipo">Tipo de propuesta</label>
          <select id="tipo" name="tipo" class="form-select-full" required>
            <option value="">-- Seleccioná un tipo --</option>
            <option value="Curso" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'Curso') ? 'selected' : ''; ?>>Curso</option>
            <option value="Servicio" <?php echo (isset($_POST['tipo']) && $_POST['tipo'] === 'Servicio') ? 'selected' : ''; ?>>Servicio</option>
          </select>
        </p>

        <p>
          <label for="id_categoria">Categoría</label>
          <select id="id_categoria" name="id_categoria" class="form-select-full" required>
            <option value="">-- Seleccioná una categoría --</option>
            <?php foreach ($categorias as $cat): ?>
              <option value="<?php echo $cat['id_categoria']; ?>" <?php echo (isset($_POST['id_categoria']) && (int)$_POST['id_categoria'] === (int)$cat['id_categoria']) ? 'selected' : ''; ?>>
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
            value="<?php echo htmlspecialchars($_POST['precio'] ?? ''); ?>"
            placeholder="Ej: 1500.00" />
        </p>

        <p>
          <label for="modalidad">Modalidad</label>
          <select id="modalidad" name="modalidad" class="form-select-full">
            <option value="">Sin especificar</option>
            <?php foreach (['virtual' => 'Virtual', 'presencial' => 'Presencial', 'hibrida' => 'Híbrida', 'producto-entregable' => 'Producto entregable'] as $valor => $etiqueta): ?>
              <option value="<?= $valor ?>" <?= ($_POST['modalidad'] ?? '') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
            <?php endforeach; ?>
          </select>
        </p>

        <p>
          <label for="nivel_experiencia">Nivel de experiencia</label>
          <select id="nivel_experiencia" name="nivel_experiencia" class="form-select-full">
            <option value="">Sin especificar</option>
            <?php foreach (['inicial' => 'Inicial', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado', 'depende-categoria' => 'Depende de la categoría'] as $valor => $etiqueta): ?>
              <option value="<?= $valor ?>" <?= ($_POST['nivel_experiencia'] ?? '') === $valor ? 'selected' : '' ?>><?= $etiqueta ?></option>
            <?php endforeach; ?>
          </select>
        </p>

        <p>
          <label for="duracion_horas">Duración aproximada (horas)</label>
          <input type="number" min="1" max="10000" id="duracion_horas" name="duracion_horas" value="<?= htmlspecialchars($_POST['duracion_horas'] ?? '') ?>" placeholder="Opcional" />
        </p>

        <p>
          <label for="imagen">Imagen de portada / vista previa (opcional)</label>
          <input
            type="file"
            id="imagen"
            name="imagen"
            accept="image/jpeg,image/png,image/webp,image/gif"
            class="form-file-input" />
          <small class="muted">Formatos permitidos: JPG, PNG, WEBP, GIF. Máximo 5MB.</small>
        </p>

        <p>
          <label for="descripcion">Descripción detallada</label>
          <textarea
            id="descripcion"
            name="descripcion"
            rows="5"
            class="form-textarea-full"
            required
            placeholder="Describí los contenidos, requerimientos y público objetivo..."><?php echo htmlspecialchars($_POST['descripcion'] ?? ''); ?></textarea>
        </p>

        <button type="submit">Guardar publicación</button>
      </form>

      <p class="auth-links">
        <a href="panel-proveedor.php">Volver al panel del proveedor</a>
      </p>
    </section>
  </main>

>>>>>>> 187b1cca0ceb4e5a72ed2c4a005b042108a51e1e
<?php include '../includes/footer.php'; ?>

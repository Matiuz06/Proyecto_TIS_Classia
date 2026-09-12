<?php
$publicacion = null;
$categorias = [];
require_once '../php/auth/roles.php';

requerir_rol(ROL_DOCENTE, 'usuario.php');

require_once '../php/publicaciones/editar_publicacion.php';
require_once '../php/publicaciones/obtener_publicaciones.php';

$title       = 'Editar publicación';
$description = 'Modificación de cursos o servicios en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';

$bodyClass   = 'auth-page';
$activePage  = 'cuenta';

include '../includes/header.php';
?>

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

<?php include '../includes/footer.php'; ?>

<?php
require_once '../php/publicaciones/editar_publicacion.php';
require_once '../php/publicaciones/obtener_publicaciones.php';

$title       = 'Editar publicación';
$description = 'Modificación de cursos o servicios en Classia.';
$cssPrefix   = '..';
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
        <form action="editar-publicacion.php?id=<?php echo (int)$publicacion['id_publicacion']; ?>" method="POST">
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

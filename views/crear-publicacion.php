<?php
require_once '../php/publicaciones/crear_publicacion.php';
require_once '../php/publicaciones/obtener_publicaciones.php';

$title       = 'Crear publicación';
$description = 'Publicación de nuevos cursos o servicios en Classia.';
$cssPrefix   = '..';
$bodyClass   = 'auth-page';
$activePage  = 'cuenta';

include '../includes/header.php';
?>

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

      <form action="crear-publicacion.php" method="POST">
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

<?php include '../includes/footer.php'; ?>

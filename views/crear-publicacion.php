<?php

/**
 * Responsabilidad: Formulario de alta para nuevos cursos y servicios.
 */

$categorias = [];
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE, 'usuario.php');
require_once '../php/publicaciones/crear_publicacion.php';

$title = 'Crear publicacion';
$description = 'Publicacion de nuevos cursos o servicios en Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$bodyClass = 'publication-editor-page';
$activePage = 'cuenta';
include '../includes/header.php';
?>
<main class="publication-editor" aria-labelledby="publication-editor-title">
  <header class="publication-editor__header">
    <div>
      <p class="publication-editor__eyebrow">Editor de publicacion</p>
      <h1 id="publication-editor-title">Nueva publicacion</h1>
      <p>Ingresa los datos necesarios para publicar un curso o servicio en Classia.</p>
    </div>
    <div class="publication-editor__header-actions">
      <a class="btn btn-secondary" href="panel-proveedor.php">Volver al panel</a>
    </div>
  </header>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <div class="publication-editor__layout">
    <aside class="publication-editor__nav" aria-label="Secciones de la publicacion">
      <strong>Configuracion</strong>
      <a href="#informacion-principal">Informacion principal</a>
      <a href="#configuracion">Configuracion</a>
      <a href="#categoria">Categoria</a>
      <a href="#servicio">Servicio</a>
      <a href="#portada">Portada</a>
    </aside>

    <form action="crear-publicacion.php" method="POST" enctype="multipart/form-data" class="publication-form">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

      <section class="publication-section" id="informacion-principal">
        <div class="publication-section__header">
          <h2>Informacion principal</h2>
          <p>Datos basicos visibles en el catalogo y en la pagina de detalle.</p>
        </div>
        <div class="publication-field-grid">
          <label class="publication-field">Titulo
            <input type="text" name="titulo" maxlength="200" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
          </label>
          <label class="publication-field">Tipo
            <select name="tipo" required>
              <option value="">-- Selecciona --</option>
              <option value="Curso" <?= ($_POST['tipo'] ?? '') === 'Curso' ? 'selected' : '' ?>>Curso</option>
              <option value="Servicio" <?= ($_POST['tipo'] ?? '') === 'Servicio' ? 'selected' : '' ?>>Servicio</option>
            </select>
          </label>
          <label class="publication-field publication-field--full">Descripcion
            <textarea name="descripcion" rows="6" required><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
          </label>
        </div>
      </section>

      <section class="publication-section" id="configuracion">
        <div class="publication-section__header">
          <h2>Configuracion</h2>
          <p>Define precio, modalidad y condiciones generales de la propuesta.</p>
        </div>
        <div class="publication-field-grid">
          <label class="publication-field">Precio ($ UYU)
            <input type="number" step="0.01" min="1" name="precio" required value="<?= htmlspecialchars($_POST['precio'] ?? '') ?>">
          </label>
          <label class="publication-field">Modalidad
            <select name="modalidad">
              <option value="">Sin especificar</option>
              <?php foreach (['virtual' => 'Virtual', 'presencial' => 'Presencial', 'hibrida' => 'Hibrida', 'producto-entregable' => 'Producto entregable'] as $v => $e): ?>
                <option value="<?= $v ?>" <?= ($_POST['modalidad'] ?? '') === $v ? 'selected' : '' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="publication-field">Nivel de experiencia
            <select name="nivel_experiencia">
              <option value="">Sin especificar</option>
              <?php foreach (['inicial' => 'Inicial', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado', 'depende-categoria' => 'Depende de la categoria'] as $v => $e): ?>
                <option value="<?= $v ?>" <?= ($_POST['nivel_experiencia'] ?? '') === $v ? 'selected' : '' ?>><?= $e ?></option>
              <?php endforeach; ?>
            </select>
          </label>
          <label class="publication-field">Duracion aproximada
            <input type="number" min="1" max="10000" name="duracion_horas" value="<?= htmlspecialchars($_POST['duracion_horas'] ?? '') ?>">
            <small>Cantidad estimada de horas del curso o servicio.</small>
          </label>
          <label class="publication-field">Cupos
            <input type="number" min="1" name="cupos" value="<?= htmlspecialchars($_POST['cupos'] ?? '') ?>">
            <small>Deja vacio si no existe un limite.</small>
          </label>
          <label class="publication-field">Disponibilidad
            <input type="text" name="disponibilidad" value="<?= htmlspecialchars($_POST['disponibilidad'] ?? '') ?>" placeholder="Ej: lunes y miercoles de 18 a 21">
            <small>Indica horarios, frecuencia o coordinacion esperada.</small>
          </label>
        </div>
      </section>

      <section class="publication-section" id="categoria">
        <div class="publication-section__header">
          <h2>Categoria</h2>
          <p>Selecciona una categoria existente o crea una nueva si ninguna aplica.</p>
        </div>
        <div class="publication-field-grid publication-category-grid">
          <div class="publication-choice">
            <h3>Usar categoria existente</h3>
            <label class="publication-field">Categoria existente
              <select name="id_categoria">
                <option value="">-- Selecciona --</option>
                <?php foreach ($categorias as $cat): ?>
                  <option value="<?= (int)$cat['id_categoria'] ?>" <?= (int)($_POST['id_categoria'] ?? 0) === (int)$cat['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          </div>
          <div class="publication-choice">
            <h3>Crear una nueva categoria</h3>
            <label class="publication-field">Nueva categoria
              <input name="nueva_categoria" maxlength="100" value="<?= htmlspecialchars($_POST['nueva_categoria'] ?? '') ?>" placeholder="Solo si ninguna categoria aplica">
            </label>
            <label class="publication-field">Descripcion de la nueva categoria
              <textarea name="descripcion_categoria" rows="3"><?= htmlspecialchars($_POST['descripcion_categoria'] ?? '') ?></textarea>
            </label>
          </div>
        </div>
      </section>

      <section class="publication-section" id="servicio">
        <div class="publication-section__header">
          <h2>Servicio</h2>
          <p>Opcional. Se usa cuando la publicacion es un servicio contratado a medida.</p>
        </div>
        <div class="publication-field-grid">
          <label class="publication-field publication-field--full">Plantilla de servicio
            <select name="tipo_servicio">
              <option value="">Sin plantilla / contratacion directa</option>
              <?php foreach ($plantillas_servicios as $clave => $plantilla): ?>
                <option value="<?= htmlspecialchars($clave) ?>" <?= ($_POST['tipo_servicio'] ?? '') === $clave ? 'selected' : '' ?>><?= htmlspecialchars($plantilla['nombre']) ?></option>
              <?php endforeach; ?>
            </select>
            <small>Para cursos, el sistema ignora este campo al guardar.</small>
          </label>
        </div>
      </section>

      <section class="publication-section" id="portada">
        <div class="publication-section__header">
          <h2>Portada</h2>
          <p>Imagen que ayuda a reconocer la propuesta en el catalogo.</p>
        </div>
        <label class="publication-upload">
          <span>Imagen de portada</span>
          <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
          <small>JPG, PNG, WEBP o GIF. Maximo 5MB.</small>
        </label>
      </section>

      <div class="publication-actions">
        <a class="btn btn-secondary" href="panel-proveedor.php">Cancelar</a>
        <button class="btn btn-primary-action" type="submit">Guardar publicacion</button>
      </div>
    </form>
  </div>
</main>
<?php include '../includes/footer.php'; ?>

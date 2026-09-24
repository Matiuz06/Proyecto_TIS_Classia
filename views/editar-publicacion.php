<?php

/**
 * Responsabilidad: Edición, actualización de cupos y cambio de estado de publicaciones.
 */

$publicacion = null;
$categorias = [];
require_once '../php/auth/roles.php';
requerir_rol(ROL_DOCENTE, 'usuario.php');
require_once '../php/publicaciones/editar_publicacion.php';

$title = 'Editar publicacion';
$description = 'Modificacion de cursos o servicios en Classia.';
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
      <h1 id="publication-editor-title">Editar publicacion</h1>
      <p>Actualiza los datos de tu curso o servicio sin perder historial, contrataciones ni contenido asociado.</p>
    </div>
    <?php if ($publicacion): ?>
      <div class="publication-editor__header-actions">
        <a class="btn btn-secondary" href="panel-proveedor.php">Volver al panel</a>
        <a class="btn btn-secondary" href="vista-previa-publicacion.php?id=<?= (int)$publicacion['id_publicacion'] ?>">Vista previa</a>
        <?php if ($publicacion['tipo'] === 'Curso'): ?>
          <a class="btn btn-secondary" href="gestionar-contenido-curso.php?id=<?= (int)$publicacion['id_publicacion'] ?>">Modulos y contenido</a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </header>

  <?php if (!empty($errores)): ?>
    <div class="alert alert-danger">
      <ul><?php foreach ($errores as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
    </div>
  <?php endif; ?>

  <?php if ($publicacion): ?>
    <div class="publication-editor__layout">
      <aside class="publication-editor__nav" aria-label="Secciones de la publicacion">
        <strong>Configuracion</strong>
        <a href="#informacion-principal">Informacion principal</a>
        <a href="#configuracion">Configuracion</a>
        <a href="#categoria">Categoria</a>
        <a href="#servicio">Servicio</a>
        <a href="#portada">Portada</a>
      </aside>

      <form action="editar-publicacion.php?id=<?= (int)$publicacion['id_publicacion'] ?>" method="POST" enctype="multipart/form-data" class="publication-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
        <input type="hidden" name="id_publicacion" value="<?= (int)$publicacion['id_publicacion'] ?>">
        <input type="hidden" name="tipo" value="<?= htmlspecialchars($publicacion['tipo']) ?>">

        <section class="publication-section" id="informacion-principal">
          <div class="publication-section__header">
            <h2>Informacion principal</h2>
            <p>Datos basicos visibles en el catalogo y en la pagina de detalle.</p>
          </div>
          <div class="publication-field-grid">
            <label class="publication-field">Titulo
              <input name="titulo" maxlength="200" required value="<?= htmlspecialchars($_POST['titulo'] ?? $publicacion['titulo']) ?>">
            </label>
            <label class="publication-field">Tipo
              <input value="<?= htmlspecialchars($publicacion['tipo']) ?>" readonly>
            </label>
            <label class="publication-field publication-field--full">Descripcion
              <textarea name="descripcion" rows="6" required><?= htmlspecialchars($_POST['descripcion'] ?? $publicacion['descripcion']) ?></textarea>
            </label>
          </div>
        </section>

        <section class="publication-section" id="configuracion">
          <div class="publication-section__header">
            <h2>Configuracion</h2>
            <p>Ajusta precio, modalidad, disponibilidad y estado de publicacion.</p>
          </div>
          <div class="publication-field-grid">
            <label class="publication-field">Precio ($ UYU)
              <input type="number" step="0.01" min="1" name="precio" required value="<?= htmlspecialchars($_POST['precio'] ?? $publicacion['precio']) ?>">
            </label>
            <?php $cm = $_POST['modalidad'] ?? ($publicacion['modalidad'] ?? ''); ?>
            <label class="publication-field">Modalidad
              <select name="modalidad">
                <option value="">Sin especificar</option>
                <?php foreach (['virtual' => 'Virtual', 'presencial' => 'Presencial', 'hibrida' => 'Hibrida', 'producto-entregable' => 'Producto entregable'] as $v => $e): ?>
                  <option value="<?= $v ?>" <?= $cm === $v ? 'selected' : '' ?>><?= $e ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <?php $cn = $_POST['nivel_experiencia'] ?? ($publicacion['nivel_experiencia'] ?? ''); ?>
            <label class="publication-field">Nivel de experiencia
              <select name="nivel_experiencia">
                <option value="">Sin especificar</option>
                <?php foreach (['inicial' => 'Inicial', 'intermedio' => 'Intermedio', 'avanzado' => 'Avanzado', 'depende-categoria' => 'Depende de la categoria'] as $v => $e): ?>
                  <option value="<?= $v ?>" <?= $cn === $v ? 'selected' : '' ?>><?= $e ?></option>
                <?php endforeach; ?>
              </select>
            </label>
            <label class="publication-field">Duracion aproximada
              <input type="number" min="1" max="10000" name="duracion_horas" value="<?= htmlspecialchars($_POST['duracion_horas'] ?? ($publicacion['duracion_horas'] ?? '')) ?>">
              <small>Cantidad estimada de horas del curso o servicio.</small>
            </label>
            <label class="publication-field">Cupos
              <input type="number" min="1" name="cupos" value="<?= htmlspecialchars($_POST['cupos'] ?? ($publicacion['cupos'] ?? '')) ?>">
              <small>Deja vacio si no existe un limite.</small>
            </label>
            <label class="publication-field">Disponibilidad
              <input name="disponibilidad" value="<?= htmlspecialchars($_POST['disponibilidad'] ?? ($publicacion['disponibilidad'] ?? '')) ?>">
              <small>Indica horarios, frecuencia o coordinacion esperada.</small>
            </label>
            <label class="publication-field">Estado
              <select name="estado">
                <?php $ce = $_POST['estado'] ?? $publicacion['estado']; foreach (['Activo', 'Pausado', 'Inactivo', 'Eliminado'] as $e): ?>
                  <option value="<?= $e ?>" <?= $ce === $e ? 'selected' : '' ?>><?= $e ?></option>
                <?php endforeach; ?>
              </select>
            </label>
          </div>
        </section>

        <section class="publication-section" id="categoria">
          <div class="publication-section__header">
            <h2>Categoria</h2>
            <p>Manten la categoria actual o crea una nueva si la publicacion cambio de enfoque.</p>
          </div>
          <div class="publication-field-grid publication-category-grid">
            <div class="publication-choice">
              <h3>Usar categoria existente</h3>
              <?php $cc = (int)($_POST['id_categoria'] ?? $publicacion['id_categoria']); ?>
              <label class="publication-field">Categoria existente
                <select name="id_categoria">
                  <option value="">-- Selecciona --</option>
                  <?php foreach ($categorias as $cat): ?>
                    <option value="<?= (int)$cat['id_categoria'] ?>" <?= $cc === (int)$cat['id_categoria'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['nombre_categoria']) ?></option>
                  <?php endforeach; ?>
                </select>
              </label>
            </div>
            <div class="publication-choice">
              <h3>Crear una nueva categoria</h3>
              <label class="publication-field">Nueva categoria
                <input name="nueva_categoria" maxlength="100" value="<?= htmlspecialchars($_POST['nueva_categoria'] ?? '') ?>">
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
            <p>Plantilla usada para solicitudes personalizadas cuando la publicacion es de tipo servicio.</p>
          </div>
          <div class="publication-field-grid">
            <?php $cts = $_POST['tipo_servicio'] ?? ($publicacion['tipo_servicio'] ?? ''); ?>
            <label class="publication-field publication-field--full">Plantilla de servicio
              <select name="tipo_servicio" <?= $publicacion['tipo'] !== 'Servicio' ? 'disabled' : '' ?>>
                <option value="">Sin plantilla / contratacion directa</option>
                <?php foreach ($plantillas_servicios as $clave => $plantilla): ?>
                  <option value="<?= htmlspecialchars($clave) ?>" <?= $cts === $clave ? 'selected' : '' ?>><?= htmlspecialchars($plantilla['nombre']) ?></option>
                <?php endforeach; ?>
              </select>
              <small><?= $publicacion['tipo'] === 'Servicio' ? 'Selecciona la plantilla que organiza la solicitud del estudiante.' : 'Este campo no aplica a publicaciones de tipo curso.' ?></small>
            </label>
          </div>
        </section>

        <section class="publication-section" id="portada">
          <div class="publication-section__header">
            <h2>Portada</h2>
            <p>Actualiza la imagen visible en catalogo y detalle.</p>
          </div>
          <div class="publication-cover-grid">
            <?php if (!empty($publicacion['imagen'])): ?>
              <figure class="publication-cover-preview">
                <img src="../<?= htmlspecialchars($publicacion['imagen']) ?>" alt="Imagen actual">
                <figcaption>Imagen actual</figcaption>
              </figure>
            <?php endif; ?>
            <label class="publication-upload">
              <span>Subir nueva imagen</span>
              <input type="file" name="imagen" accept="image/jpeg,image/png,image/webp,image/gif">
              <small>JPG, PNG, WEBP o GIF. Maximo 5MB. Si no subis otra imagen, se conserva la actual.</small>
            </label>
            <?php if (!empty($publicacion['imagen'])): ?>
              <label class="publication-check">
                <input type="checkbox" name="eliminar_imagen" value="1">
                Quitar imagen actual
              </label>
            <?php endif; ?>
          </div>
        </section>

        <div class="publication-actions">
          <a class="btn btn-secondary" href="panel-proveedor.php">Volver al panel</a>
          <a class="btn btn-secondary" href="vista-previa-publicacion.php?id=<?= (int)$publicacion['id_publicacion'] ?>">Vista previa</a>
          <button class="btn btn-primary-action" type="submit">Actualizar cambios</button>
        </div>
      </form>
    </div>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

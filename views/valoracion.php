<?php
require_once '../php/auth/roles.php';
require_once '../config/database.php';
require_once '../php/valoraciones/obtener_contrataciones_valorables.php';

requerir_autenticacion('login.php');

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errores      = $_SESSION['valoracion_errores'] ?? [];
$input_previo = $_SESSION['valoracion_input'] ?? [];
unset($_SESSION['valoracion_errores'], $_SESSION['valoracion_input']);

$id_contratacion_solicitada = (int) ($input_previo['id_contratacion'] ?? $_GET['contratacion'] ?? 0);

$contexto                  = obtener_contexto_valoracion($pdo, $id_usuario, $id_contratacion_solicitada);
$contrataciones_pendientes = $contexto['contrataciones_pendientes'];
$contratacion_seleccionada = $contexto['contratacion_seleccionada'];
$error_mensaje             = $contexto['error_mensaje'];

$title       = 'Valoración';
$description = 'Dejá tu valoración y opinión sobre cursos y servicios contratados en Classia.';
$cssPrefix   = '..';
$activePage  = 'cuenta';
include '../includes/header.php';
?>

    <main class="page-container">
      <header class="page-header">
        <span class="brand-mark">Classia</span>
        <h1>Dejá tu valoración</h1>
        <p>Tu opinión ayuda a mejorar los cursos y servicios en Classia.</p>
      </header>

      <?php if (!empty($errores)): ?>
        <div class="alert alert-danger" role="alert">
          <ul>
            <?php foreach ($errores as $err): ?>
              <li><?php echo htmlspecialchars($err); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <?php if ($error_mensaje !== ''): ?>
        <div class="alert alert-danger" role="alert">
          <p><?php echo htmlspecialchars($error_mensaje); ?></p>
        </div>
        <p>
          <a href="usuario.php" class="btn">← Volver a mi perfil</a>
        </p>
      <?php elseif (empty($contrataciones_pendientes) && !$contratacion_seleccionada): ?>
        <div class="alert alert-muted" role="status">
          <p>No tenés contrataciones pendientes de valorar en este momento.</p>
          <p>Podés valorar tus cursos y servicios cuando su estado sea <strong>En Proceso</strong> o <strong>Completada</strong>.</p>
        </div>
        <p>
          <a href="usuario.php" class="btn">← Volver a mi perfil</a>
          <a href="catalogo.php">Ver catálogo</a>
        </p>
      <?php else: ?>

        <form action="../php/valoraciones/guardar_valoracion.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" />

          <?php if (count($contrataciones_pendientes) > 1 && $id_contratacion_solicitada === 0): ?>
            <p>
              <label for="id_contratacion">Seleccioná la contratación a valorar:</label>
              <select name="id_contratacion" id="id_contratacion">
                <?php foreach ($contrataciones_pendientes as $item): ?>
                  <option value="<?php echo (int) $item['id_contratacion']; ?>" <?php echo ((int) $item['id_contratacion'] === (int) $contratacion_seleccionada['id_contratacion']) ? 'selected' : ''; ?>>
                    <?php echo (int) $item['id_contratacion']; ?> - <?php echo htmlspecialchars($item['titulo']); ?> (<?php echo htmlspecialchars($item['tipo']); ?>)
                  </option>
                <?php endforeach; ?>
              </select>
            </p>
          <?php else: ?>
            <input type="hidden" name="id_contratacion" value="<?php echo (int) $contratacion_seleccionada['id_contratacion']; ?>" />
          <?php endif; ?>

          <div class="catalog-card motion-card valoracion-card">
            <div class="placeholder-visual" aria-hidden="true">
              <?php echo htmlspecialchars($contratacion_seleccionada['tipo'] ?? 'Curso'); ?>
            </div>
            <div>
              <h2><?php echo htmlspecialchars($contratacion_seleccionada['titulo'] ?? ''); ?></h2>
              <p>
                <strong>Tipo:</strong> <?php echo htmlspecialchars($contratacion_seleccionada['tipo'] ?? ''); ?> · 
                <strong>Estado:</strong> <?php echo htmlspecialchars($contratacion_seleccionada['estado'] ?? ''); ?>
              </p>
            </div>
          </div>

          <p>
            <label for="puntuacion">Calificación general (del 1 al 5):*</label>
            <select name="puntuacion" id="puntuacion" required>
              <?php $puntuacion_actual = (int) ($input_previo['puntuacion'] ?? 5); ?>
              <option value="5" <?php echo $puntuacion_actual === 5 ? 'selected' : ''; ?>>5 - Excelente</option>
              <option value="4" <?php echo $puntuacion_actual === 4 ? 'selected' : ''; ?>>4 - Muy bueno</option>
              <option value="3" <?php echo $puntuacion_actual === 3 ? 'selected' : ''; ?>>3 - Bueno</option>
              <option value="2" <?php echo $puntuacion_actual === 2 ? 'selected' : ''; ?>>2 - Regular</option>
              <option value="1" <?php echo $puntuacion_actual === 1 ? 'selected' : ''; ?>>1 - Malo</option>
            </select>
          </p>

          <p>
            <label for="comentario">Tu reseña u opinión (opcional):</label>
            <textarea
              id="comentario"
              name="comentario"
              rows="4"
              placeholder="Contanos tu experiencia con este curso o servicio..."><?php echo htmlspecialchars($input_previo['comentario'] ?? ''); ?></textarea>
          </p>

          <button type="submit">Enviar valoración</button>
        </form>

        <p class="valoracion-links">
          <a href="usuario.php">← Volver a mi perfil</a> |
          <a href="catalogo.php">Ir al catálogo</a>
        </p>

      <?php endif; ?>
    </main>

<?php include '../includes/footer.php'; ?>

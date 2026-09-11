<?php
require_once '../php/auth/roles.php';
requerir_autenticacion('login.php');

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensaje_error = $_SESSION['error_clave'] ?? '';
unset($_SESSION['error_clave']);

$title     = 'Cambiar contraseña';
$cssPrefix = '..';
$jsPrefix  = '..';

$activePage = 'cuenta';
include '../includes/header.php';
?>

  <div class="card-container">
    <span class="brand-mark">Classia</span>
    <h1>Cambiar Contraseña</h1>

    <?php if (!empty($mensaje_error)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($mensaje_error) ?>
      </div>
    <?php endif; ?>

    <form action="../php/usuarios/procesar_cambio_clave.php" method="POST">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
      
      <p>
        <label for="cont1"><strong>Contraseña actual:*</strong></label><br />
        <input type="password" id="cont1" name="contrasena_actual" required />
      </p>

      <p>
        <label for="cont2"><strong>Nueva contraseña:*</strong></label><br />
        <input type="password" id="cont2" name="contrasena_nueva" minlength="6" required />
      </p>

      <p>
        <label for="cont3"><strong>Confirmar nueva contraseña:*</strong></label><br />
        <input type="password" id="cont3" name="contrasena_confirmar" minlength="6" required />
      </p>

      <button type="submit" class="btn">Guardar Contraseña</button>
    </form>

    <hr />

    <p>
      <a href="restablecer-contrasena.php" class="link">¿Olvidaste tu contraseña?</a>
    </p>
    <p>
      <a href="usuario.php" class="link">← Volver al Perfil</a>
    </p>
  </div>

<?php include '../includes/footer.php'; ?>

<?php
require_once '../php/auth/sesion.php';
iniciar_sesion();

$mensaje_error = $_SESSION['error_restablecer'] ?? '';
$mensaje_exito = $_SESSION['success_restablecer'] ?? '';
unset($_SESSION['error_restablecer'], $_SESSION['success_restablecer']);

$token_reset = trim($_GET['token'] ?? '');
$mostrar_form_nueva = $token_reset !== '';

if (empty($_SESSION['csrf_token'])) {
  $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$title     = 'Restablecer contraseña';
$cssPrefix = '..';
$jsPrefix  = '..';

$activePage = 'cuenta';
include '../includes/header.php';
?>

    <div class="card-container">
      <span class="brand-mark">Classia</span>
      <h1>Restablecer Contraseña</h1>

      <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($mensaje_error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success" role="alert">
          <?= htmlspecialchars($mensaje_exito) ?>
        </div>
      <?php endif; ?>

      <?php if ($mostrar_form_nueva): ?>
        <p>Ingresá tu nueva contraseña para finalizar el cambio.</p>
        <form action="../php/auth/procesar_restablecer_clave.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
          <input type="hidden" name="token" value="<?= htmlspecialchars($token_reset, ENT_QUOTES, 'UTF-8') ?>" />
          <p>
            <label for="cont2"><strong>Nueva contraseña:*</strong></label><br />
            <input
              type="password"
              id="cont2"
              name="nueva_contrasena"
              placeholder="Mínimo 6 caracteres"
              minlength="6"
              required />
          </p>

          <button type="submit" class="btn">Guardar nueva contraseña</button>
        </form>
      <?php else: ?>
        <p>Ingresá tu correo electrónico registrado para recibir un enlace seguro de recuperación.</p>
        <form action="../php/auth/procesar_restablecer_clave.php" method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>" />
          <p>
            <label for="correo1"><strong>Correo registrado:*</strong></label><br />
            <input
              type="email"
              id="correo1"
              name="correo_recuperacion"
              placeholder="ejemplo@correo.com"
              required />
          </p>

          <button type="submit" class="btn">Enviar enlace de recuperación</button>
        </form>
      <?php endif; ?>

      <hr />

      <p>
        <a href="login.php" class="link">← Iniciar Sesión</a>
      </p>
      <p>
        <a href="registro.php" class="link">¿No tienes cuenta? Regístrate</a>
      </p>
    </div>

<?php include '../includes/footer.php'; ?>

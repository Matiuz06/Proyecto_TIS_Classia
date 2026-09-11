<?php
require_once '../php/auth/session.php';
iniciar_sesion();

$mensaje_error = $_SESSION['error_restablecer'] ?? '';
unset($_SESSION['error_restablecer']);

$title     = 'Restablecer contraseña';
$cssPrefix = '..';
$jsPrefix  = '..';

$activePage = 'cuenta';
include '../includes/header.php';
?>

    <div class="card-container">
      <span class="brand-mark">Classia</span>
      <h1>Restablecer Contraseña</h1>
      <p>Ingresá tu correo electrónico registrado y tu nueva contraseña para actualizarla.</p>

      <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($mensaje_error) ?>
        </div>
      <?php endif; ?>

      <form action="../php/auth/procesar_restablecer_clave.php" method="POST">
        <p>
          <label for="correo1"><strong>Correo registrado:*</strong></label><br />
          <input
            type="email"
            id="correo1"
            name="correo_recuperacion"
            placeholder="ejemplo@correo.com"
            required />
        </p>

        <p>
          <label for="cont2"><strong>Nueva contraseña:*</strong></label><br />
          <input
            type="password"
            id="cont2"
            name="nueva_contrasena"
            placeholder="Nueva contraseña (mínimo 6 caracteres)"
            minlength="6"
            required />
        </p>

        <button type="submit" class="btn">Restablecer Contraseña</button>
      </form>

      <hr />

      <p>
        <a href="login.php" class="link">← Iniciar Sesión</a>
      </p>
      <p>
        <a href="registro.php" class="link">¿No tienes cuenta? Regístrate</a>
      </p>
    </div>

<?php include '../includes/footer.php'; ?>

<?php
$title     = 'Cambiar contraseña';
$cssPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>

  <div class="card-container">
    <span class="brand-mark">Classia</span>
    <h1>Cambiar Contraseña</h1>

    <form action="/cambiar_contra" method="POST">
      <p>
        <label for="cont1"><strong>Contraseña actual:*</strong></label><br />
        <input type="password" id="cont1" name="contrasena_actual" required />
      </p>

      <p>
        <label for="cont2"><strong>Nueva contraseña:*</strong></label><br />
        <input type="password" id="cont2" name="contrasena_nueva" required />
      </p>

      <p>
        <label for="cont3"><strong>Confirmar nueva contraseña:*</strong></label><br />
        <input type="password" id="cont3" name="contrasena_confirmar" required />
      </p>

      <button type="submit">Guardar Contraseña</button>
    </form>

    <hr />

    <p>
      <a href="restablecerContrasena.php" class="link">¿Olvidaste tu contraseña?</a>
    </p>
    <p>
      <a href="usuario.php" class="link">← Volver al Perfil</a>
    </p>
  </div>

<?php include '../includes/footer.php'; ?>

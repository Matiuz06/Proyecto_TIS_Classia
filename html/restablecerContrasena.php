<?php
$title     = 'Restablecer contraseña';
$cssPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>

    <div class="card-container">
      <span class="brand-mark">Classia</span>
      <h1>Restablecer Contraseña</h1>
      <p>Ingresa tu correo para enviarte las instrucciones de recuperación.</p>

      <form action="/restablecer_contra" method="POST">
        <p>
          <label for="correo1"><strong>Correo registrado:*</strong></label
          ><br />
          <input
            type="email"
            id="correo1"
            name="correo_recuperacion"
            placeholder="ejemplo@correo.com"
            required />
        </p>

        <p>
          <label for="cont2"><strong>Nueva contraseña:*</strong></label
          ><br />
          <input
            type="password"
            id="cont2"
            name="nueva_contrasena"
            placeholder="Nueva contraseña"
            required />
        </p>

        <button type="submit">Enviar Restablecimiento</button>
      </form>

      <hr />

      <p>
        <a href="login.php">← Iniciar Sesión</a>
      </p>
      <p>
        <a href="registro.php">¿No tienes cuenta? Regístrate</a>
      </p>
    </div>

<?php include '../includes/footer.php'; ?>

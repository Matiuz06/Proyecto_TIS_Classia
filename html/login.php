<?php
$title      = 'Iniciar sesión';
$description = 'Inicio de sesión en Classia.';
$cssPrefix  = '..';
$bodyClass  = 'auth-page';
$activePage = 'cuenta';
include '../includes/header.php';
?>

  <main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-login">
      <h1 id="intro-login">Entrá a tu espacio educativo</h1>
      <p>
        Accedé a tus cursos, solicitudes, publicaciones y herramientas de
        gestión desde una cuenta Classia.
      </p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-login">
      <h2 id="titulo-login">Iniciar sesión</h2>
      <p>Usá tu correo y contraseña para continuar.</p>
      
      <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
        <div class="alert alert-success">
          Registro completado con éxito. Ahora podés iniciar sesión.
        </div>
      <?php endif; ?>
      <form action="login.php" method="POST">
        <p>
          <label for="correo">Correo electrónico</label>
          <input type="email" id="correo" name="correo" autocomplete="email" required
            placeholder="ejemplo@correo.com" />
        </p>
        <p>
          <label for="contrasena">Contraseña</label>
          <input type="password" id="contrasena" name="contraseña" autocomplete="current-password" required
            placeholder="Ingresa tu contraseña" />
        </p>
        <label>
          <input type="checkbox" name="terminos" required /> Acepto los
          términos y condiciones
        </label>
        <button type="submit">Iniciar sesión</button>
      </form>
      <p class="auth-links">
        <a href="restablecerContrasena.php">¿Olvidaste tu contraseña?</a>
      </p>
      <p class="auth-links">
        ¿No tenés cuenta? <a href="registro.php">Registrate</a>
      </p>

      <div class="auth-divider" aria-hidden="true">
        <span>o continuá con</span>
      </div>

    
      <a href="#" class="btn-google" id="btn-google-login" aria-label="Iniciar sesión con Google">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true"
          focusable="false">
          <path fill="#EA4335"
            d="M24 9.5c3.14 0 5.95 1.08 8.17 2.86l6.1-6.1C34.46 3.06 29.52 1 24 1 14.82 1 6.97 6.48 3.41 14.34l7.12 5.53C12.3 13.38 17.68 9.5 24 9.5z" />
          <path fill="#4285F4"
            d="M46.52 24.5c0-1.64-.15-3.22-.42-4.75H24v9h12.7c-.55 2.97-2.2 5.48-4.68 7.17l7.18 5.58C43.18 37.5 46.52 31.5 46.52 24.5z" />
          <path fill="#FBBC05"
            d="M10.53 28.36A14.57 14.57 0 0 1 9.5 24c0-1.51.26-2.97.72-4.36l-7.12-5.53A23.94 23.94 0 0 0 0 24c0 3.86.93 7.5 2.56 10.72l7.97-6.36z" />
          <path fill="#34A853"
            d="M24 47c5.52 0 10.15-1.83 13.53-4.97l-7.18-5.58C28.56 37.73 26.38 38.5 24 38.5c-6.32 0-11.68-3.88-13.47-9.14l-7.97 6.36C6.97 43.52 14.82 47 24 47z" />
        </svg>
        Iniciar sesión con Google
      </a>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

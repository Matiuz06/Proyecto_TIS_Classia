<?php
require_once '../php/usuarios/registro.php';
require_once '../php/utils/recaptcha.php';

$title      = 'Crear cuenta';
$description = 'Registro de usuario en Classia.';
$cssPrefix  = '..';
$jsPrefix    = '..';

$bodyClass  = 'auth-page';
$activePage = 'cuenta';
$recaptcha_site_key = obtener_recaptcha_site_key();

include '../includes/header.php';
?>

<?php if ($recaptcha_site_key !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

  <main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-registro">
      <h1 id="intro-registro">Creá tu cuenta en Classia</h1>
      <p>
        Unifica aprendizaje, servicios educativos y gestión académica en una
        plataforma clara y segura.
      </p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-registro">
      <h2 id="titulo-registro">Registro</h2>
      <p class="muted">Completa tus datos para empezar.</p>

      <?php if (!empty($errores)): ?>
        <div class="alert alert-danger" role="alert">
          <ul>
            <?php foreach ($errores as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="registro.php" method="POST" id="form-registro">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
        
        <p>
          <label for="nombre">Nombre</label>
          <input
            type="text"
            id="nombre"
            name="nombre"
            autocomplete="given-name"
            required
            value="<?php echo htmlspecialchars($nombre ?? ''); ?>"
            placeholder="Ingresá tu nombre" />
        </p>
        
        <p>
          <label for="apellido">Apellido</label>
          <input
            type="text"
            id="apellido"
            name="apellido"
            autocomplete="family-name"
            required
            value="<?php echo htmlspecialchars($apellido ?? ''); ?>"
            placeholder="Ingresá tu apellido" />
        </p>

        <p>
          <label for="cedula_identidad">Cédula de Identidad</label>
          <input
            type="text"
            id="cedula_identidad"
            name="cedula_identidad"
            required
            pattern="[0-9.\-\s]{6,12}"
            value="<?php echo htmlspecialchars($_POST['cedula_identidad'] ?? ''); ?>"
            placeholder="Ej: 1.234.567-8"
            aria-describedby="ci-help" />
          <small id="ci-help" class="form-hint form-hint-block">
            ⚠️ <em>Por motivos de verificación de identidad y seguridad, la Cédula de Identidad <strong>no se podrá modificar</strong> una vez creada la cuenta.</em>
          </small>
        </p>

        <p>
          <label for="usuario">Nombre de usuario</label>
          <input
            type="text"
            id="usuario"
            name="usuario"
            autocomplete="username"
            required
            value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>"
            placeholder="Ingresá un usuario" />
        </p>

        <p>
          <label for="correo">Correo electrónico</label>
          <input
            type="email"
            id="correo"
            name="correo"
            autocomplete="email"
            required
            value="<?php echo htmlspecialchars($correo ?? ''); ?>"
            placeholder="ejemplo@correo.com" />
        </p>

        <p>
          <label for="contrasena">Contraseña</label>
          <input
            type="password"
            id="contrasena"
            name="contrasenia"
            autocomplete="new-password"
            required
            placeholder="Ingresá una contraseña" />
        </p>

        <p>
          <label for="confirmar-contrasena">Confirmar contraseña</label>
          <input
            type="password"
            id="confirmar-contrasena"
            name="confirmar_contrasenia"
            autocomplete="new-password"
            required
            placeholder="Confirmá tu contraseña" />
        </p>

        <fieldset>
          <legend>Género</legend>
          <label
            ><input type="radio" name="genero" value="hombre" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'hombre') ? 'checked' : ''; ?> /> Hombre</label
          >
          <label
            ><input type="radio" name="genero" value="mujer" <?php echo (isset($_POST['genero']) && $_POST['genero'] === 'mujer') ? 'checked' : ''; ?> /> Mujer</label
          >
          <label
            ><input type="radio" name="genero" value="sin-especificar" <?php echo (!isset($_POST['genero']) || $_POST['genero'] === 'sin-especificar') ? 'checked' : ''; ?> />
            Prefiero no decirlo</label
          >
        </fieldset>

        <?php if ($recaptcha_site_key !== ''): ?>
          <div class="form-group recaptcha-wrapper">
            <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_site_key); ?>"></div>
          </div>
        <?php endif; ?>

        <button type="submit" class="btn btn-full">Registrarse</button>
      </form>

      <p class="auth-links">
        ¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a>
      </p>

      <div class="auth-divider" aria-hidden="true">
        <span>o continuá con</span>
      </div>

      <div class="auth-social-buttons">
        <a href="../php/auth/inicio_oauth_google.php" class="btn-google" id="btn-google-registro" aria-label="Registrarse con Google">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true" focusable="false">
            <path fill="#EA4335" d="M24 9.5c3.14 0 5.95 1.08 8.17 2.86l6.1-6.1C34.46 3.06 29.52 1 24 1 14.82 1 6.97 6.48 3.41 14.34l7.12 5.53C12.3 13.38 17.68 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.52 24.5c0-1.64-.15-3.22-.42-4.75H24v9h12.7c-.55 2.97-2.2 5.48-4.68 7.17l7.18 5.58C43.18 37.5 46.52 31.5 46.52 24.5z"/>
            <path fill="#FBBC05" d="M10.53 28.36A14.57 14.57 0 0 1 9.5 24c0-1.51.26-2.97.72-4.36l-7.12-5.53A23.94 23.94 0 0 0 0 24c0 3.86.93 7.5 2.56 10.72l7.97-6.36z"/>
            <path fill="#34A853" d="M24 47c5.52 0 10.15-1.83 13.53-4.97l-7.18-5.58C28.56 37.73 26.38 38.5 24 38.5c-6.32 0-11.68-3.88-13.47-9.14l-7.97 6.36C6.97 43.52 14.82 47 24 47z"/>
          </svg>
          Registrarse con Google
        </a>

        <a href="../php/auth/inicio_oauth_github.php" class="btn-github" id="btn-github-registro" aria-label="Registrarse con GitHub">
          <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="currentColor" aria-hidden="true" focusable="false">
            <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"/>
          </svg>
          Registrarse con GitHub
        </a>
      </div>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

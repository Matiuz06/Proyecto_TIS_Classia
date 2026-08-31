<?php
require_once '../php/usuarios/registro.php';

$title      = 'Crear cuenta';
$description = 'Registro de usuario en Classia.';
$cssPrefix  = '..';
$bodyClass  = 'auth-page';
$activePage = 'cuenta';
include '../includes/header.php';
?>

  <main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-registro">
      <h1 id="intro-registro">Creá tu cuenta en Classia</h1>
      <p>
        Unificá aprendizaje, servicios educativos y gestión académica en una
        plataforma clara.
      </p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-registro">
      <h2 id="titulo-registro">Registro</h2>
      <p class="muted">Completá tus datos básicos para empezar.</p>

      <?php if (!empty($errores)): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px;">
          <ul style="margin: 0; padding-left: 20px;">
            <?php foreach ($errores as $error): ?>
              <li><?php echo htmlspecialchars($error); ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form action="registro.php" method="POST">
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
            placeholder="Ingresa tu nombre" />
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
            placeholder="Ingresa tu apellido" />
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
            placeholder="Ingresa un usuario" />
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
            placeholder="Ingresa una contraseña" />
        </p>
        <p>
          <label for="confirmar-contrasena">Confirmar contraseña</label>
          <input
            type="password"
            id="confirmar-contrasena"
            name="confirmar_contrasenia"
            autocomplete="new-password"
            required
            placeholder="Confirma tu contraseña" />
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
        <button type="submit">Registrarse</button>
      </form>
      <p class="auth-links">
        ¿Ya tenés cuenta? <a href="login.php">Iniciar sesión</a>
      </p>

      <div class="auth-divider" aria-hidden="true">
        <span>o continuá con</span>
      </div>

      <a href="#" class="btn-google" id="btn-google-registro" aria-label="Registrarse con Google">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20" height="20" aria-hidden="true" focusable="false">
          <path fill="#EA4335" d="M24 9.5c3.14 0 5.95 1.08 8.17 2.86l6.1-6.1C34.46 3.06 29.52 1 24 1 14.82 1 6.97 6.48 3.41 14.34l7.12 5.53C12.3 13.38 17.68 9.5 24 9.5z"/>
          <path fill="#4285F4" d="M46.52 24.5c0-1.64-.15-3.22-.42-4.75H24v9h12.7c-.55 2.97-2.2 5.48-4.68 7.17l7.18 5.58C43.18 37.5 46.52 31.5 46.52 24.5z"/>
          <path fill="#FBBC05" d="M10.53 28.36A14.57 14.57 0 0 1 9.5 24c0-1.51.26-2.97.72-4.36l-7.12-5.53A23.94 23.94 0 0 0 0 24c0 3.86.93 7.5 2.56 10.72l7.97-6.36z"/>
          <path fill="#34A853" d="M24 47c5.52 0 10.15-1.83 13.53-4.97l-7.18-5.58C28.56 37.73 26.38 38.5 24 38.5c-6.32 0-11.68-3.88-13.47-9.14l-7.97 6.36C6.97 43.52 14.82 47 24 47z"/>
        </svg>
        Registrarse con Google
      </a>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

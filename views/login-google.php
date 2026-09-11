<?php
require_once '../php/auth/session.php';
iniciar_sesion();

if (esta_autenticado()) {
    header('Location: usuario.php');
    exit;
}

$error = $_SESSION['google_error'] ?? '';
unset($_SESSION['google_error']);

$title     = 'Iniciar sesión con Google';
$cssPrefix = '..';
$jsPrefix  = '..';

$activePage = 'login';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Acceder con Google - Classia</title>
  <link rel="stylesheet" href="../css/animation.css">
  <link rel="stylesheet" href="../css/style.css">
  <link rel="icon" type="image/png" href="../assets/images/favicon.png" />
</head>
<body class="google-auth-shell">
  <div class="google-auth-card motion-entry">
    
    <div class="google-auth-header">
      <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="44" height="44" aria-hidden="true">
        <path fill="#EA4335" d="M24 9.5c3.14 0 5.95 1.08 8.17 2.86l6.1-6.1C34.46 3.06 29.52 1 24 1 14.82 1 6.97 6.48 3.41 14.34l7.12 5.53C12.3 13.38 17.68 9.5 24 9.5z"/>
        <path fill="#4285F4" d="M46.52 24.5c0-1.64-.15-3.22-.42-4.75H24v9h12.7c-.55 2.97-2.2 5.48-4.68 7.17l7.18 5.58C43.18 37.5 46.52 31.5 46.52 24.5z"/>
        <path fill="#FBBC05" d="M10.53 28.36A14.57 14.57 0 0 1 9.5 24c0-1.51.26-2.97.72-4.36l-7.12-5.53A23.94 23.94 0 0 0 0 24c0 3.86.93 7.5 2.56 10.72l7.97-6.36z"/>
        <path fill="#34A853" d="M24 47c5.52 0 10.15-1.83 13.53-4.97l-7.18-5.58C28.56 37.73 26.38 38.5 24 38.5c-6.32 0-11.68-3.88-13.47-9.14l-7.97 6.36C6.97 43.52 14.82 47 24 47z"/>
      </svg>
      <h1 class="google-auth-title">Acceder con Google</h1>
      <p class="google-auth-subtitle">Continuar a Classia</p>
    </div>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error) ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="../php/auth/google_login.php" class="google-auth-form">
      <div class="form-group">
        <label for="google_email">
          Correo electrónico de Google:*
        </label>
        <input 
          type="email" 
          id="google_email" 
          name="email" 
          placeholder="ejemplo@gmail.com" 
          required 
        />
      </div>

      <div class="form-group">
        <label for="google_nombre">
          Nombre (opcional):
        </label>
        <input 
          type="text" 
          id="google_nombre" 
          name="nombre" 
          placeholder="Tu Nombre" 
        />
      </div>

      <button type="submit" class="google-auth-submit">
        Continuar con Google
      </button>
    </form>

    <div class="google-auth-footer">
      <a href="login.php" class="link">
        ← Volver a iniciar sesión regular
      </a>
    </div>

  </div>
</body>
</html>

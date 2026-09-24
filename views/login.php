<?php

/**
 * Responsabilidad: Inicio de sesión para estudiantes, docentes y administradores.
 */

require_once '../php/auth/sesion.php';
require_once '../php/utils/recaptcha.php';

iniciar_sesion();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


if (esta_autenticado()) {
    header("Location: usuario.php");
    exit;
}

$login_error = $_SESSION["login_error"] ?? "";
$login_email = $_SESSION["login_email"] ?? "";
$login_success = $_SESSION["login_success"] ?? "";

unset($_SESSION["login_error"]);
unset($_SESSION["login_email"]);
unset($_SESSION["login_success"]);

$title = 'Iniciar sesión';
$description = 'Inicio de sesión en Classia.';
$cssPrefix = '..';
$jsPrefix    = '..';

$bodyClass = 'auth-page';
$activePage = 'cuenta';
$recaptcha_site_key = obtener_recaptcha_site_key();

include '../includes/header.php';
?>

<?php if ($recaptcha_site_key !== ''): ?>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
<?php endif; ?>

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

        <?php if ($login_success !== ""): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($login_success); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['2fa_desactivado'])): ?>
            <div class="alert alert-success">
                La autenticación en dos pasos ha sido desactivada.
            </div>
        <?php endif; ?>

        <?php if ($login_error !== ""): ?>
            <div class="alert alert-danger auth-error" role="alert">
                <?php echo htmlspecialchars($login_error); ?>
            </div>
        <?php endif; ?>

        <form action="../php/auth/procesar_login.php" method="POST" id="form-login">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" />
            <p>
                <label for="email">Correo electrónico</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    autocomplete="email"
                    required
                    value="<?php echo htmlspecialchars($login_email); ?>"
                    placeholder="ejemplo@correo.com"
                />
            </p>

            <p>
                <label for="password">Contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    autocomplete="current-password"
                    required
                    placeholder="Ingresá tu contraseña"
                />
            </p>

            <?php if ($recaptcha_site_key !== ''): ?>
              <div class="form-group recaptcha-wrapper">
                <div class="g-recaptcha" data-sitekey="<?php echo htmlspecialchars($recaptcha_site_key); ?>"></div>
              </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-full">
                Iniciar sesión
            </button>
        </form>

        <p class="auth-links">
            <a href="restablecer-contrasena.php">¿Olvidaste tu contraseña?</a>
        </p>

        <p class="auth-links">
            ¿No tenés cuenta? <a href="registro.php">Registrate</a>
        </p>

        <div class="auth-divider" aria-hidden="true">
            <span>o continuá con</span>
        </div>

        <div class="auth-social-buttons">
            <a
                href="../php/auth/inicio_oauth_google.php"
                class="btn-google"
                id="btn-google-login"
                aria-label="Iniciar sesión con Google"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 48 48"
                    width="20"
                    height="20"
                    aria-hidden="true"
                    focusable="false"
                >
                    <path
                        fill="#EA4335"
                        d="M24 9.5c3.14 0 5.95 1.08 8.17 2.86l6.1-6.1C34.46 3.06 29.52 1 24 1 14.82 1 6.97 6.48 3.41 14.34l7.12 5.53C12.3 13.38 17.68 9.5 24 9.5z"
                    />
                    <path
                        fill="#4285F4"
                        d="M46.52 24.5c0-1.64-.15-3.22-.42-4.75H24v9h12.7c-.55 2.97-2.2 5.48-4.68 7.17l7.18 5.58C43.18 37.5 46.52 31.5 46.52 24.5z"
                    />
                    <path
                        fill="#FBBC05"
                        d="M10.53 28.36A14.57 14.57 0 0 1 9.5 24c0-1.51.26-2.97.72-4.36l-7.12-5.53A23.94 23.94 0 0 0 0 24c0 3.86.93 7.5 2.56 10.72l7.97-6.36z"
                    />
                    <path
                        fill="#34A853"
                        d="M24 47c5.52 0 10.15-1.83 13.53-4.97l-7.18-5.58C28.56 37.73 26.38 38.5 24 38.5c-6.32 0-11.68-3.88-13.47-9.14l-7.97 6.36C6.97 43.52 14.82 47 24 47z"
                    />
                </svg>
                Iniciar sesión con Google
            </a>

            <a
                href="../php/auth/inicio_oauth_github.php"
                class="btn-github"
                id="btn-github-login"
                aria-label="Iniciar sesión con GitHub"
            >
                <svg
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    width="20"
                    height="20"
                    fill="currentColor"
                    aria-hidden="true"
                    focusable="false"
                >
                    <path
                        fill-rule="evenodd"
                        clip-rule="evenodd"
                        d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.53 1.032 1.53 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                    />
                </svg>
                Iniciar sesión con GitHub
            </a>
        </div>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

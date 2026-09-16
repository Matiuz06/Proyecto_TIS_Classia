<?php
require_once '../php/auth/sesion.php';

iniciar_sesion();

if (esta_autenticado()) {
    header('Location: usuario.php');
    exit;
}

$pending = $_SESSION['2fa_pending'] ?? null;
if (!$pending || empty($pending['id_usuario']) || (time() - ($pending['tiempo'] ?? 0)) > 300) {
    unset($_SESSION['2fa_pending']);
    $_SESSION['login_error'] = 'La sesión de verificación ha expirado. Por favor, ingresá nuevamente.';
    header('Location: login.php');
    exit;
}

$error_2fa = $_SESSION['2fa_error'] ?? '';
unset($_SESSION['2fa_error']);

$title = 'Verificación en dos pasos';
$description = 'Ingresá tu código de verificación de 2 factores en Classia.';
$cssPrefix = '..';
$jsPrefix    = '..';

$bodyClass = 'auth-page';
$activePage = 'cuenta';

include '../includes/header.php';
?>

<main class="auth-shell">
    <section class="auth-intro" aria-labelledby="intro-2fa">
        <h1 id="intro-2fa">Verificación de Seguridad</h1>
        <p>
            Tu cuenta tiene activada la autenticación en dos pasos (2FA) para máxima protección.
        </p>
    </section>

    <section class="auth-card" aria-labelledby="titulo-2fa">
        <div class="u-center u-mb-xl">
            <div class="icon-circle-brand">
                🔐
            </div>
            <h2 id="titulo-2fa" class="u-mb-xs">Ingresá tu código 2FA</h2>
            <p class="text-muted u-text-base">
                Abre tu aplicación de autenticación (Google Authenticator, Microsoft Authenticator, Authy, etc.) e ingresá el código de 6 dígitos.
            </p>
        </div>

        <?php if ($error_2fa !== ''): ?>
            <div class="alert alert-danger" role="alert">
                <?= htmlspecialchars($error_2fa) ?>
            </div>
        <?php endif; ?>

        <form action="../php/auth/procesar_2fa.php" method="POST" id="form-2fa">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />

            <div class="form-group u-mb-xl">
                <label for="codigo_2fa"><strong>Código de seguridad:</strong></label>
                <input
                    type="text"
                    id="codigo_2fa"
                    name="codigo_2fa"
                    inputmode="numeric"
                    autocomplete="one-time-code"
                    placeholder="123456 ó código de respaldo"
                    required
                    autofocus
                    maxlength="16"
                    class="input-otp"
                />
            </div>

            <button type="submit" class="btn btn-full">
                Verificar e iniciar sesión
            </button>
        </form>

        <p class="auth-links u-mt-xl">
            <a href="login.php">← Cancelar y volver al inicio de sesión</a>
        </p>
    </section>
</main>

<?php include '../includes/footer.php'; ?>

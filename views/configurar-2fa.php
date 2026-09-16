<?php
require_once '../php/auth/configurar_2fa.php';

$title     = 'Configurar Autenticación en Dos Pasos (2FA)';
$cssPrefix = '..';
$jsPrefix  = '..';
$activePage = 'cuenta';

include '../includes/header.php';
?>

<main class="product-detail-container motion-entry">
  <div class="card-container payment-card card-narrow">
    <span class="brand-mark">Seguridad Classia</span>
    <h1>Autenticación en Dos Pasos (2FA)</h1>
    <p class="text-muted">
      Añade una capa adicional de seguridad a tu cuenta mediante aplicaciones como 
      <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong> o <strong>Authy</strong>.
    </p>

    <?php if (!empty($error_setup)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($error_setup) ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_GET['exito']) || !empty($backup_codes_nuevos)): ?>
      <div class="alert alert-success" role="alert">
        <h4 class="u-mt-0">¡Autenticación en dos pasos activada con éxito! 🎉</h4>
        <p>A partir de tu próximo inicio de sesión, se te solicitará el código de 6 dígitos de tu aplicación autenticadora.</p>
        <?php if (!empty($backup_codes_nuevos)): ?>
          <div class="backup-codes-wrapper">
            <strong>⚠️ Códigos de Respaldo de Emergencia:</strong>
            <p class="u-text-sm u-mb-sm">Guardá estos códigos en un lugar seguro. Cada uno sirve para iniciar sesión una sola vez si perdés acceso a tu celular.</p>
            <div class="backup-codes-grid">
              <?php foreach ($backup_codes_nuevos as $code): ?>
                <div><?= htmlspecialchars($code) ?></div>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endif; ?>
        <p class="u-mt-md"><a href="usuario.php" class="btn">Volver a Mi Cuenta</a></p>
      </div>
    <?php elseif ($esta_activo): ?>
      <div class="alert-box-success">
        <h3 class="u-mb-sm u-inline-flex-center u-gap-sm">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"></polyline></svg>
          Tu cuenta está protegida con 2FA
        </h3>
        <p class="u-mb-0 u-text-md">
          Cada vez que inicies sesión se te solicitará tu clave y el código dinámico de tu aplicación autenticadora.
        </p>
      </div>

      <div class="section-divider-top">
        <h3>Desactivar Autenticación en Dos Pasos</h3>
        <p class="text-muted u-text-base">
          Si necesitas desactivar el 2FA, ingresa tu contraseña actual para confirmar:
        </p>
        <form action="../php/auth/desactivar_2fa.php" method="POST" class="u-mt-md">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
          <div class="form-group">
            <label for="contrasena_confirmar"><strong>Contraseña actual:</strong></label>
            <input type="password" id="contrasena_confirmar" name="contrasena_confirmar" required placeholder="Ingresá tu contraseña" />
          </div>
          <div class="account-security-actions u-mt-md">
            <button type="submit" class="btn btn-danger-solid">Desactivar 2FA</button>
            <a href="usuario.php" class="btn btn-ghost">Cancelar</a>
          </div>
        </form>
      </div>
    <?php else: ?>
      <div class="two-factor-setup-steps">
        <div class="setup-step-box">
          <h3 class="u-mt-0">Paso 1: Escanea el código QR</h3>
          <p class="text-muted u-text-base">
            Abrí <strong>Google Authenticator</strong>, <strong>Microsoft Authenticator</strong> o tu app TOTF preferida y escaneá este código:
          </p>
          <div class="two-factor-qr-wrapper">
            <div class="two-factor-qr-box">
              <img src="<?= htmlspecialchars($qr_image_url) ?>" alt="Código QR para 2FA" width="180" height="180" class="two-factor-qr-img" />
            </div>
            <div class="u-flex-1 u-min-w-240">
              <p class="u-text-sm u-mb-xs">¿No puedes escanear el QR? Ingresa esta clave manualmente en tu app:</p>
              <code class="two-factor-secret-code">
                <?= htmlspecialchars($setup_secret) ?>
              </code>
            </div>
          </div>
        </div>

        <div class="setup-step-box">
          <h3 class="u-mt-0">Paso 2: Confirmá el código de 6 dígitos</h3>
          <p class="text-muted u-text-base">
            Ingresa el código numérico temporal que aparece en tu aplicación para verificar la sincronización:
          </p>
          <form action="../php/auth/activar_2fa.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />
            <div class="form-group u-max-w-280 u-mb-lg card-two-factor ">
              <label for="codigo_confirmacion"><strong>Código de 6 dígitos:</strong></label>
              <input type="text" id="codigo_confirmacion" name="codigo_confirmacion" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" placeholder="123456" required autofocus class="input-otp" />
            </div>
            <div class="account-security-actions">
              <button type="submit" class="btn">Confirmar y Activar 2FA</button>
              <a href="usuario.php" class="btn btn-ghost">Cancelar</a>
            </div>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <p class="u-mt-2xl u-center">
      <a href="usuario.php" class="link">← Volver a Mi Cuenta</a>
    </p>
  </div>
</main>

<?php include '../includes/footer.php'; ?>

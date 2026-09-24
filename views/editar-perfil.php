<?php

/**
 * Responsabilidad: Edición de datos personales del perfil de usuario.
 */

require_once '../php/usuarios/perfil.php';
require_once '../php/utils/cedula_uy.php';

$title     = 'Modificar datos personales';
$cssPrefix = '..';
$jsPrefix  = '..';
$activePage = 'cuenta';
include '../includes/header.php';

$COOLDOWN_DIAS = 30;
$tiene_cedula = !empty($userData['cedula_identidad']);
$datos_cambiados_en = $userData['datos_cambiados_en'] ?? null;
$cooldown_activo = false;
$dias_restantes = 0;
if ($datos_cambiados_en) {
    $diff = (new DateTime())->diff(new DateTime($datos_cambiados_en));
    $dias_transcurridos = (int) $diff->days;
    if ($dias_transcurridos < $COOLDOWN_DIAS) {
        $cooldown_activo = true;
        $dias_restantes = $COOLDOWN_DIAS - $dias_transcurridos;
    }
}
$email_pendiente = $userData['nuevo_email_pendiente'] ?? null;
?>

  <main class="product-detail-container motion-entry">
    <div class="card-container payment-card card-narrow-650">
      <span class="brand-mark">Classia</span>
      <h1>Modificar Datos Personales</h1>
      <p class="text-muted">Actualizá tu información de contacto y datos básicos.</p>

      <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($mensaje_error) ?>
        </div>
      <?php endif; ?>

      <?php if (!empty($mensaje_exito)): ?>
        <div class="alert alert-success" role="alert">
          <?= htmlspecialchars($mensaje_exito) ?>
        </div>
      <?php endif; ?>

      <?php if ($email_pendiente): ?>
        <div class="alert alert-info" role="alert">
          <strong>Verificación de nuevo email pendiente:</strong>
          Se envió un enlace de confirmación a <strong><?= htmlspecialchars($email_pendiente) ?></strong>.
          El cambio se aplicará recién cuando hagas clic en ese enlace.
          Tu email actual sigue siendo <strong><?= htmlspecialchars($userData['email']) ?></strong>.
        </div>
      <?php endif; ?>

      <?php if ($cooldown_activo): ?>
        <div class="alert alert-warning" role="alert">
          Podrás cambiar tu email y teléfono nuevamente en <strong><?= $dias_restantes ?> día<?= $dias_restantes !== 1 ? 's' : '' ?></strong>.
          Los cambios de datos de contacto tienen un período de espera de <?= $COOLDOWN_DIAS ?> días.
        </div>
      <?php endif; ?>

      <form action="../php/usuarios/actualizar_datos.php" method="POST" class="google-auth-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>" />

        <div class="form-group">
          <label for="nombre"><strong>Nombre:*</strong></label>
          <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($userData['nombre']) ?>" required />
        </div>

        <div class="form-group">
          <label for="apellido"><strong>Apellido:</strong></label>
          <input type="text" id="apellido" name="apellido" value="<?= htmlspecialchars($userData['apellido'] ?? '') ?>" />
        </div>

        <div class="form-group">
          <label for="cedula_display"><strong>Cédula de Identidad Uruguaya:</strong></label>
          <?php if ($tiene_cedula): ?>
            <input
              type="text"
              id="cedula_display"
              value="<?= htmlspecialchars(enmascarar_ci($userData['cedula_identidad'])) ?>"
              readonly disabled class="input-disabled-state"
            />
            <small class="form-hint form-hint-block">
              🔒 <em>La Cédula de Identidad no puede modificarse por motivos de verificación y seguridad.</em>
            </small>
          <?php else: ?>
            <input
              type="text"
              id="cedula_identidad"
              name="cedula_identidad"
              pattern="[0-9.\-\s]{6,12}"
              placeholder="Ej: 1.234.567-8"
              aria-describedby="ci-edit-help"
            />
            <small id="ci-edit-help" class="form-hint form-hint-block">
              ⚠️ <em>Solo podrás ingresarla <strong>una única vez</strong>. No se podrá modificar después.</em>
            </small>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="email"><strong>Email:*</strong></label>
          <?php if ($cooldown_activo): ?>
            <input type="email" id="email" value="<?= htmlspecialchars($userData['email']) ?>" readonly disabled class="input-disabled-state" />
            <input type="hidden" name="email" value="<?= htmlspecialchars($userData['email']) ?>" />
            <small class="form-hint form-hint-block">Disponible para cambiar en <?= $dias_restantes ?> día<?= $dias_restantes !== 1 ? 's' : '' ?>.</small>
          <?php else: ?>
            <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required />
            <small class="form-hint form-hint-block">📧 Al cambiar tu email recibirás un correo de verificación en la nueva dirección antes de que se aplique el cambio.</small>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="telefono"><strong>Número de teléfono:</strong></label>
          <?php if ($cooldown_activo): ?>
            <input type="tel" id="telefono" value="<?= htmlspecialchars($userData['telefono'] ?? '') ?>" readonly disabled class="input-disabled-state" />
            <input type="hidden" name="telefono" value="<?= htmlspecialchars($userData['telefono'] ?? '') ?>" />
            <small class="form-hint form-hint-block">Disponible para cambiar en <?= $dias_restantes ?> día<?= $dias_restantes !== 1 ? 's' : '' ?>.</small>
          <?php else: ?>
            <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($userData['telefono'] ?? '') ?>" placeholder="Ej: +598 99 123 456" />
          <?php endif; ?>
        </div>

        <div class="account-security-actions mt-section">
          <button type="submit" class="btn">Guardar cambios</button>
          <a href="usuario.php" class="btn btn-ghost">Cancelar</a>
        </div>
      </form>
    </div>
  </main>

<?php include '../includes/footer.php'; ?>

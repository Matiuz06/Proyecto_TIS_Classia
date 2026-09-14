<?php
require_once '../php/usuarios/perfil.php';

$title     = 'Modificar datos personales';
$cssPrefix = '..';
$jsPrefix  = '..';

$activePage = 'cuenta';
include '../includes/header.php';
?>

  <main class="product-detail-container motion-entry">
    <div class="card-container payment-card">
      <span class="brand-mark">Classia</span>
      <h1>Modificar Datos Personales</h1>
      <p class="text-muted">Actualizá tu información de contacto y datos básicos.</p>

      <?php if (!empty($mensaje_error)): ?>
        <div class="alert alert-danger" role="alert">
          <?= htmlspecialchars($mensaje_error) ?>
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
          <label for="email"><strong>Email:*</strong></label>
          <input type="email" id="email" name="email" value="<?= htmlspecialchars($userData['email']) ?>" required />
        </div>

        <div class="form-group">
          <label for="telefono"><strong>Número de teléfono:</strong></label>
          <input type="tel" id="telefono" name="telefono" value="<?= htmlspecialchars($userData['telefono'] ?? '') ?>" placeholder="Ej: +598 99 123 456" />
        </div>

        <div class="account-security-actions mt-section">
          <button type="submit" class="btn">Guardar cambios</button>
          <a href="usuario.php" class="btn btn-ghost">Cancelar</a>
        </div>
      </form>
    </div>
  </main>

<?php include '../includes/footer.php'; ?>

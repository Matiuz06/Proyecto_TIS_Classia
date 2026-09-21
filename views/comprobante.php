<?php
require_once __DIR__ . '/../php/auth/sesion.php';
require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../php/pagos/comprobante_pago.php';

iniciar_sesion();
requerir_autenticacion('login.php');

$usuario = usuario_actual();
$id_usuario = (int) $usuario['id_usuario'];
$id_contratacion = filter_input(INPUT_GET, 'id_contratacion', FILTER_VALIDATE_INT);
$msg_correo = '';
$tipo_msg = 'info';

if (!$id_contratacion || $id_contratacion <= 0) {
    header('Location: usuario.php');
    exit;
}

$comprobante = obtener_datos_comprobante($pdo, $id_contratacion, $id_usuario);

if (!$comprobante) {
    $_SESSION['alerta_error'] = 'No se encontró el comprobante solicitado o no tenés permisos para visualizarlo.';
    header('Location: usuario.php');
    exit;
}

// Acción de reenvío por correo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'enviar_email') {
    $token = $_POST['csrf_token'] ?? '';
    if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        $email_destino = trim($_POST['email_destino'] ?? $comprobante['email']);
        if (filter_var($email_destino, FILTER_VALIDATE_EMAIL)) {
            $enviado = enviar_comprobante_email($pdo, $id_contratacion, $email_destino);
            if ($enviado) {
                $msg_correo = 'Comprobante enviado exitosamente a ' . htmlspecialchars($email_destino);
                $tipo_msg = 'success';
            } else {
                $msg_correo = 'No se pudo enviar el correo en este momento. Verificá la configuración del servidor.';
                $tipo_msg = 'danger';
            }
        } else {
            $msg_correo = 'La dirección de correo electrónico ingresada es inválida.';
            $tipo_msg = 'danger';
        }
    }
}

$title       = 'Comprobante de Pago — Classia';
$description = 'Comprobante digital de transacción y contratación de servicios en Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = 'cuenta';
include '../includes/header.php';
?>

  <main class="receipt-page motion-entry">
    <div class="receipt-actions u-no-print">
      <a href="usuario.php" class="btn btn-secondary">← Volver a mi cuenta</a>
      <div>
        <button type="button" onclick="window.print()" class="btn btn-primary">
          Imprimir / Guardar PDF
        </button>
      </div>
    </div>

    <?php if ($msg_correo !== ''): ?>
      <div class="alert alert-<?= $tipo_msg ?> u-no-print">
        <?= htmlspecialchars($msg_correo) ?>
      </div>
    <?php endif; ?>

    <article class="receipt-card">
      <!-- Encabezado del comprobante -->
      <div class="receipt-header">
        <div>
          <img src="<?= $cssPrefix ?>/assets/images/logo-classia.png" alt="Classia" class="receipt-header__logo" />
          <p class="receipt-header__sub">AniTech · Plataforma Educativa &amp; Servicios</p>
        </div>
        <div class="receipt-header__meta">
          <span class="receipt-badge-approved">
            ✓ Pago <?= htmlspecialchars($comprobante['estado_pago'] ?? 'Aprobado') ?>
          </span>
          <p class="receipt-ref-code">
            Ref: <?= htmlspecialchars($comprobante['transaccion_ref'] ?? ('ORD-' . $comprobante['id_contratacion'])) ?>
          </p>
        </div>
      </div>

      <!-- Datos generales de la transacción -->
      <div class="receipt-meta-grid">
        <div>
          <span class="receipt-meta-item__label">Cliente</span>
          <strong class="receipt-meta-item__value"><?= htmlspecialchars($comprobante['nombre'] . ' ' . $comprobante['apellido']) ?></strong>
          <p class="receipt-meta-item__sub"><?= htmlspecialchars($comprobante['email']) ?></p>
        </div>
        <div>
          <span class="receipt-meta-item__label">Fecha y Hora</span>
          <strong class="receipt-meta-item__value"><?= date('d/m/Y H:i', strtotime($comprobante['fecha_pago'] ?? $comprobante['fecha_contratacion'])) ?></strong>
          <p class="receipt-meta-item__sub">Método: <?= htmlspecialchars($comprobante['metodo_pago'] ?? 'Tarjeta') ?></p>
        </div>
        <div>
          <span class="receipt-meta-item__label">N.º de Orden</span>
          <strong class="receipt-meta-item__value">#<?= (int) $comprobante['id_contratacion'] ?></strong>
          <p class="receipt-meta-item__sub">Estado: <?= htmlspecialchars($comprobante['estado_contratacion']) ?></p>
        </div>
      </div>

      <!-- Tabla de ítems -->
      <table class="receipt-table">
        <thead>
          <tr>
            <th>Concepto / Publicación</th>
            <th class="u-text-center">Cant.</th>
            <th class="u-text-right">Precio</th>
            <th class="u-text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($comprobante['detalles'] as $item): ?>
            <tr>
              <td>
                <strong><?= htmlspecialchars($item['titulo']) ?></strong>
                <span class="u-text-muted u-text-xs u-block"><?= htmlspecialchars($item['tipo']) ?></span>
              </td>
              <td class="u-text-center"><?= (int) $item['cantidad'] ?></td>
              <td class="u-text-right">$<?= number_format((float) $item['precio_unitario'], 2, ',', '.') ?></td>
              <td class="u-text-right u-font-bold">$<?= number_format((float) $item['subtotal'], 2, ',', '.') ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td colspan="3" class="receipt-table__total-label">Total Abonado:</td>
            <td class="receipt-table__total-amount">
              $<?= number_format((float) $comprobante['monto_total'], 2, ',', '.') ?> UYU
            </td>
          </tr>
        </tfoot>
      </table>

      <!-- Pie del comprobante -->
      <div class="receipt-footer">
        <p>Comprobante emitido electrónicamente con validez institucional en Classia · AniTech.</p>
        <p>Por consultas de facturación o soporte: <a href="contacto.php">Contacto Classia</a></p>
      </div>
    </article>

    <!-- Caja para reenviar por correo -->
    <section class="receipt-email-box u-no-print">
      <h3>Enviar comprobante por correo</h3>
      <p class="u-text-muted u-text-xs">
        Puedes enviar una copia de este recibo a tu correo electrónico o a otra dirección.
      </p>
      <form method="post" action="comprobante.php?id_contratacion=<?= (int) $id_contratacion ?>" class="receipt-email-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>" />
        <input type="hidden" name="accion" value="enviar_email" />
        <input type="email" name="email_destino" value="<?= htmlspecialchars($comprobante['email']) ?>" required class="service-req-input" />
        <button type="submit" class="btn btn-primary">Enviar por Correo</button>
      </form>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

<?php
require_once __DIR__ . '/../php/contrataciones/carrito.php';

$mensaje_error = $mensaje_error ?? '';
$items = $items ?? [];
$total = $total ?? 0;

$title = 'Carrito de compras';
$cssPrefix = '..';
$jsPrefix    = '..';

$activePage = 'carrito';
include '../includes/header.php';
?>

    <main class="page-container">
      <header class="page-header">
        <p>Contratación y checkout</p>
        <h1>Carrito de compras</h1>
        <p>Revisá las publicaciones seleccionadas antes de proceder al pago seguro.</p>
      </header>

      <?php if ($mensaje_error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
      <?php endif; ?>

      <?php if (empty($items)): ?>
        <div class="card-container cart-empty-card">
          <div class="u-text-3xl u-mb-lg">🛒</div>
          <h2>Tu carrito está vacío</h2>
          <p class="text-muted">Explorá nuestro catálogo de cursos y servicios para comenzar.</p>
          <p class="u-mt-2xl"><a href="catalogo.php" class="btn">Explorar catálogo</a></p>
        </div>
      <?php else: ?>
        <div class="cart-items-stack">
          <?php foreach ($items as $item): ?>
            <article class="catalog-card motion-card" aria-labelledby="item-<?php echo (int) $item['id_publicacion']; ?>">
              <div class="placeholder-visual" aria-hidden="true"><?php echo htmlspecialchars($item['tipo']); ?></div>
              <div>
                <span class="badge"><?php echo htmlspecialchars($item['tipo']); ?></span>
                <h2 id="item-<?php echo (int) $item['id_publicacion']; ?>"><?php echo htmlspecialchars($item['titulo']); ?></h2>
                <p>Cantidad: <?php echo (int) $item['cantidad']; ?></p>
                <p><strong>$<?php echo number_format((float) $item['subtotal'], 2, ',', '.'); ?></strong></p>
                <form action="carrito.php" method="post">
                  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                  <input type="hidden" name="id_publicacion" value="<?php echo (int) $item['id_publicacion']; ?>">
                  <button type="submit" name="accion" value="quitar" class="btn btn-ghost btn-cart-remove">Quitar</button>
                </form>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <section class="panel order-summary motion-entry" aria-labelledby="resumen-pedido">
          <h2 id="resumen-pedido">Resumen del pedido</h2>
          <p>Subtotal: <strong>$<?php echo number_format($total, 2, ',', '.'); ?></strong></p>
          <p>Descuento: <strong>$0.00</strong></p>
          <p class="u-text-xl">Total: <strong>$<?php echo number_format($total, 2, ',', '.'); ?></strong></p>
          <form action="../php/contrataciones/crear_contratacion.php" method="post" id="form-confirmar-contratacion">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit" id="btn-proceder-pago" class="btn btn-full" <?php echo empty($items) ? 'disabled' : ''; ?>>
              Confirmar e ir a pagar →
            </button>
          </form>
          <?php if (!empty($items)): ?>
            <form action="carrito.php" method="post" class="u-mt-sm">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
              <button type="submit" name="accion" value="vaciar" class="btn btn-ghost u-full-width">Vaciar carrito</button>
            </form>
          <?php endif; ?>
          <p class="u-center u-mt-lg"><a href="catalogo.php" class="link">Seguir explorando catálogo</a></p>
        </section>
      <?php endif; ?>
    </main>

<?php include '../includes/footer.php'; ?>

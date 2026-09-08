<?php
require_once '../php/auth/session.php';
require_once '../config/database.php';

iniciar_sesion();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token_recibido = $_POST['csrf_token'] ?? '';
    $accion = $_POST['accion'] ?? '';
    $id_publicacion = (int) ($_POST['id_publicacion'] ?? 0);

    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token_recibido)) {
        $_SESSION['carrito_error'] = 'La sesion del formulario expiro. Recarga la pagina e intenta nuevamente.';
    } elseif ($accion === 'agregar' && $id_publicacion > 0) {
        $_SESSION['carrito_publicaciones'] = $_SESSION['carrito_publicaciones'] ?? [];
        if (!in_array($id_publicacion, $_SESSION['carrito_publicaciones'], true)) {
            $_SESSION['carrito_publicaciones'][] = $id_publicacion;
        }
        header('Location: catalogo.php?carrito=agregado');
        exit;
    } elseif ($accion === 'quitar' && $id_publicacion > 0) {
        $_SESSION['carrito_publicaciones'] = array_values(array_filter(
            $_SESSION['carrito_publicaciones'] ?? [],
            fn($id) => (int) $id !== $id_publicacion
        ));
    } elseif ($accion === 'vaciar') {
        unset($_SESSION['carrito_publicaciones']);
    }
}

$mensaje_error = $_SESSION['carrito_error'] ?? '';
unset($_SESSION['carrito_error']);

$ids_carrito = array_values(array_unique(array_map('intval', $_SESSION['carrito_publicaciones'] ?? [])));
$items = [];
$total = 0;

if (!empty($ids_carrito)) {
    $stmt = $pdo->prepare(
        "SELECT id_publicacion, titulo, precio, tipo, estado
         FROM publicaciones
         WHERE id_publicacion = :id_publicacion"
    );

    foreach ($ids_carrito as $id_publicacion) {
        $stmt->execute(['id_publicacion' => $id_publicacion]);
        $item = $stmt->fetch();

        if ($item && $item['estado'] === 'Activo') {
            $item['cantidad'] = 1;
            $item['subtotal'] = (float) $item['precio'];
            $items[] = $item;
            $total += (float) $item['subtotal'];
        }
    }
}

$title = 'Carrito de compras';
$cssPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

    <main class="page-container">
      <header class="page-header">
        <p>Contratacion</p>
        <h1>Carrito</h1>
        <p>Revisa las publicaciones seleccionadas antes de confirmar la contratacion.</p>
      </header>

      <?php if ($mensaje_error !== ''): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($mensaje_error); ?></div>
      <?php endif; ?>

      <?php if (empty($items)): ?>
        <p>No hay publicaciones en el carrito.</p>
      <?php else: ?>
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
                <button type="submit" name="accion" value="quitar">Quitar</button>
              </form>
            </div>
          </article>
        <?php endforeach; ?>
      <?php endif; ?>

      <section class="panel order-summary motion-entry" aria-labelledby="resumen-pedido">
        <h2 id="resumen-pedido">Resumen del pedido</h2>
        <p>Subtotal: <strong>$<?php echo number_format($total, 2, ',', '.'); ?></strong></p>
        <p>Descuento: <strong>$0.00</strong></p>
        <p>Total: <strong>$<?php echo number_format($total, 2, ',', '.'); ?></strong></p>
        <form action="../php/contrataciones/crear_contratacion.php" method="post">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
          <button type="submit" <?php echo empty($items) ? 'disabled' : ''; ?>>Confirmar contratacion</button>
        </form>
        <?php if (!empty($items)): ?>
          <form action="carrito.php" method="post">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit" name="accion" value="vaciar">Vaciar carrito</button>
          </form>
        <?php endif; ?>
        <p><a href="catalogo.php">Seguir explorando catalogo</a></p>
      </section>
    </main>

<?php include '../includes/footer.php'; ?>

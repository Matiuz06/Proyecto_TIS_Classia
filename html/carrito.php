<?php
$title     = 'Carrito de compras';
$cssPrefix = '..';
$activePage = 'carrito';
include '../includes/header.php';
?>

    <main class="page-container">
      <header class="page-header">
        <p>Compra simulada</p>
        <h1>Carrito</h1>
        <p>
          Este carrito muestra un único curso de demostración hasta que existan
          publicaciones reales.
        </p>
      </header>

      <article
        class="catalog-card motion-card"
        aria-labelledby="item-curso-demo">
        <div class="placeholder-visual" aria-hidden="true">Curso demo</div>
        <div>
          <span class="badge">Ejemplo</span>
          <h2 id="item-curso-demo">Robótica para principiantes</h2>
          <p>Educador: Juan Pérez</p>
          <p><strong>$29.99</strong></p>
          <p><a href="catalogo.php">Eliminar</a></p>
        </div>
      </article>

      <section
        class="panel order-summary motion-entry"
        aria-labelledby="resumen-pedido">
        <h2 id="resumen-pedido">Resumen del pedido</h2>
        <p>Subtotal: <strong>$29.99</strong></p>
        <p>Descuento: <strong>$0.00</strong></p>
        <p>Total: <strong>$29.99</strong></p>
        <form action="pasarelaPago.php" method="get">
          <p>
            <label for="cupon">¿Tenés un cupón de descuento?</label>
            <input
              type="text"
              id="cupon"
              name="cupon"
              placeholder="Código de descuento" />
          </p>
          <button type="submit">Ir a pagar</button>
        </form>
        <p><a href="catalogo.php">← Seguir explorando catálogo</a></p>
      </section>
    </main>

<?php include '../includes/footer.php'; ?>

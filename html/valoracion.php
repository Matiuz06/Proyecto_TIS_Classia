<?php
$title     = 'Valoración';
$cssPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>

    <main class="page-container">
      <header class="page-header">
        <span class="brand-mark">Classia</span>
        <h1>Dejá tu valoración</h1>
        <p>
          Tu opinión ayuda a mejorar los cursos y servicios publicados en
          Classia.
        </p>
      </header>

      <form action="/enviar_reseña" method="POST">
        <p>
          <label for="item_valorar">Elemento a valorar</label>
          <select name="item_valorar" id="item_valorar">
            <option>Curso demo: Robótica para principiantes</option>
          </select>
        </p>
        <div class="catalog-card motion-card">
          <div class="placeholder-visual" aria-hidden="true">Curso demo</div>
          <div>
            <h2>Robótica para principiantes</h2>
            <p>Categoría: curso de robótica · Demostración</p>
          </div>
        </div>
        <p>
          <label for="puntuacion">Calificación general</label>
          <select name="puntuacion" id="puntuacion">
            <option value="5">5 - Excelente</option>
            <option value="4">4 - Muy bueno</option>
            <option value="3">3 - Bueno</option>
            <option value="2">2 - Regular</option>
            <option value="1">1 - Malo</option>
          </select>
        </p>
        <p>
          <label for="comentario">Tu reseña u opinión</label>
          <textarea
            id="comentario"
            name="comentario"
            rows="4"
            placeholder="Escribí tu experiencia con el curso demo..."
            required></textarea>
        </p>
        <fieldset>
          <legend>¿Recomendarías este curso?</legend>
          <label
            ><input type="radio" name="recomienda" value="si" checked />
            Sí</label
          >
          <label><input type="radio" name="recomienda" value="no" /> No</label>
        </fieldset>
        <button type="submit">Enviar valoración</button>
      </form>
      <p>
        <a href="usuario.php">← Volver a mi perfil</a> |
        <a href="catalogo.php">Ir al catálogo</a>
      </p>
    </main>

<?php include '../includes/footer.php'; ?>

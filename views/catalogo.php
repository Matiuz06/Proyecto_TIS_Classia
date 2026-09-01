<?php
$title      = 'Catálogo de cursos y servicios';
$description = 'Catálogo de cursos y servicios educativos disponibles en Classia.';
$cssPrefix  = '..';
$activePage = 'catalogo';
include '../includes/header.php';
?>

    <main>
      <header>
        <p>Catálogo</p>
        <h1>Cursos y servicios educativos</h1>
        <p>
          Classia está preparando su catálogo público. Mientras se publican
          nuevas propuestas, podés revisar un curso de demostración y solicitar
          servicios educativos personalizados.
        </p>
        <form
          action="catalogo.php"
          method="get"
          role="search"
          class="catalog-tools">
          <p>
            <label for="busqueda">Buscar en Classia</label>
            <input
              id="Barrabusqueda"
              type="search"
              name="busqueda"
              placeholder="Curso, docente, servicio o temática" />
          </p>
          <button type="submit" class="btnAlignIzq">Buscar</button>
        </form>
      </header>

      <div class="catalog-layout">
        <aside class="catalog-filters" aria-labelledby="titulo-filtros">
          <h2 id="titulo-filtros">Filtros</h2>
          <form action="catalogo.php" method="get">
            <fieldset>
              <legend>Tipo</legend>
              <label for="tipo-curso"
                ><input
                  type="radio"
                  id="tipo-curso"
                  name="tipo"
                  value="curso"
                  checked />
                Cursos</label
              >
              <label for="tipo-servicio"
                ><input
                  type="radio"
                  id="tipo-servicio"
                  name="tipo"
                  value="servicio" />
                Servicios</label
              >
            </fieldset>
            <fieldset>
              <legend>Modalidad</legend>
              <label for="modalidad-virtual"
                ><input
                  type="checkbox"
                  id="modalidad-virtual"
                  name="modalidad"
                  value="virtual" />
                Virtual</label
              >
              <label for="modalidad-presencial"
                ><input
                  type="checkbox"
                  id="modalidad-presencial"
                  name="modalidad"
                  value="presencial" />
                Presencial</label
              >
              <label for="modalidad-hibrida"
                ><input
                  type="checkbox"
                  id="modalidad-hibrida"
                  name="modalidad"
                  value="hibrida" />
                Híbrida</label
              >
            </fieldset>
            <button type="submit">Aplicar filtros</button>
            <button class="botonLimpiar" type="reset">Limpiar</button>
          </form>
        </aside>

        <section id="cursos" aria-labelledby="titulo-cursos">
          <header>
            <p>Contenido disponible</p>
            <h2 id="titulo-cursos">Cursos</h2>
            <p>Cursos diversos para una aprendizaje profundo.</p>
          </header>

          <div class="catalog-grid">
            <article class="catalog-card" aria-labelledby="curso-demo">
              <div class="placeholder-visual" aria-hidden="true">Curso img</div>
              <div>
                <h3 id="curso-demo">Robótica para principiantes</h3>
                <p>
                  Introducción a conceptos básicos de robótica educativa,
                  sensores y pensamiento computacional.
                </p>
                <dl>
                  <div>
                    <dt>Modalidad</dt>
                    <dd>Virtual</dd>
                  </div>
                  <div>
                    <dt>Duración</dt>
                    <dd>6 horas</dd>
                  </div>
                  <div>
                    <dt>Nivel</dt>
                    <dd>Inicial</dd>
                  </div>
                </dl>
                <p class="catalog-actions">
                  <a class="btn" href="curso.php">Ver curso</a>
                </p>
              </div>
            </article>
            <h2 id="titulo-servicios">Servicios</h2>
            <p>Distintos servicios según tu necesidad.</p>

            <article
              class="catalog-card"
              aria-labelledby="servicio-impresion-3d">
              <div class="placeholder-visual" aria-hidden="true">
                Servicio img
              </div>

              <div>
                <h3 id="servicio-impresion-3d">Diseño e impresión 3D</h3>

                <p>
                  Diseñamos y materializamos tus ideas mediante modelado e
                  impresión 3D, adaptándonos a las características y necesidades
                  de cada proyecto.
                </p>

                <dl>
                  <div>
                    <dt>Modalidad</dt>
                    <dd>Virtual / Presencial</dd>
                  </div>

                  <div>
                    <dt>Tipo</dt>
                    <dd>Servicio personalizado</dd>
                  </div>

                  <div>
                    <dt>Entrega</dt>
                    <dd>A coordinar</dd>
                  </div>
                </dl>

                <p class="catalog-actions">
                  <a class="btn" href="solicitud-impresion-3d.php">
                    Solicitar servicio
                  </a>
                </p>
              </div>
            </article>

            <section class="empty-state" aria-labelledby="sin-mas-cursos">
              <h3 id="sin-mas-cursos">Aún no hay más cursos publicados</h3>
              <p>
                Cuando se carguen nuevas propuestas, aparecerán en este
                catálogo.
              </p>
            </section>
          </div>
        </section>
      </div>
    </main>

    <footer class="site-footer">
      <p>
        &copy; 2026 Classia. Plataforma educativa para estudiantes, docentes y
        administradores.
      </p>
    </footer>
</body>
</html>

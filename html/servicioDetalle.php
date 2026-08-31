<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Detalle de un servicio educativo o tecnológico publicado en Classia." />
    <title>Diseño e impresión de recursos didácticos 3D</title>
    <link rel="stylesheet" href="../css/animation.css" />
    <link rel="stylesheet" href="../css/style.css" />
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
  </head>

  <body>
    <header class="site-header">
      <div class="site-header__inner">
        <a class="site-brand" href="../index.php">
          <img src="../assets/logoC.png" />
        </a>
        <nav class="site-nav" aria-label="Navegación principal">
          <a href="../index.php">Inicio</a>
          <a href="catalogo.php" aria-current="page">Catálogo</a>
          <a href="carrito.php">Carrito</a>
          <a href="login.php">Mi cuenta</a>
        </nav>
      </div>
    </header>

    <main>
      <nav aria-label="Ruta de navegación">
        <ol>
          <li>
            <a href="../index.php">Inicio</a>
          </li>

          <li>
            <a href="catalogo.php">Servicios</a>
          </li>

          <li>
            <a href="catalogo.php?tipo=servicio&categoria=diseno-impresion-3d">
              Diseño e impresión 3D
            </a>
          </li>

          <li aria-current="page">
            Diseño e impresión de recursos didácticos 3D
          </li>
        </ol>
      </nav>

      <article>
        <header>
          <p>Diseño e impresión 3D</p>

          <h1>Diseño e impresión de recursos didácticos 3D</h1>

          <p>
            Diseño y fabricación de piezas, maquetas, prototipos y materiales
            personalizados para centros educativos, docentes y estudiantes.
          </p>

          <p>
            Publicado por
            <a href="usuario.php">
              <strong>Taller 3D Norte</strong>
            </a>
          </p>

          <p>
            <a href="#valoraciones"> 4,8 de 5 · 34 valoraciones </a>
          </p>
        </header>

        <div>
          <section aria-labelledby="titulo-contenido-visual">
            <h2 id="titulo-contenido-visual">Imágenes y video del servicio</h2>

            <figure>
              <span class="brand-mark">Classia</span>

              <figcaption>Ejemplo principal del trabajo realizado.</figcaption>
            </figure>

            <nav aria-label="Galería del servicio">
              <ul>
                <li>
                  <button type="button">Ver imagen principal</button>
                </li>

                <li>
                  <button type="button">Ver maqueta educativa</button>
                </li>

                <li>
                  <button type="button">Ver piezas para robótica</button>
                </li>

                <li>
                  <button type="button">Ver video del proceso</button>
                </li>
              </ul>
            </nav>

            <figure>
              <span class="brand-mark">Classia</span>

              <figcaption>
                Maqueta personalizada para una actividad educativa.
              </figcaption>
            </figure>

            <figure>
              <span class="brand-mark">Classia</span>

              <figcaption>
                Piezas diseñadas para proyectos de robótica.
              </figcaption>
            </figure>

            <figure>
              <video controls>
                Tu navegador no admite la reproducción de video.
              </video>

              <figcaption>
                Proceso de diseño e impresión de una pieza.
              </figcaption>
            </figure>
          </section>

          <aside aria-labelledby="titulo-contratacion">
            <h2 id="titulo-contratacion">Contratar este servicio</h2>

            <form action="pasarelaPago.php" method="get">
              <fieldset>
                <legend>Selecciona un paquete</legend>

                <label for="paquete-basico">
                  <input
                    type="radio"
                    id="paquete-basico"
                    name="paquete"
                    value="basico"
                    checked />

                  Básico
                </label>

                <label for="paquete-estandar">
                  <input
                    type="radio"
                    id="paquete-estandar"
                    name="paquete"
                    value="estandar" />

                  Estándar
                </label>

                <label for="paquete-premium">
                  <input
                    type="radio"
                    id="paquete-premium"
                    name="paquete"
                    value="premium" />

                  Premium
                </label>
              </fieldset>

              <section aria-labelledby="titulo-paquete">
                <h3 id="titulo-paquete">Paquete básico</h3>

                <p>
                  Impresión de una pieza a partir de un modelo proporcionado por
                  el cliente.
                </p>

                <p>
                  <strong>$850</strong>
                </p>

                <ul>
                  <li>1 pieza.</li>
                  <li>Tamaño máximo de 10 centímetros.</li>
                  <li>Material PLA.</li>
                  <li>Una revisión del archivo.</li>
                  <li>Entrega estimada en 5 días.</li>
                </ul>
              </section>

              <label for="cantidad"> Cantidad </label>

              <input
                type="number"
                id="cantidad"
                name="cantidad"
                min="1"
                max="10"
                value="1" />

              <fieldset>
                <legend>Forma de entrega</legend>

                <label for="retiro">
                  <input
                    type="radio"
                    id="retiro"
                    name="entrega"
                    value="retiro"
                    checked />

                  Retiro en el local
                </label>

                <label for="envio">
                  <input type="radio" id="envio" name="entrega" value="envio" />

                  Envío a coordinar
                </label>
              </fieldset>

              <section aria-labelledby="resumen-pago">
                <h3 id="resumen-pago">Resumen</h3>

                <dl>
                  <div>
                    <dt>Paquete</dt>
                    <dd>Básico</dd>
                  </div>

                  <div>
                    <dt>Cantidad</dt>
                    <dd>1</dd>
                  </div>

                  <div>
                    <dt>Subtotal</dt>
                    <dd>$850</dd>
                  </div>

                  <div>
                    <dt>Envío</dt>
                    <dd>A coordinar</dd>
                  </div>

                  <div>
                    <dt>Total estimado</dt>
                    <dd>$850</dd>
                  </div>
                </dl>
              </section>

              <button type="submit">Continuar con la contratación</button>
            </form>

            <p>El pago será simulado y no se procesará dinero real.</p>

            <a href="formsSolicitarServicio.php">
              Solicitar presupuesto personalizado
            </a>

            <button type="button">Guardar servicio</button>

            <a href="formsSolicitarServicio.php"> Contactar al proveedor </a>
          </aside>
        </div>

        <section aria-labelledby="descripcion-servicio">
          <h2 id="descripcion-servicio">Acerca de este servicio</h2>

          <p>
            Este servicio está orientado al diseño y fabricación de recursos
            tridimensionales para actividades educativas, proyectos
            tecnológicos, maquetas, prototipos y materiales adaptados.
          </p>

          <p>
            El cliente puede proporcionar un archivo ya diseñado o solicitar que
            el proveedor modele una pieza desde cero a partir de una idea,
            dibujo, imagen o descripción.
          </p>

          <h3>¿Qué podemos realizar?</h3>

          <ul>
            <li>Materiales didácticos.</li>
            <li>Figuras geométricas.</li>
            <li>Mapas y maquetas en relieve.</li>
            <li>Piezas para proyectos de robótica.</li>
            <li>Soportes para micro:bit y Arduino.</li>
            <li>Prototipos educativos.</li>
            <li>Piezas de reemplazo.</li>
            <li>Reconocimientos y recuerdos institucionales.</li>
          </ul>

          <h3>¿Qué incluye el servicio?</h3>

          <ul>
            <li>Revisión inicial del archivo.</li>
            <li>Evaluación de viabilidad.</li>
            <li>Estimación del tiempo de fabricación.</li>
            <li>Impresión en el material acordado.</li>
            <li>Terminación básica.</li>
            <li>Comunicación durante el proceso.</li>
          </ul>
        </section>

        <section aria-labelledby="caracteristicas">
          <h2 id="caracteristicas">Características</h2>

          <dl>
            <div>
              <dt>Categoría</dt>
              <dd>Diseño e impresión 3D</dd>
            </div>

            <div>
              <dt>Tipo de contratación</dt>
              <dd>Paquete o presupuesto personalizado</dd>
            </div>

            <div>
              <dt>Ubicación</dt>
              <dd>Salto, Uruguay</dd>
            </div>

            <div>
              <dt>Materiales</dt>
              <dd>PLA y PETG</dd>
            </div>

            <div>
              <dt>Formatos admitidos</dt>
              <dd>STL, OBJ y 3MF</dd>
            </div>

            <div>
              <dt>Precio inicial</dt>
              <dd>Desde $850</dd>
            </div>

            <div>
              <dt>Tiempo de entrega</dt>
              <dd>Entre 5 y 15 días</dd>
            </div>
          </dl>
        </section>

        <section aria-labelledby="proceso-servicio">
          <h2 id="proceso-servicio">¿Cómo funciona?</h2>

          <ol>
            <li>Selecciona un paquete o solicita un presupuesto.</li>

            <li>Envía el archivo o describe la pieza.</li>

            <li>El proveedor revisa la solicitud.</li>

            <li>Se confirma el precio y el plazo.</li>

            <li>El proveedor realiza el diseño o la impresión.</li>

            <li>Se coordina la entrega.</li>
          </ol>
        </section>

        <section aria-labelledby="requisitos">
          <h2 id="requisitos">Información necesaria</h2>

          <ul>
            <li>Archivo 3D, cuando ya exista.</li>
            <li>Descripción de la pieza.</li>
            <li>Medidas aproximadas.</li>
            <li>Cantidad de unidades.</li>
            <li>Color preferido.</li>
            <li>Uso previsto.</li>
            <li>Fecha en la que se necesita.</li>
          </ul>
        </section>

        <section aria-labelledby="proveedor">
          <h2 id="proveedor">Sobre el proveedor</h2>

          <span class="brand-mark">Classia</span>

          <h3>
            <a href="usuario.php"> Taller 3D Norte </a>
          </h3>

          <p>
            Taller especializado en diseño, prototipado e impresión 3D para
            proyectos educativos y tecnológicos.
          </p>

          <dl>
            <div>
              <dt>Ubicación</dt>
              <dd>Salto, Uruguay</dd>
            </div>

            <div>
              <dt>Tiempo de respuesta</dt>
              <dd>Menos de 4 horas</dd>
            </div>

            <div>
              <dt>Servicios completados</dt>
              <dd>42</dd>
            </div>

            <div>
              <dt>Valoración promedio</dt>
              <dd>4,8 de 5</dd>
            </div>
          </dl>

          <a href="usuario.php"> Ver perfil completo </a>
        </section>

        <section aria-labelledby="preguntas">
          <h2 id="preguntas">Preguntas frecuentes</h2>

          <details>
            <summary>¿Necesito tener un archivo 3D?</summary>

            <p>
              No. El proveedor puede diseñar la pieza a partir de una
              descripción o imagen.
            </p>
          </details>

          <details>
            <summary>¿Puedo elegir el color?</summary>

            <p>Sí, según la disponibilidad de materiales.</p>
          </details>

          <details>
            <summary>¿Cómo se calcula el precio?</summary>

            <p>
              El precio depende del tamaño, material, cantidad, complejidad y
              tiempo de impresión.
            </p>
          </details>

          <details>
            <summary>¿Realizan envíos?</summary>

            <p>Sí. El envío se coordina con el proveedor.</p>
          </details>
        </section>

        <section id="valoraciones" aria-labelledby="titulo-valoraciones">
          <header>
            <h2 id="titulo-valoraciones">Valoraciones</h2>

            <p>
              <strong>4,8 de 5</strong>
            </p>

            <p>34 valoraciones</p>
          </header>

          <article>
            <header>
              <h3>María González</h3>

              <p>5 de 5</p>

              <time datetime="2026-07-15"> 15 de julio de 2026 </time>
            </header>

            <p>
              El material quedó excelente y fue entregado dentro del plazo
              acordado.
            </p>
          </article>

          <article>
            <header>
              <h3>Centro Educativo del Norte</h3>

              <p>5 de 5</p>

              <time datetime="2026-06-28"> 28 de junio de 2026 </time>
            </header>

            <p>
              El proveedor comprendió la necesidad y realizó recomendaciones muy
              útiles.
            </p>
          </article>

          <a href="#valoraciones"> Ver todas las valoraciones </a>
        </section>

        <section aria-labelledby="relacionados">
          <h2 id="relacionados">Servicios relacionados</h2>

          <article>
            <h3>
              <a href="servicioDetalle.php">
                Diseño de piezas para proyectos de robótica
              </a>
            </h3>

            <p>Desde $1.200</p>
          </article>

          <article>
            <h3>
              <a href="servicioDetalle.php">
                Modelado 3D para maquetas educativas
              </a>
            </h3>

            <p>Desde $900</p>
          </article>

          <article>
            <h3>
              <a href="servicioDetalle.php">
                Asesoramiento para proyectos maker
              </a>
            </h3>

            <p>$750 por sesión</p>
          </article>
        </section>
      </article>
    </main>

    <footer class="site-footer">
      <p>
        Classia — Plataforma de comercialización de cursos y servicios
        educativos
      </p>

      <nav aria-label="Navegación secundaria">
        <ul>
          <li>
            <a href="../index.php">Inicio</a>
          </li>

          <li>
            <a href="catalogo.php">Catálogo</a>
          </li>

          <li>
            <a href="../index.php">Acerca de</a>
          </li>

          <li>
            <a href="../index.php">Contacto</a>
          </li>

          <li>
            <a href="../index.php">Privacidad</a>
          </li>
        </ul>
      </nav>

      <p>
        <small> &copy; 2026 Classia. Todos los derechos reservados. </small>
      </p>
    </footer>
  </body>
</html>

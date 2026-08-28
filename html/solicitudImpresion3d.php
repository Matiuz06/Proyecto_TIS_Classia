<!doctype html>
<html lang="es">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Solicita un presupuesto personalizado para diseño e impresión 3D en Classia." />
    <title>Solicitar impresión 3D | Classia</title>
    <link rel="icon" type="image/png" href="../assets/favicon.png" />
    <link rel="stylesheet" href="../css/animation.css" />
    <link rel="stylesheet" href="../css/style.css" />
  </head>

  <body>
    <header class="site-header">
      <div class="site-header__inner">
        <a
          class="site-brand"
          href="../index.php"
          aria-label="Classia - Inicio">
          <img src="../assets/logoC.png" alt="Classia" />
        </a>

        <nav class="site-nav" aria-label="Navegación principal">
          <a href="../index.php"> Inicio </a>

          <a href="catalogo.php" aria-current="page"> Catálogo </a>

          <a href="carrito.php"> Carrito </a>

          <a href="login.php"> Mi cuenta </a>
        </nav>
      </div>
    </header>

    <main class="solicitud-3d">
      <nav class="breadcrumb" aria-label="Ruta de navegación">
        <ol>
          <li>
            <a href="../index.php">Inicio</a>
          </li>

          <li>
            <a href="catalogo.php">Servicios</a>
          </li>

          <li aria-current="page">Diseño e impresión 3D</li>
        </ol>
      </nav>

      <header class="solicitud-3d__hero">
        <p class="eyebrow">Servicio personalizado</p>

        <h1>Convertí tu idea en una pieza real</h1>

        <p>
          Contanos qué necesitás fabricar. Podés enviarnos una idea, una imagen
          o directamente tu modelo 3D. El proveedor analizará el proyecto antes
          de enviarte un presupuesto.
        </p>

        <div class="servicio-3d-pasos">
          <div>
            <strong>1</strong>
            <span>Contanos tu idea</span>
          </div>

          <div>
            <strong>2</strong>
            <span>Evaluamos el proyecto</span>
          </div>

          <div>
            <strong>3</strong>
            <span>Recibís una propuesta</span>
          </div>
        </div>
      </header>

      <div class="solicitud-3d__layout">
        <form
          class="form-impresion-3d"
          action="confirmacion.php"
          method="post"
          enctype="multipart/form-data">
          <fieldset>
            <legend>1. ¿Qué necesitás?</legend>

            <p class="muted">
              Elegí la opción que mejor represente tu proyecto.
            </p>

            <div class="tipo-trabajo-grid">
              <label class="tipo-trabajo-card">
                <input
                  type="radio"
                  name="tipo-trabajo"
                  value="diseno"
                  required />

                <strong>Necesito el diseño</strong>

                <span>
                  Tengo una idea, dibujo o referencia y necesito crear el modelo
                  3D.
                </span>
              </label>

              <label class="tipo-trabajo-card">
                <input type="radio" name="tipo-trabajo" value="impresion" />

                <strong>Ya tengo el modelo</strong>

                <span>
                  Tengo un archivo STL, 3MF u OBJ listo para imprimir.
                </span>
              </label>

              <label class="tipo-trabajo-card">
                <input
                  type="radio"
                  name="tipo-trabajo"
                  value="diseno-impresion" />

                <strong>Diseño + impresión</strong>

                <span>
                  Necesito desarrollar el modelo y fabricar la pieza.
                </span>
              </label>

              <label class="tipo-trabajo-card">
                <input type="radio" name="tipo-trabajo" value="modificacion" />

                <strong>Modificar un modelo</strong>

                <span> Tengo un diseño pero necesito realizarle cambios. </span>
              </label>
            </div>
          </fieldset>

          <fieldset>
            <legend>2. Contanos sobre tu proyecto</legend>

            <label for="titulo-proyecto"> Nombre del proyecto o pieza </label>

            <input
              type="text"
              id="titulo-proyecto"
              name="titulo-proyecto"
              placeholder="Ej.: Soporte para micro:bit"
              required />

            <label for="descripcion"> ¿Qué querés fabricar? </label>

            <textarea
              id="descripcion"
              name="descripcion"
              rows="6"
              placeholder="Describí la pieza, para qué se utilizará y cualquier requisito importante."
              required></textarea>

            <label for="uso"> Uso de la pieza </label>

            <select id="uso" name="uso">
              <option value="">Seleccioná una opción</option>

              <option value="educativo">Recurso educativo</option>

              <option value="robotica">Robótica / electrónica</option>

              <option value="prototipo">Prototipo</option>

              <option value="repuesto">Repuesto</option>

              <option value="decoracion">Decoración</option>

              <option value="personal">Uso personal</option>

              <option value="otro">Otro</option>
            </select>
          </fieldset>

          <fieldset>
            <legend>3. Archivos de referencia</legend>

            <p class="muted">
              Si ya tenés un modelo, dibujo, fotografía o plano, podés
              adjuntarlo.
            </p>

            <label class="archivo-3d-box" for="archivo-3d">
              <strong> Adjuntar archivo </strong>

              <span> STL, 3MF, OBJ, JPG, PNG o PDF </span>

              <input
                type="file"
                id="archivo-3d"
                name="archivo-3d"
                accept=".stl,.3mf,.obj,.jpg,.jpeg,.png,.pdf" />
            </label>
          </fieldset>

          <fieldset>
            <legend>4. Características de la pieza</legend>

            <div class="form-grid-2">
              <div>
                <label for="medidas"> Medidas aproximadas </label>

                <input
                  type="text"
                  id="medidas"
                  name="medidas"
                  placeholder="Ej.: 12 × 8 × 4 cm" />
              </div>

              <div>
                <label for="cantidad"> Cantidad </label>

                <input
                  type="number"
                  id="cantidad"
                  name="cantidad"
                  min="1"
                  value="1"
                  required />
              </div>

              <div>
                <label for="material"> Material </label>

                <select id="material" name="material">
                  <option value="">Necesito asesoramiento</option>

                  <option value="pla">PLA</option>

                  <option value="petg">PETG</option>

                  <option value="otro">Otro</option>
                </select>
              </div>

              <div>
                <label for="color"> Color </label>

                <input
                  type="text"
                  id="color"
                  name="color"
                  placeholder="Ej.: Negro" />
              </div>
            </div>
          </fieldset>

          <fieldset>
            <legend>5. Plazo y presupuesto</legend>

            <div class="form-grid-2">
              <div>
                <label for="fecha-entrega"> ¿Para cuándo lo necesitás? </label>

                <input type="date" id="fecha-entrega" name="fecha-entrega" />
              </div>

              <div>
                <label for="presupuesto"> Presupuesto aproximado </label>

                <input
                  type="number"
                  id="presupuesto"
                  name="presupuesto"
                  min="0"
                  step="50"
                  placeholder="Ej.: 1500" />
              </div>
            </div>

            <p class="muted">
              Podés dejar el presupuesto vacío si todavía no sabés cuánto podría
              costar el proyecto.
            </p>
          </fieldset>

          <fieldset>
            <legend>6. Datos de contacto</legend>

            <div class="form-grid-2">
              <div>
                <label for="nombre"> Nombre y apellido </label>

                <input
                  type="text"
                  id="nombre"
                  name="nombre"
                  autocomplete="name"
                  required />
              </div>

              <div>
                <label for="correo"> Correo electrónico </label>

                <input
                  type="email"
                  id="correo"
                  name="correo"
                  autocomplete="email"
                  required />
              </div>

              <div>
                <label for="telefono"> Teléfono </label>

                <input
                  type="tel"
                  id="telefono"
                  name="telefono"
                  autocomplete="tel"
                  placeholder="099 123 456" />
              </div>

              <div>
                <label for="medio-contacto"> Medio de contacto </label>

                <select id="medio-contacto" name="medio-contacto" required>
                  <option value="">Seleccioná</option>

                  <option value="correo">Correo electrónico</option>

                  <option value="telefono">Teléfono</option>

                  <option value="classia">Mensajería Classia</option>
                </select>
              </div>
            </div>
          </fieldset>

          <fieldset>
            <legend>7. Información adicional</legend>

            <label for="comentarios"> ¿Hay algo más que debamos saber? </label>

            <textarea
              id="comentarios"
              name="comentarios"
              rows="4"
              placeholder="Detalles adicionales, restricciones, terminación deseada, referencias, etc."></textarea>

            <label class="check-line">
              <input type="checkbox" name="acepta-contacto" required />

              Autorizo al proveedor a contactarme respecto a esta solicitud.
            </label>

            <label class="check-line">
              <input type="checkbox" name="acepta-terminos" required />

              Confirmo que los datos proporcionados son correctos.
            </label>
          </fieldset>

          <section class="aviso-presupuesto">
            <h2>Enviar no significa comprar</h2>

            <p>
              <strong> No se realizará ningún cobro ahora. </strong>
              El proveedor evaluará la solicitud y te enviará una propuesta
              antes de cualquier contratación.
            </p>
          </section>

          <div class="action-row">
            <button type="submit">Solicitar presupuesto</button>

            <a class="btn btn-secondary" href="catalogo.php">
              Volver al catálogo
            </a>
          </div>
        </form>
        <aside class="resumen-servicio-3d">
          <section class="content-card">
            <p class="eyebrow">Servicio seleccionado</p>

            <h2>Diseño e impresión 3D</h2>

            <p>
              Servicio personalizado de diseño, preparación e impresión de
              piezas según tus necesidades.
            </p>

            <dl>
              <div>
                <dt>Diseño</dt>
                <dd>Disponible</dd>
              </div>

              <div>
                <dt>Impresión</dt>
                <dd>Disponible</dd>
              </div>

              <div>
                <dt>Material</dt>
                <dd>Según proyecto</dd>
              </div>

              <div>
                <dt>Entrega</dt>
                <dd>A coordinar</dd>
              </div>
            </dl>
          </section>

          <section class="content-card">
            <h3>¿Qué pasa después?</h3>

            <ol class="proceso-3d">
              <li>Revisamos tu solicitud.</li>

              <li>Evaluamos diseño, material y tiempo de impresión.</li>

              <li>Recibís una propuesta con precio y plazo.</li>

              <li>Decidís si querés continuar.</li>
            </ol>
          </section>
        </aside>
      </div>
    </main>
    <footer class="site-footer">
      <p>
        &copy; 2026 Classia. Cursos y servicios educativos en un solo lugar.
      </p>
    </footer>
  </body>
</html>

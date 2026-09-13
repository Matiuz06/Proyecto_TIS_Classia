<?php
$title       = 'Política de accesibilidad — Classia';
$description = 'Política de accesibilidad y uso responsable de Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main class="privacy-page motion-entry">
    <header>
      <p>Políticas</p>
      <h1>Política de accesibilidad</h1>
      <p>
        Classia busca brindar una experiencia digital accesible para personas con diferentes capacidades, respetando
        principios de inclusión, claridad y facilidad de uso.
      </p>
    </header>

    <section class="privacy-section" aria-labelledby="accesibilidad-objetivo">
      <h2 id="accesibilidad-objetivo"><span class="section-num" aria-hidden="true">1</span> Objetivo</h2>
      <p>
        Reducir barreras de acceso a la información y a las funcionalidades principales de la plataforma, entendiendo
        la accesibilidad como una prioridad de diseño y desarrollo.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-medidas">
      <h2 id="accesibilidad-medidas"><span class="section-num" aria-hidden="true">2</span> Medidas</h2>
      <ul>
        <li>Uso de contrastes adecuados y textos legibles.</li>
        <li>Estructura semántica del contenido con etiquetas claras.</li>
        <li>Compatibilidad con lectores de pantalla y navegación por teclado.</li>
        <li>Diseño sensible a distintos dispositivos y tamaños de pantalla.</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-estandar">
      <h2 id="accesibilidad-estandar"><span class="section-num" aria-hidden="true">3</span> Referencia y revisión</h2>
      <p>
        El diseño toma como referencia WCAG 2.1 nivel AA: texto alternativo, foco visible, orden lógico de encabezados,
        contraste suficiente, formularios etiquetados y mensajes de error comprensibles. La revisión se realiza con teclado,
        lector de pantalla y Lighthouse, sin sustituir pruebas con personas usuarias.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="accesibilidad-contacto">
      <h2 id="accesibilidad-contacto"><span class="section-num" aria-hidden="true">4</span> Comentarios</h2>
      <p>
        Si encontrás barreras de acceso o dificultades de uso, podés comunicarlo a través de <a href="contacto.php">Contacto</a>.
      </p>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

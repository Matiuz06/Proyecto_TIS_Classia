<?php
$title       = 'Política de cookies — Classia';
$description = 'Política de cookies y tecnologías similares de Classia.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main class="privacy-page motion-entry">
    <header>
      <p>Políticas</p>
      <h1>Política de cookies</h1>
      <p>
        La plataforma Classia utiliza cookies y tecnologías similares para mejorar la experiencia del usuario,
        mantener sesiones activas y analizar el uso de la aplicación.
      </p>
    </header>

    <section class="privacy-section" aria-labelledby="cookies-que-son">
      <h2 id="cookies-que-son"><span class="section-num" aria-hidden="true">1</span> ¿Qué son las cookies?</h2>
      <p>
        Las cookies son pequeños archivos de texto guardados en el navegador para recordar preferencias, facilitar la
        navegación y permitir ciertas funcionalidades esenciales de la plataforma.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="cookies-tipos">
      <h2 id="cookies-tipos"><span class="section-num" aria-hidden="true">2</span> Tipos de cookies</h2>
      <ul>
        <li><strong>Esenciales:</strong> permiten iniciar sesión, mantener la navegación y proteger la seguridad del sitio.</li>
        <li><strong>Funcionales:</strong> recuerdan preferencias relevantes del usuario.</li>
        <li><strong>Analíticas:</strong> ayudan a entender cómo se utiliza la plataforma para mejorar la experiencia.</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="cookies-configuracion">
      <h2 id="cookies-configuracion"><span class="section-num" aria-hidden="true">3</span> Configuración</h2>
      <p>
        El usuario puede gestionar o desactivar cookies desde la configuración de su navegador. Sin embargo, algunas
        cookies esenciales pueden ser necesarias para el correcto funcionamiento de la plataforma.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="cookies-sesion">
      <h2 id="cookies-sesion"><span class="section-num" aria-hidden="true">4</span> Sesión y entorno</h2>
      <p>
        Fuera de entornos locales, la cookie de sesión no se conserva al cerrar el navegador. En localhost, 127.0.0.1 y ::1
        se permite una duración de desarrollo de 30 días para facilitar las pruebas. La cookie es HttpOnly, usa SameSite=Lax
        y se marca Secure cuando la conexión utiliza HTTPS.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="cookies-analiticas">
      <h2 id="cookies-analiticas"><span class="section-num" aria-hidden="true">5</span> Analítica y consentimiento</h2>
      <p>
        No se deben incorporar cookies analíticas o publicitarias de terceros sin informar su finalidad y obtener el
        consentimiento correspondiente. Las recomendaciones de catálogo solo se personalizan cuando la persona acepta esa finalidad.
      </p>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

<?php
$title       = 'Divulgación responsable — Classia';
$description = 'Política de divulgación responsable de vulnerabilidades de Classia y AniTech S.A.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main class="privacy-page motion-entry">
    <header>
      <p>Seguridad</p>
      <h1>Divulgación responsable</h1>
      <p>
        Agradecemos a las personas investigadoras que nos ayudan a mantener
        Classia segura. Esta política explica cómo reportar vulnerabilidades
        de forma coordinada y responsable.
      </p>
    </header>

    <p class="update-banner">
      <strong>Última actualización:</strong> septiembre de 2026.
    </p>

    <section class="privacy-section" aria-labelledby="titulo-reporte">
      <h2 id="titulo-reporte">
        <span class="section-num" aria-hidden="true">1</span>
        Canal de reporte
      </h2>
      <p>
        Enviá el reporte a
        <a href="mailto:seguridad@anitech.com">seguridad@anitech.com</a>.
        Incluí una descripción clara, los pasos para reproducir el problema,
        las rutas o componentes afectados y el impacto estimado.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="titulo-alcance">
      <h2 id="titulo-alcance">
        <span class="section-num" aria-hidden="true">2</span>
        Alcance
      </h2>
      <p>
        Podés reportar vulnerabilidades que afecten a la aplicación Classia,
        sus mecanismos de autenticación, control de acceso, gestión de
        sesiones, exposición de datos o infraestructura bajo nuestro control.
        Si tenés dudas sobre el alcance, consultanos antes de realizar pruebas.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="titulo-compromisos">
      <h2 id="titulo-compromisos">
        <span class="section-num" aria-hidden="true">3</span>
        Nuestros compromisos
      </h2>
      <ul>
        <li>Confirmaremos la recepción del reporte dentro de 48 horas.</li>
        <li>Evaluaremos el problema y comunicaremos un plan inicial dentro de 5 días hábiles.</li>
        <li>Mantendremos la comunicación sobre el avance cuando sea posible.</li>
        <li>Reconoceremos la contribución de quienes lo soliciten, respetando su anonimato.</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="titulo-limites">
      <h2 id="titulo-limites">
        <span class="section-num" aria-hidden="true">4</span>
        Prácticas no permitidas
      </h2>
      <p>
        No está permitido acceder, modificar, eliminar o exponer datos de
        otras personas, degradar la disponibilidad del servicio, realizar
        ingeniería social, enviar spam, ejecutar ataques de denegación de
        servicio o probar sistemas de terceros sin autorización. Usá cuentas
        propias o entornos de prueba y detené la investigación si encontrás
        datos reales.
      </p>
    </section>

    <div class="privacy-contact-box">
      <h2>¿Encontraste un problema?</h2>
      <p>
        Reportalo de forma confidencial y coordinemos los próximos pasos.
      </p>
      <p class="action-row">
        <a class="btn" href="mailto:seguridad@anitech.com">Contactar a Seguridad</a>
        <a href="contacto.php">Ver datos de contacto</a>
      </p>
    </div>
  </main>

<?php include '../includes/footer.php'; ?>
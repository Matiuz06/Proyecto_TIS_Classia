<?php

/**
 * Responsabilidad: Política de cancelaciones y reembolsos — Regla de negocio RN-14.
 */

$title       = 'Política de cancelaciones y reembolsos — Classia';
$description = 'Política de cancelaciones y reembolsos de Classia, conforme a la regla de negocio RN-14 definida por AniTech para cada tipo de servicio contratado.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main id="main-content" class="privacy-page motion-entry">
    <header>
      <p>Políticas</p>
      <h1>Política de cancelaciones y reembolsos</h1>
      <p>
        Las cancelaciones o reembolsos deberán ajustarse a la política de cancelación definida por AniTech
        para cada tipo de servicio contratado dentro de la plataforma Classia. 
      </p>
    </header>

    <section class="privacy-section" aria-labelledby="cancel-alcance">
      <h2 id="cancel-alcance"><span class="section-num" aria-hidden="true">1</span> Alcance</h2>
      <p>
        Esta política aplica a todos los usuarios (estudiantes, clientes y proveedores) que hayan realizado una
        contratación a través de la plataforma Classia, ya sea de cursos o de servicios del catálogo institucional.
        La contratación se considerará confirmada únicamente cuando el estado de pago sea <strong>Aprobado.</strong>
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="cancel-plazos">
      <h2 id="cancel-plazos"><span class="section-num" aria-hidden="true">2</span> Plazos y condiciones de cancelación</h2>
      <p>
        Los plazos de cancelación varían según el tipo de servicio contratado:
      </p>
      <ul>
        <li>
          <strong>Cursos en línea:</strong> El estudiante podrá solicitar la cancelación dentro de las
          <strong>48 horas</strong> siguientes a la contratación, siempre que no haya accedido a más del
          <strong>20 %</strong> del contenido del curso.
        </li>
        <li>
          <strong>Servicios a medida (impresión 3D, tutorías, robótica, etc.):</strong> La cancelación deberá
          solicitarse antes del inicio efectivo del servicio. Una vez iniciado, no se procesarán reembolsos,
          salvo que exista incumplimiento por parte del proveedor.
        </li>
        <li>
          <strong>Servicios ya iniciados o entregados:</strong> No se gestionarán reembolsos una vez que el
          servicio haya sido marcado como <em>Completado</em> o que se haya entregado el producto acordado.
        </li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="cancel-proceso">
      <h2 id="cancel-proceso"><span class="section-num" aria-hidden="true">3</span> Proceso de solicitud de reembolso</h2>
      <p>
        Para iniciar una solicitud de cancelación o reembolso, el usuario deberá:
      </p>
      <ol>
        <li>Ingresar a su cuenta y acceder a la sección <strong>Mis solicitudes de servicios</strong>.</li>
        <li>Identificar la contratación y utilizar la opción de <em>Solicitar cancelación</em>, si está disponible según el estado actual.</li>
        <li>En caso de que la opción no esté disponible dentro de la plataforma, enviar un correo a
          <a href="mailto:anitechsa2026@gmail.com">anitechsa2026@gmail.com</a> indicando el número de
          contratación, motivo y fecha.</li>
      </ol>
      <p>
        El equipo de AniTech responderá dentro de los <strong>5 días hábiles</strong> siguientes a la
        recepción de la solicitud.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="cancel-mercadopago">
      <h2 id="cancel-mercadopago"><span class="section-num" aria-hidden="true">4</span> Reembolsos mediante MercadoPago</h2>
      <p>
        Cuando el pago haya sido procesado a través de <strong>MercadoPago</strong>, el reembolso se
        gestionará conforme a las condiciones y tiempos establecidos por dicha plataforma de cobro.
        AniTech iniciará la devolución desde su panel de MercadoPago una vez aprobada la solicitud.
        El plazo efectivo de acreditación dependerá del banco o medio de pago utilizado por el cliente
        (generalmente entre <strong>5 y 15 días hábiles</strong>).
      </p>
      <p>
        Para mayor información sobre los plazos de devolución de MercadoPago, consultá el
        <a href="https://www.mercadopago.com.uy/ayuda" rel="noopener noreferrer" target="_blank">
          Centro de Ayuda de MercadoPago
        </a>.
      </p>
    </section>

    <section class="privacy-section" aria-labelledby="cancel-excepciones">
      <h2 id="cancel-excepciones"><span class="section-num" aria-hidden="true">5</span> Excepciones y casos especiales</h2>
      <p>
        AniTech podrá realizar reembolsos totales o parciales fuera de los plazos indicados en los siguientes
        casos excepcionales, a criterio del equipo administrativo:
      </p>
      <ul>
        <li>Errores técnicos en la plataforma que hayan impedido el acceso al servicio contratado.</li>
        <li>Incumplimiento documentado por parte del proveedor o docente asignado.</li>
        <li>Cancelación del servicio o curso por decisión de AniTech.</li>
        <li>Fuerza mayor debidamente acreditada por el usuario.</li>
      </ul>
    </section>

    <section class="privacy-section" aria-labelledby="cancel-contacto">
      <h2 id="cancel-contacto"><span class="section-num" aria-hidden="true">6</span> Contacto</h2>
      <p>
        Para consultas sobre esta política o para iniciar una solicitud, escribí a:
        <a href="mailto:anitechsa2026@gmail.com">anitechsa2026@gmail.com</a>.
      </p>
      <p>
        Esta política fue aprobada por AniTech S.A. y podrá ser actualizada periódicamente.
        La versión vigente siempre estará disponible en esta página.
        Última actualización: <time datetime="2026-10">octubre de 2026</time>.
      </p>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

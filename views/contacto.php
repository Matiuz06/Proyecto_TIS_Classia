<?php
$errores = [];
$enviado = false;
require_once '../php/contacto/enviar.php';

$title       = 'Contacto — Classia';
$description = 'Contactá al equipo de Classia y AniTech S.A. Encontrá nuestros datos de contacto y envianos tu consulta.';
$cssPrefix   = '..';
$jsPrefix    = '..';
$activePage  = '';
include '../includes/header.php';
?>

  <main class="contact-page motion-entry">

    <header>
      <p>AniTech S.A.</p>
      <h1>Contacto</h1>
      <p>
        ¿Tenés alguna consulta, sugerencia o necesitás asistencia con la plataforma?
        Estamos disponibles para ayudarte.
      </p>
    </header>

    <div class="contact-layout">

      <!-- Tarjetas de contacto -->
      <section class="contact-cards" aria-label="Canales de contacto">

        <article class="contact-card">
          <h2 class="contact-card__title">Dirección</h2>
          <p class="contact-card__body">
            Florencio Sánchez 389<br>
            Salto, Uruguay
          </p>
        </article>

        <article class="contact-card">
          <h2 class="contact-card__title">Teléfono</h2>
          <p class="contact-card__body">
            <a href="tel:+59847333530">+598 4733 3530</a>
          </p>
        </article>

        <article class="contact-card">
          <h2 class="contact-card__title">Correo general</h2>
          <p class="contact-card__body">
            <a href="mailto:anitechsa2026@gmail.com">anitechsa2026@gmail.com</a>
          </p>
        </article>

        <article class="contact-card">
          <h2 class="contact-card__title">Privacidad de datos</h2>
          <p class="contact-card__body">
            Para consultas sobre el manejo de tus datos personales (Ley 18.331):<br>
            <a href="mailto:privacidad@classia.uy">privacidad@classia.uy</a>
          </p>
        </article>

        <article class="contact-card">
          <h2 class="contact-card__title">Seguridad</h2>
          <p class="contact-card__body">
            Para reportar vulnerabilidades de forma responsable:<br>
            <a href="mailto:seguridad@anitech.com">seguridad@anitech.com</a>
          </p>
        </article>

      </section>

      <!-- Formulario -->
      <section class="contact-form-wrap" aria-label="Formulario de contacto">
        <h2 class="contact-form-wrap__title">Envianos un mensaje</h2>
        <p class="contact-form-wrap__subtitle">
          Respondemos consultas generales en un plazo de <strong>2 días hábiles</strong>.
        </p>

        <form
          class="contact-form"
          id="contact-form"
          action="contacto.php"
          method="post"
          novalidate
          aria-label="Formulario de contacto"
        >
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
          <?php if ($enviado): ?>
            <div class="alert alert-success">Tu mensaje fue enviado correctamente.</div>
          <?php elseif ($errores): ?>
            <div class="alert alert-danger"><ul><?php foreach ($errores as $error): ?><li><?= htmlspecialchars($error) ?></li><?php endforeach; ?></ul></div>
          <?php endif; ?>
          <div class="contact-form__row">
            <div class="contact-form__field">
              <label for="contact-nombre">Nombre completo <span aria-hidden="true">*</span></label>
              <input
                type="text"
                id="contact-nombre"
                name="nombre"
                autocomplete="name"
                required
                placeholder="Tu nombre y apellido"
              />
            </div>

            <div class="contact-form__field">
              <label for="contact-email">Correo electrónico <span aria-hidden="true">*</span></label>
              <input
                type="email"
                id="contact-email"
                name="email"
                autocomplete="email"
                required
                placeholder="tu@correo.com"
              />
            </div>
          </div>

          <div class="contact-form__field">
            <label for="contact-asunto">Asunto <span aria-hidden="true">*</span></label>
            <select id="contact-asunto" name="asunto" required>
              <option value="" disabled selected>Seleccioná un tema</option>
              <option value="soporte">Soporte técnico</option>
              <option value="cuenta">Consulta sobre mi cuenta</option>
              <option value="facturacion">Facturación y pagos</option>
              <option value="privacidad">Privacidad y datos personales</option>
              <option value="sugerencia">Sugerencia o feedback</option>
              <option value="otro">Otro</option>
            </select>
          </div>

          <div class="contact-form__field">
            <label for="contact-mensaje">Mensaje <span aria-hidden="true">*</span></label>
            <textarea
              id="contact-mensaje"
              name="mensaje"
              rows="5"
              required
              placeholder="Describí tu consulta con el mayor detalle posible…"
            ></textarea>
          </div>

          <p class="contact-form__note">
            Al enviar este formulario aceptás nuestra
            <a href="politica-privacidad.php">Política de privacidad</a>.
            Los campos marcados con <span aria-hidden="true">*</span> son obligatorios.
          </p>

          <button type="submit" class="btn" id="btn-enviar-contacto">
            Enviar mensaje
          </button>
        </form>
      </section>

    </div>

  </main>

<?php include '../includes/footer.php'; ?>

<?php
$cssPrefix = $cssPrefix ?? '..';

$contactoHref      = ($cssPrefix === '.') ? 'views/contacto.php' : 'contacto.php';
$primerosPasosHref = ($cssPrefix === '.') ? 'views/primeros-pasos.php' : 'primeros-pasos.php';
$reglamentoHref    = ($cssPrefix === '.') ? 'views/reglamento.php' : 'reglamento.php';
$faqHref           = ($cssPrefix === '.') ? 'views/preguntas-frecuentes.php' : 'preguntas-frecuentes.php';
$privacidadHref    = ($cssPrefix === '.') ? 'views/politica-privacidad.php' : 'politica-privacidad.php';
$terminosHref      = ($cssPrefix === '.') ? 'views/terminos-de-servicio.php' : 'terminos-de-servicio.php';
$divulgHref        = ($cssPrefix === '.') ? 'views/divulgacion-responsable.php' : 'divulgacion-responsable.php';
$institucionalHref = ($cssPrefix === '.') ? 'views/institucional.php' : 'institucional.php';
$calidadHref       = ($cssPrefix === '.') ? 'views/politica-calidad.php' : 'politica-calidad.php';
$seguridadHref     = ($cssPrefix === '.') ? 'views/politica-seguridad.php' : 'politica-seguridad.php';
$cookiesHref       = ($cssPrefix === '.') ? 'views/politica-cookies.php' : 'politica-cookies.php';
$accesibilidadHref = ($cssPrefix === '.') ? 'views/politica-accesibilidad.php' : 'politica-accesibilidad.php';
?>
  <footer class="site-footer">
    <div class="site-footer__grid">

      <div class="site-footer__about">
        <h3 class="site-footer__brand-title">Classia</h3>
        <p class="site-footer__brand-desc">
          Classia es una plataforma educativa y de contratación de servicios especializados desarrollada por AniTech.
          Conectamos estudiantes, docentes, instituciones y profesionales para aprender, colaborar y acceder a
          oportunidades formativas con una experiencia segura, moderna y centrada en la comunidad.
        </p>
      </div>

      <div>
        <h4 class="site-footer__section-title">Institucional</h4>
        <ul class="site-footer__list">
          <li><a href="<?= $institucionalHref ?>">Nosotros</a></li>
          <li><a href="<?= $primerosPasosHref ?>">Primeros pasos</a></li>
          <li><a href="<?= $contactoHref ?>">Contacto</a></li>
          <li><a href="<?= $reglamentoHref ?>">Reglamento</a></li>
        </ul>
      </div>

      <div>
        <h4 class="site-footer__section-title">Políticas</h4>
        <ul class="site-footer__list">
          <li><a href="<?= $privacidadHref ?>">Política de privacidad</a></li>
          <li><a href="<?= $terminosHref ?>">Términos de servicio</a></li>
          <li><a href="<?= $divulgHref ?>">Divulgación responsable</a></li>
          <li><a href="<?= $seguridadHref ?>">Política de seguridad</a></li>
          <li><a href="<?= $calidadHref ?>">Política de calidad</a></li>
          <li><a href="<?= $cookiesHref ?>">Política de cookies</a></li>
          <li><a href="<?= $accesibilidadHref ?>">Política de accesibilidad</a></li>
        </ul>
      </div>

      <div>
        <h4 class="site-footer__section-title">Ayuda</h4>
        <ul class="site-footer__list">
          <li><a href="<?= $faqHref ?>">Preguntas frecuentes</a></li>
          <li><a href="<?= $reglamentoHref ?>">Reglas de uso</a></li>
        </ul>
      </div>

    </div>

    <div class="site-footer__bottom">
      <p class="site-footer__powered">
        Desarrollado por <a href="<?= $contactoHref ?>" class="site-footer__powered-link">AniTech S.A.</a>
      </p>
      <p>&copy; <?= date('Y') ?> Classia &middot; AniTech S.A. Todos los derechos reservados.</p>
    </div>
  </footer>
</body>
</html>

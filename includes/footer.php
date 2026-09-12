<?php
$cssPrefix      = $cssPrefix ?? '..';
$privacidadHref = ($cssPrefix === '.') ? 'views/politica-privacidad.php'   : 'politica-privacidad.php';
$contactoHref   = ($cssPrefix === '.') ? 'views/contacto.php'              : 'contacto.php';
$terminosHref   = ($cssPrefix === '.') ? 'views/terminos-de-servicio.php'  : 'terminos-de-servicio.php';
$divulgHref     = ($cssPrefix === '.') ? 'views/divulgacion-responsable.php' : 'divulgacion-responsable.php';
$primerosPasosHref = ($cssPrefix === '.') ? 'views/primeros-pasos.php'     : 'primeros-pasos.php';
?>
  <footer class="site-footer">
    <div class="site-footer__grid">

      <!-- Sobre Classia -->
      <div class="site-footer__about">
        <h3 class="site-footer__brand-title">Classia</h3>
        <p class="site-footer__brand-desc">
          Plataforma integradora de gestión educativa desarrollada por AniTech.
          Centraliza cursos, servicios técnicos especializados y su contratación
          en un único entorno accesible y seguro.
        </p>
      </div>

      <!-- Soporte -->
      <div>
        <h4 class="site-footer__section-title">Soporte</h4>
        <ul class="site-footer__list">
          <li>
            <a href="<?= $primerosPasosHref ?>">Primeros pasos</a>
          </li>
        </ul>
      </div>

      <!-- Empresa -->
      <div>
        <h4 class="site-footer__section-title">Empresa</h4>
        <ul class="site-footer__list">
          <li><a href="<?= $contactoHref ?>">Contacto</a></li>
          <li><a href="<?= $terminosHref ?>">Términos de servicio</a></li>
          <li><a href="<?= $privacidadHref ?>">Política de privacidad</a></li>
          <li><a href="<?= $divulgHref ?>">Divulgación responsable</a></li>
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

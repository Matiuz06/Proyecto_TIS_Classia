<?php

/**
 * Responsabilidad: Pie de página institucional, enlaces de navegación e información legal.
 */

$cssPrefix = $cssPrefix ?? '..';
$jsPrefix  = $jsPrefix  ?? '..';
require_once __DIR__ . '/../php/utils/toast.php';
$flashToasts = consume_toasts();

$contactoHref      = ($cssPrefix === '.') ? 'views/contacto.php' : 'contacto.php';
$primerosPasosHref = ($cssPrefix === '.') ? 'views/primeros-pasos.php' : 'primeros-pasos.php';
$reglamentoHref    = ($cssPrefix === '.') ? 'views/reglamento.php' : 'reglamento.php';
$faqHref           = ($cssPrefix === '.') ? 'views/preguntas-frecuentes.php' : 'preguntas-frecuentes.php';
$privacidadHref    = ($cssPrefix === '.') ? 'views/politica-privacidad.php' : 'politica-privacidad.php';
$terminosHref      = ($cssPrefix === '.') ? 'views/terminos-de-servicio.php' : 'terminos-de-servicio.php';
$divulgHref        = ($cssPrefix === '.') ? 'views/divulgacion-responsable.php' : 'divulgacion-responsable.php';
$institucionalHref = ($cssPrefix === '.') ? 'views/institucional.php' : 'institucional.php';
$verificarCertificadoHref = ($cssPrefix === '.') ? 'views/verificar-certificado.php' : 'verificar-certificado.php';
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
          <?= __t('footer_about') ?>
        </p>
      </div>

      <div>
        <h4 class="site-footer__section-title"><?= __t('footer_institutional') ?></h4>
        <ul class="site-footer__list">
          <li><a href="<?= $institucionalHref ?>"><?= __t('footer_about_us') ?></a></li>
          <li><a href="<?= $primerosPasosHref ?>"><?= __t('footer_first_steps') ?></a></li>
          <li><a href="<?= $verificarCertificadoHref ?>">Verificar certificado</a></li>
          <li><a href="<?= $contactoHref ?>"><?= __t('footer_contact') ?></a></li>
          <li><a href="<?= $reglamentoHref ?>"><?= __t('footer_rules') ?></a></li>
        </ul>
      </div>

      <div>
        <h4 class="site-footer__section-title"><?= __t('footer_policies') ?></h4>
        <ul class="site-footer__list">
          <li><a href="<?= $privacidadHref ?>"><?= __t('footer_privacy') ?></a></li>
          <li><a href="<?= $terminosHref ?>"><?= __t('footer_terms') ?></a></li>
          <li><a href="<?= $divulgHref ?>"><?= __t('footer_disclosure') ?></a></li>
          <li><a href="<?= $seguridadHref ?>"><?= __t('footer_security') ?></a></li>
          <li><a href="<?= $calidadHref ?>"><?= __t('footer_quality') ?></a></li>
          <li><a href="<?= $cookiesHref ?>"><?= __t('footer_cookies') ?></a></li>
          <li><a href="<?= $accesibilidadHref ?>"><?= __t('footer_accessibility') ?></a></li>
        </ul>
      </div>

      <div>
        <h4 class="site-footer__section-title"><?= __t('footer_help') ?></h4>
        <ul class="site-footer__list">
          <li><a href="<?= $faqHref ?>"><?= __t('footer_faq') ?></a></li>
          <li><a href="<?= $reglamentoHref ?>"><?= __t('footer_usage_rules') ?></a></li>
        </ul>
      </div>

    </div>

    <div class="site-footer__bottom">
      <p class="site-footer__powered">
        <?= __t('footer_developed_by') ?> <a href="<?= $contactoHref ?>" class="site-footer__powered-link">AniTech S.A.</a>
      </p>
      <p>&copy; <?= date('Y') ?> Classia &middot; AniTech S.A. <?= __t('footer_rights') ?></p>
    </div>
  </footer>
  <div id="classia-toast-container" class="classia-toast-container" aria-live="polite" aria-atomic="false"></div>
  <script src="<?= $jsPrefix ?>/js/toast.js?v=<?= filemtime(__DIR__ . '/../js/toast.js') ?>" defer></script>
  <script src="<?= $jsPrefix ?>/js/script.js?v=<?= filemtime(__DIR__ . '/../js/script.js') ?>" defer></script>
  <?php if (!empty($flashToasts)): ?>
    <script>
      window.addEventListener('DOMContentLoaded', function () {
        var toasts = <?= json_encode($flashToasts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
        if (!window.ClassiaToast || !Array.isArray(toasts)) return;
        toasts.forEach(function (toast) {
          var type = ['success', 'error', 'warning', 'info'].includes(toast.type) ? toast.type : 'info';
          window.ClassiaToast[type](toast);
        });
      });
    </script>
  <?php endif; ?>
</body>
</html>

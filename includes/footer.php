<?php

/**
 * Responsabilidad: Pie de página institucional, enlaces de navegación e información legal.
 */

$cssPrefix = $cssPrefix ?? '..';
$jsPrefix  = $jsPrefix ?? '..';

require_once __DIR__ . '/../php/utils/toast.php';
$flashToasts = consume_toasts();

$publicBase = isset($publicBase) && trim((string) $publicBase) !== ''
    ? rtrim((string) $publicBase, '/')
    : null;

$assetPrefix = $publicBase ?? rtrim($jsPrefix, '/');

if ($publicBase !== null) {
    $contactoHref              = $publicBase . '/views/contacto.php';
    $primerosPasosHref         = $publicBase . '/views/primeros-pasos.php';
    $reglamentoHref            = $publicBase . '/views/reglamento.php';
    $faqHref                   = $publicBase . '/views/preguntas-frecuentes.php';
    $privacidadHref            = $publicBase . '/views/politica-privacidad.php';
    $terminosHref              = $publicBase . '/views/terminos-de-servicio.php';
    $divulgHref                = $publicBase . '/views/divulgacion-responsable.php';
    $institucionalHref         = $publicBase . '/views/institucional.php';
    $verificarCertificadoHref  = $publicBase . '/views/verificar-certificado.php';
    $calidadHref               = $publicBase . '/views/politica-calidad.php';
    $seguridadHref             = $publicBase . '/views/politica-seguridad.php';
    $cookiesHref               = $publicBase . '/views/politica-cookies.php';
    $accesibilidadHref         = $publicBase . '/views/politica-accesibilidad.php';
    $cancelacionesHref         = $publicBase . '/views/politica-cancelaciones.php';
} else {
    $contactoHref              = ($cssPrefix === '.') ? 'views/contacto.php' : 'contacto.php';
    $primerosPasosHref         = ($cssPrefix === '.') ? 'views/primeros-pasos.php' : 'primeros-pasos.php';
    $reglamentoHref            = ($cssPrefix === '.') ? 'views/reglamento.php' : 'reglamento.php';
    $faqHref                   = ($cssPrefix === '.') ? 'views/preguntas-frecuentes.php' : 'preguntas-frecuentes.php';
    $privacidadHref            = ($cssPrefix === '.') ? 'views/politica-privacidad.php' : 'politica-privacidad.php';
    $terminosHref              = ($cssPrefix === '.') ? 'views/terminos-de-servicio.php' : 'terminos-de-servicio.php';
    $divulgHref                = ($cssPrefix === '.') ? 'views/divulgacion-responsable.php' : 'divulgacion-responsable.php';
    $institucionalHref         = ($cssPrefix === '.') ? 'views/institucional.php' : 'institucional.php';
    $verificarCertificadoHref  = ($cssPrefix === '.') ? 'views/verificar-certificado.php' : 'verificar-certificado.php';
    $calidadHref               = ($cssPrefix === '.') ? 'views/politica-calidad.php' : 'politica-calidad.php';
    $seguridadHref             = ($cssPrefix === '.') ? 'views/politica-seguridad.php' : 'politica-seguridad.php';
    $cookiesHref               = ($cssPrefix === '.') ? 'views/politica-cookies.php' : 'politica-cookies.php';
    $accesibilidadHref         = ($cssPrefix === '.') ? 'views/politica-accesibilidad.php' : 'politica-accesibilidad.php';
    $cancelacionesHref         = ($cssPrefix === '.') ? 'views/politica-cancelaciones.php' : 'politica-cancelaciones.php';
}

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
      <h4 class="site-footer__section-title">
        <?= __t('footer_institutional') ?>
      </h4>

      <ul class="site-footer__list">
        <li><a href="<?= htmlspecialchars($institucionalHref) ?>"><?= __t('footer_about_us') ?></a></li>
        <li><a href="<?= htmlspecialchars($primerosPasosHref) ?>"><?= __t('footer_first_steps') ?></a></li>
        <li><a href="<?= htmlspecialchars($verificarCertificadoHref) ?>">Verificar certificado</a></li>
        <li><a href="<?= htmlspecialchars($contactoHref) ?>"><?= __t('footer_contact') ?></a></li>
        <li><a href="<?= htmlspecialchars($reglamentoHref) ?>"><?= __t('footer_rules') ?></a></li>
      </ul>
    </div>

    <div>
      <h4 class="site-footer__section-title">
        <?= __t('footer_policies') ?>
      </h4>

      <ul class="site-footer__list">
        <li><a href="<?= htmlspecialchars($privacidadHref) ?>"><?= __t('footer_privacy') ?></a></li>
        <li><a href="<?= htmlspecialchars($terminosHref) ?>"><?= __t('footer_terms') ?></a></li>
        <li><a href="<?= htmlspecialchars($cancelacionesHref) ?>"><?= __t('footer_cancellations_policy') ?></a></li>
        <li><a href="<?= htmlspecialchars($divulgHref) ?>"><?= __t('footer_disclosure') ?></a></li>
        <li><a href="<?= htmlspecialchars($seguridadHref) ?>"><?= __t('footer_security') ?></a></li>
        <li><a href="<?= htmlspecialchars($calidadHref) ?>"><?= __t('footer_quality') ?></a></li>
        <li><a href="<?= htmlspecialchars($cookiesHref) ?>"><?= __t('footer_cookies') ?></a></li>
        <li><a href="<?= htmlspecialchars($accesibilidadHref) ?>"><?= __t('footer_accessibility') ?></a></li>
      </ul>
    </div>

    <div>
      <h4 class="site-footer__section-title">
        <?= __t('footer_help') ?>
      </h4>

      <ul class="site-footer__list">
        <li><a href="<?= htmlspecialchars($faqHref) ?>"><?= __t('footer_faq') ?></a></li>
        <li><a href="<?= htmlspecialchars($reglamentoHref) ?>"><?= __t('footer_usage_rules') ?></a></li>
      </ul>
    </div>

  </div>

  <div class="site-footer__bottom">
    <p class="site-footer__powered">
      <?= __t('footer_developed_by') ?>
      <a href="<?= htmlspecialchars($contactoHref) ?>" class="site-footer__powered-link">
        AniTech S.A.
      </a>
    </p>

    <p>
      &copy; <?= date('Y') ?> Classia &middot; AniTech S.A.
      <?= __t('footer_rights') ?>
    </p>
  </div>
</footer>

<div
  id="classia-toast-container"
  class="classia-toast-container"
  aria-live="polite"
  aria-atomic="false">
</div>

<script
  src="<?= htmlspecialchars($assetPrefix) ?>/js/toast.js?v=<?= filemtime(__DIR__ . '/../js/toast.js') ?>"
  defer>
</script>

<script
  src="<?= htmlspecialchars($assetPrefix) ?>/js/script.js?v=<?= filemtime(__DIR__ . '/../js/script.js') ?>"
  defer>
</script>

<?php if (!empty($flashToasts)): ?>
  <script>
    window.addEventListener('DOMContentLoaded', function () {
      const toasts = <?= json_encode(
          $flashToasts,
          JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
      ) ?>;

      if (!window.ClassiaToast || !Array.isArray(toasts)) {
        return;
      }

      toasts.forEach(function (toast) {
        const type = ['success', 'error', 'warning', 'info'].includes(toast.type)
          ? toast.type
          : 'info';

        window.ClassiaToast[type](toast);
      });
    });
  </script>
<?php endif; ?>

</body>
</html>
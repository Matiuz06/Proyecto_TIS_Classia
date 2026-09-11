<?php
$cssPrefix      = $cssPrefix ?? '..';
$privacidadHref = ($cssPrefix === '.') ? 'views/politica-privacidad.php' : 'politica-privacidad.php';
?>
  <footer class="site-footer">
    <div class="site-footer__grid">
      
      <div>
        <h3 class="site-footer__brand-title">Classia</h3>
        <p class="site-footer__brand-desc">
          Plataforma integradora para la gestión, aprendizaje y contratación de cursos y servicios técnicos especializados.
        </p>
      </div>

      <div>
        <h4 class="site-footer__section-title">Privacidad y Legal</h4>
        <ul class="site-footer__list">
          <li>
            <a href="<?= $privacidadHref ?>" class="link-privacy">
              Manejo de Datos Personales
            </a>
          </li>
        </ul>
      </div>

      <div>
        <h4 class="site-footer__section-title">Desarrollado por</h4>
        <div class="site-footer__company-info">
          <p><strong class="company-name">AniTech S.A.</strong></p>
          <p>📍 <strong>Casa Matriz:</strong> Florencio Sanchez 389, Salto, Uruguay</p>
          <p>📞 <strong>Teléfono:</strong> +598 4733 3530</p>
          <p>✉️ <strong>Correo:</strong> <a href="mailto:anitechsa2026@gmail.com">anitechsa2026@gmail.com</a></p>
        </div>
      </div>

    </div>

    <div class="site-footer__bottom">
      <p>&copy; <?= date('Y') ?> Classia · AniTech S.A. Todos los derechos reservados.</p>
    </div>
  </footer>
</body>
</html>

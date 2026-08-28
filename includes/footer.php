<?php

$footerText = $footerText ?? '&copy; 2026 Classia. Todos los derechos reservados.';
$jsPrefix   = $jsPrefix   ?? null;
?>
  <footer class="site-footer">
    <p><?= $footerText ?></p>
  </footer>
<?php if ($jsPrefix !== null): ?>
  <script src="<?= $jsPrefix ?>/js/script.js"></script>
<?php endif; ?>
</body>
</html>

<?php

$titulo = htmlspecialchars((string) ($titulo ?? 'Classia'), ENT_QUOTES, 'UTF-8');
$contenido = (string) ($contenido ?? '');
?>
<!doctype html>
<html lang="es">
<body style="margin:0;padding:24px;background:#f5f7fb;font-family:Arial,sans-serif;color:#17212b;line-height:1.5">
  <div style="max-width:620px;margin:0 auto;background:#ffffff;border:1px solid #d8e0ea;border-radius:10px;overflow:hidden">
    <div style="padding:22px 24px;background:#146c94;color:#ffffff">
      <h1 style="margin:0;font-size:22px"><?= $titulo ?></h1>
    </div>
    <div style="padding:24px">
      <?= $contenido ?>
      <p style="margin-top:28px;color:#5f6f80">Saludos,<br>Equipo Classia</p>
    </div>
  </div>
</body>
</html>

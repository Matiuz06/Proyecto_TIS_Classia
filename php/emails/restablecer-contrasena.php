<?php

$enlace = htmlspecialchars((string) ($enlace ?? ''), ENT_QUOTES, 'UTF-8');
$contenido = '<p>Recibimos una solicitud para restablecer tu contraseña.</p>'
    . '<p><a href="' . $enlace . '" style="display:inline-block;padding:12px 18px;background:#146c94;color:#ffffff;text-decoration:none;border-radius:8px">Restablecer contraseña</a></p>'
    . '<p>Este enlace vence en 1 hora. Si no solicitaste este cambio, podés ignorar este correo.</p>';
$titulo = 'Restablecer contraseña - Classia';
require __DIR__ . '/base.php';

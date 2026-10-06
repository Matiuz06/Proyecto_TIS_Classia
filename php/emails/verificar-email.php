<?php

$nombre = htmlspecialchars((string) ($nombre ?? 'usuario'), ENT_QUOTES, 'UTF-8');
$enlace = htmlspecialchars((string) ($enlace ?? ''), ENT_QUOTES, 'UTF-8');
$email = htmlspecialchars((string) ($email ?? ''), ENT_QUOTES, 'UTF-8');
$destino = $email !== '' ? '<strong>' . $email . '</strong>' : 'tu correo';
$contenido = '<p>Hola ' . $nombre . ',</p>'
    . '<p>Confirmá ' . $destino . ' para activar o actualizar tu cuenta de Classia.</p>'
    . '<p><a href="' . $enlace . '" style="display:inline-block;padding:12px 18px;background:#146c94;color:#ffffff;text-decoration:none;border-radius:8px">Confirmar correo</a></p>'
    . '<p>Este enlace vence en 24 horas. Si no solicitaste este cambio, podés ignorar este correo.</p>';
$titulo = 'Confirmá tu correo - Classia';
require __DIR__ . '/base.php';

<?php

$nombre = htmlspecialchars((string) ($nombre ?? 'usuario'), ENT_QUOTES, 'UTF-8');
$enlace = htmlspecialchars((string) ($enlace ?? ''), ENT_QUOTES, 'UTF-8');
$contenido = '<p>Hola ' . $nombre . ',</p>'
    . '<p>Te informamos que tu solicitud para convertirte en docente en Classia fue aprobada por un administrador.</p>'
    . '<p>A partir de ahora podrás utilizar las funcionalidades correspondientes al rol docente y publicar contenido dentro de la plataforma.</p>'
    . '<p><a href="' . $enlace . '">Ingresar a Classia</a></p>';
$titulo = 'Solicitud docente aprobada - Classia';
require __DIR__ . '/base.php';

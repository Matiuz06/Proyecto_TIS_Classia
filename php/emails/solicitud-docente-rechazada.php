<?php

/**
 * Responsabilidad: Plantilla de correo informativo tras la desestimación o rechazo de la solicitud docente.
 */

$nombre = htmlspecialchars((string) ($nombre ?? 'usuario'), ENT_QUOTES, 'UTF-8');
$motivo = trim((string) ($motivo ?? ''));
$enlace = htmlspecialchars((string) ($enlace ?? ''), ENT_QUOTES, 'UTF-8');
$contenido = '<p>Hola ' . $nombre . ',</p>'
    . '<p>Te informamos que tu solicitud para convertirte en docente en Classia no fue aprobada en esta oportunidad.</p>';
if ($motivo !== '') {
    $contenido .= '<p><strong>Motivo indicado por el administrador:</strong><br>'
        . nl2br(htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8')) . '</p>';
}
$contenido .= '<p>Podrás revisar la información de tu cuenta y volver a solicitarlo si la plataforma lo permite.</p>'
    . '<p><a href="' . $enlace . '">Ingresar a Classia</a></p>';
$titulo = 'Actualización sobre tu solicitud docente - Classia';
require __DIR__ . '/base.php';

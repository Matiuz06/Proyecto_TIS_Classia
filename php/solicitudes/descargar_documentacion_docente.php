<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';

requerir_rol(ROL_ADMIN, '../../views/usuario.php');

$idSolicitud = (int) ($_GET['id'] ?? 0);
$modo = ($_GET['modo'] ?? 'descargar') === 'inline' ? 'inline' : 'attachment';

if ($idSolicitud <= 0) {
    http_response_code(404);
    exit('Documentación no disponible.');
}

$stmt = $pdo->prepare(
    'SELECT documentacion_ruta, documentacion_nombre, documentacion_mime
     FROM solicitudes_docente
     WHERE id_solicitud_docente = :id
     LIMIT 1'
);
$stmt->execute(['id' => $idSolicitud]);
$solicitud = $stmt->fetch();

$ruta = $solicitud ? ($solicitud['documentacion_ruta'] ?? null) : null;
$archivo = ruta_absoluta_archivo_guardado($ruta);
if (!$solicitud || !$archivo || !is_file($archivo)) {
    http_response_code(404);
    exit('Documentación no disponible.');
}

$mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo);
if (!in_array($mime, ['application/pdf', 'image/jpeg', 'image/png'], true)) {
    http_response_code(404);
    exit('Documentación no disponible.');
}

$nombre = preg_replace('/[^A-Za-z0-9._-]+/', '_', (string) ($solicitud['documentacion_nombre'] ?? 'documentacion')) ?: 'documentacion';

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($archivo));
header('Content-Disposition: ' . $modo . '; filename="' . $nombre . '"');
header('X-Content-Type-Options: nosniff');
readfile($archivo);
exit;

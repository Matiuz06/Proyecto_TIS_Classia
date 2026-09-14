<?php

function ruta_absoluta_archivo_guardado(?string $ruta): ?string
{
    if (!$ruta) return null;
    $ruta = str_replace('\\', '/', trim($ruta));
    if ($ruta === '' || str_contains($ruta, '..')) return null;
    if (!str_starts_with($ruta, 'storage/private/') && !str_starts_with($ruta, 'assets/uploads/')) return null;
    $base = realpath(__DIR__ . '/../..');
    if ($base === false) return null;
    $abs = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $ruta);
    return $abs;
}

function eliminar_archivo_guardado(?string $ruta): bool
{
    $abs = ruta_absoluta_archivo_guardado($ruta);
    if ($abs === null || !file_exists($abs)) return true;
    return is_file($abs) ? unlink($abs) : false;
}

function guardar_archivo_subido(array $archivo, string $subcarpeta, int $max_megabytes = 20): array
{
    if (!isset($archivo['error']) || is_array($archivo['error'])) return ['ok'=>false,'error'=>'Parámetros de archivo no válidos.'];
    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) return ['ok'=>false,'error'=>'No se seleccionó ningún archivo.'];
    if ($archivo['error'] !== UPLOAD_ERR_OK) return ['ok'=>false,'error'=>'Error al subir el archivo.'];
    if (($archivo['size'] ?? 0) > $max_megabytes * 1024 * 1024) return ['ok'=>false,'error'=>"El archivo supera el máximo de {$max_megabytes}MB."];
    if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) return ['ok'=>false,'error'=>'El archivo temporal no es válido.'];

    $nombreOriginal = (string)($archivo['name'] ?? '');
    $extensionOriginal = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extPermitidas = ['pdf','zip','doc','docx','txt','jpg','jpeg','png','webp','gif','mp4','webm','stl','obj','3mf'];
    if (!in_array($extensionOriginal, $extPermitidas, true)) return ['ok'=>false,'error'=>'La extensión del archivo no está permitida.'];

    $mimePermitidos = [
        'application/pdf'=>['pdf'],
        'application/zip'=>['zip'],
        'application/x-zip-compressed'=>['zip'],
        'application/msword'=>['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'=>['docx'],
        'text/plain'=>['txt'],
        'image/jpeg'=>['jpg','jpeg'],
        'image/png'=>['png'],
        'image/webp'=>['webp'],
        'image/gif'=>['gif'],
        'video/mp4'=>['mp4'],
        'video/webm'=>['webm'],
        'model/stl'=>['stl'],
        'application/sla'=>['stl'],
        'model/obj'=>['obj'],
        'model/3mf'=>['3mf'],
        'application/vnd.ms-package.3dmanufacturing-3dmodel+xml'=>['3mf'],
        'application/octet-stream'=>['stl','obj','3mf'],
    ];
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    if (!$mime || !isset($mimePermitidos[$mime]) || !in_array($extensionOriginal, $mimePermitidos[$mime], true)) {
        return ['ok'=>false,'error'=>'El tipo real del archivo no coincide con una extensión permitida.'];
    }

    $subcarpeta = trim($subcarpeta, '/\\');
    if ($subcarpeta === '' || str_contains($subcarpeta, '..')) return ['ok'=>false,'error'=>'Directorio de destino no válido.'];
    $base = __DIR__ . '/../../storage/private';
    $destino = $base . DIRECTORY_SEPARATOR . $subcarpeta;
    if (!is_dir($destino) && !mkdir($destino, 0755, true) && !is_dir($destino)) return ['ok'=>false,'error'=>'No se pudo crear el directorio privado de destino.'];
    if (!is_writable($destino)) return ['ok'=>false,'error'=>'El directorio privado no permite escritura.'];

    $extension = $extensionOriginal === 'jpeg' ? 'jpg' : $extensionOriginal;
    $nombre = $subcarpeta . '_' . bin2hex(random_bytes(12)) . '_' . time() . '.' . $extension;
    $rutaCompleta = $destino . DIRECTORY_SEPARATOR . $nombre;
    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) return ['ok'=>false,'error'=>'No se pudo guardar el archivo.'];
    return ['ok'=>true,'ruta'=>'storage/private/' . $subcarpeta . '/' . $nombre];
}

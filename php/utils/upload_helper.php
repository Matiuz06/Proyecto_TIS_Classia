<?php

const TIPOS_MIME_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

function guardar_imagen_subida(array $archivo, string $subcarpeta, int $max_megabytes = 5): array
{
    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        return ['ok' => false, 'error' => 'Parámetros de archivo no válidos.'];
    }
    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'No se seleccionó ningún archivo.'];
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Error al subir el archivo (código ' . $archivo['error'] . ').'];
    }
    if (($archivo['size'] ?? 0) > $max_megabytes * 1024 * 1024) {
        return ['ok' => false, 'error' => "El tamaño de la imagen no puede superar los {$max_megabytes}MB."];
    }
    if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        return ['ok' => false, 'error' => 'El archivo temporal de subida no es válido.'];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);
    if (!$mime || !array_key_exists($mime, TIPOS_MIME_PERMITIDOS)) {
        return ['ok' => false, 'error' => 'Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }

    $subcarpeta = trim($subcarpeta, '/\\');
    if ($subcarpeta === '' || str_contains($subcarpeta, '..')) {
        return ['ok' => false, 'error' => 'La carpeta de destino no es válida.'];
    }

    $base = __DIR__ . '/../../assets/uploads';
    $destino = $base . DIRECTORY_SEPARATOR . $subcarpeta;
    if (!is_dir($destino) && !mkdir($destino, 0755, true) && !is_dir($destino)) {
        return ['ok' => false, 'error' => 'No se pudo crear el directorio de destino.'];
    }
    if (!is_writable($destino)) {
        return ['ok' => false, 'error' => 'El directorio de destino no tiene permisos de escritura.'];
    }

    $extension = TIPOS_MIME_PERMITIDOS[$mime];
    $nombre = $subcarpeta . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
    $rutaCompleta = $destino . DIRECTORY_SEPARATOR . $nombre;
    if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
        return ['ok' => false, 'error' => 'Error al guardar la imagen en el servidor.'];
    }

    return ['ok' => true, 'ruta' => 'assets/uploads/' . $subcarpeta . '/' . $nombre];
}

function eliminar_imagen_subida(?string $ruta_relativa): bool
{
    if (empty($ruta_relativa)) return true;
    $ruta_relativa = str_replace('\\', '/', $ruta_relativa);
    if (str_contains($ruta_relativa, '..') || !str_starts_with($ruta_relativa, 'assets/uploads/')) return false;
    $ruta_absoluta = __DIR__ . '/../../' . $ruta_relativa;
    if (!file_exists($ruta_absoluta)) return true;
    if (!is_file($ruta_absoluta) || basename($ruta_absoluta) === '.gitkeep') return false;
    return unlink($ruta_absoluta);
}

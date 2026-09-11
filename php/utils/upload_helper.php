<?php

const TIPOS_MIME_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

function guardar_imagen_subida(
    array $archivo,
    string $subcarpeta,
    int $max_megabytes = 5
): array {

    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        return [
            'ok' => false,
            'error' => 'Parámetros de archivo no válidos.'
        ];
    }

    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return [
            'ok' => false,
            'error' => 'No se seleccionó ningún archivo.'
        ];
    }

    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return [
            'ok' => false,
            'error' => 'Error al subir el archivo (código '
                . $archivo['error']
                . ').'
        ];
    }

    $tamano_maximo = $max_megabytes * 1024 * 1024;

    if ($archivo['size'] > $tamano_maximo) {
        return [
            'ok' => false,
            'error' => "El tamaño de la imagen no puede superar los {$max_megabytes}MB."
        ];
    }

    if (
        !isset($archivo['tmp_name']) ||
        !is_uploaded_file($archivo['tmp_name'])
    ) {
        return [
            'ok' => false,
            'error' => 'El archivo temporal de subida no es válido.'
        ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    if ($mime === false) {
        return [
            'ok' => false,
            'error' => 'No se pudo determinar el formato de la imagen.'
        ];
    }

    if (!array_key_exists($mime, TIPOS_MIME_PERMITIDOS)) {
        return [
            'ok' => false,
            'error' => 'Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.'
        ];
    }

    $extension = TIPOS_MIME_PERMITIDOS[$mime];

    $directorio_base = __DIR__ . '/../../assets/uploads';

    if (!is_dir($directorio_base)) {
        if (
            !mkdir($directorio_base, 0755, true) &&
            !is_dir($directorio_base)
        ) {
            return [
                'ok' => false,
                'error' => 'No se pudo crear el directorio de uploads.'
            ];
        }
    }

    $subcarpeta = trim($subcarpeta, '/\\');

    if (
        $subcarpeta === '' ||
        strpos($subcarpeta, '..') !== false
    ) {
        return [
            'ok' => false,
            'error' => 'La carpeta de destino no es válida.'
        ];
    }

    $directorio_destino =
        $directorio_base
        . DIRECTORY_SEPARATOR
        . $subcarpeta;

    if (!is_dir($directorio_destino)) {
        if (
            !mkdir($directorio_destino, 0755, true) &&
            !is_dir($directorio_destino)
        ) {
            return [
                'ok' => false,
                'error' => 'No se pudo crear el directorio de destino.'
            ];
        }
    }

    if (!is_writable($directorio_destino)) {
        return [
            'ok' => false,
            'error' => 'El directorio de destino no tiene permisos de escritura.'
        ];
    }

    try {
        $nombre_aleatorio = bin2hex(random_bytes(8));
    } catch (Throwable $e) {
        return [
            'ok' => false,
            'error' => 'No se pudo generar un nombre seguro para la imagen.'
        ];
    }

    $nombre_archivo =
        $subcarpeta
        . '_'
        . $nombre_aleatorio
        . '_'
        . time()
        . '.'
        . $extension;

    $ruta_completa =
        $directorio_destino
        . DIRECTORY_SEPARATOR
        . $nombre_archivo;

    if (!move_uploaded_file(
        $archivo['tmp_name'],
        $ruta_completa
    )) {
        return [
            'ok' => false,
            'error' => 'Error al guardar la imagen en el servidor.'
        ];
    }

    return [
        'ok' => true,
        'ruta' => 'assets/uploads/'
            . $subcarpeta
            . '/'
            . $nombre_archivo,
    ];
}

function eliminar_imagen_subida(?string $ruta_relativa): bool
{
    if (empty($ruta_relativa)) {
        return true;
    }

    $ruta_relativa = str_replace(
        '\\',
        '/',
        $ruta_relativa
    );

    if (strpos($ruta_relativa, '..') !== false) {
        return false;
    }

    if (
        strpos(
            $ruta_relativa,
            'assets/uploads/'
        ) !== 0
    ) {
        return false;
    }

    if ($ruta_relativa === 'assets/uploads/') {
        return false;
    }

    $ruta_absoluta =
        __DIR__
        . '/../../'
        . $ruta_relativa;

    if (!file_exists($ruta_absoluta)) {
        return true;
    }

    if (!is_file($ruta_absoluta)) {
        return false;
    }

    if (basename($ruta_absoluta) === '.gitkeep') {
        return true;
    }

    return unlink($ruta_absoluta);
}

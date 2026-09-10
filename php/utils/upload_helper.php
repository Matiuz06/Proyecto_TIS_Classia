<?php


const TIPOS_MIME_PERMITIDOS = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/webp' => 'webp',
    'image/gif'  => 'gif',
];

// Procesa la subida de un archivo de imagen.
// @param array  $archivo            Array de $_FILES['nombre_campo']
// @param string $subcarpeta         Nombre de subcarpeta dentro de assets/uploads (ej: 'perfiles', 'publicaciones')
// @param int    $max_megabytes      Tamaño máximo permitido en megabytes (por defecto 5)
// @return array                     ['ok' => true, 'ruta' => 'assets/uploads/...'] o ['ok' => false, 'error' => '...']
 
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

    $tamano_maximo = $max_megabytes * 1024 * 1024;
    if ($archivo['size'] > $tamano_maximo) {
        return ['ok' => false, 'error' => "El tamaño de la imagen no puede superar los {$max_megabytes}MB."];
    }

    // Validar tipo MIME real del contenido
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($archivo['tmp_name']);

    if (!array_key_exists($mime, TIPOS_MIME_PERMITIDOS)) {
        return ['ok' => false, 'error' => 'Formato de imagen no permitido. Usa JPG, PNG, WEBP o GIF.'];
    }

    $extension = TIPOS_MIME_PERMITIDOS[$mime];
    $directorio_destino = realpath(__DIR__ . '/../../assets/uploads') . DIRECTORY_SEPARATOR . $subcarpeta;

    if (!is_dir($directorio_destino)) {
        if (!mkdir($directorio_destino, 0755, true) && !is_dir($directorio_destino)) {
            return ['ok' => false, 'error' => 'No se pudo crear el directorio de destino.'];
        }
    }

    // Generar nombre de archivo único y seguro
    $nombre_archivo = $subcarpeta . '_' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $extension;
    $ruta_completa = $directorio_destino . DIRECTORY_SEPARATOR . $nombre_archivo;

    if (!move_uploaded_file($archivo['tmp_name'], $ruta_completa)) {
        return ['ok' => false, 'error' => 'Error al guardar la imagen en el servidor.'];
    }

    chmod($ruta_completa, 0644);

    return [
        'ok'   => true,
        'ruta' => 'assets/uploads/' . $subcarpeta . '/' . $nombre_archivo,
    ];
}


// @param string|null $ruta_relativa Ruta almacenada en base de datos (ej: 'assets/uploads/perfiles/foto.jpg')
// @return bool                      True si se eliminó o no existía, False en caso de error.
 
function eliminar_imagen_subida(?string $ruta_relativa): bool
{
    if (empty($ruta_relativa)) {
        return true;
    }

    // Evitar path traversal
    if (strpos($ruta_relativa, '..') !== false) {
        return false;
    }

    // Validar que esté dentro de assets/uploads/
    if (strpos($ruta_relativa, 'assets/uploads/') !== 0) {
        return false;
    }

    $ruta_absoluta = __DIR__ . '/../../' . $ruta_relativa;

    if (file_exists($ruta_absoluta) && is_file($ruta_absoluta)) {
        // No borrar .gitkeep
        if (basename($ruta_absoluta) === '.gitkeep') {
            return true;
        }
        return @unlink($ruta_absoluta);
    }

    return true;
}

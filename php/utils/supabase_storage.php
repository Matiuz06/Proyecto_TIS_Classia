<?php

/**
 * Responsabilidad: Integra la aplicación con Supabase Storage para recursos privados de cursos.
 */

function supabase_config(): array
{
    static $config = null;
    if ($config === null) {
        $path = __DIR__ . '/../../config/supabase.php';
        $config = file_exists($path) ? require $path : [];
    }
    return $config ?: [
        'url'         => '',
        'anon_key'    => '',
        'service_key' => '',
        'bucket_name' => 'recursos-cursos',
        'activo'      => false,
    ];
}

function supabase_esta_activo(): bool
{
    $cfg = supabase_config();
    return !empty($cfg['activo']) && !empty($cfg['url']) && (!empty($cfg['service_key']) || !empty($cfg['anon_key']));
}

function supabase_tipos_permitidos(): array
{
    return [
        'application/pdf' => ['pdf'],
        'image/jpeg'      => ['jpg', 'jpeg'],
        'image/png'       => ['png'],
        'image/webp'      => ['webp'],
        'image/gif'       => ['gif'],
        'video/mp4'       => ['mp4'],
        'video/webm'      => ['webm'],
        'application/msword' => ['doc'],
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => ['docx'],
        'application/vnd.ms-powerpoint' => ['ppt', 'pps'],
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => ['pptx'],
        'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => ['ppsx'],
        'application/vnd.ms-excel' => ['xls'],
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => ['xlsx'],
        'application/vnd.oasis.opendocument.text' => ['odt'],
        'application/vnd.oasis.opendocument.spreadsheet' => ['ods'],
        'application/vnd.oasis.opendocument.presentation' => ['odp'],
        'text/plain'      => ['txt', 'csv'],
        'text/csv'        => ['csv'],
        'application/zip' => ['zip'],
        'application/x-zip-compressed' => ['zip'],
        'application/x-rar-compressed' => ['rar'],
        'application/vnd.rar' => ['rar'],
        'application/x-7z-compressed' => ['7z'],
        'model/stl'       => ['stl'],
        'application/sla' => ['stl'],
        'model/obj'       => ['obj'],
        'model/3mf'       => ['3mf'],
        'application/vnd.ms-package.3dmanufacturing-3dmodel+xml' => ['3mf'],
        'application/octet-stream' => ['stl', 'obj', '3mf', 'rar', '7z'],
    ];
}

function supabase_mensaje_error_subida(int $errorCode, int $max_mb = 50): string
{
    return match ($errorCode) {
        UPLOAD_ERR_INI_SIZE   => "El archivo supera el tamaño máximo permitido por el servidor (máx. {$max_mb}MB).",
        UPLOAD_ERR_FORM_SIZE  => "El archivo supera el tamaño máximo permitido por el formulario.",
        UPLOAD_ERR_PARTIAL    => "El archivo se subió solo parcialmente. Por favor, intentalo de nuevo.",
        UPLOAD_ERR_NO_FILE    => "No se seleccionó ningún archivo.",
        UPLOAD_ERR_NO_TMP_DIR => "Falta la carpeta temporal de subida en el servidor.",
        UPLOAD_ERR_CANT_WRITE => "Error de permisos al escribir el archivo en el servidor.",
        UPLOAD_ERR_EXTENSION  => "La subida fue interrumpida por una extensión del servidor.",
        default               => "Error al procesar el archivo subido (código {$errorCode}).",
    };
}

/**
 * Valida de forma estricta un archivo subido (extensión, MIME real y tamaño) antes de enviarlo a Supabase.
 *
 * @param array  $archivo      Array de $_FILES['nombre']
 * @param string $tipo_recurso Tipo seleccionado ('Archivo', 'Foro', 'Entrega de Tareas', 'Video', 'PDF', 'Imagen')
 * @param int    $max_mb       Límite en megabytes (por defecto 50MB)
 * @return array ['ok' => bool, 'error' => string|null, 'mime' => string|null, 'ext' => string|null]
 */
function supabase_validar_archivo_subido(array $archivo, string $tipo_recurso = '', int $max_mb = 50): array
{
    if (!isset($archivo['error']) || is_array($archivo['error'])) {
        return ['ok' => false, 'error' => 'Parámetros del archivo no válidos.'];
    }
    if ($archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => false, 'error' => 'No se seleccionó ningún archivo.'];
    }
    if ($archivo['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => supabase_mensaje_error_subida((int)$archivo['error'], $max_mb)];
    }
    if (($archivo['size'] ?? 0) > $max_mb * 1024 * 1024) {
        return ['ok' => false, 'error' => "El archivo supera el tamaño máximo permitido de {$max_mb}MB."];
    }
    if (empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        return ['ok' => false, 'error' => 'El archivo temporal no es válido.'];
    }

    $nombreOriginal = (string)($archivo['name'] ?? '');
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    if ($ext === 'jpeg') $ext = 'jpg';

    // Lista blanca estricta de extensiones permitidas
    $extPermitidas = [
        'pdf',
        'jpg', 'jpeg', 'png', 'webp', 'gif',
        'mp4', 'webm',
        'doc', 'docx', 'ppt', 'pptx', 'pps', 'ppsx', 'xls', 'xlsx', 'odt', 'ods', 'odp', 'csv', 'txt',
        'zip', 'rar', '7z',
        'stl', 'obj', '3mf'
    ];

    if (!in_array($ext, $extPermitidas, true)) {
        return [
            'ok' => false,
            'error' => "El formato .{$ext} no está permitido. Formatos soportados: PDF, documentos de Office (.docx, .xlsx, .pptx, .ppsx), imágenes (.jpg, .png, .webp), videos (.mp4, .webm) o comprimidos (.zip, .rar)."
        ];
    }

    // Coherencia según el tipo de recurso seleccionado
    if ($tipo_recurso === 'PDF' && $ext !== 'pdf') {
        return ['ok' => false, 'error' => 'Para recursos tipo PDF solo podés subir archivos .pdf.'];
    }
    if ($tipo_recurso === 'Imagen' && !in_array($ext, ['jpg', 'png', 'webp', 'gif'], true)) {
        return ['ok' => false, 'error' => 'Para recursos tipo Imagen solo podés subir JPG, PNG, WEBP o GIF.'];
    }
    if ($tipo_recurso === 'Video' && !in_array($ext, ['mp4', 'webm'], true)) {
        return ['ok' => false, 'error' => 'Para recursos tipo Video solo podés subir videos MP4 o WEBM.'];
    }


    // Validación del tipo MIME real usando finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeReal = $finfo->file($archivo['tmp_name']);
    $mapaMimes = supabase_tipos_permitidos();

    if (!$mimeReal || !isset($mapaMimes[$mimeReal]) || !in_array($ext, $mapaMimes[$mimeReal], true)) {
        return ['ok' => false, 'error' => 'El contenido real del archivo no coincide con su extensión permitida.'];
    }

    return [
        'ok'   => true,
        'ext'  => $ext,
        'mime' => $mimeReal,
    ];
}

/**
 * Sube un archivo a un bucket de Supabase Storage.
 *
 * @param string $ruta_archivo_local Ruta absoluta del archivo local a subir.
 * @param string $nombre_destino     Nombre/ruta con la que se guardará en el bucket (ej: 'cursos/1/guia.pdf').
 * @param string $mime_type          Tipo MIME del archivo (ej: 'application/pdf').
 * @return array ['ok' => bool, 'url' => string|null, 'error' => string|null]
 */
function supabase_subir_archivo(string $ruta_archivo_local, string $nombre_destino, string $mime_type = 'application/octet-stream'): array
{
    $cfg = supabase_config();
    if (!supabase_esta_activo()) {
        return ['ok' => false, 'error' => 'Supabase no está configurado o activo.'];
    }

    if (!file_exists($ruta_archivo_local) || !is_readable($ruta_archivo_local)) {
        return ['ok' => false, 'error' => 'El archivo local no existe o no se puede leer.'];
    }

    $baseUrl = rtrim($cfg['url'], '/');
    $bucket  = rawurlencode($cfg['bucket_name']);
    $path    = ltrim($nombre_destino, '/');
    $apiKey  = $cfg['service_key'] ?: $cfg['anon_key'];

    $endpoint = "{$baseUrl}/storage/v1/object/{$bucket}/{$path}";
    $fileData = file_get_contents($ruta_archivo_local);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $fileData,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$apiKey}",
            "apikey: {$apiKey}",
            "Content-Type: {$mime_type}",
            "x-upsert: true",
        ],
        CURLOPT_TIMEOUT        => 30,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return ['ok' => false, 'error' => 'Error de conexión con Supabase: ' . $curlErr];
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        $publicUrl = "{$baseUrl}/storage/v1/object/public/{$bucket}/{$path}";
        return ['ok' => true, 'url' => $publicUrl, 'path' => $path];
    }

    $json = json_decode((string)$response, true);
    $msg = $json['message'] ?? $json['error'] ?? "HTTP error {$httpCode}";
    return ['ok' => false, 'error' => 'Error de Supabase: ' . $msg];
}

/**
 * Elimina un archivo de Supabase Storage.
 *
 * @param string $nombre_destino Ruta en el bucket (ej: 'cursos/1/guia.pdf').
 * @return bool
 */
function supabase_eliminar_archivo(string $nombre_destino): bool
{
    $cfg = supabase_config();
    if (!supabase_esta_activo()) {
        return false;
    }

    $baseUrl = rtrim($cfg['url'], '/');
    $bucket  = rawurlencode($cfg['bucket_name']);
    $path    = ltrim($nombre_destino, '/');
    $apiKey  = $cfg['service_key'] ?: $cfg['anon_key'];

    $endpoint = "{$baseUrl}/storage/v1/object/{$bucket}";
    $payload  = json_encode(['prefixes' => [$path]]);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'DELETE',
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$apiKey}",
            "apikey: {$apiKey}",
            "Content-Type: application/json",
        ],
        CURLOPT_TIMEOUT        => 15,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return ($httpCode >= 200 && $httpCode < 300);
}

/**
 * Genera una URL firmada con tiempo de expiración para descarga segura de archivos privados.
 *
 * @param string $nombre_destino
 * @param int $segundos_expiracion Por defecto 15 minutos (900 segundos).
 * @return string|null
 */
function supabase_obtener_url_firmada(string $nombre_destino, int $segundos_expiracion = 900): ?string
{
    $cfg = supabase_config();
    if (!supabase_esta_activo()) {
        return null;
    }

    $baseUrl = rtrim($cfg['url'], '/');
    $bucket  = rawurlencode($cfg['bucket_name']);
    $path    = ltrim($nombre_destino, '/');
    $apiKey  = $cfg['service_key'] ?: $cfg['anon_key'];

    $endpoint = "{$baseUrl}/storage/v1/object/sign/{$bucket}/{$path}";
    $payload  = json_encode(['expiresIn' => $segundos_expiracion]);

    $ch = curl_init($endpoint);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            "Authorization: Bearer {$apiKey}",
            "apikey: {$apiKey}",
            "Content-Type: application/json",
        ],
        CURLOPT_TIMEOUT        => 10,
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $json = json_decode((string)$response, true);
        if (!empty($json['signedURL'])) {
            $signedPath = $json['signedURL'];
            if (str_starts_with($signedPath, 'http://') || str_starts_with($signedPath, 'https://')) {
                return $signedPath;
            }
            if (str_starts_with($signedPath, '/storage/v1/')) {
                return $baseUrl . $signedPath;
            }
            if (str_starts_with($signedPath, '/')) {
                return $baseUrl . '/storage/v1' . $signedPath;
            }
            return $baseUrl . '/storage/v1/' . $signedPath;
        }
    }

    return null;
}

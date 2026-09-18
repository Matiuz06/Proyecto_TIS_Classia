<?php

/**
 * Configuración de Supabase Storage para Classia.
 *
 * Carga automáticamente las credenciales desde el archivo .env o desde variables de entorno del servidor.
 */

$envPath = __DIR__ . '/../.env';
if (file_exists($envPath) && getenv('SUPABASE_URL') === false) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) {
            continue;
        }
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        if (getenv($key) === false) {
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
        }
    }
}

$rawActivo = getenv('SUPABASE_ACTIVO');
$activo = filter_var($rawActivo, FILTER_VALIDATE_BOOLEAN);

return [
    'url'         => getenv('SUPABASE_URL') ?: '',
    'anon_key'    => getenv('SUPABASE_ANON_KEY') ?: '',
    'service_key' => getenv('SUPABASE_SERVICE_KEY') ?: '',
    'bucket_name' => getenv('SUPABASE_BUCKET') ?: 'recursos-cursos',
    'activo'      => $activo,
];

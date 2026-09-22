<?php

/**
 * Responsabilidad: Valida tokens de Google reCAPTCHA antes de aceptar formularios sensibles.
 */

/**
 * Utilidades para verificación de Google reCAPTCHA v2 / v3
 */

function obtener_recaptcha_site_key(): string
{
    return getenv('RECAPTCHA_SITE_KEY') ?: '';
}

function verificar_recaptcha(?string $token, ?string $ip = null): array
{
    $secret = getenv('RECAPTCHA_SECRET_KEY');

    // Si no hay clave secreta configurada en el entorno (ej: desarrollo local), se permite el paso seguro
    if (!$secret || $secret === 'TU_RECAPTCHA_SECRET_KEY' || $secret === '') {
        return [
            'exito' => true,
            'mensaje' => 'Modo desarrollo (reCAPTCHA no configurado en .env)'
        ];
    }

    if (empty($token)) {
        return [
            'exito' => false,
            'mensaje' => 'Por favor, completá la verificación de seguridad (reCAPTCHA).'
        ];
    }

    $postData = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $ip ?? ($_SERVER['REMOTE_ADDR'] ?? '')
    ]);

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'content' => $postData,
            'timeout' => 5
        ]
    ];

    $context = stream_context_create($opts);
    $response = @file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);

    if ($response === false) {
        error_log("Error al contactar servicio reCAPTCHA de Google");
        // Fallback en caso de timeout de red
        return [
            'exito' => true,
            'mensaje' => 'Servicio reCAPTCHA no disponible temporalmente'
        ];
    }

    $json = json_decode($response, true);
    if (!empty($json['success'])) {
        return [
            'exito' => true,
            'score' => $json['score'] ?? null
        ];
    }

    $error_codes = $json['error-codes'] ?? [];
    error_log("reCAPTCHA falló: " . json_encode($error_codes));

    return [
        'exito' => false,
        'mensaje' => 'La verificación de seguridad (reCAPTCHA) falló. Por favor, intentá nuevamente.'
    ];
}

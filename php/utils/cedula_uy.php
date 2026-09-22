<?php

/**
 * Responsabilidad: Valida y normaliza cédulas uruguayas para datos de usuario.
 */

/**
 * Utilidades de validación, cifrado y enmascaramiento de Cédula de Identidad Uruguaya.
 * Cumplimiento con Ley N° 18.331 de Protección de Datos Personales.
 */

function limpiar_ci(string $ci): string
{
    return preg_replace('/\D+/', '', $ci);
}

function obtener_clave_cifrado_ci(): string
{
    $key = getenv('APP_KEY') ?: getenv('CI_SECRET_KEY') ?: 'classia_ci_data_protection_key_2026_anitech_uy';
    if (str_starts_with($key, 'base64:')) {
        $decoded = base64_decode(substr($key, 7), true);
        if ($decoded !== false) {
            $key = $decoded;
        }
    }
    return hash('sha256', $key, true); // 32 bytes binarios para AES-256
}

function encriptar_ci(?string $ci): ?string
{
    if ($ci === null || trim($ci) === '') {
        return null;
    }
    $limpia = limpiar_ci($ci);
    if ($limpia === '') {
        return null;
    }

    $key = obtener_clave_cifrado_ci();
    $iv = random_bytes(12); // IV de 96 bits recomendado para AES-GCM
    $tag = '';
    $ciphertext = openssl_encrypt($limpia, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, '', 16);

    if ($ciphertext === false) {
        throw new RuntimeException('Error al cifrar cédula de identidad.');
    }

    return 'enc_v1:' . base64_encode($iv) . ':' . base64_encode($tag) . ':' . base64_encode($ciphertext);
}

function desencriptar_ci(?string $payload): ?string
{
    if ($payload === null || trim($payload) === '') {
        return null;
    }

    $payload = trim($payload);

    // Compatibilidad: si aún está en formato plano numérico
    if (!str_starts_with($payload, 'enc_v1:')) {
        return limpiar_ci($payload);
    }

    $partes = explode(':', $payload);
    if (count($partes) !== 4) {
        return null;
    }

    $iv = base64_decode($partes[1], true);
    $tag = base64_decode($partes[2], true);
    $ciphertext = base64_decode($partes[3], true);

    if ($iv === false || $tag === false || $ciphertext === false) {
        return null;
    }

    $key = obtener_clave_cifrado_ci();
    $plaintext = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

    return ($plaintext !== false) ? $plaintext : null;
}

function hash_ci(string $ci): string
{
    $limpia = limpiar_ci($ci);
    $key = obtener_clave_cifrado_ci();
    return hash_hmac('sha256', $limpia, $key);
}

function calcular_digito_verificador_ci(string $base_ci): int
{
    $limpia = limpiar_ci($base_ci);
    
    $padded = str_pad($limpia, 7, '0', STR_PAD_LEFT);
    $factores = [2, 9, 8, 7, 6, 3, 4];
    $suma = 0;

    for ($i = 0; $i < 7; $i++) {
        $suma += ((int) $padded[$i]) * $factores[$i];
    }

    $resto = $suma % 10;
    return ($resto === 0) ? 0 : (10 - $resto);
}

function validar_cedula_uruguaya(string $ci): bool
{
    $limpia = limpiar_ci($ci);
    $longitud = strlen($limpia);

    if ($longitud < 6 || $longitud > 8) {
        return false;
    }

    $cuerpo = substr($limpia, 0, -1);
    $dv_ingresado = (int) substr($limpia, -1);
    $dv_calculado = calcular_digito_verificador_ci($cuerpo);

    return $dv_ingresado === $dv_calculado;
}

function formatear_ci(?string $ci): string
{
    if ($ci === null || trim($ci) === '') {
        return '';
    }
    $ci_plana = desencriptar_ci($ci) ?? $ci;
    $limpia = limpiar_ci($ci_plana);
    if (strlen($limpia) < 6 || strlen($limpia) > 8) {
        return $ci_plana;
    }

    $dv = substr($limpia, -1);
    $numero = substr($limpia, 0, -1);

    $formateado = number_format((int) $numero, 0, '', '.');
    return $formateado . '-' . $dv;
}

function enmascarar_ci(?string $ci): string
{
    if ($ci === null || trim($ci) === '') {
        return 'No especificada';
    }
    $ci_plana = desencriptar_ci($ci) ?? $ci;
    $limpia = limpiar_ci($ci_plana);
    if (strlen($limpia) < 6) {
        return '***';
    }

    $dv = substr($limpia, -1);
    $primer_digito = $limpia[0];
    $penultimo_digito = substr($limpia, -2, 1);

    return $primer_digito . '.***.**' . $penultimo_digito . '-' . $dv;
}


<?php

/**
 * Responsabilidad: Resolver la URL de avatar de usuario con foto real, Blobatar o fallback local.
 */

const BLOBATAR_BASE_URL = 'https://blobatar.dev';

function obtener_avatar_default(string $prefix = '..'): string
{
    $prefix = rtrim($prefix, '/');
    return ($prefix !== '' ? $prefix . '/' : '') . 'assets/images/default-avatar.svg';
}

function obtener_avatar_usuario(array $usuario, string $prefix = '..', int $size = 128): string
{
    $foto_perfil = trim((string) ($usuario['foto_perfil'] ?? ''));

    if ($foto_perfil !== '') {
        if (preg_match('#^https?://#i', $foto_perfil)) {
            return $foto_perfil;
        }

        $prefix = rtrim($prefix, '/');
        return ($prefix !== '' ? $prefix . '/' : '') . ltrim($foto_perfil, '/');
    }

    $id_usuario = (int) ($usuario['id_usuario'] ?? 0);
    if ($id_usuario <= 0) {
        return obtener_avatar_default($prefix);
    }

    $size = max(8, min(1024, $size));
    $seed = 'classia-user-' . $id_usuario;
    $query = http_build_query([
        'size' => $size,
        'gen' => 2,
        'background' => 'circle',
    ], '', '&', PHP_QUERY_RFC3986);

    return rtrim(BLOBATAR_BASE_URL, '/') . '/avatar/' . rawurlencode($seed) . '?' . $query;
}

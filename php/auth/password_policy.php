<?php

/**
 * Responsabilidad: Define reglas reutilizables para validar fortaleza de contraseñas.
 */

/**
 * Valida la robustez de una contraseña según los estándares de seguridad de la plataforma.
 *
 * @param string $contrasena Contraseña en texto plano a validar.
 * @return array Lista de errores encontrados (vacío si cumple con todas las reglas).
 */
function validar_contrasena(string $contrasena): array
{
    $errores = [];

    if ($contrasena === '') {
        return ['La contraseña es obligatoria.'];
    }

    if (strlen($contrasena) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }

    if (!preg_match('/[a-z]/', $contrasena) || !preg_match('/[A-Z]/', $contrasena)) {
        $errores[] = 'La contraseña debe incluir al menos una letra minúscula y una mayúscula.';
    }

    if (!preg_match('/\d/', $contrasena)) {
        $errores[] = 'La contraseña debe incluir al menos un número.';
    }

    if (!preg_match('/[^A-Za-z0-9]/', $contrasena)) {
        $errores[] = 'La contraseña debe incluir al menos un carácter especial.';
    }

    if (preg_match('/\s/', $contrasena)) {
        $errores[] = 'La contraseña no puede contener espacios.';
    }

    return $errores;
}

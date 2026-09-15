<?php
function limpiar_ci(string $ci): string
{
    return preg_replace('/\D+/', '', $ci);
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

function formatear_ci(string $ci): string
{
    $limpia = limpiar_ci($ci);
    if (strlen($limpia) < 6 || strlen($limpia) > 8) {
        return $ci;
    }

    $dv = substr($limpia, -1);
    $numero = substr($limpia, 0, -1);

    $formateado = number_format((int) $numero, 0, '', '.');
    return $formateado . '-' . $dv;
}
function enmascarar_ci(string $ci): string
{
    $limpia = limpiar_ci($ci);
    if (strlen($limpia) < 6) {
        return '***';
    }

    $dv = substr($limpia, -1);
    $primer_digito = $limpia[0];
    $penultimo_digito = substr($limpia, -2, 1);

    return $primer_digito . '.***.**' . $penultimo_digito . '-' . $dv;
}

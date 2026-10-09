<?php

/**
 * Responsabilidad: Registra eventos de seguridad y auditoría en base de datos (RNF-23).
 * Alineación normativa: ISO/IEC 27001 A.8.15, CIS Controls v8 (8.2), NIST CSF DE.AE-1, OWASP ASVS V10.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/sesion.php';

function registrar_auditoria(
    string $tipo_evento,
    string $descripcion,
    string $severidad = 'INFO',
    ?array $datos_adicionales = null,
    ?int $id_usuario_override = null,
    ?PDO $pdo_override = null
): bool {
    global $pdo;
    $db = $pdo_override ?? $pdo ?? null;

    if (!$db instanceof PDO) {
        return false;
    }

    $id_usuario = $id_usuario_override;
    if ($id_usuario === null && function_exists('id_usuario_actual')) {
        $actual = id_usuario_actual();
        if ($actual > 0) {
            $id_usuario = $actual;
        }
    }

    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    if (str_contains($ip, ',')) {
        $ip = trim(explode(',', $ip)[0]);
    }
    $ip = substr($ip, 0, 45);

    $user_agent = substr($_SERVER['HTTP_USER_AGENT'] ?? 'Desconocido', 0, 255);
    $json_extra = !empty($datos_adicionales) ? json_encode($datos_adicionales, JSON_UNESCAPED_UNICODE) : null;
    $severidad_valida = in_array(strtoupper($severidad), ['INFO', 'WARNING', 'CRITICAL'], true) ? strtoupper($severidad) : 'INFO';

    try {
        $stmt = $db->prepare("
            INSERT INTO auditoria_seguridad 
                (id_usuario, tipo_evento, descripcion, direccion_ip, user_agent, nivel_severidad, datos_adicionales)
            VALUES 
                (:id_usuario, :tipo_evento, :descripcion, :ip, :user_agent, :severidad, :datos_adicionales)
        ");

        return $stmt->execute([
            'id_usuario'        => $id_usuario,
            'tipo_evento'       => substr($tipo_evento, 0, 60),
            'descripcion'       => $descripcion,
            'ip'                => $ip,
            'user_agent'        => $user_agent,
            'severidad'         => $severidad_valida,
            'datos_adicionales' => $json_extra,
        ]);
    } catch (Throwable $e) {
        error_log("Error al persistir auditoria de seguridad: " . $e->getMessage());
        return false;
    }
}

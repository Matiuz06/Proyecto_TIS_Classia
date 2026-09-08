<?php

require_once __DIR__ . '/../../config/database.php';

// Verifica si un usuario ya tiene una solicitud docente en estado Pendiente
function tiene_solicitud_docente_pendiente(PDO $pdo, int $id_usuario): bool
{
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM solicitudes_docente WHERE id_usuario = :id_usuario AND estado = 'Pendiente'"
    );
    $stmt->execute(['id_usuario' => $id_usuario]);

    return (int) $stmt->fetchColumn() > 0;
}

// Procesa el registro de una nueva solicitud docente
function crear_solicitud_docente(PDO $pdo, int $id_usuario, string $motivo, string $token_recibido, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token_recibido)) {
        return [
            'error'   => 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.',
            'mensaje' => '',
            'exito'   => false,
        ];
    }

    if (tiene_solicitud_docente_pendiente($pdo, $id_usuario)) {
        return [
            'error'   => 'Ya tenés una solicitud pendiente.',
            'mensaje' => '',
            'exito'   => false,
        ];
    }

    try {
        $stmt = $pdo->prepare(
            "INSERT INTO solicitudes_docente (id_usuario, estado, motivo)
             VALUES (:id_usuario, 'Pendiente', :motivo)"
        );
        $stmt->execute([
            'id_usuario' => $id_usuario,
            'motivo'     => ($motivo !== '' ? $motivo : null),
        ]);

        return [
            'error'   => '',
            'mensaje' => 'Solicitud enviada correctamente.',
            'exito'   => true,
        ];
    } catch (PDOException $e) {
        error_log('Error al crear solicitud docente: ' . $e->getMessage());
        return [
            'error'   => 'No se pudo enviar la solicitud. Intentá nuevamente.',
            'mensaje' => '',
            'exito'   => false,
        ];
    }
}

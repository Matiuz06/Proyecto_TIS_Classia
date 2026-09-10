<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';

// Obtiene todas las solicitudes docentes pendientes de revisión
function obtener_solicitudes_docente_pendientes(?PDO $pdo_param = null): array
{
    global $pdo;
    $db = $pdo_param ?? $pdo;
    $sql = "SELECT sd.id_solicitud_docente, sd.estado, sd.motivo, sd.fecha_solicitud,
                   u.nombre, u.apellido, u.email
            FROM solicitudes_docente sd
            INNER JOIN usuarios u ON u.id_usuario = sd.id_usuario
            WHERE sd.estado = 'Pendiente'
            ORDER BY sd.fecha_solicitud ASC";

    $stmt = $db->query($sql);
    return $stmt->fetchAll();
}

// Procesa la decisión (aprobar o rechazar) de una solicitud docente
function procesar_decision_solicitud_docente(int $id_solicitud, string $accion, string $token_recibido, string $csrf_session, ?PDO $pdo_param = null): array
{
    global $pdo;
    $db = $pdo_param ?? $pdo;
    if (empty($csrf_session) || !hash_equals($csrf_session, $token_recibido)) {
        return [
            'error'   => 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.',
            'mensaje' => '',
        ];
    }

    if ($id_solicitud <= 0 || !in_array($accion, ['aprobar', 'rechazar'], true)) {
        return [
            'error'   => 'Solicitud inválida.',
            'mensaje' => '',
        ];
    }

    if ($accion === 'aprobar') {
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare(
                "SELECT id_usuario
                 FROM solicitudes_docente
                 WHERE id_solicitud_docente = :id_solicitud AND estado = 'Pendiente'
                 FOR UPDATE"
            );
            $stmt->execute(['id_solicitud' => $id_solicitud]);
            $solicitud = $stmt->fetch();

            if (!$solicitud) {
                $pdo->rollBack();
                return [
                    'error'   => 'La solicitud ya fue procesada.',
                    'mensaje' => '',
                ];
            }

            $stmt = $pdo->prepare(
                "UPDATE solicitudes_docente
                 SET estado = 'Aprobada', fecha_respuesta = CURRENT_TIMESTAMP
                 WHERE id_solicitud_docente = :id_solicitud"
            );
            $stmt->execute(['id_solicitud' => $id_solicitud]);

            $stmt = $pdo->prepare(
                "UPDATE usuarios SET id_rol = :id_rol WHERE id_usuario = :id_usuario"
            );
            $stmt->execute([
                'id_rol'     => ROL_DOCENTE,
                'id_usuario' => $solicitud['id_usuario'],
            ]);

            $pdo->commit();
            return [
                'error'   => '',
                'mensaje' => 'Solicitud aprobada.',
            ];
        } catch (PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Error al aprobar solicitud docente: ' . $e->getMessage());
            return [
                'error'   => 'No se pudo aprobar la solicitud.',
                'mensaje' => '',
            ];
        }
    } else {
        try {
            $stmt = $pdo->prepare(
                "UPDATE solicitudes_docente
                 SET estado = 'Rechazada', fecha_respuesta = CURRENT_TIMESTAMP
                 WHERE id_solicitud_docente = :id_solicitud AND estado = 'Pendiente'"
            );
            $stmt->execute(['id_solicitud' => $id_solicitud]);

            $mensaje = ($stmt->rowCount() > 0) ? 'Solicitud rechazada.' : 'La solicitud ya fue procesada.';
            return [
                'error'   => '',
                'mensaje' => $mensaje,
            ];
        } catch (PDOException $e) {
            error_log('Error al rechazar solicitud docente: ' . $e->getMessage());
            return [
                'error'   => 'No se pudo rechazar la solicitud.',
                'mensaje' => '',
            ];
        }
    }
}

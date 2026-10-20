<?php

/**
 * Responsabilidad: Registra solicitudes para obtener rol docente/proveedor.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';

function asegurar_tabla_solicitudes_docente(PDO $db): void
{
    try {
        $db->exec("
            CREATE TABLE IF NOT EXISTS solicitudes_docente (
                id_solicitud_docente INT AUTO_INCREMENT PRIMARY KEY,
                id_usuario INT NOT NULL,
                estado ENUM('Pendiente', 'Aprobada', 'Rechazada') NOT NULL DEFAULT 'Pendiente',
                motivo TEXT NULL,
                documentacion_ruta VARCHAR(500) NULL,
                documentacion_nombre VARCHAR(255) NULL,
                documentacion_mime VARCHAR(100) NULL,
                documentacion_tamano INT UNSIGNED NULL,
                documentacion_fecha DATETIME NULL,
                fecha_solicitud DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                fecha_respuesta DATETIME NULL,
                CONSTRAINT fk_solicitudes_docente_usuarios
                    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
                    ON DELETE CASCADE ON UPDATE CASCADE,
                INDEX idx_solicitudes_docente_estado (estado),
                INDEX idx_solicitudes_docente_usuario_estado (id_usuario, estado)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    } catch (PDOException $e) {
        error_log("Error al asegurar tabla solicitudes_docente: " . $e->getMessage());
    }
}

function tiene_solicitud_docente_pendiente(int $id_usuario, ?PDO $pdo_param = null): bool
{
    global $pdo;
    $db = $pdo_param ?? $pdo;

    try {
        $stmt = $db->prepare(
            "SELECT COUNT(*) FROM solicitudes_docente WHERE id_usuario = :id_usuario AND estado = 'Pendiente'"
        );
        $stmt->execute(['id_usuario' => $id_usuario]);

        return (int) $stmt->fetchColumn() > 0;
    } catch (PDOException $e) {
        error_log("Error al comprobar solicitud pendiente: " . $e->getMessage());
        return false;
    }
}

function obtener_solicitud_docente_usuario(int $id_usuario, ?PDO $pdo_param = null): ?array
{
    global $pdo;
    $db = $pdo_param ?? $pdo;

    try {
        $stmt = $db->prepare(
            'SELECT estado, motivo, documentacion_nombre, documentacion_mime,
                    documentacion_tamano, documentacion_fecha, fecha_solicitud
             FROM solicitudes_docente
             WHERE id_usuario = :id_usuario
             ORDER BY fecha_solicitud DESC
             LIMIT 1'
        );
        $stmt->execute(['id_usuario' => $id_usuario]);
        $solicitud = $stmt->fetch();
        return $solicitud ?: null;
    } catch (PDOException $e) {
        error_log('Error al obtener solicitud docente del usuario: ' . $e->getMessage());
        return null;
    }
}

// Procesa el registro de una nueva solicitud docente
function crear_solicitud_docente(int $id_usuario, string $motivo, array $archivo, string $token_recibido, string $csrf_session, ?PDO $pdo_param = null): array
{
    global $pdo;
    $db = $pdo_param ?? $pdo;

    if (empty($csrf_session) || !hash_equals($csrf_session, $token_recibido)) {
        return [
            'error'   => 'La sesión del formulario expiró. Recargá la página e intentá nuevamente.',
            'mensaje' => '',
            'exito'   => false,
        ];
    }

    if (tiene_solicitud_docente_pendiente($id_usuario, $db)) {
        return [
            'error'   => 'Ya tenés una solicitud pendiente.',
            'mensaje' => '',
            'exito'   => false,
        ];
    }

    if (!isset($archivo['error']) || (int) $archivo['error'] === UPLOAD_ERR_NO_FILE) {
        return ['error' => 'Debes adjuntar documentación que acredite tus conocimientos.', 'mensaje' => '', 'exito' => false];
    }

    $nombreOriginal = (string) ($archivo['name'] ?? '');
    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $mimesPermitidos = [
        'application/pdf' => ['pdf'],
        'image/jpeg' => ['jpg', 'jpeg'],
        'image/png' => ['png'],
    ];

    if ((int) ($archivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return ['error' => 'No se pudo recibir la documentación adjunta.', 'mensaje' => '', 'exito' => false];
    }
    if (($archivo['size'] ?? 0) > 10 * 1024 * 1024) {
        return ['error' => 'La documentación no puede superar los 10 MB.', 'mensaje' => '', 'exito' => false];
    }
    if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true) || empty($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name'])) {
        return ['error' => 'El archivo debe ser un PDF, JPG, JPEG o PNG válido.', 'mensaje' => '', 'exito' => false];
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($archivo['tmp_name']);
    if (!$mime || !isset($mimesPermitidos[$mime]) || !in_array($extension, $mimesPermitidos[$mime], true)) {
        return ['error' => 'El tipo real del archivo no coincide con un formato permitido.', 'mensaje' => '', 'exito' => false];
    }

    $rutaArchivo = null;

    try {
        $db->beginTransaction();
        $guardado = guardar_archivo_subido($archivo, 'solicitudes_docente', 10);
        if (!$guardado['ok']) {
            $db->rollBack();
            return ['error' => $guardado['error'], 'mensaje' => '', 'exito' => false];
        }
        $rutaArchivo = $guardado['ruta'];
        $stmt = $db->prepare(
            "INSERT INTO solicitudes_docente
                (id_usuario, estado, motivo, documentacion_ruta, documentacion_nombre,
                 documentacion_mime, documentacion_tamano, documentacion_fecha)
             VALUES
                (:id_usuario, 'Pendiente', :motivo, :ruta, :nombre, :mime, :tamano, CURRENT_TIMESTAMP)"
        );
        $stmt->execute([
            'id_usuario' => $id_usuario,
            'motivo'     => ($motivo !== '' ? $motivo : null),
            'ruta'       => $rutaArchivo,
            'nombre'     => $nombreOriginal,
            'mime'       => $mime,
            'tamano'     => (int) $archivo['size'],
        ]);
        $db->commit();

        return [
            'error'   => '',
            'mensaje' => 'Solicitud enviada correctamente.',
            'exito'   => true,
        ];
    } catch (PDOException $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        if ($rutaArchivo !== null) {
            eliminar_archivo_storage($rutaArchivo);
        }
        error_log('Error al crear solicitud docente: ' . $e->getMessage());
        return [
            'error'   => 'No se pudo enviar la solicitud. Intentá nuevamente.',
            'mensaje' => '',
            'exito'   => false,
        ];
    }
}

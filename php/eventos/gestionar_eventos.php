<?php

/**
 * Responsabilidad: Administra creación, moderación y consulta de eventos de la plataforma.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../notificaciones/NotificacionService.php';

function notificar_nuevo_evento(PDO $pdo, int $idEvento, int $idUsuario, array $datos): void
{
    try {
        $autor = trim((string)($datos['autor'] ?? 'Un docente'));
        if ($idUsuario > 0) {
            $autorStmt = $pdo->prepare('SELECT nombre, apellido FROM usuarios WHERE id_usuario = :id LIMIT 1');
            $autorStmt->execute(['id' => $idUsuario]);
            $autorDatos = $autorStmt->fetch(PDO::FETCH_ASSOC);
            if ($autorDatos) {
                $autor = trim(($autorDatos['nombre'] ?? '') . ' ' . ($autorDatos['apellido'] ?? '')) ?: $autor;
            }
        }

        $administradores = $pdo->prepare(
            'SELECT id_usuario FROM usuarios WHERE id_rol = :rol AND COALESCE(activo, 1) = 1'
        );
        $administradores->execute(['rol' => ROL_ADMIN]);
        $idsAdministradores = array_map('intval', $administradores->fetchAll(PDO::FETCH_COLUMN));

        if (!$idsAdministradores) {
            return;
        }

        $notificaciones = new NotificacionService(new NotificacionRepository($pdo));
        $notificaciones->crearParaUsuarios($idsAdministradores, [
            'categoria' => 'eventos',
            'titulo' => 'Nueva propuesta de evento',
            'mensaje' => $autor . ' propuso el evento «' . trim((string)($datos['titulo'] ?? 'Sin título')) . '». Está pendiente de revisión.',
            'url_destino' => 'views/eventos.php',
            'tipo_entidad' => 'evento',
            'id_entidad' => $idEvento,
            'clave_idempotencia' => 'evento:nuevo:' . $idEvento,
        ]);
    } catch (Throwable $error) {
        error_log('Notificacion nuevo evento #' . $idEvento . ': ' . $error->getMessage());
    }
}

function notificar_decision_evento(PDO $pdo, array $evento, string $estado): void
{
    $idUsuario = (int)($evento['id_usuario'] ?? 0);
    if ($idUsuario <= 0) {
        return;
    }

    try {
        $notificaciones = new NotificacionService(new NotificacionRepository($pdo));
        $publicado = $estado === 'Abierto';
        $notificaciones->crear([
            'id_usuario' => $idUsuario,
            'categoria' => 'eventos',
            'titulo' => $publicado ? 'Evento aprobado' : 'Evento rechazado',
            'mensaje' => $publicado
                ? 'Tu evento «' . $evento['titulo'] . '» fue aprobado y publicado.'
                : 'Tu evento «' . $evento['titulo'] . '» fue rechazado.',
            'url_destino' => 'views/eventos.php',
            'tipo_entidad' => 'evento',
            'id_entidad' => (int)$evento['id_evento'],
            'clave_idempotencia' => 'evento:' . (int)$evento['id_evento'] . ':estado:' . strtolower($estado),
        ]);
    } catch (Throwable $error) {
        error_log('Notificacion decision evento #' . (int)$evento['id_evento'] . ': ' . $error->getMessage());
    }
}

function obtener_eventos(PDO $pdo, int $limite = 0, string $estado = 'Abierto', ?int $id_usuario_docente = null, bool $es_admin = false): array
{
    try {
        $sql = "SELECT id_evento, titulo, descripcion, tipo, modalidad, fecha_evento, ubicacion_enlace, cupos, imagen, id_usuario, estado 
                FROM eventos";
        $params = [];

        if ($es_admin) {
            if ($estado !== '') {
                $sql .= " WHERE estado = :estado";
                $params['estado'] = $estado;
            }
        } elseif ($id_usuario_docente !== null && $id_usuario_docente > 0) {
            $sql .= " WHERE (estado = 'Abierto' OR id_usuario = :docente_id)";
            $params['docente_id'] = $id_usuario_docente;
        } else {
            $sql .= " WHERE estado = 'Abierto'";
        }

        $sql .= " ORDER BY fecha_evento ASC";

        if ($limite > 0) {
            $sql .= " LIMIT " . (int)$limite;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            $sql = "SELECT id_evento, titulo, descripcion, tipo, modalidad, fecha_evento, ubicacion_enlace, cupos, imagen, estado 
                    FROM eventos WHERE estado = 'Abierto' ORDER BY fecha_evento ASC";
            if ($limite > 0) {
                $sql .= " LIMIT " . (int)$limite;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            error_log("Error al obtener eventos: " . $e2->getMessage());
            return [];
        }
    }
}

function obtener_evento_por_id(PDO $pdo, int $id_evento): ?array
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM eventos WHERE id_evento = :id LIMIT 1");
        $stmt->execute(['id' => $id_evento]);
        $evento = $stmt->fetch(PDO::FETCH_ASSOC);
        return $evento ?: null;
    } catch (PDOException $e) {
        error_log("Error al obtener evento #$id_evento: " . $e->getMessage());
        return null;
    }
}

function crear_evento(PDO $pdo, array $datos, ?int $id_usuario = null, bool $es_admin = false): int
{
    $estado = $es_admin ? 'Abierto' : 'Pendiente';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO eventos (titulo, descripcion, tipo, modalidad, fecha_evento, ubicacion_enlace, cupos, imagen, id_usuario, estado)
            VALUES (:titulo, :descripcion, :tipo, :modalidad, :fecha_evento, :ubicacion_enlace, :cupos, :imagen, :id_usuario, :estado)
        ");
        $stmt->execute([
            'titulo'           => trim($datos['titulo']),
            'descripcion'      => trim($datos['descripcion']),
            'tipo'             => trim($datos['tipo'] ?? 'Webinar'),
            'modalidad'        => trim($datos['modalidad'] ?? 'Online'),
            'fecha_evento'     => trim($datos['fecha_evento']),
            'ubicacion_enlace' => trim($datos['ubicacion_enlace'] ?? ''),
            'cupos'            => (int)($datos['cupos'] ?? 0),
            'imagen'           => trim($datos['imagen'] ?? ''),
            'id_usuario'       => $id_usuario,
            'estado'           => $estado,
        ]);
        $idEvento = (int)$pdo->lastInsertId();
        if (!$es_admin && $idEvento > 0) {
            notificar_nuevo_evento($pdo, $idEvento, (int)$id_usuario, $datos);
        }
        return $idEvento;
    } catch (PDOException $e) {
        $stmt = $pdo->prepare("
            INSERT INTO eventos (titulo, descripcion, tipo, modalidad, fecha_evento, ubicacion_enlace, cupos, imagen, estado)
            VALUES (:titulo, :descripcion, :tipo, :modalidad, :fecha_evento, :ubicacion_enlace, :cupos, :imagen, :estado)
        ");
        $stmt->execute([
            'titulo'           => trim($datos['titulo']),
            'descripcion'      => trim($datos['descripcion']),
            'tipo'             => trim($datos['tipo'] ?? 'Webinar'),
            'modalidad'        => trim($datos['modalidad'] ?? 'Online'),
            'fecha_evento'     => trim($datos['fecha_evento']),
            'ubicacion_enlace' => trim($datos['ubicacion_enlace'] ?? ''),
            'cupos'            => (int)($datos['cupos'] ?? 0),
            'imagen'           => trim($datos['imagen'] ?? ''),
            'estado'           => $estado,
        ]);
        $idEvento = (int)$pdo->lastInsertId();
        if (!$es_admin && $idEvento > 0) {
            notificar_nuevo_evento($pdo, $idEvento, (int)$id_usuario, $datos);
        }
        return $idEvento;
    }
}

function aprobar_evento(PDO $pdo, int $id_evento): bool
{
    $stmt = $pdo->prepare('SELECT id_evento, id_usuario, titulo, estado FROM eventos WHERE id_evento = :id LIMIT 1');
    $stmt->execute(['id' => $id_evento]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$evento || $evento['estado'] !== 'Pendiente') {
        return false;
    }

    $stmt = $pdo->prepare("UPDATE eventos SET estado = 'Abierto' WHERE id_evento = :id AND estado = 'Pendiente'");
    $stmt->execute(['id' => $id_evento]);
    if ($stmt->rowCount() !== 1) {
        return false;
    }

    notificar_decision_evento($pdo, $evento, 'Abierto');
    return true;
}

function rechazar_evento(PDO $pdo, int $id_evento): bool
{
    $stmt = $pdo->prepare('SELECT id_evento, id_usuario, titulo, estado FROM eventos WHERE id_evento = :id LIMIT 1');
    $stmt->execute(['id' => $id_evento]);
    $evento = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$evento || $evento['estado'] !== 'Pendiente') {
        return false;
    }

    $stmt = $pdo->prepare("UPDATE eventos SET estado = 'Rechazado' WHERE id_evento = :id AND estado = 'Pendiente'");
    $stmt->execute(['id' => $id_evento]);
    if ($stmt->rowCount() !== 1) {
        return false;
    }

    notificar_decision_evento($pdo, $evento, 'Rechazado');
    return true;
}

function eliminar_evento(PDO $pdo, int $id_evento, ?int $id_usuario = null, bool $es_admin = false): bool
{
    if ($es_admin) {
        $stmt = $pdo->prepare("DELETE FROM eventos WHERE id_evento = :id");
        return $stmt->execute(['id' => $id_evento]);
    }

    if ($id_usuario !== null && $id_usuario > 0) {
        $stmt = $pdo->prepare("DELETE FROM eventos WHERE id_evento = :id AND id_usuario = :id_usuario");
        return $stmt->execute(['id' => $id_evento, 'id_usuario' => $id_usuario]);
    }

    return false;
}

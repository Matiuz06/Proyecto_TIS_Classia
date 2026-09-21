<?php

require_once __DIR__ . '/../../config/database.php';

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
        return (int)$pdo->lastInsertId();
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
        return (int)$pdo->lastInsertId();
    }
}

function aprobar_evento(PDO $pdo, int $id_evento): bool
{
    $stmt = $pdo->prepare("UPDATE eventos SET estado = 'Abierto' WHERE id_evento = :id");
    return $stmt->execute(['id' => $id_evento]);
}

function rechazar_evento(PDO $pdo, int $id_evento): bool
{
    $stmt = $pdo->prepare("UPDATE eventos SET estado = 'Rechazado' WHERE id_evento = :id");
    return $stmt->execute(['id' => $id_evento]);
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

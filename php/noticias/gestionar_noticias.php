<?php

/**
 * Responsabilidad: Administra creación, moderación, ordenamiento y consulta de noticias.
 */

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../notificaciones/NotificacionService.php';

function notificar_nueva_noticia(PDO $pdo, int $idNoticia, int $idUsuario, array $datos): void
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
            'categoria' => 'noticias',
            'titulo' => 'Nueva propuesta de noticia',
            'mensaje' => $autor . ' propuso la noticia «' . trim((string)($datos['titulo'] ?? 'Sin título')) . '». Está pendiente de revisión.',
            'url_destino' => 'views/noticias.php',
            'tipo_entidad' => 'noticia',
            'id_entidad' => $idNoticia,
            'clave_idempotencia' => 'noticia:nueva:' . $idNoticia,
        ]);
    } catch (Throwable $error) {
        error_log('Notificacion nueva noticia #' . $idNoticia . ': ' . $error->getMessage());
    }
}

function notificar_decision_noticia(PDO $pdo, array $noticia, string $estado): void
{
    $idUsuario = (int)($noticia['id_usuario'] ?? 0);
    if ($idUsuario <= 0) {
        return;
    }

    try {
        $notificaciones = new NotificacionService(new NotificacionRepository($pdo));
        $publicada = $estado === 'Publicada';
        $notificaciones->crear([
            'id_usuario' => $idUsuario,
            'categoria' => 'noticias',
            'titulo' => $publicada ? 'Noticia aprobada' : 'Noticia rechazada',
            'mensaje' => $publicada
                ? 'Tu noticia «' . $noticia['titulo'] . '» fue aprobada y publicada.'
                : 'Tu noticia «' . $noticia['titulo'] . '» fue rechazada.',
            'url_destino' => 'views/noticias.php',
            'tipo_entidad' => 'noticia',
            'id_entidad' => (int)$noticia['id_noticia'],
            'clave_idempotencia' => 'noticia:' . (int)$noticia['id_noticia'] . ':estado:' . strtolower($estado),
        ]);
    } catch (Throwable $error) {
        error_log('Notificacion decision noticia #' . (int)$noticia['id_noticia'] . ': ' . $error->getMessage());
    }
}

function obtener_noticias(PDO $pdo, int $limite = 0, string $estado = 'Publicada', ?int $id_usuario_docente = null, bool $es_admin = false): array
{
    try {
        $sql = "SELECT id_noticia, titulo, subtitulo, cuerpo, imagen, categoria, autor, id_usuario, fecha_publicacion, orden, estado 
                FROM noticias";
        $params = [];

        if ($es_admin) {
            if ($estado !== '') {
                $sql .= " WHERE estado = :estado";
                $params['estado'] = $estado;
            }
        } elseif ($id_usuario_docente !== null && $id_usuario_docente > 0) {
            $sql .= " WHERE (estado = 'Publicada' OR id_usuario = :docente_id)";
            $params['docente_id'] = $id_usuario_docente;
        } else {
            $sql .= " WHERE estado = 'Publicada'";
        }

        $sql .= " ORDER BY orden ASC, fecha_publicacion DESC";

        if ($limite > 0) {
            $sql .= " LIMIT " . (int)$limite;
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        try {
            $sql = "SELECT id_noticia, titulo, subtitulo, cuerpo, imagen, categoria, autor, fecha_publicacion, orden, estado 
                    FROM noticias WHERE estado = 'Publicada' ORDER BY orden ASC, fecha_publicacion DESC";
            if ($limite > 0) {
                $sql .= " LIMIT " . (int)$limite;
            }
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e2) {
            error_log("Error al obtener noticias: " . $e2->getMessage());
            return [];
        }
    }
}

function obtener_noticia_por_id(PDO $pdo, int $id_noticia): ?array
{
    try {
        $stmt = $pdo->prepare("SELECT * FROM noticias WHERE id_noticia = :id LIMIT 1");
        $stmt->execute(['id' => $id_noticia]);
        $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
        return $noticia ?: null;
    } catch (PDOException $e) {
        error_log("Error al obtener noticia #$id_noticia: " . $e->getMessage());
        return null;
    }
}

function crear_noticia(PDO $pdo, array $datos, ?int $id_usuario = null, bool $es_admin = false): int
{
    $estado = $es_admin ? 'Publicada' : 'Pendiente';

    try {
        $stmt = $pdo->prepare("
            INSERT INTO noticias (titulo, subtitulo, cuerpo, imagen, categoria, autor, id_usuario, orden, estado, fecha_publicacion)
            VALUES (:titulo, :subtitulo, :cuerpo, :imagen, :categoria, :autor, :id_usuario, :orden, :estado, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            'titulo'     => trim($datos['titulo']),
            'subtitulo'  => trim($datos['subtitulo'] ?? ''),
            'cuerpo'     => trim($datos['cuerpo']),
            'imagen'     => trim($datos['imagen'] ?? ''),
            'categoria'  => trim($datos['categoria'] ?? 'Institucional'),
            'autor'      => trim($datos['autor'] ?? 'Equipo AniTech'),
            'id_usuario' => $id_usuario,
            'orden'      => (int)($datos['orden'] ?? 0),
            'estado'     => $estado,
        ]);
        $idNoticia = (int)$pdo->lastInsertId();
        if (!$es_admin && $idNoticia > 0) {
            notificar_nueva_noticia($pdo, $idNoticia, (int)$id_usuario, $datos);
        }
        return $idNoticia;
    } catch (PDOException $e) {
        $stmt = $pdo->prepare("
            INSERT INTO noticias (titulo, subtitulo, cuerpo, imagen, categoria, autor, orden, estado, fecha_publicacion)
            VALUES (:titulo, :subtitulo, :cuerpo, :imagen, :categoria, :autor, :orden, :estado, CURRENT_TIMESTAMP)
        ");
        $stmt->execute([
            'titulo'     => trim($datos['titulo']),
            'subtitulo'  => trim($datos['subtitulo'] ?? ''),
            'cuerpo'     => trim($datos['cuerpo']),
            'imagen'     => trim($datos['imagen'] ?? ''),
            'categoria'  => trim($datos['categoria'] ?? 'Institucional'),
            'autor'      => trim($datos['autor'] ?? 'Equipo AniTech'),
            'orden'      => (int)($datos['orden'] ?? 0),
            'estado'     => $estado,
        ]);
        $idNoticia = (int)$pdo->lastInsertId();
        if (!$es_admin && $idNoticia > 0) {
            notificar_nueva_noticia($pdo, $idNoticia, (int)$id_usuario, $datos);
        }
        return $idNoticia;
    }
}

function aprobar_noticia(PDO $pdo, int $id_noticia): bool
{
    $stmt = $pdo->prepare('SELECT id_noticia, id_usuario, titulo, estado FROM noticias WHERE id_noticia = :id LIMIT 1');
    $stmt->execute(['id' => $id_noticia]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$noticia || $noticia['estado'] !== 'Pendiente') {
        return false;
    }

    $stmt = $pdo->prepare("UPDATE noticias SET estado = 'Publicada' WHERE id_noticia = :id AND estado = 'Pendiente'");
    $stmt->execute(['id' => $id_noticia]);
    if ($stmt->rowCount() !== 1) {
        return false;
    }

    notificar_decision_noticia($pdo, $noticia, 'Publicada');
    return true;
}

function rechazar_noticia(PDO $pdo, int $id_noticia): bool
{
    $stmt = $pdo->prepare('SELECT id_noticia, id_usuario, titulo, estado FROM noticias WHERE id_noticia = :id LIMIT 1');
    $stmt->execute(['id' => $id_noticia]);
    $noticia = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$noticia || $noticia['estado'] !== 'Pendiente') {
        return false;
    }

    $stmt = $pdo->prepare("UPDATE noticias SET estado = 'Rechazada' WHERE id_noticia = :id AND estado = 'Pendiente'");
    $stmt->execute(['id' => $id_noticia]);
    if ($stmt->rowCount() !== 1) {
        return false;
    }

    notificar_decision_noticia($pdo, $noticia, 'Rechazada');
    return true;
}

function eliminar_noticia(PDO $pdo, int $id_noticia, ?int $id_usuario = null, bool $es_admin = false): bool
{
    if ($es_admin) {
        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id_noticia = :id");
        return $stmt->execute(['id' => $id_noticia]);
    }

    if ($id_usuario !== null && $id_usuario > 0) {
        $stmt = $pdo->prepare("DELETE FROM noticias WHERE id_noticia = :id AND id_usuario = :id_usuario");
        return $stmt->execute(['id' => $id_noticia, 'id_usuario' => $id_usuario]);
    }

    return false;
}

function reordenar_noticia(PDO $pdo, int $id_noticia, int $nuevo_orden): bool
{
    $stmt = $pdo->prepare("UPDATE noticias SET orden = :orden WHERE id_noticia = :id");
    return $stmt->execute(['orden' => $nuevo_orden, 'id' => $id_noticia]);
}

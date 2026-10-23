<?php

/**
 * Responsabilidad: Administra creación, moderación, ordenamiento y consulta de noticias.
 */

require_once __DIR__ . '/../../config/database.php';

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
        return (int)$pdo->lastInsertId();
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
        return (int)$pdo->lastInsertId();
    }
}

function aprobar_noticia(PDO $pdo, int $id_noticia): bool
{
    $stmt = $pdo->prepare("UPDATE noticias SET estado = 'Publicada' WHERE id_noticia = :id");
    return $stmt->execute(['id' => $id_noticia]);
}

function rechazar_noticia(PDO $pdo, int $id_noticia): bool
{
    $stmt = $pdo->prepare("UPDATE noticias SET estado = 'Rechazada' WHERE id_noticia = :id");
    return $stmt->execute(['id' => $id_noticia]);
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

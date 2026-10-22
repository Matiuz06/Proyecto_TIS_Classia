<?php

/** Persistencia de notificaciones, siempre acotada al usuario destinatario. */
class NotificacionRepository
{
    private PDO $pdo;
    private bool $postgresql;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->postgresql = $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql';
    }

    public function usuarioExiste(int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $idUsuario]);
        return (bool) $stmt->fetchColumn();
    }

    public function crear(array $datos): array
    {
        $existente = $this->buscarPorClave((int) $datos['id_usuario'], $datos['clave_idempotencia']);
        if ($existente !== null) {
            return ['id' => (int) $existente['id_notificacion'], 'creada' => false];
        }

        $sql = 'INSERT INTO notificaciones
            (id_usuario, categoria, titulo, mensaje, url_destino, tipo_entidad,
             id_entidad, leida, fecha_creacion, clave_idempotencia)
            VALUES (:id_usuario, :categoria, :titulo, :mensaje, :url_destino,
                    :tipo_entidad, :id_entidad, :leida, CURRENT_TIMESTAMP,
                    :clave_idempotencia)';

        if ($this->postgresql) {
            $sql .= ' ON CONFLICT (id_usuario, clave_idempotencia) DO NOTHING';
        } else {
            $sql .= ' ON DUPLICATE KEY UPDATE id_notificacion = id_notificacion';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'id_usuario' => (int) $datos['id_usuario'],
            'categoria' => $datos['categoria'],
            'titulo' => $datos['titulo'],
            'mensaje' => $datos['mensaje'],
            'url_destino' => $datos['url_destino'],
            'tipo_entidad' => $datos['tipo_entidad'],
            'id_entidad' => $datos['id_entidad'],
            'leida' => 0,
            'clave_idempotencia' => $datos['clave_idempotencia'],
        ]);

        $notificacion = $this->buscarPorClave((int) $datos['id_usuario'], $datos['clave_idempotencia']);
        if ($notificacion === null) {
            throw new RuntimeException('No se pudo registrar la notificación.');
        }

        return [
            'id' => (int) $notificacion['id_notificacion'],
            'creada' => $stmt->rowCount() > 0,
        ];
    }

    public function buscarPorClave(int $idUsuario, string $clave): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT * FROM notificaciones
             WHERE id_usuario = :usuario AND clave_idempotencia = :clave
             LIMIT 1'
        );
        $stmt->execute(['usuario' => $idUsuario, 'clave' => $clave]);
        $fila = $stmt->fetch();
        return $fila ?: null;
    }

    public function ultimas(int $idUsuario, int $limite = 10): array
    {
        $limite = max(1, min($limite, 50));
        $stmt = $this->pdo->prepare(
            "SELECT id_notificacion, categoria, titulo, mensaje, url_destino,
                    tipo_entidad, id_entidad, leida, fecha_creacion, fecha_leida
             FROM notificaciones
             WHERE id_usuario = :usuario
             ORDER BY fecha_creacion DESC, id_notificacion DESC
             LIMIT {$limite}"
        );
        $stmt->execute(['usuario' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function contarNoLeidas(int $idUsuario): int
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) FROM notificaciones
             WHERE id_usuario = :usuario AND leida = :leida'
        );
        $stmt->execute(['usuario' => $idUsuario, 'leida' => $this->postgresql ? false : 0]);
        return (int) $stmt->fetchColumn();
    }

    public function historial(int $idUsuario, int $pagina = 1, int $porPagina = 20, string $filtro = 'all'): array
    {
        if (!in_array($filtro, ['all', 'read', 'unread'], true)) {
            throw new InvalidArgumentException('Filtro de notificaciones inválido.');
        }

        $pagina = max(1, $pagina);
        $porPagina = max(1, min($porPagina, 50));
        $offset = ($pagina - 1) * $porPagina;
        $where = ['id_usuario = :usuario'];
        $params = ['usuario' => $idUsuario];
        if ($filtro !== 'all') {
            $where[] = 'leida = :estado_leida';
            $params['estado_leida'] = $filtro === 'read'
                ? ($this->postgresql ? true : 1)
                : ($this->postgresql ? false : 0);
        }
        $whereSql = implode(' AND ', $where);

        $stmt = $this->pdo->prepare(
            "SELECT id_notificacion, categoria, titulo, mensaje, url_destino,
                    tipo_entidad, id_entidad, leida, fecha_creacion, fecha_leida
             FROM notificaciones
             WHERE {$whereSql}
             ORDER BY fecha_creacion DESC, id_notificacion DESC
             LIMIT {$porPagina} OFFSET {$offset}"
        );
        $stmt->execute($params);

        $total = $this->pdo->prepare(
            "SELECT COUNT(*) FROM notificaciones WHERE {$whereSql}"
        );
        $total->execute($params);

        return [
            'items' => $stmt->fetchAll(),
            'total' => (int) $total->fetchColumn(),
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
        ];
    }

    public function marcarComoLeida(int $idNotificacion, int $idUsuario): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notificaciones
             SET leida = :leida, fecha_leida = CURRENT_TIMESTAMP
             WHERE id_notificacion = :notificacion AND id_usuario = :usuario'
        );
        $stmt->execute([
            'leida' => $this->postgresql ? true : 1,
            'notificacion' => $idNotificacion,
            'usuario' => $idUsuario,
        ]);
        return $stmt->rowCount() > 0;
    }

    public function marcarTodasComoLeidas(int $idUsuario): int
    {
        $stmt = $this->pdo->prepare(
            'UPDATE notificaciones
             SET leida = :leida, fecha_leida = CURRENT_TIMESTAMP
             WHERE id_usuario = :usuario AND leida = :no_leida'
        );
        $stmt->execute([
            'leida' => $this->postgresql ? true : 1,
            'usuario' => $idUsuario,
            'no_leida' => $this->postgresql ? false : 0,
        ]);
        return $stmt->rowCount();
    }
}

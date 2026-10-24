<?php

require_once __DIR__ . '/NotificacionRepository.php';

/** Crea notificaciones seguras e idempotentes sin acoplarlas a los módulos emisores. */
class NotificacionService
{
    private NotificacionRepository $repository;

    public function __construct(NotificacionRepository $repository)
    {
        $this->repository = $repository;
    }

    public function crear(array $datos): ?array
    {
        try {
            $normalizados = $this->normalizar($datos);
            if (!$this->repository->usuarioExiste($normalizados['id_usuario'])) {
                throw new InvalidArgumentException('El destinatario no existe.');
            }
            return $this->repository->crear($normalizados);
        } catch (Throwable $error) {
            error_log('Error al crear notificacion: ' . $error->getMessage());
            return null;
        }
    }

    public function crearParaUsuarios(array $idsUsuarios, array $datos): array
    {
        $resultados = [];
        foreach (array_unique(array_map('intval', $idsUsuarios)) as $idUsuario) {
            if ($idUsuario <= 0) {
                continue;
            }
            $resultados[$idUsuario] = $this->crear(array_merge($datos, [
                'id_usuario' => $idUsuario,
            ]));
        }
        return $resultados;
    }

    private function normalizar(array $datos): array
    {
        $idUsuario = (int) ($datos['id_usuario'] ?? 0);
        $categoria = trim((string) ($datos['categoria'] ?? 'general'));
        $titulo = trim((string) ($datos['titulo'] ?? ''));
        $mensaje = trim((string) ($datos['mensaje'] ?? ''));
        $url = $datos['url_destino'] ?? null;
        $url = $url === null ? null : trim((string) $url);

        if ($idUsuario <= 0 || $titulo === '' || $mensaje === '') {
            throw new InvalidArgumentException('Faltan datos de la notificación.');
        }
        if (!preg_match('/^[a-zA-Z0-9_.:-]{1,60}$/', $categoria)) {
            throw new InvalidArgumentException('Categoría de notificación inválida.');
        }
        if (strlen($titulo) > 180 || strlen($categoria) > 60 || strlen($mensaje) > 10000) {
            throw new InvalidArgumentException('La notificación excede el tamaño permitido.');
        }
        if ($url !== null && !$this->urlInterna($url)) {
            throw new InvalidArgumentException('La URL de notificación no es interna.');
        }

        $tipoEntidad = $datos['tipo_entidad'] ?? null;
        $tipoEntidad = $tipoEntidad === null ? null : trim((string) $tipoEntidad);
        if ($tipoEntidad !== null && ($tipoEntidad === '' || !preg_match('/^[a-zA-Z0-9_.:-]{1,80}$/', $tipoEntidad))) {
            throw new InvalidArgumentException('Tipo de entidad inválido.');
        }

        $idEntidad = $datos['id_entidad'] ?? null;
        $idEntidad = $idEntidad === null || $idEntidad === '' ? null : (int) $idEntidad;
        if ($idEntidad !== null && $idEntidad <= 0) {
            throw new InvalidArgumentException('Identificador de entidad inválido.');
        }

        $clave = trim((string) ($datos['clave_idempotencia'] ?? ''));
        if ($clave === '') {
            $clave = hash('sha256', implode('|', [
                $idUsuario,
                $categoria,
                $titulo,
                $mensaje,
                $url ?? '',
                $tipoEntidad ?? '',
                $idEntidad ?? '',
            ]));
        }
        if (!preg_match('/^[a-zA-Z0-9:_-]{1,64}$/', $clave)) {
            throw new InvalidArgumentException('Clave de idempotencia inválida.');
        }

        return [
            'id_usuario' => $idUsuario,
            'categoria' => $categoria,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url_destino' => $url,
            'tipo_entidad' => $tipoEntidad,
            'id_entidad' => $idEntidad,
            'clave_idempotencia' => $clave,
        ];
    }

    private function urlInterna(string $url): bool
    {
        if ($url === '' || preg_match('/[\x00-\x1F\\\\]/', $url)) {
            return false;
        }
        if (preg_match('/^(?:[a-z][a-z0-9+.-]*:|\/\/)/i', $url)) {
            return false;
        }
        return true;
    }
}

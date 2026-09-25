<?php

require_once __DIR__ . '/Publicacion.php';

/**
 * Publicacion de tipo servicio, con plantilla opcional para solicitudes.
 */
class Servicio extends Publicacion
{
    private ?string $tipo_servicio;

    /**
     * @param array $datos Fila de publicaciones o datos validados del formulario.
     */
    public function __construct(array $datos)
    {
        $datos['tipo'] = 'Servicio';
        parent::__construct($datos);
        $this->tipo_servicio = self::textoOpcional($datos['tipo_servicio'] ?? null);
    }

    /** Devuelve la plantilla de solicitud asociada al servicio, si existe. */
    public function getTipoServicio(): ?string
    {
        return $this->tipo_servicio;
    }

    /** Exporta el servicio en formato compatible con las vistas existentes. */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'tipo_servicio' => $this->tipo_servicio,
        ]);
    }
}

<?php

require_once __DIR__ . '/ElementoCurso.php';
require_once __DIR__ . '/Recurso.php';

/**
 * Responsabilidad: Representa una unidad (clase) dentro de un módulo de curso.
 * Extiende ElementoCurso y compone una colección de objetos Recurso.
 * No tiene dependencias de PDO ni de la capa de acceso a datos.
 */
class Unidad extends ElementoCurso
{
    private int $id_unidad;
    private int $id_modulo;

    /** @var Recurso[] */
    private array $recursos = [];

    public function __construct(
        int     $id_unidad,
        int     $id_modulo,
        string  $titulo,
        ?string $descripcion,
        int     $orden
    ) {
        parent::__construct($id_unidad, $titulo, $descripcion, $orden);
        $this->id_unidad = $id_unidad;
        $this->id_modulo = $id_modulo;
    }

    //Getters propios

    public function getId(): int       { return $this->id_unidad; }
    public function getIdModulo(): int  { return $this->id_modulo; }

    //Composición: recursos

    public function addRecurso(Recurso $recurso): void
    {
        $this->recursos[] = $recurso;
    }

    /**
     * @return Recurso[]
     */
    public function getRecursos(): array
    {
        return $this->recursos;
    }

    public function contarRecursos(): int
    {
        return count($this->recursos);
    }

    //Construcción / exportación 

    public static function fromArray(array $row): static
    {
        return new static(
            (int) $row['id_unidad'],
            (int) $row['id_modulo'],
            $row['titulo']      ?? '',
            $row['descripcion'] ?? null,
            (int) ($row['orden'] ?? 0),
        );
    }

    /**
     * Exporta el objeto como array asociativo, incluyendo los recursos.
     * Mantiene la estructura que esperan las plantillas existentes.
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'id_unidad' => $this->id_unidad,
            'id_modulo' => $this->id_modulo,
            'recursos'  => array_map(fn(Recurso $r) => $r->toArray(), $this->recursos),
        ]);
    }
}

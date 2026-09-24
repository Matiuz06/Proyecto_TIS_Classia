<?php

require_once __DIR__ . '/ElementoCurso.php';
require_once __DIR__ . '/Unidad.php';

/**
 * Responsabilidad: Representa un módulo dentro de un curso.
 * Extiende ElementoCurso y compone una colección de objetos Unidad
 * (que a su vez componen Recurso).
 * No tiene dependencias de PDO ni de la capa de acceso a datos.
 */
class Modulo extends ElementoCurso
{
    private int $id_modulo;
    private int $id_publicacion;

    /** @var Unidad[] */
    private array $unidades = [];

    public function __construct(
        int     $id_modulo,
        int     $id_publicacion,
        string  $titulo,
        ?string $descripcion,
        int     $orden
    ) {
        parent::__construct($id_modulo, $titulo, $descripcion, $orden);
        $this->id_modulo      = $id_modulo;
        $this->id_publicacion = $id_publicacion;
    }

    //Getters propios 

    public function getId(): int            { return $this->id_modulo; }
    public function getIdPublicacion(): int  { return $this->id_publicacion; }

    //Composición: unidades 

    public function addUnidad(Unidad $unidad): void
    {
        $this->unidades[] = $unidad;
    }

    /**
     * @return Unidad[]
     */
    public function getUnidades(): array
    {
        return $this->unidades;
    }

    public function contarUnidades(): int
    {
        return count($this->unidades);
    }

    public function contarRecursosTotales(): int
    {
        return array_sum(array_map(fn(Unidad $u) => $u->contarRecursos(), $this->unidades));
    }

    //Construcción / exportación

    public static function fromArray(array $row): static
    {
        return new static(
            (int) $row['id_modulo'],
            (int) $row['id_publicacion'],
            $row['titulo']      ?? '',
            $row['descripcion'] ?? null,
            (int) ($row['orden'] ?? 0),
        );
    }

    /**
     * Exporta el objeto como array asociativo, incluyendo unidades y recursos.
     * Mantiene la estructura que esperan las plantillas existentes:
     *   $modulo['unidades'][$i]['recursos'][$j]
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'id_modulo'      => $this->id_modulo,
            'id_publicacion' => $this->id_publicacion,
            'unidades'       => array_map(fn(Unidad $u) => $u->toArray(), $this->unidades),
        ]);
    }
}

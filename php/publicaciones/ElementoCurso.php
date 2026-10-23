<?php

/**
 * Clase base abstracta para los elementos que componen el contenido de un curso.
 *
 * Todos los elementos (Módulo, Unidad, Recurso) comparten:
 *  - Un identificador entero único.
 *  - Un título obligatorio.
 *  - Una descripción opcional.
 *  - Un número de orden dentro de su contenedor.
 *
 * La herencia evita duplicar getters y la implementación parcial de toArray()
 * en las tres subclases.
 *
 * Árbol de herencia:
 *   ElementoCurso (abstract)
 *    ├── Modulo
 *    ├── Unidad
 *    └── Recurso
 */
abstract class ElementoCurso
{
    protected int    $titulo_id;   // id_modulo | id_unidad | id_recurso
    protected string $titulo;
    protected ?string $descripcion;
    protected int    $orden;

    public function __construct(
        int     $id,
        string  $titulo,
        ?string $descripcion,
        int     $orden
    ) {
        $this->titulo_id   = $id;
        $this->titulo      = $titulo;
        $this->descripcion = $descripcion;
        $this->orden       = $orden;
    }

    //Getters compartidos

    /** Devuelve el ID del elemento (id_modulo, id_unidad o id_recurso). */
    abstract public function getId(): int;

    public function getTitulo(): string    { return $this->titulo; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function getOrden(): int       { return $this->orden; }

    //Construcción / exportación

    /**
     * Construye el elemento desde una fila de la base de datos.
     * Cada subclase implementa su propia lógica de construcción.
     */
    abstract public static function fromArray(array $row): static;

    /**
     * Exporta los campos comunes del elemento como array asociativo.
     * Las subclases deben llamar parent::toArray() y fusionar sus campos propios:
     *
     *   return array_merge(parent::toArray(), ['campo_propio' => $this->campo]);
     */
    public function toArray(): array
    {
        return [
            'titulo'      => $this->titulo,
            'descripcion' => $this->descripcion,
            'orden'       => $this->orden,
        ];
    }
}

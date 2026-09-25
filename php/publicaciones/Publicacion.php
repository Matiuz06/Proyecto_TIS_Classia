<?php

/**
 * Representa los datos comunes de una publicacion del catalogo.
 * No conoce PDO ni detalles de persistencia.
 */
abstract class Publicacion
{
    protected ?int $id_publicacion;
    protected string $titulo;
    protected string $descripcion;
    protected float $precio;
    protected string $tipo;
    protected ?string $modalidad;
    protected ?string $nivel_experiencia;
    protected ?int $duracion_horas;
    protected ?int $cupos;
    protected ?string $disponibilidad;
    protected string $estado;
    protected ?string $imagen;
    protected ?string $eliminado_en;
    protected ?string $fecha_creacion;
    protected ?string $fecha_actualizacion;
    protected int $id_usuario;
    protected int $id_categoria;

    /**
     * @param array $datos Fila de publicaciones o datos validados del formulario.
     */
    public function __construct(array $datos)
    {
        $this->id_publicacion = isset($datos['id_publicacion']) ? (int) $datos['id_publicacion'] : null;
        $this->titulo = trim((string) ($datos['titulo'] ?? ''));
        $this->descripcion = trim((string) ($datos['descripcion'] ?? ''));
        $this->setPrecio((float) ($datos['precio'] ?? 0));
        $this->tipo = (string) ($datos['tipo'] ?? '');
        $this->modalidad = self::textoOpcional($datos['modalidad'] ?? null);
        $this->nivel_experiencia = self::textoOpcional($datos['nivel_experiencia'] ?? null);
        $this->duracion_horas = self::enteroOpcional($datos['duracion_horas'] ?? null);
        $this->cupos = self::enteroOpcional($datos['cupos'] ?? null);
        $this->disponibilidad = self::textoOpcional($datos['disponibilidad'] ?? null);
        $this->estado = (string) ($datos['estado'] ?? 'Activo');
        $this->imagen = self::textoOpcional($datos['imagen'] ?? null);
        $this->eliminado_en = self::textoOpcional($datos['eliminado_en'] ?? null);
        $this->fecha_creacion = self::textoOpcional($datos['fecha_creacion'] ?? null);
        $this->fecha_actualizacion = self::textoOpcional($datos['fecha_actualizacion'] ?? null);
        $this->id_usuario = (int) ($datos['id_usuario'] ?? 0);
        $this->id_categoria = (int) ($datos['id_categoria'] ?? 0);
    }

    /** Devuelve el identificador de la publicacion, si ya fue persistida. */
    public function getId(): ?int { return $this->id_publicacion; }

    /** Devuelve el titulo visible de la publicacion. */
    public function getTitulo(): string { return $this->titulo; }

    /** Devuelve el tipo discriminador usado por la base de datos. */
    public function getTipo(): string { return $this->tipo; }

    /** Devuelve el usuario propietario de la publicacion. */
    public function getIdUsuario(): int { return $this->id_usuario; }

    /** Devuelve el estado actual de publicacion. */
    public function getEstado(): string { return $this->estado; }

    /** Actualiza el precio sin permitir valores negativos. */
    public function setPrecio(float $precio): void
    {
        if ($precio < 0) {
            throw new InvalidArgumentException('El precio no puede ser negativo.');
        }
        $this->precio = $precio;
    }

    /** Actualiza el estado respetando los valores permitidos por el esquema. */
    public function setEstado(string $estado): void
    {
        if (!in_array($estado, ['Activo', 'Pausado', 'Inactivo', 'Eliminado'], true)) {
            throw new InvalidArgumentException('Estado no valido.');
        }
        $this->estado = $estado;
    }

    /** Define la fecha de eliminacion logica cuando corresponde. */
    public function setEliminadoEn(?string $eliminado_en): void
    {
        $this->eliminado_en = self::textoOpcional($eliminado_en);
    }

    /**
     * Exporta en formato compatible con las vistas procedurales existentes.
     */
    public function toArray(): array
    {
        return [
            'id_publicacion' => $this->id_publicacion,
            'titulo' => $this->titulo,
            'descripcion' => $this->descripcion,
            'precio' => $this->precio,
            'tipo' => $this->tipo,
            'modalidad' => $this->modalidad,
            'nivel_experiencia' => $this->nivel_experiencia,
            'duracion_horas' => $this->duracion_horas,
            'cupos' => $this->cupos,
            'disponibilidad' => $this->disponibilidad,
            'tipo_servicio' => null,
            'estado' => $this->estado,
            'imagen' => $this->imagen,
            'eliminado_en' => $this->eliminado_en,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion,
            'id_usuario' => $this->id_usuario,
            'id_categoria' => $this->id_categoria,
        ];
    }

    protected static function textoOpcional(mixed $valor): ?string
    {
        $texto = trim((string) ($valor ?? ''));
        return $texto === '' ? null : $texto;
    }

    protected static function enteroOpcional(mixed $valor): ?int
    {
        $texto = trim((string) ($valor ?? ''));
        return $texto === '' ? null : (int) $texto;
    }
}

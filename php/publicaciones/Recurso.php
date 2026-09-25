<?php

require_once __DIR__ . '/ElementoCurso.php';

/**
 * Responsabilidad: Representa un recurso de una unidad de curso.
 * Extiende ElementoCurso y añade campos específicos: tipo, url, archivo.
 * Encapsula la lógica de presentación (iconos, embed, visualización).
 * No tiene dependencias de PDO ni de la capa de acceso a datos.
 */
class Recurso extends ElementoCurso
{
    //Tipos válidos
    public const TIPOS = [
        'Archivo',
        'Foro',
        'Entrega de Tareas',
        'Video',
        'PDF',
        'Imagen',
        'Enlace',
    ];

    private int    $id_recurso;
    private int    $id_unidad;
    private string $tipo;
    private ?string $url;
    private ?string $archivo;

    public function __construct(
        int     $id_recurso,
        int     $id_unidad,
        string  $titulo,
        string  $tipo,
        ?string $url,
        ?string $archivo,
        ?string $descripcion,
        int     $orden
    ) {
        parent::__construct($id_recurso, $titulo, $descripcion, $orden);
        $this->id_recurso = $id_recurso;
        $this->id_unidad  = $id_unidad;
        $this->tipo       = $tipo;
        $this->url        = $url;
        $this->archivo    = $archivo;
    }

    //Getters propios

    public function getId(): int       { return $this->id_recurso; }
    public function getIdUnidad(): int  { return $this->id_unidad; }
    public function getTipo(): string   { return $this->tipo; }
    public function getUrl(): ?string   { return $this->url; }
    public function getArchivo(): ?string { return $this->archivo; }

    //Presentación / utilidades

    /**
     * Icono corto de texto para el tipo de recurso.
     */
    public function getIcono(): string
    {
        return [
            'Archivo'           => 'DOC',
            'Foro'              => 'FORO',
            'Entrega de Tareas' => 'TAREA',
            'Video'             => 'VID',
            'PDF'               => 'PDF',
            'Imagen'            => 'IMG',
            'Enlace'            => 'URL',
        ][$this->tipo] ?? 'REC';
    }

    /**
     * Emoji representativo del tipo de recurso.
     */
    public function getEmojiIcono(): string
    {
        return match ($this->tipo) {
            'Video'             => '🎥',
            'PDF'               => '📄',
            'Entrega de Tareas' => '📝',
            'Foro'              => '💬',
            'Imagen'            => '🖼️',
            'Enlace'            => '🔗',
            default             => '📥',
        };
    }

    /**
     * Devuelve la URL de embed para videos (YouTube, Vimeo, Dailymotion, Loom).
     * Retorna null si la URL no es de un proveedor soportado.
     */
    public function getVideoEmbedUrl(): ?string
    {
        $url = $this->url;
        if (!$url) return null;
        $url = trim($url);

        // YouTube
        if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|v/|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})#i', $url, $m)) {
            return 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        }
        // Vimeo
        if (preg_match('#(?:vimeo\.com/(?:video/)?|player\.vimeo\.com/video/)([0-9]+)#i', $url, $m)) {
            return 'https://player.vimeo.com/video/' . $m[1];
        }
        // Dailymotion
        if (preg_match('#(?:dailymotion\.com/(?:video/|embed/video/)|dai\.ly/)([a-zA-Z0-9]+)#i', $url, $m)) {
            return 'https://www.dailymotion.com/embed/video/' . $m[1];
        }
        // Loom
        if (preg_match('#(?:loom\.com/share/|loom\.com/embed/)([a-zA-Z0-9]+)#i', $url, $m)) {
            return 'https://www.loom.com/embed/' . $m[1];
        }

        return null;
    }

    /**
     * Indica si el recurso puede visualizarse directamente en el navegador.
     */
    public function esVisualizableNativamente(): bool
    {
        if ($this->tipo === 'PDF' || $this->tipo === 'Imagen') {
            return true;
        }
        if ($this->archivo) {
            $ext = strtolower(pathinfo($this->archivo, PATHINFO_EXTENSION));
            return in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'txt'], true);
        }
        return false;
    }

    //Construcción / exportación

    public static function fromArray(array $row): static
    {
        return new static(
            (int) $row['id_recurso'],
            (int) $row['id_unidad'],
            $row['titulo']      ?? '',
            $row['tipo']        ?? 'Enlace',
            $row['url']         ?? null,
            $row['archivo']     ?? null,
            $row['descripcion'] ?? null,
            (int) ($row['orden'] ?? 0),
        );
    }

    /**
     * Exporta el objeto como array asociativo.
     * Fusiona los campos comunes (parent) con los propios de Recurso.
     */
    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'id_recurso' => $this->id_recurso,
            'id_unidad'  => $this->id_unidad,
            'tipo'       => $this->tipo,
            'url'        => $this->url,
            'archivo'    => $this->archivo,
        ]);
    }
}

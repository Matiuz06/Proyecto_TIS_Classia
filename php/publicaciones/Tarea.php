<?php

/**
 * Responsabilidad: Configuracion pedagogica de un recurso "Entrega de Tareas".
 * Objeto de dominio puro: no conoce PDO ni detalles de persistencia.
 */
class Tarea
{
    public const REQUISITOS = ['cualquiera', 'todos'];
    public const TIPOS_CALIFICACION = ['numerica', 'aprobado_reprobado', 'sin_calificacion'];

    public function __construct(
        private ?int $id_tarea,
        private int $id_recurso,
        private ?string $fecha_disponible,
        private ?string $fecha_limite,
        private ?string $fecha_cierre,
        private bool $permite_entrega_tardia,
        private bool $permite_archivos,
        private bool $permite_texto,
        private bool $permite_enlace,
        private int $max_archivos,
        private int $max_tamano_mb,
        private array $formatos_permitidos,
        private string $requisito_entrega,
        private ?float $puntaje_maximo,
        private string $tipo_calificacion,
        private bool $permite_feedback_archivo,
        private ?string $fecha_creacion = null,
        private ?string $fecha_actualizacion = null
    ) {
    }

    public static function fromArray(array $row): self
    {
        $formatos = $row['formatos_permitidos'] ?? [];
        if (is_string($formatos)) {
            $formatos = json_decode($formatos, true) ?: [];
        }

        return new self(
            isset($row['id_tarea']) ? (int)$row['id_tarea'] : null,
            (int)$row['id_recurso'],
            $row['fecha_disponible'] ?? null,
            $row['fecha_limite'] ?? null,
            $row['fecha_cierre'] ?? null,
            (bool)($row['permite_entrega_tardia'] ?? false),
            (bool)($row['permite_archivos'] ?? true),
            (bool)($row['permite_texto'] ?? false),
            (bool)($row['permite_enlace'] ?? false),
            max(1, (int)($row['max_archivos'] ?? 1)),
            max(1, (int)($row['max_tamano_mb'] ?? 20)),
            array_values(array_unique(array_map('strtolower', $formatos))),
            in_array(($row['requisito_entrega'] ?? 'cualquiera'), self::REQUISITOS, true) ? $row['requisito_entrega'] : 'cualquiera',
            isset($row['puntaje_maximo']) ? (float)$row['puntaje_maximo'] : null,
            in_array(($row['tipo_calificacion'] ?? 'numerica'), self::TIPOS_CALIFICACION, true) ? $row['tipo_calificacion'] : 'numerica',
            (bool)($row['permite_feedback_archivo'] ?? false),
            $row['fecha_creacion'] ?? null,
            $row['fecha_actualizacion'] ?? null
        );
    }

    public static function desdeFormulario(int $id_recurso, array $post): self
    {
        $tipos = $post['tipos_entrega'] ?? [];
        $formatos = self::formatosDesdeGrupos($post['formatos_grupos'] ?? []);
        $tipoCalificacion = $post['tipo_calificacion'] ?? 'numerica';
        $puntaje = $tipoCalificacion === 'numerica' ? (float)($post['puntaje_maximo'] ?? 0) : null;

        return new self(
            isset($post['id_tarea']) && (int)$post['id_tarea'] > 0 ? (int)$post['id_tarea'] : null,
            $id_recurso,
            self::normalizarFecha($post['fecha_disponible'] ?? null),
            (string)self::normalizarFecha($post['fecha_limite'] ?? ''),
            self::normalizarFecha($post['fecha_cierre'] ?? null),
            !empty($post['permite_entrega_tardia']),
            in_array('archivos', $tipos, true),
            in_array('texto', $tipos, true),
            in_array('enlace', $tipos, true),
            max(1, (int)($post['max_archivos'] ?? 1)),
            max(1, (int)($post['max_tamano_mb'] ?? 20)),
            $formatos,
            ($post['requisito_entrega'] ?? '') === 'todos' ? 'todos' : 'cualquiera',
            $puntaje,
            in_array($tipoCalificacion, self::TIPOS_CALIFICACION, true) ? $tipoCalificacion : 'numerica',
            !empty($post['permite_feedback_archivo'])
        );
    }

    public function validar(): array
    {
        $errores = [];
        if ($this->fecha_limite === null || $this->fecha_limite === '') $errores[] = 'La fecha limite es obligatoria.';
        if (!$this->permite_archivos && !$this->permite_texto && !$this->permite_enlace) $errores[] = 'Selecciona al menos un tipo de entrega.';
        if ($this->permite_archivos && empty($this->formatos_permitidos)) $errores[] = 'Selecciona al menos un grupo de formatos permitidos.';
        if ($this->tipo_calificacion === 'numerica' && (!$this->puntaje_maximo || $this->puntaje_maximo <= 0)) $errores[] = 'El puntaje maximo debe ser mayor que cero.';
        if ($this->tipo_calificacion !== 'numerica' && $this->puntaje_maximo !== null) $errores[] = 'Este tipo de calificacion no acepta nota numerica.';

        try {
            $disponible = $this->fecha_disponible ? new DateTimeImmutable($this->fecha_disponible) : null;
            $limite = $this->fecha_limite ? new DateTimeImmutable($this->fecha_limite) : null;
            $cierre = $this->fecha_cierre ? new DateTimeImmutable($this->fecha_cierre) : null;
        } catch (Exception) {
            return ['Las fechas ingresadas no son validas.'];
        }
        if ($disponible && $limite && $disponible > $limite) $errores[] = 'La fecha disponible no puede ser posterior a la fecha limite.';
        if ($limite && $cierre && $limite > $cierre) $errores[] = 'La fecha de cierre no puede ser anterior a la fecha limite.';

        return $errores;
    }

    public function estaDisponible(DateTimeImmutable $ahora): bool
    {
        return !$this->fecha_disponible || $ahora >= new DateTimeImmutable($this->fecha_disponible);
    }

    public function estaCerrada(DateTimeImmutable $ahora): bool
    {
        return $this->fecha_cierre !== null && $ahora > new DateTimeImmutable($this->fecha_cierre);
    }

    public function puedeEntregar(DateTimeImmutable $ahora): bool
    {
        if (!$this->estaDisponible($ahora) || $this->estaCerrada($ahora)) return false;
        if ($this->fecha_limite === null || $this->fecha_limite === '') return true;
        if ($ahora <= new DateTimeImmutable($this->fecha_limite)) return true;
        return $this->permite_entrega_tardia;
    }

    public function esEntregaTardia(DateTimeImmutable $fechaEntrega): bool
    {
        if ($this->fecha_limite === null || $this->fecha_limite === '') return false;
        return $fechaEntrega > new DateTimeImmutable($this->fecha_limite);
    }

    public function permiteTipoEntrega(string $tipo): bool
    {
        return match ($tipo) {
            'archivos' => $this->permite_archivos,
            'texto' => $this->permite_texto,
            'enlace' => $this->permite_enlace,
            default => false,
        };
    }

    public function requiereTodosLosTipos(): bool
    {
        return $this->requisito_entrega === 'todos';
    }

    public function tiposHabilitados(): array
    {
        return array_values(array_filter([
            $this->permite_archivos ? 'archivos' : null,
            $this->permite_texto ? 'texto' : null,
            $this->permite_enlace ? 'enlace' : null,
        ]));
    }

    public static function gruposFormatos(): array
    {
        return [
            'documentos' => ['pdf', 'doc', 'docx', 'odt', 'txt'],
            'presentaciones' => ['ppt', 'pptx', 'odp'],
            'hojas' => ['xls', 'xlsx', 'ods', 'csv'],
            'imagenes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
            'comprimidos' => ['zip', 'rar', '7z'],
            'programacion' => ['txt', 'json', 'xml', 'csv', 'zip'],
            'fabricacion' => ['stl', 'obj', '3mf'],
        ];
    }

    public static function formatosDesdeGrupos(array $grupos): array
    {
        $permitidos = [];
        foreach ($grupos as $grupo) {
            $permitidos = array_merge($permitidos, self::gruposFormatos()[$grupo] ?? []);
        }
        return array_values(array_unique($permitidos));
    }

    private static function normalizarFecha(?string $valor): ?string
    {
        $valor = trim((string)$valor);
        if ($valor === '') return null;
        return str_replace('T', ' ', $valor) . (strlen($valor) === 16 ? ':00' : '');
    }

    public function toArray(): array
    {
        return [
            'id_tarea' => $this->id_tarea,
            'id_recurso' => $this->id_recurso,
            'fecha_disponible' => $this->fecha_disponible,
            'fecha_limite' => $this->fecha_limite,
            'fecha_cierre' => $this->fecha_cierre,
            'permite_entrega_tardia' => $this->permite_entrega_tardia,
            'permite_archivos' => $this->permite_archivos,
            'permite_texto' => $this->permite_texto,
            'permite_enlace' => $this->permite_enlace,
            'max_archivos' => $this->max_archivos,
            'max_tamano_mb' => $this->max_tamano_mb,
            'formatos_permitidos' => $this->formatos_permitidos,
            'requisito_entrega' => $this->requisito_entrega,
            'puntaje_maximo' => $this->puntaje_maximo,
            'tipo_calificacion' => $this->tipo_calificacion,
            'permite_feedback_archivo' => $this->permite_feedback_archivo,
            'fecha_creacion' => $this->fecha_creacion,
            'fecha_actualizacion' => $this->fecha_actualizacion,
        ];
    }

    public function getId(): ?int { return $this->id_tarea; }
    public function getIdRecurso(): int { return $this->id_recurso; }
    public function getFechaLimite(): ?string { return $this->fecha_limite; }
    public function getFechaCierre(): ?string { return $this->fecha_cierre; }
    public function permiteArchivos(): bool { return $this->permite_archivos; }
    public function permiteTexto(): bool { return $this->permite_texto; }
    public function permiteEnlace(): bool { return $this->permite_enlace; }
    public function getMaxArchivos(): int { return $this->max_archivos; }
    public function getMaxTamanoMb(): int { return $this->max_tamano_mb; }
    public function getFormatosPermitidos(): array { return $this->formatos_permitidos; }
    public function getTipoCalificacion(): string { return $this->tipo_calificacion; }
    public function getPuntajeMaximo(): ?float { return $this->puntaje_maximo; }
    public function permiteFeedbackArchivo(): bool { return $this->permite_feedback_archivo; }
}

<?php

/**
 * Responsabilidad: Representa la entrega de un estudiante para una tarea.
 * Objeto de dominio puro.
 */
class Entrega
{
    public const ESTADOS = ['borrador', 'entregada'];

    public function __construct(
        private ?int $id_entrega,
        private int $id_tarea,
        private int $id_usuario,
        private ?string $texto_entrega,
        private ?string $enlace_entrega,
        private ?string $comentario_entrega,
        private ?string $fecha_entrega,
        private ?string $fecha_actualizacion,
        private string $estado_entrega,
        private ?string $calificacion,
        private ?string $feedback_docente,
        private ?string $archivo_feedback,
        private ?string $fecha_calificacion,
        private ?int $id_docente_calificador
    ) {
    }

    public static function fromArray(array $row): self
    {
        return new self(
            isset($row['id_entrega']) ? (int)$row['id_entrega'] : null,
            (int)($row['id_tarea'] ?? 0),
            (int)$row['id_usuario'],
            $row['texto_entrega'] ?? null,
            $row['enlace_entrega'] ?? null,
            $row['comentario_entrega'] ?? null,
            $row['fecha_entrega'] ?? null,
            $row['fecha_actualizacion'] ?? null,
            $row['estado_entrega'] ?? strtolower((string)($row['estado'] ?? 'entregada')),
            isset($row['calificacion']) ? (string)$row['calificacion'] : null,
            $row['feedback_docente'] ?? null,
            $row['archivo_feedback'] ?? null,
            $row['fecha_calificacion'] ?? null,
            isset($row['id_docente_calificador']) ? (int)$row['id_docente_calificador'] : null
        );
    }

    public function tieneTexto(): bool
    {
        return trim((string)$this->texto_entrega) !== '';
    }

    public function tieneEnlace(): bool
    {
        return trim((string)$this->enlace_entrega) !== '';
    }

    public function toArray(): array
    {
        return [
            'id_entrega' => $this->id_entrega,
            'id_tarea' => $this->id_tarea,
            'id_usuario' => $this->id_usuario,
            'texto_entrega' => $this->texto_entrega,
            'enlace_entrega' => $this->enlace_entrega,
            'comentario_entrega' => $this->comentario_entrega,
            'fecha_entrega' => $this->fecha_entrega,
            'fecha_actualizacion' => $this->fecha_actualizacion,
            'estado_entrega' => $this->estado_entrega,
            'calificacion' => $this->calificacion,
            'feedback_docente' => $this->feedback_docente,
            'archivo_feedback' => $this->archivo_feedback,
            'fecha_calificacion' => $this->fecha_calificacion,
            'id_docente_calificador' => $this->id_docente_calificador,
        ];
    }

    public function getId(): ?int { return $this->id_entrega; }
    public function getIdTarea(): int { return $this->id_tarea; }
    public function getIdUsuario(): int { return $this->id_usuario; }
    public function getTexto(): ?string { return $this->texto_entrega; }
    public function getEnlace(): ?string { return $this->enlace_entrega; }
    public function getFechaEntrega(): ?string { return $this->fecha_entrega; }
}

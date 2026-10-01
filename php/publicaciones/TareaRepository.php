<?php

require_once __DIR__ . '/Tarea.php';

/**
 * Responsabilidad: Persistencia de curso_tareas.
 */
class TareaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function crear(Tarea $tarea): int
    {
        $datos = $this->params($tarea);
        $returning = $this->driver() === 'pgsql';
        $sql = "INSERT INTO curso_tareas
            (id_recurso, fecha_disponible, fecha_limite, fecha_cierre, permite_entrega_tardia,
             permite_archivos, permite_texto, permite_enlace, max_archivos, max_tamano_mb,
             formatos_permitidos, requisito_entrega, puntaje_maximo, tipo_calificacion, permite_feedback_archivo)
            VALUES
            (:id_recurso, :fecha_disponible, :fecha_limite, :fecha_cierre, :permite_entrega_tardia,
             :permite_archivos, :permite_texto, :permite_enlace, :max_archivos, :max_tamano_mb,
             :formatos_permitidos, :requisito_entrega, :puntaje_maximo, :tipo_calificacion, :permite_feedback_archivo)";
        if ($returning) $sql .= ' RETURNING id_tarea';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($datos);
        return $returning ? (int)$stmt->fetchColumn() : (int)$this->pdo->lastInsertId();
    }

    public function actualizar(Tarea $tarea): bool
    {
        $datos = $this->params($tarea);
        $datos['id_tarea'] = $tarea->getId();
        $stmt = $this->pdo->prepare("
            UPDATE curso_tareas SET
                fecha_disponible = :fecha_disponible,
                fecha_limite = :fecha_limite,
                fecha_cierre = :fecha_cierre,
                permite_entrega_tardia = :permite_entrega_tardia,
                permite_archivos = :permite_archivos,
                permite_texto = :permite_texto,
                permite_enlace = :permite_enlace,
                max_archivos = :max_archivos,
                max_tamano_mb = :max_tamano_mb,
                formatos_permitidos = :formatos_permitidos,
                requisito_entrega = :requisito_entrega,
                puntaje_maximo = :puntaje_maximo,
                tipo_calificacion = :tipo_calificacion,
                permite_feedback_archivo = :permite_feedback_archivo,
                fecha_actualizacion = CURRENT_TIMESTAMP
            WHERE id_tarea = :id_tarea AND id_recurso = :id_recurso
        ");
        $stmt->execute($datos);
        return $stmt->rowCount() > 0;
    }

    public function guardar(Tarea $tarea): int
    {
        if ($tarea->getId()) {
            $this->actualizar($tarea);
            return $tarea->getId();
        }
        $actual = $this->buscarPorRecurso($tarea->getIdRecurso());
        if ($actual) {
            $datos = $tarea->toArray();
            $datos['id_tarea'] = $actual->getId();
            $this->actualizar(Tarea::fromArray($datos));
            return (int)$actual->getId();
        }
        return $this->crear($tarea);
    }

    public function buscarPorId(int $id_tarea): ?Tarea
    {
        $stmt = $this->pdo->prepare('SELECT * FROM curso_tareas WHERE id_tarea = :id LIMIT 1');
        $stmt->execute(['id' => $id_tarea]);
        $row = $stmt->fetch();
        return $row ? Tarea::fromArray($row) : null;
    }

    public function buscarPorRecurso(int $id_recurso): ?Tarea
    {
        $stmt = $this->pdo->prepare('SELECT * FROM curso_tareas WHERE id_recurso = :id LIMIT 1');
        $stmt->execute(['id' => $id_recurso]);
        $row = $stmt->fetch();
        return $row ? Tarea::fromArray($row) : null;
    }

    public function buscarPorRecursoYCurso(int $id_recurso, int $id_publicacion): ?Tarea
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*
            FROM curso_tareas t
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE t.id_recurso = :r AND m.id_publicacion = :p AND r.tipo = 'Entrega de Tareas'
            LIMIT 1
        ");
        $stmt->execute(['r' => $id_recurso, 'p' => $id_publicacion]);
        $row = $stmt->fetch();
        return $row ? Tarea::fromArray($row) : null;
    }

    public function listarPorCurso(int $id_publicacion): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*
            FROM curso_tareas t
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE m.id_publicacion = :id
        ");
        $stmt->execute(['id' => $id_publicacion]);
        $tareas = [];
        foreach ($stmt->fetchAll() as $row) {
            $tareas[(int)$row['id_recurso']] = Tarea::fromArray($row);
        }
        return $tareas;
    }

    public function obtenerInfoDocente(int $id_tarea): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.*, r.titulo AS tarea_titulo, r.descripcion, r.id_recurso,
                   cu.titulo AS unidad_titulo, cm.titulo AS modulo_titulo,
                   p.id_publicacion, p.titulo AS curso_titulo, p.id_usuario AS docente_id
            FROM curso_tareas t
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades cu ON cu.id_unidad = r.id_unidad
            JOIN curso_modulos cm ON cm.id_modulo = cu.id_modulo
            JOIN publicaciones p ON p.id_publicacion = cm.id_publicacion
            WHERE t.id_tarea = :id AND r.tipo = 'Entrega de Tareas'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id_tarea]);
        return $stmt->fetch() ?: null;
    }

    public function eliminarPorRecurso(int $id_recurso): void
    {
        $stmt = $this->pdo->prepare('DELETE FROM curso_tareas WHERE id_recurso = :id');
        $stmt->execute(['id' => $id_recurso]);
    }

    public function asegurarParaRecurso(array $recurso): Tarea
    {
        $actual = $this->buscarPorRecurso((int)$recurso['id_recurso']);
        if ($actual) return $actual;

        $tarea = Tarea::fromArray([
            'id_recurso' => (int)$recurso['id_recurso'],
            'fecha_limite' => null,
            'permite_entrega_tardia' => true,
            'permite_archivos' => true,
            'permite_texto' => false,
            'permite_enlace' => false,
            'max_archivos' => 1,
            'max_tamano_mb' => 20,
            'formatos_permitidos' => ['pdf', 'doc', 'docx', 'zip', 'txt'],
            'requisito_entrega' => 'cualquiera',
            'puntaje_maximo' => 10,
            'tipo_calificacion' => 'numerica',
            'permite_feedback_archivo' => false,
        ]);
        $id = $this->crear($tarea);
        return $this->buscarPorId($id) ?: $tarea;
    }

    private function params(Tarea $tarea): array
    {
        $d = $tarea->toArray();
        return [
            'id_recurso' => $d['id_recurso'],
            'fecha_disponible' => $d['fecha_disponible'],
            'fecha_limite' => $d['fecha_limite'],
            'fecha_cierre' => $d['fecha_cierre'],
            'permite_entrega_tardia' => (int)$d['permite_entrega_tardia'],
            'permite_archivos' => (int)$d['permite_archivos'],
            'permite_texto' => (int)$d['permite_texto'],
            'permite_enlace' => (int)$d['permite_enlace'],
            'max_archivos' => $d['max_archivos'],
            'max_tamano_mb' => $d['max_tamano_mb'],
            'formatos_permitidos' => json_encode($d['formatos_permitidos'], JSON_UNESCAPED_UNICODE),
            'requisito_entrega' => $d['requisito_entrega'],
            'puntaje_maximo' => $d['puntaje_maximo'],
            'tipo_calificacion' => $d['tipo_calificacion'],
            'permite_feedback_archivo' => (int)$d['permite_feedback_archivo'],
        ];
    }

    private function driver(): string
    {
        return (string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}

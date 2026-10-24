<?php

/**
 * Responsabilidad: calcula progreso academico de cursos y emite certificados.
 */
class CursoProgresoRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function obtenerProgresoAlumnoCurso(int $idAlumno, int $idCurso): array
    {
        $minimo = $this->obtenerPorcentajeMinimo($idCurso);

        $stmt = $this->pdo->prepare("
            SELECT
                SUM(CASE WHEN r.tipo <> 'Entrega de Tareas' THEN 1 ELSE 0 END) AS recursos_total,
                COUNT(t.id_tarea) AS tareas_total
            FROM curso_recursos r
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            LEFT JOIN curso_tareas t ON t.id_recurso = r.id_recurso
            WHERE m.id_publicacion = :curso
        ");
        $stmt->execute(['curso' => $idCurso]);
        $totales = $stmt->fetch() ?: [];

        $recursosTotal = (int)($totales['recursos_total'] ?? 0);
        $tareasTotal = (int)($totales['tareas_total'] ?? 0);

        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM curso_recursos_completados crc
            JOIN curso_recursos r ON r.id_recurso = crc.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE crc.id_usuario = :usuario
              AND m.id_publicacion = :curso
              AND r.tipo <> 'Entrega de Tareas'
        ");
        $stmt->execute(['usuario' => $idAlumno, 'curso' => $idCurso]);
        $recursosCompletados = (int)$stmt->fetchColumn();

        $stmt = $this->pdo->prepare("
            SELECT t.id_tarea, t.puntaje_maximo, t.tipo_calificacion,
                   e.id_entrega, e.estado_entrega, e.calificacion, e.estado, e.fecha_calificacion
            FROM curso_tareas t
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            LEFT JOIN curso_entregas e
                ON e.id_tarea = t.id_tarea AND e.id_usuario = :usuario
            WHERE m.id_publicacion = :curso
            ORDER BY t.id_tarea
        ");
        $stmt->execute(['usuario' => $idAlumno, 'curso' => $idCurso]);
        $tareas = $stmt->fetchAll() ?: [];

        $tareasCompletadas = 0;
        $tareasEvaluables = 0;
        $tareasEvaluadas = 0;
        $puntajeObtenido = 0.0;
        $puntajeMaximo = 0.0;
        foreach ($tareas as $tarea) {
            $entregada = !empty($tarea['id_entrega'])
                && ($tarea['estado_entrega'] ?? 'entregada') === 'entregada';
            if ($entregada) {
                $tareasCompletadas++;
            }

            if (($tarea['tipo_calificacion'] ?? 'numerica') === 'sin_calificacion') {
                continue;
            }

            $tareasEvaluables++;
            $evaluada = $entregada
                && (!empty($tarea['fecha_calificacion']) || ($tarea['estado'] ?? '') === 'Calificada');
            if (!$evaluada) {
                continue;
            }

            $tareasEvaluadas++;
            if (($tarea['tipo_calificacion'] ?? 'numerica') === 'aprobado_reprobado') {
                $puntajeMaximo += 1.0;
                $puntajeObtenido += strtolower(trim((string)($tarea['calificacion'] ?? ''))) === 'aprobado' ? 1.0 : 0.0;
                continue;
            }

            $maximo = (float)($tarea['puntaje_maximo'] ?? 0);
            if ($maximo <= 0) {
                continue;
            }
            $puntajeMaximo += $maximo;
            $nota = is_numeric($tarea['calificacion'] ?? null) ? (float)$tarea['calificacion'] : 0.0;
            $puntajeObtenido += max(0.0, min($maximo, $nota));
        }

        $total = $recursosTotal + $tareasTotal;
        $completados = $recursosCompletados + $tareasCompletadas;
        $porcentaje = $total > 0 ? round(($completados / $total) * 100, 2) : 0.0;
        $completado = $total > 0
            && $recursosCompletados >= $recursosTotal
            && $tareasCompletadas >= $tareasTotal;
        $pendienteEvaluacion = $completado && $tareasEvaluadas < $tareasEvaluables;
        $porcentajeAcademico = $tareasEvaluables > 0
            ? ($puntajeMaximo > 0 ? round(($puntajeObtenido / $puntajeMaximo) * 100, 2) : 0.0)
            : ($completado ? 100.0 : $porcentaje);
        $aprobado = $completado
            && !$pendienteEvaluacion
            && $porcentajeAcademico >= $minimo;

        if ($aprobado) {
            $estado = 'Aprobado';
        } elseif ($pendienteEvaluacion) {
            $estado = 'Completado, pendiente de evaluación';
        } elseif ($completado) {
            $estado = 'Completado, no aprobado';
        } else {
            $estado = 'En curso';
        }

        return [
            'total' => $total,
            'completados' => $completados,
            'porcentaje' => $porcentaje,
            'porcentaje_academico' => $porcentajeAcademico,
            'porcentaje_minimo' => $minimo,
            'estado' => $estado,
            'completado' => $completado,
            'pendiente_evaluacion' => $pendienteEvaluacion,
            'aprobado' => $aprobado,
            'recursos_total' => $recursosTotal,
            'recursos_completados' => $recursosCompletados,
            'tareas_total' => $tareasTotal,
            'tareas_completadas' => $tareasCompletadas,
            'tareas_evaluables' => $tareasEvaluables,
            'tareas_evaluadas' => $tareasEvaluadas,
        ];
    }

    public function obtenerProgresoAlumnoCursos(int $idAlumno, array $idsCursos): array
    {
        $idsCursos = array_values(array_unique(array_filter(array_map('intval', $idsCursos), static fn(int $id): bool => $id > 0)));
        if (!$idsCursos) {
            return [];
        }

        $placeholders = [];
        $params = [
            'usuario_recursos' => $idAlumno,
            'usuario_entregas' => $idAlumno,
        ];
        foreach ($idsCursos as $indice => $idCurso) {
            $nombre = 'curso_' . $indice;
            $placeholders[] = ':' . $nombre;
            $params[$nombre] = $idCurso;
        }

        $stmt = $this->pdo->prepare(
            "SELECT m.id_publicacion AS id_curso,
                    SUM(CASE WHEN r.tipo <> 'Entrega de Tareas' THEN 1 ELSE 0 END) AS recursos_total,
                    SUM(CASE WHEN r.tipo <> 'Entrega de Tareas' AND crc.id_completado IS NOT NULL THEN 1 ELSE 0 END) AS recursos_completados,
                    COUNT(t.id_tarea) AS tareas_total,
                    SUM(CASE WHEN t.id_tarea IS NOT NULL
                                  AND e.id_entrega IS NOT NULL
                                  AND COALESCE(e.estado_entrega, 'entregada') = 'entregada'
                             THEN 1 ELSE 0 END) AS tareas_completadas
             FROM curso_modulos m
             INNER JOIN curso_unidades u ON u.id_modulo = m.id_modulo
             INNER JOIN curso_recursos r ON r.id_unidad = u.id_unidad
             LEFT JOIN curso_tareas t ON t.id_recurso = r.id_recurso
             LEFT JOIN curso_recursos_completados crc
                    ON crc.id_recurso = r.id_recurso
                   AND crc.id_usuario = :usuario_recursos
             LEFT JOIN curso_entregas e
                    ON e.id_tarea = t.id_tarea
                   AND e.id_usuario = :usuario_entregas
             WHERE m.id_publicacion IN (" . implode(', ', $placeholders) . ")
             GROUP BY m.id_publicacion"
        );
        $stmt->execute($params);

        $progresos = [];
        foreach ($stmt->fetchAll() as $fila) {
            $recursosTotal = (int)($fila['recursos_total'] ?? 0);
            $recursosCompletados = (int)($fila['recursos_completados'] ?? 0);
            $tareasTotal = (int)($fila['tareas_total'] ?? 0);
            $tareasCompletadas = (int)($fila['tareas_completadas'] ?? 0);
            $total = $recursosTotal + $tareasTotal;
            $completados = $recursosCompletados + $tareasCompletadas;

            $progresos[(int)$fila['id_curso']] = [
                'porcentaje' => $total > 0 ? round(($completados / $total) * 100, 2) : 0.0,
                'completado' => $total > 0
                    && $recursosCompletados >= $recursosTotal
                    && $tareasCompletadas >= $tareasTotal,
            ];
        }

        return $progresos;
    }

    public function listarRecursosCompletadosCurso(int $idAlumno, int $idCurso): array
    {
        $stmt = $this->pdo->prepare("
            SELECT crc.id_recurso
            FROM curso_recursos_completados crc
            JOIN curso_recursos r ON r.id_recurso = crc.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE crc.id_usuario = :usuario AND m.id_publicacion = :curso
        ");
        $stmt->execute(['usuario' => $idAlumno, 'curso' => $idCurso]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN) ?: []);
    }

    public function recursoPerteneceACurso(int $idRecurso, int $idCurso): bool
    {
        $stmt = $this->pdo->prepare("
            SELECT COUNT(*)
            FROM curso_recursos r
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE r.id_recurso = :recurso
              AND m.id_publicacion = :curso
              AND r.tipo <> 'Entrega de Tareas'
        ");
        $stmt->execute(['recurso' => $idRecurso, 'curso' => $idCurso]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function marcarRecurso(int $idAlumno, int $idRecurso, bool $completado): void
    {
        if ($completado) {
            $sql = $this->driver() === 'pgsql'
                ? 'INSERT INTO curso_recursos_completados (id_usuario, id_recurso) VALUES (:usuario, :recurso) ON CONFLICT (id_usuario, id_recurso) DO NOTHING'
                : 'INSERT IGNORE INTO curso_recursos_completados (id_usuario, id_recurso) VALUES (:usuario, :recurso)';
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute(['usuario' => $idAlumno, 'recurso' => $idRecurso]);
            return;
        }

        $stmt = $this->pdo->prepare('DELETE FROM curso_recursos_completados WHERE id_usuario = :usuario AND id_recurso = :recurso');
        $stmt->execute(['usuario' => $idAlumno, 'recurso' => $idRecurso]);
    }

    public function obtenerCertificado(int $idAlumno, int $idCurso): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.*, u.nombre, u.apellido, p.titulo AS curso_titulo
            FROM certificados c
            JOIN usuarios u ON u.id_usuario = c.id_usuario
            JOIN publicaciones p ON p.id_publicacion = c.id_curso
            WHERE c.id_usuario = :usuario AND c.id_curso = :curso
            LIMIT 1
        ");
        $stmt->execute(['usuario' => $idAlumno, 'curso' => $idCurso]);
        return $stmt->fetch() ?: null;
    }

    public function emitirCertificadoSiCorresponde(int $idAlumno, int $idCurso): ?array
    {
        $progreso = $this->obtenerProgresoAlumnoCurso($idAlumno, $idCurso);
        if (empty($progreso['aprobado'])) {
            return null;
        }

        $actual = $this->obtenerCertificado($idAlumno, $idCurso);
        if ($actual) {
            return $actual;
        }

        $codigo = $this->generarCodigo();
        $returning = $this->driver() === 'pgsql';
        $sql = "INSERT INTO certificados (id_usuario, id_curso, codigo_verificacion, porcentaje_aprobacion)
                VALUES (:usuario, :curso, :codigo, :porcentaje)";
        if ($returning) {
            $sql .= ' RETURNING id_certificado';
        }
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'usuario' => $idAlumno,
            'curso' => $idCurso,
            'codigo' => $codigo,
            'porcentaje' => $progreso['porcentaje_academico'] ?? $progreso['porcentaje'],
        ]);

        return $this->obtenerCertificado($idAlumno, $idCurso);
    }

    public function obtenerCertificadoPublico(string $codigo): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT c.codigo_verificacion, c.fecha_emision, c.porcentaje_aprobacion,
                   u.nombre, u.apellido, p.titulo AS curso_titulo
            FROM certificados c
            JOIN usuarios u ON u.id_usuario = c.id_usuario
            JOIN publicaciones p ON p.id_publicacion = c.id_curso
            WHERE c.codigo_verificacion = :codigo
            LIMIT 1
        ");
        $stmt->execute(['codigo' => $codigo]);
        return $stmt->fetch() ?: null;
    }

    private function obtenerPorcentajeMinimo(int $idCurso): int
    {
        $stmt = $this->pdo->prepare('SELECT COALESCE(porcentaje_minimo_aprobacion, 70) FROM publicaciones WHERE id_publicacion = :curso AND tipo = \'Curso\' LIMIT 1');
        $stmt->execute(['curso' => $idCurso]);
        $valor = (int)($stmt->fetchColumn() ?: 70);
        return max(0, min(100, $valor));
    }

    private function generarCodigo(): string
    {
        do {
            $codigo = strtoupper(bin2hex(random_bytes(6)));
            $stmt = $this->pdo->prepare('SELECT COUNT(*) FROM certificados WHERE codigo_verificacion = :codigo');
            $stmt->execute(['codigo' => $codigo]);
        } while ((int)$stmt->fetchColumn() > 0);

        return $codigo;
    }

    private function driver(): string
    {
        return (string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }
}

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
            SELECT COUNT(DISTINCT e.id_tarea)
            FROM curso_entregas e
            JOIN curso_tareas t ON t.id_tarea = e.id_tarea
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE e.id_usuario = :usuario
              AND m.id_publicacion = :curso
              AND (e.fecha_calificacion IS NOT NULL OR e.estado = 'Calificada')
        ");
        $stmt->execute(['usuario' => $idAlumno, 'curso' => $idCurso]);
        $tareasCompletadas = (int)$stmt->fetchColumn();

        $total = $recursosTotal + $tareasTotal;
        $completados = $recursosCompletados + $tareasCompletadas;
        $porcentaje = $total > 0 ? round(($completados / $total) * 100, 2) : 0.0;
        $aprobado = $total > 0 && $porcentaje >= $minimo;

        return [
            'total' => $total,
            'completados' => $completados,
            'porcentaje' => $porcentaje,
            'porcentaje_minimo' => $minimo,
            'estado' => $aprobado ? 'Aprobado' : 'En curso',
            'aprobado' => $aprobado,
            'recursos_total' => $recursosTotal,
            'recursos_completados' => $recursosCompletados,
            'tareas_total' => $tareasTotal,
            'tareas_completadas' => $tareasCompletadas,
        ];
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
            'porcentaje' => $progreso['porcentaje'],
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

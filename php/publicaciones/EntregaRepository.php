<?php

require_once __DIR__ . '/Entrega.php';
require_once __DIR__ . '/Tarea.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';

/**
 * Responsabilidad: Persistencia de entregas, archivos, calificaciones y resumenes.
 */
class EntregaRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function guardarPresentacion(Tarea $tarea, int $id_usuario, ?string $texto, ?string $enlace, ?string $comentario, array $archivosNuevos): array
    {
        $actual = $this->buscarPorTareaYUsuario((int)$tarea->getId(), $id_usuario);
        $idActual = $actual?->getId();
        $feedbackAnterior = $actual?->toArray()['archivo_feedback'] ?? null;
        $reemplazados = [];

        $this->pdo->beginTransaction();
        try {
            if ($idActual) {
                $stmt = $this->pdo->prepare("
                    UPDATE curso_entregas
                    SET texto_entrega = :texto,
                        enlace_entrega = :enlace,
                        comentario_entrega = :comentario,
                        estado_entrega = 'entregada',
                        estado = 'Entregada',
                        calificacion = NULL,
                        feedback_docente = NULL,
                        archivo_feedback = NULL,
                        fecha_calificacion = NULL,
                        id_docente_calificador = NULL,
                        fecha_entrega = CURRENT_TIMESTAMP,
                        fecha_actualizacion = CURRENT_TIMESTAMP
                    WHERE id_entrega = :id
                ");
                $stmt->execute([
                    'texto' => $texto,
                    'enlace' => $enlace,
                    'comentario' => $comentario,
                    'id' => $idActual,
                ]);
                $idEntrega = $idActual;
            } else {
                $returning = $this->driver() === 'pgsql';
                $sql = "INSERT INTO curso_entregas
                    (id_tarea, id_recurso, id_usuario, texto_entrega, enlace_entrega, comentario_entrega, estado_entrega, estado)
                    VALUES (:tarea, :recurso, :usuario, :texto, :enlace, :comentario, 'entregada', 'Entregada')";
                if ($returning) $sql .= ' RETURNING id_entrega';
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute([
                    'tarea' => $tarea->getId(),
                    'recurso' => $tarea->getIdRecurso(),
                    'usuario' => $id_usuario,
                    'texto' => $texto,
                    'enlace' => $enlace,
                    'comentario' => $comentario,
                ]);
                $idEntrega = $returning ? (int)$stmt->fetchColumn() : (int)$this->pdo->lastInsertId();
            }

            if ($archivosNuevos) {
                $reemplazados = $this->listarArchivosIncluyeLegacy($idEntrega);
                $this->pdo->prepare('DELETE FROM curso_entrega_archivos WHERE id_entrega = :id')->execute(['id' => $idEntrega]);
                $this->pdo->prepare('UPDATE curso_entregas SET archivo_entrega = NULL WHERE id_entrega = :id')->execute(['id' => $idEntrega]);
                foreach ($archivosNuevos as $archivo) {
                    $this->agregarArchivo($idEntrega, $archivo['nombre_original'], $archivo['ruta'], $archivo['mime_type'], $archivo['extension'], (int)$archivo['tamano']);
                }
            }

            $this->pdo->commit();
            $this->eliminarFeedbackAnterior($feedbackAnterior);
            return ['id_entrega' => $idEntrega, 'archivos_reemplazados' => $reemplazados];
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $e;
        }
    }

    public function crearOActualizarEntrega(Tarea $tarea, int $id_usuario, ?string $texto, ?string $enlace, ?string $comentario): int
    {
        $actual = $this->buscarPorTareaYUsuario((int)$tarea->getId(), $id_usuario);
        if ($actual) {
            $feedbackAnterior = $actual->toArray()['archivo_feedback'] ?? null;
            $stmt = $this->pdo->prepare("
                UPDATE curso_entregas
                SET texto_entrega = :texto,
                    enlace_entrega = :enlace,
                    comentario_entrega = :comentario,
                    estado_entrega = 'entregada',
                    estado = 'Entregada',
                    calificacion = NULL,
                    feedback_docente = NULL,
                    archivo_feedback = NULL,
                    fecha_calificacion = NULL,
                    id_docente_calificador = NULL,
                    fecha_entrega = CURRENT_TIMESTAMP,
                    fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_entrega = :id
            ");
            $stmt->execute([
                'texto' => $texto,
                'enlace' => $enlace,
                'comentario' => $comentario,
                'id' => (int)$actual->getId(),
            ]);
            $this->eliminarFeedbackAnterior($feedbackAnterior);
            return (int)$actual->getId();
        }

        $returning = $this->driver() === 'pgsql';
        $sql = "INSERT INTO curso_entregas
            (id_tarea, id_recurso, id_usuario, texto_entrega, enlace_entrega, comentario_entrega, estado_entrega, estado)
            VALUES (:tarea, :recurso, :usuario, :texto, :enlace, :comentario, 'entregada', 'Entregada')";
        if ($returning) $sql .= ' RETURNING id_entrega';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'tarea' => $tarea->getId(),
            'recurso' => $tarea->getIdRecurso(),
            'usuario' => $id_usuario,
            'texto' => $texto,
            'enlace' => $enlace,
            'comentario' => $comentario,
        ]);
        return $returning ? (int)$stmt->fetchColumn() : (int)$this->pdo->lastInsertId();
    }

    public function agregarArchivo(int $id_entrega, string $nombreOriginal, string $ruta, string $mime, string $extension, int $tamano): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO curso_entrega_archivos (id_entrega, nombre_original, ruta, mime_type, extension, tamano)
            VALUES (:e, :n, :r, :m, :x, :t)
        ");
        $stmt->execute([
            'e' => $id_entrega,
            'n' => $nombreOriginal,
            'r' => $ruta,
            'm' => $mime,
            'x' => $extension,
            't' => $tamano,
        ]);
    }

    public function buscarPorTareaYUsuario(int $id_tarea, int $id_usuario): ?Entrega
    {
        $stmt = $this->pdo->prepare('SELECT * FROM curso_entregas WHERE id_tarea = :t AND id_usuario = :u LIMIT 1');
        $stmt->execute(['t' => $id_tarea, 'u' => $id_usuario]);
        $row = $stmt->fetch();
        return $row ? Entrega::fromArray($row) : null;
    }

    public function buscarPorId(int $id_entrega): ?Entrega
    {
        $stmt = $this->pdo->prepare('SELECT * FROM curso_entregas WHERE id_entrega = :id LIMIT 1');
        $stmt->execute(['id' => $id_entrega]);
        $row = $stmt->fetch();
        return $row ? Entrega::fromArray($row) : null;
    }

    public function obtenerDetalleParaRevision(int $id_entrega): ?array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*, t.id_recurso, r.titulo AS tarea_titulo, r.descripcion AS tarea_descripcion,
                   p.id_publicacion, p.titulo AS curso_titulo, p.id_usuario AS docente_id,
                   u.nombre, u.apellido, u.email,
                   t.fecha_apertura, t.fecha_cierre, t.puntaje_maximo, t.tipo_calificacion, t.permite_feedback_archivo
            FROM curso_entregas e
            JOIN curso_tareas t ON t.id_tarea = e.id_tarea
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades cu ON cu.id_unidad = r.id_unidad
            JOIN curso_modulos cm ON cm.id_modulo = cu.id_modulo
            JOIN publicaciones p ON p.id_publicacion = cm.id_publicacion
            JOIN usuarios u ON u.id_usuario = e.id_usuario
            WHERE e.id_entrega = :id
            LIMIT 1
        ");
        $stmt->execute(['id' => $id_entrega]);
        return $stmt->fetch() ?: null;
    }

    public function listarArchivos(int $id_entrega): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM curso_entrega_archivos WHERE id_entrega = :id ORDER BY id_archivo');
        $stmt->execute(['id' => $id_entrega]);
        return $stmt->fetchAll();
    }

    public function listarArchivosIncluyeLegacy(int $id_entrega): array
    {
        $archivos = $this->listarArchivos($id_entrega);
        $stmt = $this->pdo->prepare('SELECT archivo_entrega FROM curso_entregas WHERE id_entrega = :id LIMIT 1');
        $stmt->execute(['id' => $id_entrega]);
        $legacy = $stmt->fetchColumn();
        if ($legacy && !in_array($legacy, array_column($archivos, 'ruta'), true)) {
            $archivos[] = [
                'id_archivo' => null,
                'id_entrega' => $id_entrega,
                'nombre_original' => basename((string)$legacy),
                'ruta' => $legacy,
                'mime_type' => null,
                'extension' => strtolower(pathinfo((string)$legacy, PATHINFO_EXTENSION)),
                'tamano' => null,
                'legacy' => true,
            ];
        }
        return $archivos;
    }

    public function listarPorTareaConEstudiantes(int $id_tarea, int $id_publicacion): array
    {
        $stmt = $this->pdo->prepare("
            SELECT u.id_usuario, u.nombre, u.apellido, u.email,
                   e.id_entrega, e.id_tarea, e.archivo_entrega, e.texto_entrega, e.enlace_entrega, e.comentario_entrega,
                   e.fecha_entrega, e.estado, e.estado_entrega, e.calificacion, e.fecha_calificacion,
                   COUNT(a.id_archivo) AS cantidad_archivos
            FROM usuarios u
            JOIN contrataciones c ON c.id_usuario = u.id_usuario AND c.estado IN ('Completada', 'En Proceso')
            JOIN pagos pag ON pag.id_contratacion = c.id_contratacion AND pag.estado_pago = 'Aprobado'
            JOIN detalles_contratacion dc ON dc.id_contratacion = c.id_contratacion AND dc.id_publicacion = :p
            LEFT JOIN curso_entregas e ON e.id_usuario = u.id_usuario AND e.id_tarea = :t
            LEFT JOIN curso_entrega_archivos a ON a.id_entrega = e.id_entrega
            GROUP BY u.id_usuario, u.nombre, u.apellido, u.email,
                     e.id_entrega, e.id_tarea, e.archivo_entrega, e.texto_entrega, e.enlace_entrega, e.comentario_entrega,
                     e.fecha_entrega, e.estado, e.estado_entrega, e.calificacion, e.fecha_calificacion
            ORDER BY u.apellido, u.nombre
        ");
        $stmt->execute(['t' => $id_tarea, 'p' => $id_publicacion]);
        return array_map(fn($fila) => $this->enriquecerTipos($fila), $stmt->fetchAll());
    }

    public function obtenerResumenPorTarea(int $id_tarea, int $id_publicacion, ?Tarea $tarea = null): array
    {
        $filas = $this->listarPorTareaConEstudiantes($id_tarea, $id_publicacion);
        $entregadas = array_filter($filas, fn($f) => !empty($f['id_entrega']));
        return [
            'total' => count($filas),
            'entregaron' => count($entregadas),
            'sin_entregar' => count($filas) - count($entregadas),
            'calificadas' => count(array_filter($entregadas, fn($f) => !empty($f['fecha_calificacion']) || (($f['estado'] ?? '') === 'Calificada'))),
            'sin_calificar' => count(array_filter($entregadas, fn($f) => empty($f['fecha_calificacion']) && (($f['estado'] ?? '') !== 'Calificada'))),
        ];
    }

    public function obtenerResumenEntregasCurso(int $id_publicacion): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id_tarea, t.id_recurso, t.puntaje_maximo, t.tipo_calificacion,
                   r.titulo, u.id_unidad, u.titulo AS unidad_titulo,
                   m.id_modulo, m.titulo AS modulo_titulo,
                   COUNT(e.id_entrega) AS total_entregas,
                   SUM(CASE WHEN e.id_entrega IS NOT NULL AND (e.fecha_calificacion IS NOT NULL OR e.estado = 'Calificada') THEN 1 ELSE 0 END) AS calificadas,
                   SUM(CASE WHEN e.id_entrega IS NOT NULL AND (e.fecha_calificacion IS NULL AND (e.estado IS NULL OR e.estado <> 'Calificada')) THEN 1 ELSE 0 END) AS pendientes_correccion
            FROM curso_tareas t
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            LEFT JOIN curso_entregas e ON e.id_tarea = t.id_tarea
            WHERE m.id_publicacion = :p AND r.tipo = 'Entrega de Tareas'
            GROUP BY t.id_tarea, t.id_recurso, t.puntaje_maximo, t.tipo_calificacion,
                     r.titulo, u.id_unidad, u.titulo, m.id_modulo, m.titulo, m.orden, u.orden, r.orden
            ORDER BY m.orden, u.orden, r.orden, t.id_tarea
        ");
        $stmt->execute(['p' => $id_publicacion]);
        $resumen = [];
        foreach ($stmt->fetchAll() as $fila) {
            $resumen[(int)$fila['id_tarea']] = $fila;
        }
        return $resumen;
    }

    public function listarEntregasCursoAgrupadas(int $id_publicacion): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.id_entrega, e.id_tarea, e.id_usuario, e.fecha_entrega, e.estado, e.calificacion, e.fecha_calificacion,
                   t.puntaje_maximo, t.tipo_calificacion,
                   u.nombre, u.apellido, u.email
            FROM curso_entregas e
            JOIN curso_tareas t ON t.id_tarea = e.id_tarea
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades cu ON cu.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = cu.id_modulo
            JOIN usuarios u ON u.id_usuario = e.id_usuario
            WHERE m.id_publicacion = :p AND r.tipo = 'Entrega de Tareas'
            ORDER BY m.orden, cu.orden, r.orden, e.fecha_entrega DESC, u.apellido, u.nombre
        ");
        $stmt->execute(['p' => $id_publicacion]);
        $entregas = [];
        foreach ($stmt->fetchAll() as $fila) {
            $entregas[(int)$fila['id_tarea']][] = $fila;
        }
        return $entregas;
    }

    public function listarEntregasUsuarioPorCurso(int $id_publicacion, int $id_usuario): array
    {
        $stmt = $this->pdo->prepare("
            SELECT e.*
            FROM curso_entregas e
            JOIN curso_tareas t ON t.id_tarea = e.id_tarea
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            WHERE m.id_publicacion = :id AND e.id_usuario = :u
        ");
        $stmt->execute(['id' => $id_publicacion, 'u' => $id_usuario]);
        $entregas = [];
        foreach ($stmt->fetchAll() as $row) {
            $entregas[(int)$row['id_recurso']] = $row;
        }
        return $entregas;
    }

    public function obtenerTareasAlumnoCurso(int $id_publicacion, int $id_usuario): array
    {
        $stmt = $this->pdo->prepare("
            SELECT t.id_tarea, t.id_recurso, t.fecha_apertura, t.fecha_cierre,
                   t.permite_archivos, t.permite_texto, t.permite_enlace,
                   t.max_archivos, t.max_tamano_mb, t.formatos_permitidos, t.requisito_entrega,
                   t.puntaje_maximo, t.tipo_calificacion, t.permite_feedback_archivo,
                   r.titulo, r.descripcion, u.id_unidad, u.titulo AS unidad_titulo,
                   m.id_modulo, m.titulo AS modulo_titulo,
                   e.id_entrega, e.estado, e.estado_entrega, e.calificacion, e.fecha_entrega, e.fecha_calificacion
            FROM curso_tareas t
            JOIN curso_recursos r ON r.id_recurso = t.id_recurso
            JOIN curso_unidades u ON u.id_unidad = r.id_unidad
            JOIN curso_modulos m ON m.id_modulo = u.id_modulo
            LEFT JOIN curso_entregas e ON e.id_tarea = t.id_tarea AND e.id_usuario = :u
            WHERE m.id_publicacion = :p AND r.tipo = 'Entrega de Tareas'
            ORDER BY m.orden, u.orden, r.orden, t.id_tarea
        ");
        $stmt->execute(['p' => $id_publicacion, 'u' => $id_usuario]);
        return $stmt->fetchAll();
    }

    public function guardarCalificacion(int $id_entrega, ?string $calificacion, ?string $feedback, ?string $archivoFeedback, int $id_docente): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE curso_entregas
            SET calificacion = :calificacion,
                feedback_docente = :feedback,
                archivo_feedback = COALESCE(:archivo, archivo_feedback),
                fecha_calificacion = CURRENT_TIMESTAMP,
                id_docente_calificador = :docente,
                estado = 'Calificada',
                fecha_actualizacion = CURRENT_TIMESTAMP
            WHERE id_entrega = :id
        ");
        $stmt->execute([
            'calificacion' => $calificacion,
            'feedback' => $feedback,
            'archivo' => $archivoFeedback,
            'docente' => $id_docente,
            'id' => $id_entrega,
        ]);
    }

    private function driver(): string
    {
        return (string)$this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
    }

    private function eliminarFeedbackAnterior(?string $ruta): void
    {
        if ($ruta && !eliminar_archivo_storage($ruta)) {
            error_log('No se pudo eliminar feedback invalidado por reentrega: ' . $ruta);
        }
    }

    private function enriquecerTipos(array $fila): array
    {
        $tipos = [];
        $cantidad = (int)($fila['cantidad_archivos'] ?? 0);
        $tieneArchivos = $cantidad > 0 || trim((string)($fila['archivo_entrega'] ?? '')) !== '';
        if ($tieneArchivos) $tipos[] = 'Archivo';
        if (trim((string)($fila['texto_entrega'] ?? '')) !== '') $tipos[] = 'Texto';
        if (trim((string)($fila['enlace_entrega'] ?? '')) !== '') $tipos[] = 'Enlace';
        $fila['tiene_archivos'] = $tieneArchivos;
        $fila['cantidad_archivos'] = $cantidad ?: (!empty($fila['archivo_entrega']) ? 1 : 0);
        $fila['tipos_entrega'] = $tipos;
        return $fila;
    }
}

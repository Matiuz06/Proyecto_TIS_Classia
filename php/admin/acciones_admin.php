<?php

/**
 * Responsabilidad: Funciones de gestión administrativa de la plataforma Classia.
 *
 * Proporciona operaciones de supervisión general:
 * - Métricas del estado de la plataforma.
 * - Gestión de usuarios (cambio de rol, bloqueo con justificación, desbloqueo).
 * - Moderación de publicaciones y matriculación manual a cursos.
 * - Moderación de intervenciones en foros de cursos.
 */

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';


// ESTADÍSTICAS

/**
 * Devuelve un resumen de métricas clave para el dashboard de administración.
 */
function obtener_stats_plataforma(PDO $pdo): array
{
    $stats = [
        'total_usuarios'        => 0,
        'total_estudiantes'     => 0,
        'total_docentes'        => 0,
        'publicaciones_activas' => 0,
        'publicaciones_total'   => 0,
        'solicitudes_docente'   => 0,
        'noticias_pendientes'   => 0,
        'eventos_pendientes'    => 0,
        'servicios_en_proceso'  => 0,
    ];

    try {
        // Conteo de usuarios por rol
        $stmt = $pdo->query("SELECT id_rol, COUNT(*) AS total FROM usuarios GROUP BY id_rol");
        foreach ($stmt->fetchAll() as $row) {
            $stats['total_usuarios'] += (int) $row['total'];
            if ((int) $row['id_rol'] === ROL_ESTUDIANTE) $stats['total_estudiantes'] = (int) $row['total'];
            if ((int) $row['id_rol'] === ROL_DOCENTE)    $stats['total_docentes']    = (int) $row['total'];
        }

        // Publicaciones
        $stmt = $pdo->query("SELECT estado, COUNT(*) AS total FROM publicaciones GROUP BY estado");
        foreach ($stmt->fetchAll() as $row) {
            $stats['publicaciones_total'] += (int) $row['total'];
            if ($row['estado'] === 'Activo') $stats['publicaciones_activas'] = (int) $row['total'];
        }

        // Solicitudes docentes pendientes
        $stmt = $pdo->query("SELECT COUNT(*) FROM solicitudes_docente WHERE estado = 'Pendiente'");
        $stats['solicitudes_docente'] = (int) $stmt->fetchColumn();

        // Noticias pendientes
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM noticias WHERE estado = 'Pendiente'");
            $stats['noticias_pendientes'] = (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Stats noticias: ' . $e->getMessage());
        }

        // Eventos pendientes
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM eventos WHERE estado = 'Pendiente'");
            $stats['eventos_pendientes'] = (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Stats eventos: ' . $e->getMessage());
        }

        // Servicios en proceso
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM solicitudes WHERE estado = 'En Proceso'");
            $stats['servicios_en_proceso'] = (int) $stmt->fetchColumn();
        } catch (PDOException $e) {
            error_log('Stats servicios: ' . $e->getMessage());
        }
    } catch (PDOException $e) {
        error_log('Error en obtener_stats_plataforma: ' . $e->getMessage());
    }

    return $stats;
}


// USUARIOS

/**
 * Devuelve todos los usuarios registrados (excluyendo al administrador autenticado).
 */
function obtener_todos_usuarios(PDO $pdo, int $excluir_id = 0): array
{
    try {
        // Intenta consultar con motivo_bloqueo
        $stmt = $pdo->prepare(
            "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.id_rol, u.fecha_registro,
                    COALESCE(u.activo, 1) AS activo, u.fecha_baja, u.motivo_bloqueo
             FROM usuarios u
             WHERE u.id_usuario <> :excluir
             ORDER BY u.fecha_registro DESC"
        );
        $stmt->execute(['excluir' => $excluir_id]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        // Fallback por si la columna motivo_bloqueo aún no fue migrada en la base
        try {
            $stmt = $pdo->prepare(
                "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.id_rol, u.fecha_registro,
                        COALESCE(u.activo, 1) AS activo, u.fecha_baja, NULL AS motivo_bloqueo
                 FROM usuarios u
                 WHERE u.id_usuario <> :excluir
                 ORDER BY u.fecha_registro DESC"
            );
            $stmt->execute(['excluir' => $excluir_id]);
            return $stmt->fetchAll();
        } catch (PDOException $ex) {
            error_log('Error al obtener usuarios: ' . $ex->getMessage());
            return [];
        }
    }
}

/**
 * Cambia el rol de un usuario (solo Estudiante ↔ Docente, nunca Admin).
 */
function cambiar_rol_usuario(PDO $pdo, int $id_usuario, int $nuevo_rol, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    if ($id_usuario <= 0 || !in_array($nuevo_rol, [ROL_ESTUDIANTE, ROL_DOCENTE], true)) {
        return ['ok' => false, 'error' => 'Parámetros inválidos.', 'mensaje' => ''];
    }

    try {
        $stmt = $pdo->prepare(
            "UPDATE usuarios SET id_rol = :rol WHERE id_usuario = :id AND id_rol <> :rol AND id_rol <> :admin"
        );
        $stmt->execute(['rol' => $nuevo_rol, 'id' => $id_usuario, 'admin' => ROL_ADMIN]);

        if ($stmt->rowCount() === 0) {
            return ['ok' => true, 'error' => '', 'mensaje' => 'El usuario ya tenía ese rol o es administrador.'];
        }
        return ['ok' => true, 'error' => '', 'mensaje' => 'Rol actualizado correctamente.'];
    } catch (PDOException $e) {
        error_log('Error al cambiar rol: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo actualizar el rol.', 'mensaje' => ''];
    }
}

/**
 * Bloquea la cuenta de un usuario registrando una justificación obligatoria.
 */
function bloquear_usuario(PDO $pdo, int $id_usuario, string $motivo, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    if ($id_usuario <= 0) {
        return ['ok' => false, 'error' => 'Usuario no válido.', 'mensaje' => ''];
    }

    $motivo = trim($motivo);
    if ($motivo === '') {
        return ['ok' => false, 'error' => 'Es obligatorio ingresar un motivo o justificación para el bloqueo.', 'mensaje' => ''];
    }

    try {
        // Intenta actualizar con columna motivo_bloqueo
        try {
            $stmt = $pdo->prepare(
                "UPDATE usuarios 
                 SET activo = 0, fecha_baja = CURRENT_TIMESTAMP, motivo_bloqueo = :motivo 
                 WHERE id_usuario = :id AND id_rol <> :admin"
            );
            $stmt->execute(['motivo' => $motivo, 'id' => $id_usuario, 'admin' => ROL_ADMIN]);
        } catch (PDOException $e) {
            // Fallback si la columna motivo_bloqueo aún no fue migrada
            $stmt = $pdo->prepare(
                "UPDATE usuarios 
                 SET activo = 0, fecha_baja = CURRENT_TIMESTAMP 
                 WHERE id_usuario = :id AND id_rol <> :admin"
            );
            $stmt->execute(['id' => $id_usuario, 'admin' => ROL_ADMIN]);
        }

        if ($stmt->rowCount() === 0) {
            return ['ok' => false, 'error' => 'No se pudo bloquear al usuario (usuario inexistente o administrador).', 'mensaje' => ''];
        }
        return ['ok' => true, 'error' => '', 'mensaje' => 'Usuario bloqueado correctamente con justificación registrada.'];
    } catch (PDOException $e) {
        error_log('Error al bloquear usuario: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo completar el bloqueo.', 'mensaje' => ''];
    }
}

/**
 * Desbloquea la cuenta de un usuario restableciendo su acceso.
 */
function desbloquear_usuario(PDO $pdo, int $id_usuario, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    if ($id_usuario <= 0) {
        return ['ok' => false, 'error' => 'Usuario no válido.', 'mensaje' => ''];
    }

    try {
        try {
            $stmt = $pdo->prepare(
                "UPDATE usuarios 
                 SET activo = 1, fecha_baja = NULL, motivo_bloqueo = NULL 
                 WHERE id_usuario = :id AND id_rol <> :admin"
            );
            $stmt->execute(['id' => $id_usuario, 'admin' => ROL_ADMIN]);
        } catch (PDOException $e) {
            $stmt = $pdo->prepare(
                "UPDATE usuarios 
                 SET activo = 1, fecha_baja = NULL 
                 WHERE id_usuario = :id AND id_rol <> :admin"
            );
            $stmt->execute(['id' => $id_usuario, 'admin' => ROL_ADMIN]);
        }

        return ['ok' => true, 'error' => '', 'mensaje' => 'Usuario reactivado y desbloqueado correctamente.'];
    } catch (PDOException $e) {
        error_log('Error al desbloquear usuario: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo desbloquear al usuario.', 'mensaje' => ''];
    }
}


// PUBLICACIONES Y CURSOS

/**
 * Devuelve todas las publicaciones de la plataforma con datos del autor.
 */
function obtener_todas_publicaciones(PDO $pdo, int $limite = 60): array
{
    try {
        $stmt = $pdo->prepare(
            "SELECT p.id_publicacion, p.titulo, p.tipo, p.estado, p.precio,
                    p.fecha_creacion, c.nombre_categoria,
                    u.nombre AS autor_nombre, u.apellido AS autor_apellido
             FROM publicaciones p
             LEFT JOIN categorias c ON c.id_categoria = p.id_categoria
             LEFT JOIN usuarios   u ON u.id_usuario   = p.id_usuario
             WHERE p.estado <> 'Eliminado'
             ORDER BY p.fecha_creacion DESC
             LIMIT :limite"
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al obtener publicaciones admin: ' . $e->getMessage());
        return [];
    }
}

/**
 * Obtiene la lista simplificada de cursos activos para selección en formularios de matriculación.
 */
function obtener_todos_cursos(PDO $pdo): array
{
    try {
        $stmt = $pdo->query(
            "SELECT p.id_publicacion, p.titulo, p.precio, u.nombre AS docente_nombre, u.apellido AS docente_apellido
             FROM publicaciones p
             JOIN usuarios u ON u.id_usuario = p.id_usuario
             WHERE p.tipo = 'Curso' AND p.estado <> 'Eliminado'
             ORDER BY p.titulo ASC"
        );
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Error al obtener cursos para admin: ' . $e->getMessage());
        return [];
    }
}

/**
 * Inscribe / matricula a un usuario manualmente en un curso.
 */
function inscribir_usuario_en_curso(PDO $pdo, int $id_usuario, int $id_publicacion, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    if ($id_usuario <= 0 || $id_publicacion <= 0) {
        return ['ok' => false, 'error' => 'Seleccioná un usuario y un curso válidos.', 'mensaje' => ''];
    }

    try {
        // Verificar que el curso exista y sea de tipo Curso
        $stmt_c = $pdo->prepare("SELECT id_publicacion, titulo, tipo, estado FROM publicaciones WHERE id_publicacion = :id LIMIT 1");
        $stmt_c->execute(['id' => $id_publicacion]);
        $curso = $stmt_c->fetch();

        if (!$curso || $curso['tipo'] !== 'Curso') {
            return ['ok' => false, 'error' => 'La publicación seleccionada no es un curso válido.', 'mensaje' => ''];
        }

        // Verificar si el usuario ya está inscrito con pago aprobado
        $stmt_check = $pdo->prepare("
            SELECT 1 
            FROM detalles_contratacion dc 
            JOIN contrataciones c ON c.id_contratacion = dc.id_contratacion 
            JOIN pagos p ON p.id_contratacion = c.id_contratacion AND p.estado_pago = 'Aprobado'
            WHERE c.id_usuario = :u AND dc.id_publicacion = :p AND c.estado IN ('En Proceso', 'Completada')
            LIMIT 1
        ");
        $stmt_check->execute(['u' => $id_usuario, 'p' => $id_publicacion]);
        if ($stmt_check->fetch()) {
            return ['ok' => false, 'error' => 'El usuario ya se encuentra matriculado en este curso.', 'mensaje' => ''];
        }

        $pdo->beginTransaction();

        $stmt_con = $pdo->prepare("
            INSERT INTO contrataciones (monto_total, estado, id_usuario)
            VALUES (0.00, 'En Proceso', :id_usuario)
        ");
        $stmt_con->execute(['id_usuario' => $id_usuario]);
        $id_contratacion = (int) $pdo->lastInsertId();

        $stmt_det = $pdo->prepare("
            INSERT INTO detalles_contratacion (cantidad, precio_unitario, subtotal, id_contratacion, id_publicacion)
            VALUES (1, 0.00, 0.00, :id_c, :id_p)
        ");
        $stmt_det->execute([
            'id_c' => $id_contratacion,
            'id_p' => $id_publicacion,
        ]);

        $ref = 'ADM-ENROLL-' . strtoupper(bin2hex(random_bytes(4)));
        $stmt_pago = $pdo->prepare("
            INSERT INTO pagos (monto, metodo_pago, estado_pago, fecha_pago, transaccion_ref, id_contratacion)
            VALUES (0.00, 'Transferencia', 'Aprobado', CURRENT_TIMESTAMP, :ref, :id_c)
        ");
        $stmt_pago->execute([
            'ref'  => $ref,
            'id_c' => $id_contratacion,
        ]);

        $pdo->commit();

        return ['ok' => true, 'error' => '', 'mensaje' => "Usuario matriculado exitosamente en el curso «{$curso['titulo']}»."];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Error al inscribir usuario en curso: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'Ocurrió un error al procesar la inscripción.', 'mensaje' => ''];
    }
}

/**
 * Elimina lógicamente una publicación (estado = 'Eliminado') con validación CSRF.
 */
function eliminar_publicacion_admin(PDO $pdo, int $id_publicacion, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    if ($id_publicacion <= 0) {
        return ['ok' => false, 'error' => 'ID de publicación inválido.', 'mensaje' => ''];
    }
    try {
        $stmt = $pdo->prepare("UPDATE publicaciones SET estado = 'Eliminado' WHERE id_publicacion = :id");
        $stmt->execute(['id' => $id_publicacion]);
        return ['ok' => true, 'error' => '', 'mensaje' => 'Publicación eliminada correctamente.'];
    } catch (PDOException $e) {
        error_log('Error al eliminar publicación admin: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo eliminar la publicación.', 'mensaje' => ''];
    }
}

/**
 * Cambia el estado de una publicación como administrador.
 */
function cambiar_estado_publicacion_admin(PDO $pdo, int $id_publicacion, string $nuevo_estado, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    $estados_permitidos = ['Activo', 'Pausado', 'Inactivo', 'Eliminado'];
    if ($id_publicacion <= 0 || !in_array($nuevo_estado, $estados_permitidos, true)) {
        return ['ok' => false, 'error' => 'Parámetros inválidos.', 'mensaje' => ''];
    }
    try {
        $stmt = $pdo->prepare("UPDATE publicaciones SET estado = :estado WHERE id_publicacion = :id");
        $stmt->execute(['estado' => $nuevo_estado, 'id' => $id_publicacion]);
        return ['ok' => true, 'error' => '', 'mensaje' => "Estado cambiado a «{$nuevo_estado}» correctamente."];
    } catch (PDOException $e) {
        error_log('Error al cambiar estado publicación admin: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo cambiar el estado.', 'mensaje' => ''];
    }
}

/**
 * Elimina un mensaje del foro de un curso (moderación por parte de administrador o docente).
 */
function eliminar_mensaje_foro(PDO $pdo, int $id_mensaje_foro, int $id_usuario_actual, bool $es_admin, string $token, string $csrf_session): array
{
    if (empty($csrf_session) || !hash_equals($csrf_session, $token)) {
        return ['ok' => false, 'error' => 'Sesión de formulario inválida.', 'mensaje' => ''];
    }
    if ($id_mensaje_foro <= 0) {
        return ['ok' => false, 'error' => 'Mensaje no válido.', 'mensaje' => ''];
    }

    try {
        if ($es_admin) {
            $stmt = $pdo->prepare("DELETE FROM curso_foro_mensajes WHERE id_mensaje = :id");
            $stmt->execute(['id' => $id_mensaje_foro]);
        } else {
            // Verificar si el usuario es el autor del mensaje o el docente creador del curso
            $stmt = $pdo->prepare("
                SELECT fm.id_mensaje, fm.id_usuario, p.id_usuario AS docente_id
                FROM curso_foro_mensajes fm
                JOIN curso_recursos r ON r.id_recurso = fm.id_recurso
                JOIN curso_unidades u ON u.id_unidad = r.id_unidad
                JOIN curso_modulos m ON m.id_modulo = u.id_modulo
                JOIN publicaciones p ON p.id_publicacion = m.id_publicacion
                WHERE fm.id_mensaje = :id
                LIMIT 1
            ");
            $stmt->execute(['id' => $id_mensaje_foro]);
            $msg = $stmt->fetch();

            if (!$msg) {
                return ['ok' => false, 'error' => 'El mensaje no existe.', 'mensaje' => ''];
            }

            if ((int)$msg['id_usuario'] !== $id_usuario_actual && (int)$msg['docente_id'] !== $id_usuario_actual) {
                return ['ok' => false, 'error' => 'No tenés permisos para eliminar este mensaje.', 'mensaje' => ''];
            }

            $stmt_del = $pdo->prepare("DELETE FROM curso_foro_mensajes WHERE id_mensaje = :id");
            $stmt_del->execute(['id' => $id_mensaje_foro]);
        }

        return ['ok' => true, 'error' => '', 'mensaje' => 'Mensaje moderado y eliminado correctamente.'];
    } catch (PDOException $e) {
        error_log('Error al eliminar mensaje del foro: ' . $e->getMessage());
        return ['ok' => false, 'error' => 'No se pudo eliminar el mensaje.', 'mensaje' => ''];
    }
}

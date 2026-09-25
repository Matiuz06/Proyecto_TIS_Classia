<?php

/**
 * Responsabilidad: Carga el detalle de un curso, su contenido y valoraciones asociadas.
 *
 * Arquitectura POO:
 *   El contenido del curso se carga a través de ContenidoCursoRepository,
 *   que construye objetos Modulo > Unidad > Recurso.
 *   La variable $contenido_curso es un array de arrays (via toArray()) para
 *   mantener compatibilidad con las plantillas de la vista curso.php.
 *   Para trabajar con objetos nativos, usar:
 *     $repo = new ContenidoCursoRepository($pdo);
 *     $modulos = $repo->obtenerPorCurso($id_curso); // devuelve Modulo[]
 */

require_once __DIR__ . '/../auth/sesion.php';
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/contenido_curso.php';   // carga ContenidoCursoRepository + clases de dominio
require_once __DIR__ . '/../../config/database.php';

iniciar_sesion();

$id_curso = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$curso = null;
$resenas = [];
$promedio_calificacion = 0.0;
$total_resenas = 0;
$cursos_relacionados = [];
$comprado = false;
$contenido_curso = [];
$puede_ver_recursos = false;

$usuario_actual = usuario_actual();

$contratacion_curso = null;

if ($id_curso > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido,
                   u.id_usuario AS autor_id, u.foto_perfil AS autor_foto
            FROM publicaciones p 
            JOIN categorias c ON p.id_categoria = c.id_categoria 
            JOIN usuarios u ON p.id_usuario = u.id_usuario 
            WHERE p.id_publicacion = :id AND p.tipo = 'Curso'
            LIMIT 1
        ");
        $stmt->execute(['id' => $id_curso]);
        $curso = $stmt->fetch();

        if ($curso) {
            // Verificar si el usuario actual ya contrató o compró el curso
            if ($usuario_actual) {
                $stmt_compra = $pdo->prepare("
                    SELECT c.id_contratacion, c.estado
                    FROM detalles_contratacion dc
                    JOIN contrataciones c ON dc.id_contratacion = c.id_contratacion
                    JOIN pagos pag ON pag.id_contratacion = c.id_contratacion AND pag.estado_pago = 'Aprobado'
                    WHERE c.id_usuario = :id_usuario AND dc.id_publicacion = :id_publicacion
                      AND c.estado IN ('Completada', 'En Proceso')
                    ORDER BY c.id_contratacion DESC
                    LIMIT 1
                ");
                $stmt_compra->execute([
                    'id_usuario' => (int) $usuario_actual['id_usuario'],
                    'id_publicacion' => $id_curso,
                ]);
                $contratacion_curso = $stmt_compra->fetch() ?: null;
                $comprado = !empty($contratacion_curso);

                // Procesar acción de finalizar / completar curso
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'completar_curso' && $contratacion_curso) {
                    $token = $_POST['csrf_token'] ?? '';
                    if (hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                        $stmt_comp = $pdo->prepare("UPDATE contrataciones SET estado = 'Completada' WHERE id_contratacion = :id AND id_usuario = :u");
                        $stmt_comp->execute([
                            'id' => (int) $contratacion_curso['id_contratacion'],
                            'u'  => (int) $usuario_actual['id_usuario'],
                        ]);
                        $contratacion_curso['estado'] = 'Completada';
                        header("Location: curso.php?id=" . $id_curso . "&completado=1");
                        exit;
                    }
                }

                // Procesar entrega de tarea por parte del estudiante
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'entregar_tarea') {
                    $token = $_POST['csrf_token'] ?? '';
                    $id_recurso_tarea = (int)($_POST['id_recurso'] ?? 0);
                    $id_unidad_tarea = (int)($_POST['id_unidad'] ?? 0);
                    $comentario = trim($_POST['comentario_entrega'] ?? '');

                    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                        $_SESSION['curso_error'] = 'Token de seguridad inválido. Recarga la página e intenta nuevamente.';
                    } elseif ($id_recurso_tarea <= 0) {
                        $_SESSION['curso_error'] = 'Recurso de tarea inválido.';
                    } else {
                        $rutaArchivo = null;
                        if (!empty($_FILES['archivo_entrega']['name'])) {
                            $archivo = $_FILES['archivo_entrega'];
                            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                            $formatosValidos = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'zip', 'rar', 'jpg', 'jpeg', 'png', 'webp', 'stl', 'obj', '3mf'];
                            if (!in_array($ext, $formatosValidos, true)) {
                                $_SESSION['curso_error'] = 'Formato no permitido. Sube un PDF, documento Office, comprimido o imagen.';
                            } else {
                                $prefijo = 'entrega_r' . $id_recurso_tarea . '_u' . $usuario_actual['id_usuario'];
                                $resUpload = subir_archivo_supabase($archivo, 'recursos-cursos', 'entregas/' . $prefijo);
                                if ($resUpload['ok']) {
                                    $rutaArchivo = $resUpload['path'];
                                } else {
                                    $resLocal = guardar_archivo_subido($archivo, 'entregas', $formatosValidos, 50 * 1024 * 1024);
                                    if ($resLocal['ok']) {
                                        $rutaArchivo = $resLocal['ruta'];
                                    } else {
                                        $_SESSION['curso_error'] = 'Error al subir archivo de entrega: ' . $resUpload['error'];
                                    }
                                }
                            }
                        }

                        if (empty($_SESSION['curso_error'])) {
                            $stmt_ent = $pdo->prepare("
                                INSERT INTO curso_entregas (id_recurso, id_usuario, archivo_entrega, comentario_entrega, estado)
                                VALUES (:id_r, :id_u, :arch, :com, 'Entregada')
                                ON DUPLICATE KEY UPDATE
                                    archivo_entrega = COALESCE(VALUES(archivo_entrega), archivo_entrega),
                                    comentario_entrega = VALUES(comentario_entrega),
                                    estado = 'Entregada',
                                    fecha_entrega = CURRENT_TIMESTAMP
                            ");
                            $stmt_ent->execute([
                                'id_r' => $id_recurso_tarea,
                                'id_u' => (int)$usuario_actual['id_usuario'],
                                'arch' => $rutaArchivo,
                                'com'  => $comentario,
                            ]);
                            $_SESSION['curso_exito'] = '¡Tu entrega fue enviada correctamente!';
                            header("Location: curso.php?id=" . $id_curso . ($id_unidad_tarea ? "&unidad=" . $id_unidad_tarea : ""));
                            exit;
                        }
                    }
                }

                // Procesar nuevo mensaje en el foro de debate
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'publicar_mensaje_foro') {
                    $token = $_POST['csrf_token'] ?? '';
                    $id_recurso_foro = (int)($_POST['id_recurso'] ?? 0);
                    $id_unidad_foro = (int)($_POST['id_unidad'] ?? 0);
                    $mensaje = trim($_POST['mensaje_foro'] ?? '');

                    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                        $_SESSION['curso_error'] = 'Token de seguridad inválido. Recarga la página e intenta nuevamente.';
                    } elseif ($id_recurso_foro <= 0 || empty($mensaje)) {
                        $_SESSION['curso_error'] = 'El mensaje no puede estar vacío.';
                    } else {
                        $stmt_foro_post = $pdo->prepare("INSERT INTO curso_foro_mensajes (id_recurso, id_usuario, mensaje) VALUES (:id_r, :id_u, :msg)");
                        $stmt_foro_post->execute([
                            'id_r' => $id_recurso_foro,
                            'id_u' => (int)$usuario_actual['id_usuario'],
                            'msg'  => $mensaje,
                        ]);
                        $_SESSION['curso_exito'] = '¡Tu comentario fue publicado en el foro de debate!';
                        header("Location: curso.php?id=" . $id_curso . ($id_unidad_foro ? "&unidad=" . $id_unidad_foro : ""));
                        exit;
                    }
                }

                // Moderar o eliminar mensaje del foro
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_mensaje_foro') {
                    $token = $_POST['csrf_token'] ?? '';
                    $id_msg = (int)($_POST['id_mensaje'] ?? 0);
                    $id_unidad_foro = (int)($_POST['id_unidad'] ?? 0);

                    if (!hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
                        $_SESSION['curso_error'] = 'Token de seguridad inválido.';
                    } elseif ($id_msg <= 0) {
                        $_SESSION['curso_error'] = 'Mensaje no válido.';
                    } else {
                        $es_adm = es_admin();
                        $uid_act = (int)$usuario_actual['id_usuario'];
                        $stmt_check_msg = $pdo->prepare("SELECT id_usuario FROM curso_foro_mensajes WHERE id_mensaje = :id");
                        $stmt_check_msg->execute(['id' => $id_msg]);
                        $msg_row = $stmt_check_msg->fetch();

                        if ($msg_row && ($es_adm || $uid_act === (int)$curso['id_usuario'] || $uid_act === (int)$msg_row['id_usuario'])) {
                            $stmt_del_m = $pdo->prepare("DELETE FROM curso_foro_mensajes WHERE id_mensaje = :id");
                            $stmt_del_m->execute(['id' => $id_msg]);
                            $_SESSION['curso_exito'] = 'Mensaje eliminado correctamente.';
                        } else {
                            $_SESSION['curso_error'] = 'No tenés permisos para eliminar este mensaje.';
                        }
                        header("Location: curso.php?id=" . $id_curso . ($id_unidad_foro ? "&unidad=" . $id_unidad_foro : ""));
                        exit;
                    }
                }
            }

            $es_propietario = $usuario_actual && (int)$usuario_actual['id_usuario'] === (int)$curso['id_usuario'];
            if ($curso['estado'] !== 'Activo' && !$comprado && !$es_propietario && !es_admin()) {
                $curso = null;
            }

            if (!$curso) {
                return;
            }

            $contenido_curso = obtener_contenido_curso($pdo, $id_curso);
            $puede_ver_recursos = $comprado || $es_propietario || es_admin();

            $mis_entregas = [];
            $mensajes_foro = [];
            if ($usuario_actual) {
                $stmt_entregas = $pdo->prepare("
                    SELECT e.*
                    FROM curso_entregas e
                    JOIN curso_recursos r ON r.id_recurso = e.id_recurso
                    JOIN curso_unidades u ON u.id_unidad = r.id_unidad
                    JOIN curso_modulos m ON m.id_modulo = u.id_modulo
                    WHERE m.id_publicacion = :id AND e.id_usuario = :u
                ");
                $stmt_entregas->execute([
                    'id' => $id_curso,
                    'u'  => (int)$usuario_actual['id_usuario'],
                ]);
                foreach ($stmt_entregas->fetchAll() as $ent) {
                    $mis_entregas[(int)$ent['id_recurso']] = $ent;
                }

                $stmt_foro_msgs = $pdo->prepare("
                    SELECT fm.*, u.nombre, u.apellido, u.foto_perfil, u.id_rol, r.nombre_rol
                    FROM curso_foro_mensajes fm
                    JOIN usuarios u ON u.id_usuario = fm.id_usuario
                    JOIN roles r ON r.id_rol = u.id_rol
                    JOIN curso_recursos cr ON cr.id_recurso = fm.id_recurso
                    JOIN curso_unidades cu ON cu.id_unidad = cr.id_unidad
                    JOIN curso_modulos cm ON cm.id_modulo = cu.id_modulo
                    WHERE cm.id_publicacion = :id
                    ORDER BY fm.fecha_mensaje ASC
                ");
                $stmt_foro_msgs->execute(['id' => $id_curso]);
                foreach ($stmt_foro_msgs->fetchAll() as $fmsg) {
                    $mensajes_foro[(int)$fmsg['id_recurso']][] = $fmsg;
                }
            }

            // Reseñas del curso
            $stmt_res = $pdo->prepare("
                SELECT v.*, u.nombre, u.apellido, u.foto_perfil
                FROM valoraciones v
                JOIN usuarios u ON v.id_usuario = u.id_usuario
                WHERE v.id_publicacion = :id
                ORDER BY v.fecha_valoracion DESC
            ");
            $stmt_res->execute(['id' => $id_curso]);
            $resenas = $stmt_res->fetchAll();
            $total_resenas = count($resenas);

            if ($total_resenas > 0) {
                $suma = array_sum(array_column($resenas, 'puntuacion'));
                $promedio_calificacion = round($suma / $total_resenas, 1);
            }

            // Cursos relacionados
            $stmt_rel = $pdo->prepare("
                SELECT p.*, c.nombre_categoria, u.nombre AS autor_nombre, u.apellido AS autor_apellido
                FROM publicaciones p
                JOIN categorias c ON p.id_categoria = c.id_categoria
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.tipo = 'Curso' AND p.id_publicacion != :id AND p.estado = 'Activo'
                ORDER BY (p.id_categoria = :id_categoria) DESC, p.fecha_creacion DESC
                LIMIT 3
            ");
            $stmt_rel->execute([
                'id' => $id_curso,
                'id_categoria' => (int) $curso['id_categoria']
            ]);
            $cursos_relacionados = $stmt_rel->fetchAll();
        }
    } catch (PDOException $e) {
        error_log("Error al consultar detalle del curso: " . $e->getMessage());
        $curso = null;
    }
}

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
require_once __DIR__ . '/contenido_curso.php';
require_once __DIR__ . '/EntregaRepository.php';
require_once __DIR__ . '/../certificados/CursoProgresoRepository.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../admin/estadisticas_admin.php';
require_once __DIR__ . '/../utils/toast.php';

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
$tareas_curso = [];
$tareas_alumno_curso = [];
$puede_gestionar_entregas = false;
$resumen_entregas_docente = [];
$entregas_docente_por_tarea = [];
$archivos_entregas = [];

$progreso_curso = null;
$recursos_completados_ids = [];

$usuario_actual = usuario_actual();

$contratacion_curso = null;


/**
 * Normaliza un input de archivos múltiples.
 */
function normalizar_archivos_multiples(array $files, string $campo): array
{
    if (empty($files[$campo]['name'])) {
        return [];
    }

    if (!is_array($files[$campo]['name'])) {
        return [$files[$campo]];
    }

    $archivos = [];

    foreach ($files[$campo]['name'] as $i => $name) {
        $archivos[] = [
            'name' => $name,
            'type' => $files[$campo]['type'][$i] ?? '',
            'tmp_name' => $files[$campo]['tmp_name'][$i] ?? '',
            'error' => $files[$campo]['error'][$i] ?? UPLOAD_ERR_NO_FILE,
            'size' => $files[$campo]['size'][$i] ?? 0,
        ];
    }

    return array_values(
        array_filter(
            $archivos,
            fn($a) =>
                ($a['error'] ?? UPLOAD_ERR_NO_FILE)
                !== UPLOAD_ERR_NO_FILE
        )
    );
}


/**
 * Valida un archivo presentado como entrega de una tarea.
 */
function validar_archivo_tarea(array $archivo, Tarea $tarea): array
{
    if (
        ($archivo['error'] ?? UPLOAD_ERR_NO_FILE)
        !== UPLOAD_ERR_OK
    ) {
        return [
            'ok' => false,
            'error' => 'No se pudo procesar el archivo seleccionado.'
        ];
    }

    if (
        ($archivo['size'] ?? 0)
        > $tarea->getMaxTamanoMb() * 1024 * 1024
    ) {
        return [
            'ok' => false,
            'error' => 'El archivo supera el tamaño máximo permitido.'
        ];
    }

    $ext = strtolower(
        pathinfo(
            (string) ($archivo['name'] ?? ''),
            PATHINFO_EXTENSION
        )
    );

    if ($ext === 'jpeg') {
        $ext = 'jpg';
    }

    if (
        !in_array(
            $ext,
            $tarea->getFormatosPermitidos(),
            true
        )
    ) {
        return [
            'ok' => false,
            'error' => 'Este tipo de archivo no está permitido para esta tarea.'
        ];
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);

    $mime = $finfo->file(
        $archivo['tmp_name']
    ) ?: 'application/octet-stream';

    $mimes = supabase_tipos_permitidos();

    if (
        !isset($mimes[$mime])
        || !in_array($ext, $mimes[$mime], true)
    ) {
        return [
            'ok' => false,
            'error' => 'El contenido real del archivo no coincide con su extensión.'
        ];
    }

    return [
        'ok' => true,
        'ext' => $ext,
        'mime' => $mime
    ];
}


/**
 * Elimina un archivo utilizado por una entrega.
 */
function eliminar_ruta_entrega(?string $ruta): void
{
    if (!$ruta) {
        return;
    }

    $ok = eliminar_archivo_storage($ruta);

    if (!$ok) {
        error_log(
            'No se pudo eliminar archivo reemplazado: ' . $ruta
        );
    }
}


if ($id_curso > 0) {
    try {
        $stmt = $pdo->prepare("
            SELECT
                p.id_publicacion,
                p.id_usuario,
                p.titulo,
                p.descripcion,
                p.precio,
                p.tipo,
                p.modalidad,
                p.imagen,
                p.id_categoria,
                p.nivel_experiencia,
                p.duracion_horas,
                p.fecha_creacion,
                p.estado,
                COALESCE(
                    p.porcentaje_minimo_aprobacion,
                    70
                ) AS porcentaje_minimo_aprobacion,
                c.nombre_categoria,
                u.nombre AS autor_nombre,
                u.apellido AS autor_apellido,
                u.id_usuario AS autor_id,
                u.foto_perfil AS autor_foto
            FROM publicaciones p
            JOIN categorias c
                ON p.id_categoria = c.id_categoria
            JOIN usuarios u
                ON p.id_usuario = u.id_usuario
            WHERE
                p.id_publicacion = :id
                AND p.tipo = 'Curso'
            LIMIT 1
        ");

        $stmt->execute([
            'id' => $id_curso
        ]);

        $curso = $stmt->fetch();

        if ($curso) {
            // Registrar visita
            registrar_visita_publicacion(
                $pdo,
                $id_curso
            );

            /*
             * Verificar si el usuario actual contrató el curso
             * y dispone de un pago aprobado.
             */
            if ($usuario_actual) {
                $stmt_compra = $pdo->prepare("
                    SELECT
                        c.id_contratacion,
                        c.estado
                    FROM detalles_contratacion dc
                    JOIN contrataciones c
                        ON dc.id_contratacion = c.id_contratacion
                    JOIN pagos pag
                        ON pag.id_contratacion = c.id_contratacion
                        AND pag.estado_pago = 'Aprobado'
                    WHERE
                        c.id_usuario = :id_usuario
                        AND dc.id_publicacion = :id_publicacion
                        AND c.estado IN (
                            'Completada',
                            'En Proceso'
                        )
                    ORDER BY c.id_contratacion DESC
                    LIMIT 1
                ");

                $stmt_compra->execute([
                    'id_usuario' =>
                        (int) $usuario_actual['id_usuario'],
                    'id_publicacion' => $id_curso,
                ]);

                $contratacion_curso =
                    $stmt_compra->fetch() ?: null;

                $comprado =
                    !empty($contratacion_curso);


                /*
                 * Compatibilidad con flujo anterior de
                 * finalización manual del curso.
                 */
                if (
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                    && ($_POST['accion'] ?? '') === 'completar_curso'
                    && $contratacion_curso
                ) {
                    $token =
                        $_POST['csrf_token'] ?? '';

                    if (
                        hash_equals(
                            $_SESSION['csrf_token'] ?? '',
                            $token
                        )
                    ) {
                        $stmt_comp = $pdo->prepare("
                            UPDATE contrataciones
                            SET estado = 'Completada'
                            WHERE
                                id_contratacion = :id
                                AND id_usuario = :u
                        ");

                        $stmt_comp->execute([
                            'id' =>
                                (int) $contratacion_curso['id_contratacion'],
                            'u' =>
                                (int) $usuario_actual['id_usuario'],
                        ]);

                        $contratacion_curso['estado'] =
                            'Completada';

                        header(
                            "Location: curso.php?id="
                            . $id_curso
                            . "&completado=1"
                        );

                        exit;
                    }
                }


                /*
                 * ENTREGA DE TAREA
                 */
                if (
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                    && ($_POST['accion'] ?? '') === 'entregar_tarea'
                ) {
                    $token =
                        $_POST['csrf_token'] ?? '';

                    $id_recurso_tarea =
                        (int) ($_POST['id_recurso'] ?? 0);

                    $id_unidad_tarea =
                        (int) ($_POST['id_unidad'] ?? 0);

                    $comentario =
                        trim(
                            $_POST['comentario_entrega']
                            ?? ''
                        );

                    $textoEntrega =
                        trim(
                            $_POST['texto_entrega']
                            ?? ''
                        ) ?: null;

                    $enlaceEntrega =
                        trim(
                            $_POST['enlace_entrega']
                            ?? ''
                        ) ?: null;

                    try {
                        if (
                            !hash_equals(
                                $_SESSION['csrf_token'] ?? '',
                                $token
                            )
                        ) {
                            $_SESSION['curso_error'] =
                                'Token de seguridad inválido. Recarga la página e intenta nuevamente.';

                        } elseif (
                            ($curso['estado'] ?? '')
                            === 'Archivado'
                        ) {
                            $_SESSION['curso_error'] =
                                'Este curso se encuentra archivado y no admite nuevas entregas (RN-05).';

                        } elseif (
                            !$contratacion_curso
                        ) {
                            $_SESSION['curso_error'] =
                                'No tenés acceso para entregar en este curso.';

                        } else {
                            $contenidoRepo =
                                new ContenidoCursoRepository(
                                    $pdo
                                );

                            $recursoTarea =
                                $contenidoRepo
                                    ->obtenerRecurso(
                                        $id_recurso_tarea,
                                        $id_curso
                                    );

                            $tareaRepo =
                                new TareaRepository($pdo);

                            $tarea =
                                $recursoTarea
                                && (
                                    $recursoTarea['tipo']
                                    ?? ''
                                ) === 'Entrega de Tareas'
                                    ? $tareaRepo
                                        ->asegurarParaRecurso(
                                            $recursoTarea
                                        )
                                    : null;

                            $ahora =
                                new DateTimeImmutable();

                            if (!$tarea) {
                                $_SESSION['curso_error'] =
                                    'Recurso de tarea inválido.';

                            } elseif (
                                !$tarea->puedeEntregar(
                                    $ahora
                                )
                            ) {
                                $_SESSION['curso_error'] =
                                    'La tarea ya no acepta entregas.';

                            } elseif (
                                $enlaceEntrega
                                && !filter_var(
                                    $enlaceEntrega,
                                    FILTER_VALIDATE_URL
                                )
                            ) {
                                $_SESSION['curso_error'] =
                                    'El enlace de entrega no es válido.';

                            } elseif (
                                $enlaceEntrega
                                && !$tarea
                                    ->permiteTipoEntrega(
                                        'enlace'
                                    )
                            ) {
                                $_SESSION['curso_error'] =
                                    'Esta tarea no acepta entrega por enlace.';

                            } elseif (
                                $textoEntrega
                                && !$tarea
                                    ->permiteTipoEntrega(
                                        'texto'
                                    )
                            ) {
                                $_SESSION['curso_error'] =
                                    'Esta tarea no acepta texto en línea.';

                            } else {
                                $entregaRepo =
                                    new EntregaRepository(
                                        $pdo
                                    );

                                $entregaActual =
                                    $entregaRepo
                                        ->buscarPorTareaYUsuario(
                                            (int) $tarea->getId(),
                                            (int) $usuario_actual['id_usuario']
                                        );

                                $archivosActuales =
                                    $entregaActual
                                        ? $entregaRepo
                                            ->listarArchivosIncluyeLegacy(
                                                (int) $entregaActual->getId()
                                            )
                                        : [];

                                $archivos =
                                    normalizar_archivos_multiples(
                                        $_FILES,
                                        'archivo_entrega'
                                    );

                                $archivosFinales =
                                    $archivos
                                    ?: $archivosActuales;

                                if (
                                    $archivos
                                    && !$tarea
                                        ->permiteTipoEntrega(
                                            'archivos'
                                        )
                                ) {
                                    $_SESSION['curso_error'] =
                                        'Esta tarea no acepta archivos.';

                                } elseif (
                                    count(
                                        $archivosFinales
                                    )
                                    > $tarea->getMaxArchivos()
                                ) {
                                    $_SESSION['curso_error'] =
                                        'Superaste la cantidad máxima de archivos permitidos.';
                                }

                                $tiposFinales =
                                    array_values(
                                        array_filter([
                                            $archivosFinales
                                                ? 'archivos'
                                                : null,
                                            $textoEntrega
                                                ? 'texto'
                                                : null,
                                            $enlaceEntrega
                                                ? 'enlace'
                                                : null,
                                        ])
                                    );

                                $tiposRequeridos =
                                    $tarea
                                        ->tiposHabilitados();

                                if (
                                    empty(
                                        $_SESSION['curso_error']
                                    )
                                    && array_diff(
                                        $tiposFinales,
                                        $tiposRequeridos
                                    )
                                ) {
                                    $_SESSION['curso_error'] =
                                        'La entrega contiene tipos no habilitados para esta tarea.';
                                }

                                if (
                                    empty(
                                        $_SESSION['curso_error']
                                    )
                                    && (
                                        !$tiposFinales
                                        || (
                                            $tarea
                                                ->requiereTodosLosTipos()
                                            && array_diff(
                                                $tiposRequeridos,
                                                $tiposFinales
                                            )
                                        )
                                    )
                                ) {
                                    $_SESSION['curso_error'] =
                                        $tarea
                                            ->requiereTodosLosTipos()
                                            ? 'Esta tarea requiere completar todos los tipos de entrega habilitados.'
                                            : 'Completa al menos un tipo de entrega.';
                                }

                                if (
                                    empty(
                                        $_SESSION['curso_error']
                                    )
                                ) {
                                    $subidos = [];

                                    try {
                                        $archivosValidados =
                                            [];

                                        foreach (
                                            $archivos
                                            as $archivo
                                        ) {
                                            $val =
                                                validar_archivo_tarea(
                                                    $archivo,
                                                    $tarea
                                                );

                                            if (!$val['ok']) {
                                                throw new RuntimeException(
                                                    $val['error']
                                                );
                                            }

                                            $archivosValidados[] =
                                                [
                                                    $archivo,
                                                    $val
                                                ];
                                        }

                                        foreach (
                                            $archivosValidados
                                            as [$archivo, $val]
                                        ) {
                                            if (
                                                supabase_esta_activo()
                                            ) {
                                                $remotePath =
                                                    'entregas/tarea_'
                                                    . (int) $tarea->getId()
                                                    . '/usuario_'
                                                    . (int) $usuario_actual['id_usuario']
                                                    . '/'
                                                    . bin2hex(
                                                        random_bytes(8)
                                                    )
                                                    . '_'
                                                    . time()
                                                    . '.'
                                                    . $val['ext'];

                                                $subida =
                                                    supabase_subir_archivo(
                                                        $archivo['tmp_name'],
                                                        $remotePath,
                                                        $val['mime']
                                                    );

                                                if (
                                                    !$subida['ok']
                                                ) {
                                                    throw new RuntimeException(
                                                        'Error al subir archivo de entrega.'
                                                    );
                                                }

                                                $ruta =
                                                    'supabase:'
                                                    . $remotePath;

                                            } else {
                                                $res =
                                                    guardar_archivo_subido(
                                                        $archivo,
                                                        'entregas',
                                                        $tarea
                                                            ->getMaxTamanoMb()
                                                    );

                                                if (
                                                    !$res['ok']
                                                ) {
                                                    throw new RuntimeException(
                                                        $res['error']
                                                    );
                                                }

                                                $ruta =
                                                    $res['ruta'];
                                            }

                                            $subidos[] = [
                                                'nombre_original' =>
                                                    (string) $archivo['name'],
                                                'ruta' =>
                                                    $ruta,
                                                'mime_type' =>
                                                    $val['mime'],
                                                'extension' =>
                                                    $val['ext'],
                                                'tamano' =>
                                                    (int) $archivo['size'],
                                            ];
                                        }

                                        $resultado =
                                            $entregaRepo
                                                ->guardarPresentacion(
                                                    $tarea,
                                                    (int) $usuario_actual['id_usuario'],
                                                    $textoEntrega,
                                                    $enlaceEntrega,
                                                    $comentario
                                                        ?: null,
                                                    $subidos
                                                );

                                    } catch (
                                        Throwable $e
                                    ) {
                                        foreach (
                                            $subidos
                                            as $subido
                                        ) {
                                            eliminar_ruta_entrega(
                                                $subido['ruta']
                                            );
                                        }

                                        throw $e;
                                    }

                                    foreach (
                                        $resultado['archivos_reemplazados']
                                        as $reemplazado
                                    ) {
                                        eliminar_ruta_entrega(
                                            $reemplazado['ruta']
                                            ?? null
                                        );
                                    }

                                    set_toast(
                                        'success',
                                        $entregaActual
                                            ? 'Entrega actualizada'
                                            : 'Entrega enviada',
                                        $entregaActual
                                            ? 'Tu nueva entrega fue registrada correctamente.'
                                            : 'Tu entrega fue registrada correctamente.'
                                    );

                                    header(
                                        "Location: curso.php?id="
                                        . $id_curso
                                        . (
                                            $id_unidad_tarea
                                                ? "&unidad="
                                                    . $id_unidad_tarea
                                                : ""
                                        )
                                    );

                                    exit;
                                }
                            }
                        }

                    } catch (Throwable $e) {
                        error_log(
                            'Entrega tarea: '
                            . $e->getMessage()
                        );

                        $_SESSION['curso_error'] =
                            $e instanceof RuntimeException
                                ? $e->getMessage()
                                : 'No se pudo guardar la entrega.';
                    }
                }


                /*
                 * MARCAR RECURSO COMO COMPLETADO
                 */
                if (
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                    && ($_POST['accion'] ?? '')
                        === 'marcar_recurso_completado'
                ) {
                    $token =
                        $_POST['csrf_token'] ?? '';

                    $id_recurso =
                        (int) (
                            $_POST['id_recurso']
                            ?? 0
                        );

                    $id_unidad =
                        (int) (
                            $_POST['id_unidad']
                            ?? 0
                        );

                    $completado =
                        ($_POST['completado'] ?? '')
                        === '1';

                    if (
                        !hash_equals(
                            $_SESSION['csrf_token'] ?? '',
                            $token
                        )
                    ) {
                        $_SESSION['curso_error'] =
                            'Token de seguridad inválido. Recarga la página e intenta nuevamente.';

                    } elseif (
                        !$contratacion_curso
                    ) {
                        $_SESSION['curso_error'] =
                            'No tenés acceso para actualizar este recurso.';

                    } else {
                        $progresoRepo =
                            new CursoProgresoRepository(
                                $pdo
                            );

                        if (
                            $id_recurso > 0
                            && $progresoRepo
                                ->recursoPerteneceACurso(
                                    $id_recurso,
                                    $id_curso
                                )
                        ) {
                            $progresoRepo
                                ->marcarRecurso(
                                    (int) $usuario_actual['id_usuario'],
                                    $id_recurso,
                                    $completado
                                );

                            set_toast(
                                $completado
                                    ? 'success'
                                    : 'info',
                                $completado
                                    ? 'Recurso marcado como completado'
                                    : 'Recurso marcado como pendiente'
                            );

                        } else {
                            $_SESSION['curso_error'] =
                                'Recurso inválido.';
                        }
                    }

                    header(
                        "Location: curso.php?id="
                        . $id_curso
                        . (
                            $id_unidad
                                ? "&unidad="
                                    . $id_unidad
                                : ""
                        )
                        . (
                            $id_recurso
                                ? "#recurso-"
                                    . $id_recurso
                                : ""
                        )
                    );

                    exit;
                }


                /*
                 * PUBLICAR MENSAJE EN FORO
                 */
                if (
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                    && ($_POST['accion'] ?? '')
                        === 'publicar_mensaje_foro'
                ) {
                    $token =
                        $_POST['csrf_token'] ?? '';

                    $id_recurso_foro =
                        (int) (
                            $_POST['id_recurso']
                            ?? 0
                        );

                    $id_unidad_foro =
                        (int) (
                            $_POST['id_unidad']
                            ?? 0
                        );

                    $mensaje =
                        trim(
                            $_POST['mensaje_foro']
                            ?? ''
                        );

                    if (
                        !hash_equals(
                            $_SESSION['csrf_token'] ?? '',
                            $token
                        )
                    ) {
                        $_SESSION['curso_error'] =
                            'Token de seguridad inválido. Recarga la página e intenta nuevamente.';

                    } elseif (
                        ($curso['estado'] ?? '')
                        === 'Archivado'
                    ) {
                        $_SESSION['curso_error'] =
                            'Este curso se encuentra archivado y no admite nuevos comentarios en el foro (RN-05).';

                        header(
                            "Location: curso.php?id="
                            . $id_curso
                            . (
                                $id_unidad_foro
                                    ? "&unidad="
                                        . $id_unidad_foro
                                    : ""
                            )
                        );

                        exit;

                    } elseif (
                        $id_recurso_foro <= 0
                        || empty($mensaje)
                    ) {
                        $_SESSION['curso_error'] =
                            'El mensaje no puede estar vacío.';

                    } else {
                        $contenidoRepo =
                            new ContenidoCursoRepository(
                                $pdo
                            );

                        $recursoForo =
                            $contenidoRepo
                                ->obtenerRecurso(
                                    $id_recurso_foro,
                                    $id_curso
                                );

                        if (
                            !$recursoForo
                            || (
                                $recursoForo['tipo']
                                ?? ''
                            ) !== 'Foro'
                            || !$contratacion_curso
                        ) {
                            $_SESSION['curso_error'] =
                                'No tenés permisos para publicar en este foro.';

                            header(
                                "Location: curso.php?id="
                                . $id_curso
                                . (
                                    $id_unidad_foro
                                        ? "&unidad="
                                            . $id_unidad_foro
                                        : ""
                                )
                            );

                            exit;
                        }

                        $contenidoRepo
                            ->crearMensajeForo(
                                $id_recurso_foro,
                                (int) $usuario_actual['id_usuario'],
                                $mensaje
                            );

                        set_toast(
                            'success',
                            'Comentario publicado'
                        );

                        header(
                            "Location: curso.php?id="
                            . $id_curso
                            . (
                                $id_unidad_foro
                                    ? "&unidad="
                                        . $id_unidad_foro
                                    : ""
                            )
                        );

                        exit;
                    }
                }


                /*
                 * MODERAR / ELIMINAR MENSAJE DE FORO
                 */
                if (
                    $_SERVER['REQUEST_METHOD'] === 'POST'
                    && ($_POST['accion'] ?? '')
                        === 'eliminar_mensaje_foro'
                ) {
                    $token =
                        $_POST['csrf_token'] ?? '';

                    $id_msg =
                        (int) (
                            $_POST['id_mensaje']
                            ?? 0
                        );

                    $id_unidad_foro =
                        (int) (
                            $_POST['id_unidad']
                            ?? 0
                        );

                    if (
                        !hash_equals(
                            $_SESSION['csrf_token'] ?? '',
                            $token
                        )
                    ) {
                        $_SESSION['curso_error'] =
                            'Token de seguridad inválido.';

                    } elseif (
                        $id_msg <= 0
                    ) {
                        $_SESSION['curso_error'] =
                            'Mensaje no válido.';

                    } else {
                        $borrado =
                            (
                                new ContenidoCursoRepository(
                                    $pdo
                                )
                            )
                                ->eliminarMensajeForoAutorizado(
                                    $id_msg,
                                    $id_curso,
                                    (int) $usuario_actual['id_usuario'],
                                    es_admin(),
                                    (int) $usuario_actual['id_usuario']
                                        === (int) $curso['id_usuario']
                                );

                        if ($borrado) {
                            set_toast(
                                'success',
                                'Mensaje eliminado'
                            );

                        } else {
                            $_SESSION['curso_error'] =
                                'No tenés permisos para eliminar este mensaje.';
                        }

                        header(
                            "Location: curso.php?id="
                            . $id_curso
                            . (
                                $id_unidad_foro
                                    ? "&unidad="
                                        . $id_unidad_foro
                                    : ""
                            )
                        );

                        exit;
                    }
                }
            }


            /*
             * PERMISOS Y VISIBILIDAD DEL CURSO
             */
            $es_propietario =
                $usuario_actual
                && (
                    (int) $usuario_actual['id_usuario']
                    === (int) $curso['id_usuario']
                );

            $puede_gestionar_entregas =
                $usuario_actual
                && (
                    $es_propietario
                    || es_admin()
                );

            if (
                $curso['estado'] !== 'Activo'
                && !$comprado
                && !$es_propietario
                && !es_admin()
            ) {
                $curso = null;
            }

            if (!$curso) {
                return;
            }


            /*
             * CONTENIDO DEL CURSO
             *
             * Propietario/admin ve todo.
             * Alumno ve únicamente contenido visible.
             */
            $solo_visibles =
                !(
                    $es_propietario
                    || es_admin()
                );

            $contenido_curso =
                obtener_contenido_curso(
                    $pdo,
                    $id_curso,
                    $solo_visibles
                );

            $puede_ver_recursos =
                $comprado
                || $es_propietario
                || es_admin();


            /*
             * TAREAS, ENTREGAS, PROGRESO Y FORO
             */
            $mis_entregas = [];
            $mensajes_foro = [];

            if ($usuario_actual) {
                $contenidoRepo =
                    new ContenidoCursoRepository(
                        $pdo
                    );

                $entregaRepo =
                    new EntregaRepository(
                        $pdo
                    );

                $tareas_curso =
                    (
                        new TareaRepository(
                            $pdo
                        )
                    )->listarPorCurso(
                        $id_curso
                    );

                $mis_entregas =
                    $entregaRepo
                        ->listarEntregasUsuarioPorCurso(
                            $id_curso,
                            (int) $usuario_actual['id_usuario']
                        );


                /*
                 * Información docente/admin
                 */
                if ($puede_gestionar_entregas) {
                    $resumen_entregas_docente =
                        $entregaRepo
                            ->obtenerResumenEntregasCurso(
                                $id_curso
                            );

                    $entregas_docente_por_tarea =
                        $entregaRepo
                            ->listarEntregasCursoAgrupadas(
                                $id_curso
                            );
                }


                /*
                 * Información específica del alumno
                 */
                if ($comprado) {
                    $tareas_alumno_curso =
                        $entregaRepo
                            ->obtenerTareasAlumnoCurso(
                                $id_curso,
                                (int) $usuario_actual['id_usuario']
                            );

                    $progresoRepo =
                        new CursoProgresoRepository(
                            $pdo
                        );

                    $progreso_curso =
                        $progresoRepo
                            ->obtenerProgresoAlumnoCurso(
                                (int) $usuario_actual['id_usuario'],
                                $id_curso
                            );

                    $recursos_completados_ids =
                        $progresoRepo
                            ->listarRecursosCompletadosCurso(
                                (int) $usuario_actual['id_usuario'],
                                $id_curso
                            );
                }


                /*
                 * Archivos de entregas del usuario
                 */
                if ($mis_entregas) {
                    foreach (
                        $mis_entregas
                        as $ent
                    ) {
                        $archivos_entregas[
                            (int) $ent['id_entrega']
                        ] =
                            $entregaRepo
                                ->listarArchivosIncluyeLegacy(
                                    (int) $ent['id_entrega']
                                );
                    }
                }


                /*
                 * Mensajes del foro
                 */
                $mensajes_foro =
                    $contenidoRepo
                        ->listarMensajesForoPorCurso(
                            $id_curso
                        );
            }


            /*
             * RESEÑAS
             */
            $stmt_res = $pdo->prepare("
                SELECT
                    v.id_valoracion,
                    v.id_publicacion,
                    v.id_usuario,
                    v.puntuacion,
                    v.comentario,
                    v.fecha_valoracion,
                    u.nombre,
                    u.apellido,
                    u.foto_perfil
                FROM valoraciones v
                JOIN usuarios u
                    ON v.id_usuario = u.id_usuario
                WHERE
                    v.id_publicacion = :id
                ORDER BY
                    v.fecha_valoracion DESC
            ");

            $stmt_res->execute([
                'id' => $id_curso
            ]);

            $resenas =
                $stmt_res->fetchAll();

            $total_resenas =
                count($resenas);

            if ($total_resenas > 0) {
                $suma =
                    array_sum(
                        array_column(
                            $resenas,
                            'puntuacion'
                        )
                    );

                $promedio_calificacion =
                    round(
                        $suma
                        / $total_resenas,
                        1
                    );
            }


            /*
             * CURSOS RELACIONADOS
             */
            $stmt_rel = $pdo->prepare("
                SELECT
                    p.id_publicacion,
                    p.titulo,
                    p.descripcion,
                    p.precio,
                    p.tipo,
                    p.modalidad,
                    p.imagen,
                    c.nombre_categoria,
                    u.nombre AS autor_nombre,
                    u.apellido AS autor_apellido
                FROM publicaciones p
                JOIN categorias c
                    ON p.id_categoria = c.id_categoria
                JOIN usuarios u
                    ON p.id_usuario = u.id_usuario
                WHERE
                    p.tipo = 'Curso'
                    AND p.id_publicacion != :id
                    AND p.estado = 'Activo'
                ORDER BY
                    (
                        CASE
                            WHEN p.id_categoria = :id_categoria
                                THEN 1
                            ELSE 0
                        END
                    ) DESC,
                    p.fecha_creacion DESC
                LIMIT 3
            ");

            $stmt_rel->execute([
                'id' => $id_curso,
                'id_categoria' =>
                    (int) $curso['id_categoria']
            ]);

            $cursos_relacionados =
                $stmt_rel->fetchAll();
        }

    } catch (PDOException $e) {
        error_log(
            "Error al consultar detalle del curso: "
            . $e->getMessage()
        );

        $curso = null;
    }
}
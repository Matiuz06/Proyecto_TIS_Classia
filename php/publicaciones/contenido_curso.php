<?php

/**
 * Responsabilidad: Gestiona módulos, unidades y recursos asociados a cursos publicados.
 *
 * Arquitectura POO:
 *   Las clases de dominio (Modulo, Unidad, Recurso) y el repositorio
 *   (ContenidoCursoRepository) son los responsables de la lógica de negocio
 *   y del acceso a datos respectivamente.
 *
 *   Las funciones libres de este archivo son wrappers de compatibilidad que
 *   delegan al repositorio. Esto permite que las vistas existentes sigan
 *   funcionando sin cambios de firma.
 */

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';
require_once __DIR__ . '/../utils/supabase_storage.php';
require_once __DIR__ . '/ContenidoCursoRepository.php';   // incluye Modulo, Unidad, Recurso

//Instancia del repositorio

/**
 * Devuelve la instancia compartida del repositorio para el PDO global.
 * Evita instanciar el repositorio en cada llamada a las funciones wrapper.
 */
function _repo_contenido(PDO $pdo): ContenidoCursoRepository
{
    static $repo = null;
    if ($repo === null) {
        $repo = new ContenidoCursoRepository($pdo);
    }
    return $repo;
}

// WRAPPERS DE COMPATIBILIDAD
// Mantienen las firmas originales; delegan al repositorio y/o a las clases.

/**
 * Obtiene los datos del curso verificando que pertenezca al docente.
 * Devuelve null si no existe o no tiene permisos.
 *
 * @param PDO  $pdo
 * @param int  $id_publicacion
 * @param int  $id_usuario
 * @param bool $admin
 * @return array|null
 */
function obtener_curso_del_docente(PDO $pdo, int $id_publicacion, int $id_usuario, bool $admin = false): ?array
{
    return _repo_contenido($pdo)->obtenerCursoDelDocente($id_publicacion, $id_usuario, $admin);
}

/**
 * Devuelve el contenido completo de un curso como array multidimensional.
 * Compatible con las vistas que iteran $modulo['unidades'][$i]['recursos'][$j].
 *
 * Para trabajar con objetos, usar directamente:
 *   (new ContenidoCursoRepository($pdo))->obtenerPorCurso($id)
 *
 * @return array[]
 */
function obtener_contenido_curso(PDO $pdo, int $id_publicacion): array
{
    return _repo_contenido($pdo)->obtenerPorCursoComoArray($id_publicacion);
}

//HELPERS DE PRESENTACIÓN
//Delegados a la clase Recurso; mantenidos aquí por compatibilidad con vistas.

/**
 * Valida que una URL sea http/https.
 */
function url_recurso_valida(?string $url): bool
{
    if (!$url) return false;
    $p = parse_url($url);
    return is_array($p) && isset($p['scheme']) && in_array(strtolower($p['scheme']), ['http', 'https'], true);
}

/**
 * @deprecated Usar Recurso::getVideoEmbedUrl().
 */
function obtener_video_embed_url(?string $url): ?string
{
    if (!$url) return null;
    $r = Recurso::fromArray(['id_recurso' => 0, 'id_unidad' => 0, 'titulo' => '', 'tipo' => 'Video', 'url' => $url, 'archivo' => null, 'descripcion' => null, 'orden' => 0]);
    return $r->getVideoEmbedUrl();
}

/**
 * @deprecated Usar Recurso::getVideoEmbedUrl().
 */
function obtener_youtube_embed_url(?string $url): ?string
{
    return obtener_video_embed_url($url);
}

/** @deprecated Usar Recurso::TIPOS. */
function tipos_recurso_curso(): array
{
    return Recurso::TIPOS;
}

/**
 * @deprecated Usar (new Recurso(...))->getIcono() o instanciar desde array.
 */
function icono_recurso_curso(string $tipo): string
{
    $r = Recurso::fromArray(['id_recurso' => 0, 'id_unidad' => 0, 'titulo' => '', 'tipo' => $tipo, 'url' => null, 'archivo' => null, 'descripcion' => null, 'orden' => 0]);
    return $r->getIcono();
}

/**
 * @deprecated Usar Recurso->getEmojiIcono().
 */
function icono_emoji_recurso(string $tipo): string
{
    $r = Recurso::fromArray(['id_recurso' => 0, 'id_unidad' => 0, 'titulo' => '', 'tipo' => $tipo, 'url' => null, 'archivo' => null, 'descripcion' => null, 'orden' => 0]);
    return $r->getEmojiIcono();
}

function recurso_archivo_permite_tipo(string $tipo, string $nombre): bool
{
    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    if ($tipo === 'PDF')    return $ext === 'pdf';
    if ($tipo === 'Imagen') return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    if ($tipo === 'Video')  return in_array($ext, ['mp4', 'webm'], true);
    if (in_array($tipo, ['Archivo', 'Foro', 'Entrega de Tareas'], true)) {
        return in_array($ext, ['pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'stl', 'obj', '3mf'], true);
    }
    return true;
}

/**
 * @deprecated Usar Recurso->esVisualizableNativamente().
 */
function recurso_es_visualizable_nativamente(string $tipo, ?string $archivo = null): bool
{
    $r = Recurso::fromArray(['id_recurso' => 0, 'id_unidad' => 0, 'titulo' => '', 'tipo' => $tipo, 'url' => null, 'archivo' => $archivo, 'descripcion' => null, 'orden' => 0]);
    return $r->esVisualizableNativamente();
}

/**
 * @deprecated Usar ContenidoCursoRepository->intercambiarOrden().
 */
function intercambiar_orden(PDO $pdo, string $tabla, string $id_col, int $id, string $scope_col, int $scope_id, string $direccion): bool
{
    return _repo_contenido($pdo)->intercambiarOrden($tabla, $id_col, $id, $scope_col, $scope_id, $direccion);
}

//PROCESAMIENTO DE ACCIONES (POST)
//Delega la lógica de escritura al repositorio; mantiene la firma original.

function procesar_contenido_curso(PDO $pdo, array $post, array $files, int $id_publicacion, int $id_usuario, bool $admin, string $csrf): array
{
    if ($csrf === '' || !hash_equals($csrf, $post['csrf_token'] ?? '')) {
        return ['ok' => false, 'mensaje' => 'La sesión del formulario expiró.'];
    }

    $repo = _repo_contenido($pdo);

    if (!$repo->obtenerCursoDelDocente($id_publicacion, $id_usuario, $admin)) {
        return ['ok' => false, 'mensaje' => 'No tenés permisos para gestionar este curso.'];
    }

    $a = $post['accion'] ?? '';

    try {
        //Módulos
        if ($a === 'agregar_modulo') {
            $t = trim($post['titulo_modulo'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'El módulo necesita un título.'];
            $repo->crearModulo($id_publicacion, $t, trim($post['descripcion_modulo'] ?? '') ?: null, (int)($post['orden'] ?? 1));

        } elseif ($a === 'editar_modulo') {
            $t = trim($post['titulo'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'El módulo necesita un título.'];
            $repo->editarModulo((int)$post['id_modulo'], $id_publicacion, $t, trim($post['descripcion'] ?? '') ?: null, (int)($post['orden'] ?? 1));

        } elseif ($a === 'eliminar_modulo') {
            $repo->eliminarModulo((int)$post['id_modulo'], $id_publicacion);

        } elseif (in_array($a, ['subir_modulo', 'bajar_modulo'], true)) {
            $repo->intercambiarOrden('curso_modulos', 'id_modulo', (int)$post['id_modulo'], 'id_publicacion', $id_publicacion, $a === 'subir_modulo' ? 'subir' : 'bajar');

        //Unidades
        } elseif ($a === 'agregar_unidad') {
            $m = (int)($post['id_modulo'] ?? 0);
            if (!$repo->verificarModuloPerteneceACurso($m, $id_publicacion)) {
                return ['ok' => false, 'mensaje' => 'El módulo no pertenece a este curso.'];
            }
            $t = trim($post['titulo_unidad'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'La clase necesita un título.'];
            $repo->crearUnidad($m, $t, trim($post['descripcion_unidad'] ?? '') ?: null, (int)($post['orden'] ?? 1));

        } elseif ($a === 'editar_unidad') {
            $t = trim($post['titulo'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'La clase necesita un título.'];
            $repo->editarUnidad((int)$post['id_unidad'], $id_publicacion, $t, trim($post['descripcion'] ?? '') ?: null, (int)($post['orden'] ?? 1));

        } elseif ($a === 'eliminar_unidad') {
            $repo->eliminarUnidad((int)$post['id_unidad'], $id_publicacion);

        } elseif (in_array($a, ['subir_unidad', 'bajar_unidad'], true)) {
            $mid = $repo->obtenerModuloDeLaUnidad((int)$post['id_unidad'], $id_publicacion);
            if (!$mid) return ['ok' => false, 'mensaje' => 'La clase no pertenece a este curso.'];
            $repo->intercambiarOrden('curso_unidades', 'id_unidad', (int)$post['id_unidad'], 'id_modulo', $mid, $a === 'subir_unidad' ? 'subir' : 'bajar');

        //Recursos
        } elseif (in_array($a, ['agregar_recurso', 'editar_recurso'], true)) {
            $rid    = (int)($post['id_recurso'] ?? 0);
            $unidad = (int)($post['id_unidad'] ?? 0);
            $actual = null;

            if ($a === 'editar_recurso') {
                $actual = $repo->obtenerRecurso($rid, $id_publicacion);
                if (!$actual) return ['ok' => false, 'mensaje' => 'Recurso inexistente o sin permisos.'];
                $unidad = (int)$actual['id_unidad'];
            }

            if (!$repo->verificarUnidadPerteneceACurso($unidad, $id_publicacion)) {
                return ['ok' => false, 'mensaje' => 'La clase no pertenece a este curso.'];
            }

            $tipo = $post['tipo_recurso'] ?? ($actual['tipo'] ?? 'Enlace');
            if (!in_array($tipo, Recurso::TIPOS, true)) {
                return ['ok' => false, 'mensaje' => 'Tipo de recurso no válido.'];
            }

            $url = trim($post['url_recurso'] ?? '') ?: null;
            if ($url && !url_recurso_valida($url)) {
                return ['ok' => false, 'mensaje' => 'La URL debe comenzar con http:// o https://.'];
            }

            $archivo = $actual['archivo'] ?? null;
            $nuevo   = null;

            // Subida de archivo
            if ($tipo !== 'Enlace' && isset($files['archivo_recurso']) && ($files['archivo_recurso']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (supabase_esta_activo()) {
                    $val = supabase_validar_archivo_subido($files['archivo_recurso'], $tipo, 50);
                    if (!$val['ok']) return ['ok' => false, 'mensaje' => $val['error']];
                    $remotePath = 'cursos/' . $id_publicacion . '/' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $val['ext'];
                    $subida     = supabase_subir_archivo($files['archivo_recurso']['tmp_name'], $remotePath, $val['mime']);
                    if (!$subida['ok']) return ['ok' => false, 'mensaje' => 'Error al subir a la nube (Supabase): ' . $subida['error']];
                    $nuevo = $archivo = 'supabase:' . $remotePath;
                } else {
                    if (!recurso_archivo_permite_tipo($tipo, (string)($files['archivo_recurso']['name'] ?? ''))) {
                        return ['ok' => false, 'mensaje' => 'El archivo no coincide con el tipo de recurso seleccionado.'];
                    }
                    $res = guardar_archivo_subido($files['archivo_recurso'], 'cursos', 50);
                    if (!$res['ok']) return ['ok' => false, 'mensaje' => $res['error']];
                    $nuevo = $archivo = $res['ruta'];
                }
            }

            // Exclusión mutua URL / archivo
            if ($nuevo !== null) {
                $url = null;
            } elseif ($url !== null && !in_array($tipo, ['Foro', 'Entrega de Tareas'], true)) {
                if (!empty($actual['archivo'])) {
                    _eliminar_archivo_storage($actual['archivo']);
                }
                $archivo = null;
            }

            // Validaciones de tipo
            if ($tipo === 'Enlace') {
                $archivo = null;
                if (!$url) return ['ok' => false, 'mensaje' => 'Indicá una URL http/https válida para el enlace.'];
            }
            if ($tipo === 'Video' && !$url && !$archivo) {
                return ['ok' => false, 'mensaje' => 'Indicá el enlace del video (YouTube/Vimeo) o subí un archivo de video.'];
            }
            if ($tipo === 'Archivo' && !$archivo && !$url) {
                return ['ok' => false, 'mensaje' => 'Subí un archivo o indicá un enlace al material.'];
            }
            if (in_array($tipo, ['Foro', 'Entrega de Tareas'], true)) {
                $desc = trim($post['descripcion_recurso'] ?? '');
                if (!$desc && !$url && !$archivo) {
                    return ['ok' => false, 'mensaje' => 'Indicá una consigna o descripción para ' . strtolower($tipo) . '.'];
                }
            }
            if (!in_array($tipo, ['Enlace', 'Foro', 'Entrega de Tareas'], true) && !$archivo && !$url) {
                return ['ok' => false, 'mensaje' => 'Subí un archivo o indicá una URL.'];
            }

            $data = [
                'u'   => $unidad,
                't'   => trim($post['titulo_recurso'] ?? '') ?: 'Recurso',
                'tipo'=> $tipo,
                'url' => $url,
                'a'   => $archivo,
                'd'   => trim($post['descripcion_recurso'] ?? '') ?: null,
                'o'   => max(1, (int)($post['orden'] ?? 1)),
            ];

            if ($a === 'agregar_recurso') {
                $repo->crearRecurso($data);
            } else {
                $repo->editarRecurso($rid, $data);
                // Limpiar archivo anterior si se reemplazó
                if ($nuevo && !empty($actual['archivo']) && $actual['archivo'] !== $nuevo) {
                    _eliminar_archivo_storage($actual['archivo']);
                }
                if ($tipo === 'Enlace' && !empty($actual['archivo'])) {
                    _eliminar_archivo_storage($actual['archivo']);
                }
            }

        } elseif ($a === 'eliminar_recurso') {
            $ruta = $repo->eliminarRecurso((int)$post['id_recurso'], $id_publicacion);
            if ($ruta) _eliminar_archivo_storage($ruta);

        } elseif (in_array($a, ['subir_recurso', 'bajar_recurso'], true)) {
            $uid = $repo->obtenerUnidadDelRecurso((int)$post['id_recurso'], $id_publicacion);
            if (!$uid) return ['ok' => false, 'mensaje' => 'El recurso no pertenece a este curso.'];
            $repo->intercambiarOrden('curso_recursos', 'id_recurso', (int)$post['id_recurso'], 'id_unidad', $uid, $a === 'subir_recurso' ? 'subir' : 'bajar');

        } else {
            return ['ok' => false, 'mensaje' => 'Acción no reconocida.'];
        }

        return ['ok' => true, 'mensaje' => 'Contenido actualizado correctamente.'];

    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        error_log('Contenido curso: ' . $e->getMessage());
        return ['ok' => false, 'mensaje' => 'No se pudo actualizar el contenido del curso.'];
    }
}

//UTILIDAD INTERNA — eliminación de archivos del storage

/**
 * Elimina un archivo del storage (Supabase o local) según el prefijo de ruta.
 * Uso interno del módulo; no exponer como API pública.
 */
function _eliminar_archivo_storage(string $ruta): void
{
    if (str_starts_with($ruta, 'supabase:')) {
        supabase_eliminar_archivo(substr($ruta, 9));
    } else {
        eliminar_archivo_guardado($ruta);
    }
}

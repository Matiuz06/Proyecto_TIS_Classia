<?php
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';
require_once __DIR__ . '/../utils/supabase_storage.php';

function obtener_curso_del_docente(PDO $pdo, int $id_publicacion, int $id_usuario, bool $admin = false): ?array
{
    $sql = "SELECT p.*,c.nombre_categoria FROM publicaciones p JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.id_publicacion=:id AND p.tipo='Curso'";
    $params = ['id' => $id_publicacion];
    if (!$admin) {
        $sql .= " AND p.id_usuario=:u";
        $params['u'] = $id_usuario;
    }
    $s = $pdo->prepare($sql);
    $s->execute($params);
    return $s->fetch() ?: null;
}

function obtener_contenido_curso(PDO $pdo, int $id_publicacion): array
{
    // 1. Obtener los modulos del curso
    $s = $pdo->prepare("SELECT * FROM curso_modulos WHERE id_publicacion = :id ORDER BY orden, id_modulo");
    $s->execute(['id' => $id_publicacion]);
    $mods = $s->fetchAll();

    if (empty($mods)) {
        return [];
    }

    // 2. Obtener todas las unidades de los modulos del curso en una sola consulta
    $s_unidades = $pdo->prepare("
        SELECT u.* 
        FROM curso_unidades u 
        JOIN curso_modulos m ON m.id_modulo = u.id_modulo 
        WHERE m.id_publicacion = :id 
        ORDER BY u.orden, u.id_unidad
    ");
    $s_unidades->execute(['id' => $id_publicacion]);
    $todas_unidades = $s_unidades->fetchAll();

    // 3. Obtener todos los recursos de las unidades en una sola consulta
    $s_recursos = $pdo->prepare("
        SELECT r.* 
        FROM curso_recursos r 
        JOIN curso_unidades u ON u.id_unidad = r.id_unidad 
        JOIN curso_modulos m ON m.id_modulo = u.id_modulo 
        WHERE m.id_publicacion = :id 
        ORDER BY r.orden, r.id_recurso
    ");
    $s_recursos->execute(['id' => $id_publicacion]);
    $todos_recursos = $s_recursos->fetchAll();

    // 4. Agrupar recursos por unidad
    $recursos_por_unidad = [];
    foreach ($todos_recursos as $rec) {
        $id_u = (int) $rec['id_unidad'];
        $recursos_por_unidad[$id_u][] = $rec;
    }

    // 5. Agrupar unidades por modulo y asociar recursos
    $unidades_por_modulo = [];
    foreach ($todas_unidades as $uni) {
        $id_u = (int) $uni['id_unidad'];
        $id_m = (int) $uni['id_modulo'];
        $uni['recursos'] = $recursos_por_unidad[$id_u] ?? [];
        $unidades_por_modulo[$id_m][] = $uni;
    }

    // 6. Asociar unidades a cada modulo
    foreach ($mods as &$m) {
        $id_m = (int) $m['id_modulo'];
        $m['unidades'] = $unidades_por_modulo[$id_m] ?? [];
    }
    unset($m);

    return $mods;
}

function url_recurso_valida(?string $url): bool
{
    if (!$url) return false;
    $p = parse_url($url);
    return is_array($p) && isset($p['scheme']) && in_array(strtolower($p['scheme']), ['http', 'https'], true);
}

function obtener_video_embed_url(?string $url): ?string
{
    if (!$url) return null;
    $url = trim($url);
    // YouTube
    if (preg_match('#(?:youtube\.com/(?:watch\?v=|embed/|v/|shorts/)|youtu\.be/)([a-zA-Z0-9_-]{11})#i', $url, $matches)) {
        return 'https://www.youtube-nocookie.com/embed/' . $matches[1] . '?rel=0&modestbranding=1';
    }
    // Vimeo
    if (preg_match('#(?:vimeo\.com/(?:video/)?|player\.vimeo\.com/video/)([0-9]+)#i', $url, $matches)) {
        return 'https://player.vimeo.com/video/' . $matches[1];
    }
    // Dailymotion
    if (preg_match('#(?:dailymotion\.com/(?:video/|embed/video/)|dai\.ly/)([a-zA-Z0-9]+)#i', $url, $matches)) {
        return 'https://www.dailymotion.com/embed/video/' . $matches[1];
    }
    // Loom
    if (preg_match('#(?:loom\.com/share/|loom\.com/embed/)([a-zA-Z0-9]+)#i', $url, $matches)) {
        return 'https://www.loom.com/embed/' . $matches[1];
    }
    return null;
}

function obtener_youtube_embed_url(?string $url): ?string
{
    return obtener_video_embed_url($url);
}

function tipos_recurso_curso(): array
{
    return ['Archivo', 'Foro', 'Entrega de Tareas', 'Video', 'PDF', 'Imagen', 'Enlace'];
}

function icono_recurso_curso(string $tipo): string
{
    return [
        'Archivo'           => 'DOC',
        'Foro'              => 'FORO',
        'Entrega de Tareas' => 'TAREA',
        'Video'             => 'VID',
        'PDF'               => 'PDF',
        'Imagen'            => 'IMG',
        'Enlace'            => 'URL',
    ][$tipo] ?? 'REC';
}

function icono_emoji_recurso(string $tipo): string
{
    return match($tipo) {
        'Video'             => '🎥',
        'PDF'               => '📄',
        'Entrega de Tareas' => '📝',
        'Foro'              => '💬',
        'Imagen'            => '🖼️',
        'Enlace'            => '🔗',
        default             => '📥'
    };
}

function recurso_archivo_permite_tipo(string $tipo, string $nombre): bool
{
    $ext = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));
    if ($tipo === 'PDF') return $ext === 'pdf';
    if ($tipo === 'Imagen') return in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true);
    if ($tipo === 'Video') return in_array($ext, ['mp4', 'webm'], true);
    if (in_array($tipo, ['Archivo', 'Foro', 'Entrega de Tareas'], true)) {
        return in_array($ext, ['pdf', 'zip', 'rar', '7z', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt', 'csv', 'jpg', 'jpeg', 'png', 'webp', 'gif', 'mp4', 'webm', 'stl', 'obj', '3mf'], true);
    }
    return true;
}

function recurso_es_visualizable_nativamente(string $tipo, ?string $archivo = null): bool
{
    if ($tipo === 'PDF' || $tipo === 'Imagen') {
        return true;
    }
    if ($archivo) {
        $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        return in_array($ext, ['pdf', 'png', 'jpg', 'jpeg', 'webp', 'gif', 'svg', 'txt'], true);
    }
    return false;
}

function intercambiar_orden(PDO $pdo, string $tabla, string $id_col, int $id, string $scope_col, int $scope_id, string $direccion): bool
{
    if (!in_array($tabla, ['curso_modulos', 'curso_unidades', 'curso_recursos'], true) || !in_array($direccion, ['subir', 'bajar'], true)) {
        return false;
    }

    $s = $pdo->prepare("SELECT $id_col,orden FROM $tabla WHERE $id_col=:id AND $scope_col=:scope");
    $s->execute(['id' => $id, 'scope' => $scope_id]);
    $actual = $s->fetch();
    if (!$actual) return false;

    $op = $direccion === 'subir' ? '<' : '>';
    $ord = $direccion === 'subir' ? 'DESC' : 'ASC';
    $n = $pdo->prepare("SELECT $id_col,orden FROM $tabla WHERE $scope_col=:scope AND orden $op :orden ORDER BY orden $ord,$id_col $ord LIMIT 1");
    $n->execute(['scope' => $scope_id, 'orden' => (int)$actual['orden']]);
    $vecino = $n->fetch();
    if (!$vecino) return true;

    $pdo->beginTransaction();
    $u = $pdo->prepare("UPDATE $tabla SET orden=:orden WHERE $id_col=:id");
    $u->execute(['orden' => (int)$vecino['orden'], 'id' => $id]);
    $u->execute(['orden' => (int)$actual['orden'], 'id' => (int)$vecino[$id_col]]);
    return $pdo->commit();
}

function procesar_contenido_curso(PDO $pdo, array $post, array $files, int $id_publicacion, int $id_usuario, bool $admin, string $csrf): array
{
    if ($csrf === '' || !hash_equals($csrf, $post['csrf_token'] ?? '')) return ['ok' => false, 'mensaje' => 'La sesión del formulario expiró.'];
    if (!obtener_curso_del_docente($pdo, $id_publicacion, $id_usuario, $admin)) return ['ok' => false, 'mensaje' => 'No tenés permisos para gestionar este curso.'];
    $a = $post['accion'] ?? '';

    try {
        if ($a === 'agregar_modulo') {
            $t = trim($post['titulo_modulo'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'El módulo necesita un título.'];
            $q = $pdo->prepare("INSERT INTO curso_modulos(id_publicacion,titulo,descripcion,orden) VALUES(:p,:t,:d,:o)");
            $q->execute(['p' => $id_publicacion, 't' => $t, 'd' => trim($post['descripcion_modulo'] ?? '') ?: null, 'o' => max(1, (int)($post['orden'] ?? 1))]);
        } elseif ($a === 'editar_modulo') {
            $t = trim($post['titulo'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'El módulo necesita un título.'];
            $q = $pdo->prepare("UPDATE curso_modulos SET titulo=:t,descripcion=:d,orden=:o WHERE id_modulo=:m AND id_publicacion=:p");
            $q->execute(['t' => $t, 'd' => trim($post['descripcion'] ?? '') ?: null, 'o' => max(1, (int)($post['orden'] ?? 1)), 'm' => (int)$post['id_modulo'], 'p' => $id_publicacion]);
        } elseif ($a === 'eliminar_modulo') {
            $q = $pdo->prepare("DELETE FROM curso_modulos WHERE id_modulo=:m AND id_publicacion=:p");
            $q->execute(['m' => (int)$post['id_modulo'], 'p' => $id_publicacion]);
        } elseif (in_array($a, ['subir_modulo', 'bajar_modulo'], true)) {
            intercambiar_orden($pdo, 'curso_modulos', 'id_modulo', (int)$post['id_modulo'], 'id_publicacion', $id_publicacion, $a === 'subir_modulo' ? 'subir' : 'bajar');
        } elseif ($a === 'agregar_unidad') {
            $m = (int)($post['id_modulo'] ?? 0);
            $c = $pdo->prepare("SELECT COUNT(*) FROM curso_modulos WHERE id_modulo=:m AND id_publicacion=:p");
            $c->execute(['m' => $m, 'p' => $id_publicacion]);
            if (!$c->fetchColumn()) return ['ok' => false, 'mensaje' => 'El módulo no pertenece a este curso.'];
            $t = trim($post['titulo_unidad'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'La clase necesita un título.'];
            $q = $pdo->prepare("INSERT INTO curso_unidades(id_modulo,titulo,descripcion,orden) VALUES(:m,:t,:d,:o)");
            $q->execute(['m' => $m, 't' => $t, 'd' => trim($post['descripcion_unidad'] ?? '') ?: null, 'o' => max(1, (int)($post['orden'] ?? 1))]);
        } elseif ($a === 'editar_unidad') {
            $t = trim($post['titulo'] ?? '');
            if ($t === '') return ['ok' => false, 'mensaje' => 'La clase necesita un título.'];
            $q = $pdo->prepare("UPDATE curso_unidades SET titulo=:t, descripcion=:d, orden=:o WHERE id_unidad=:u AND id_modulo IN (SELECT id_modulo FROM curso_modulos WHERE id_publicacion=:p)");
            $q->execute(['t' => $t, 'd' => trim($post['descripcion'] ?? '') ?: null, 'o' => max(1, (int)($post['orden'] ?? 1)), 'u' => (int)$post['id_unidad'], 'p' => $id_publicacion]);
        } elseif ($a === 'eliminar_unidad') {
            $q = $pdo->prepare("DELETE FROM curso_unidades WHERE id_unidad=:u AND id_modulo IN (SELECT id_modulo FROM curso_modulos WHERE id_publicacion=:p)");
            $q->execute(['u' => (int)$post['id_unidad'], 'p' => $id_publicacion]);
        } elseif (in_array($a, ['subir_unidad', 'bajar_unidad'], true)) {
            $q = $pdo->prepare("SELECT u.id_modulo FROM curso_unidades u JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE u.id_unidad=:u AND m.id_publicacion=:p");
            $q->execute(['u' => (int)$post['id_unidad'], 'p' => $id_publicacion]);
            $mid = (int)$q->fetchColumn();
            if (!$mid) return ['ok' => false, 'mensaje' => 'La clase no pertenece a este curso.'];
            intercambiar_orden($pdo, 'curso_unidades', 'id_unidad', (int)$post['id_unidad'], 'id_modulo', $mid, $a === 'subir_unidad' ? 'subir' : 'bajar');
        } elseif (in_array($a, ['agregar_recurso', 'editar_recurso'], true)) {
            $rid = (int)($post['id_recurso'] ?? 0);
            $unidad = (int)($post['id_unidad'] ?? 0);
            $actual = null;
            if ($a === 'editar_recurso') {
                $q = $pdo->prepare("SELECT r.* FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE r.id_recurso=:r AND m.id_publicacion=:p");
                $q->execute(['r' => $rid, 'p' => $id_publicacion]);
                $actual = $q->fetch();
                if (!$actual) return ['ok' => false, 'mensaje' => 'Recurso inexistente o sin permisos.'];
                $unidad = (int)$actual['id_unidad'];
            }
            $c = $pdo->prepare("SELECT COUNT(*) FROM curso_unidades u JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE u.id_unidad=:u AND m.id_publicacion=:p");
            $c->execute(['u' => $unidad, 'p' => $id_publicacion]);
            if (!$c->fetchColumn()) return ['ok' => false, 'mensaje' => 'La clase no pertenece a este curso.'];

            $tipo = $post['tipo_recurso'] ?? ($actual['tipo'] ?? 'Enlace');
            if (!in_array($tipo, tipos_recurso_curso(), true)) return ['ok' => false, 'mensaje' => 'Tipo de recurso no válido.'];
            $url = trim($post['url_recurso'] ?? '') ?: null;
            if ($url && !url_recurso_valida($url)) return ['ok' => false, 'mensaje' => 'La URL debe comenzar con http:// o https://.'];

            $archivo = $actual['archivo'] ?? null;
            $nuevo = null;
            if ($tipo !== 'Enlace' && isset($files['archivo_recurso']) && ($files['archivo_recurso']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
                if (supabase_esta_activo()) {
                    $val = supabase_validar_archivo_subido($files['archivo_recurso'], $tipo, 50);
                    if (!$val['ok']) {
                        return ['ok' => false, 'mensaje' => $val['error']];
                    }
                    $ext = $val['ext'];
                    $mime = $val['mime'];
                    $remotePath = 'cursos/' . $id_publicacion . '/' . bin2hex(random_bytes(8)) . '_' . time() . '.' . $ext;
                    $subida = supabase_subir_archivo($files['archivo_recurso']['tmp_name'], $remotePath, $mime);
                    if (!$subida['ok']) {
                        return ['ok' => false, 'mensaje' => 'Error al subir a la nube (Supabase): ' . $subida['error']];
                    }
                    $nuevo = 'supabase:' . $remotePath;
                    $archivo = $nuevo;
                } else {
                    if (!recurso_archivo_permite_tipo($tipo, (string)($files['archivo_recurso']['name'] ?? ''))) {
                        return ['ok' => false, 'mensaje' => 'El archivo no coincide con el tipo de recurso seleccionado.'];
                    }
                    $res = guardar_archivo_subido($files['archivo_recurso'], 'cursos', 50);
                    if (!$res['ok']) return ['ok' => false, 'mensaje' => $res['error']];
                    $nuevo = $res['ruta'];
                    $archivo = $nuevo;
                }
            }
            // Exclusión mutua: si se sube un nuevo archivo se anula la URL; si se especifica URL y no se sube archivo, se elimina el archivo previo
            if ($nuevo !== null) {
                $url = null;
            } elseif ($url !== null && $tipo !== 'Foro' && $tipo !== 'Entrega de Tareas') {
                if (!empty($actual['archivo'])) {
                    if (str_starts_with($actual['archivo'], 'supabase:')) {
                        supabase_eliminar_archivo(substr($actual['archivo'], 9));
                    } else {
                        eliminar_archivo_guardado($actual['archivo']);
                    }
                }
                $archivo = null;
            }

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

            $data = ['u' => $unidad, 't' => trim($post['titulo_recurso'] ?? '') ?: 'Recurso', 'tipo' => $tipo, 'url' => $url, 'a' => $archivo, 'd' => trim($post['descripcion_recurso'] ?? '') ?: null, 'o' => max(1, (int)($post['orden'] ?? 1))];
            if ($a === 'agregar_recurso') {
                $q = $pdo->prepare("INSERT INTO curso_recursos(id_unidad,titulo,tipo,url,archivo,descripcion,orden) VALUES(:u,:t,:tipo,:url,:a,:d,:o)");
                $q->execute($data);
            } else {
                $data['r'] = $rid;
                $q = $pdo->prepare("UPDATE curso_recursos SET id_unidad=:u,titulo=:t,tipo=:tipo,url=:url,archivo=:a,descripcion=:d,orden=:o WHERE id_recurso=:r");
                $q->execute($data);
                if ($nuevo && !empty($actual['archivo']) && $actual['archivo'] !== $nuevo) {
                    if (str_starts_with($actual['archivo'], 'supabase:')) {
                        supabase_eliminar_archivo(substr($actual['archivo'], 9));
                    } else {
                        eliminar_archivo_guardado($actual['archivo']);
                    }
                }
                if ($tipo === 'Enlace' && !empty($actual['archivo'])) {
                    if (str_starts_with($actual['archivo'], 'supabase:')) {
                        supabase_eliminar_archivo(substr($actual['archivo'], 9));
                    } else {
                        eliminar_archivo_guardado($actual['archivo']);
                    }
                }
            }
        } elseif ($a === 'eliminar_recurso') {
            $q = $pdo->prepare("SELECT r.archivo FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE r.id_recurso=:r AND m.id_publicacion=:p");
            $q->execute(['r' => (int)$post['id_recurso'], 'p' => $id_publicacion]);
            $ruta = $q->fetchColumn();
            $d = $pdo->prepare("DELETE FROM curso_recursos WHERE id_recurso=:r AND id_unidad IN (SELECT u.id_unidad FROM curso_unidades u JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE m.id_publicacion=:p)");
            $d->execute(['r' => (int)$post['id_recurso'], 'p' => $id_publicacion]);
            if ($ruta) {
                if (str_starts_with($ruta, 'supabase:')) {
                    supabase_eliminar_archivo(substr($ruta, 9));
                } else {
                    eliminar_archivo_guardado($ruta);
                }
            }
        } elseif (in_array($a, ['subir_recurso', 'bajar_recurso'], true)) {
            $q = $pdo->prepare("SELECT r.id_unidad FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE r.id_recurso=:r AND m.id_publicacion=:p");
            $q->execute(['r' => (int)$post['id_recurso'], 'p' => $id_publicacion]);
            $uid = (int)$q->fetchColumn();
            if (!$uid) return ['ok' => false, 'mensaje' => 'El recurso no pertenece a este curso.'];
            intercambiar_orden($pdo, 'curso_recursos', 'id_recurso', (int)$post['id_recurso'], 'id_unidad', $uid, $a === 'subir_recurso' ? 'subir' : 'bajar');
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

<?php

/**
 * Responsabilidad: Gestiona solicitudes personalizadas de servicios entre usuarios y proveedores.
 */
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/plantillas_servicio.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';

function obtener_servicio_activo(PDO $pdo, int $id): ?array
{
    $s = $pdo->prepare("SELECT p.*, u.nombre autor_nombre, u.apellido autor_apellido, c.nombre_categoria FROM publicaciones p JOIN usuarios u ON u.id_usuario=p.id_usuario JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.id_publicacion=:id AND p.tipo='Servicio' AND p.estado='Activo'");
    $s->execute(['id' => $id]);
    return $s->fetch() ?: null;
}

function mapear_campos_formulario_solicitud(string $tipo, array $post, array $files): array
{
    $contacto = [
        'nombre_contacto' => trim($post['nombre'] ?? ''),
        'correo_contacto' => trim($post['correo'] ?? ''),
        'telefono'        => trim($post['telefono'] ?? ''),
        'medio_contacto'  => trim($post['medio-contacto'] ?? ''),
    ];

    $campos_especificos = [];
    $archivoKey = null;

    switch ($tipo) {
        case 'impresion_3d':
            $campos_especificos = [
                'nombre_proyecto'   => trim($post['titulo-proyecto-3d'] ?? ''),
                'tipo_trabajo'      => trim($post['tipo-trabajo-3d'] ?? ''),
                'cantidad'          => trim($post['cantidad-piezas'] ?? ''),
                'material'          => trim($post['material-preferido'] ?? ''),
                'dimensiones'       => trim($post['medidas'] ?? ''),
                'color'             => trim($post['color-preferido'] ?? ''),
                'boceto'            => !empty($files['archivo-3d']['name']) ? 'Sí' : (trim($post['boceto'] ?? 'No')),
                'descripcion_pieza' => trim($post['descripcion-3d'] ?? ''),
                'uso_previsto'      => trim($post['uso-pieza'] ?? ''),
                'fecha_necesaria'   => trim($post['fecha-entrega-3d'] ?? ''),
                'presupuesto'       => trim($post['presupuesto-3d'] ?? ''),
            ];
            $archivoKey = 'archivo-3d';
            break;

        case 'mentoria':
            $campos_especificos = [
                'tema'                  => trim($post['tema-mentoria'] ?? ''),
                'dificultades'          => trim($post['descripcion-mentoria'] ?? ''),
                'conocimientos_previos' => trim($post['nivel-conocimiento'] ?? ''),
                'objetivo'              => trim($post['comentarios-generales'] ?? '') ?: trim($post['descripcion-mentoria'] ?? ''),
                'proyecto'              => trim($post['descripcion-mentoria'] ?? ''),
                'modalidad_preferida'   => trim($post['modalidad-mentoria'] ?? ''),
                'fecha_preferida'       => trim($post['fecha-mentoria'] ?? ''),
                'segunda_fecha'         => trim($post['segunda-fecha-mentoria'] ?? ''),
                'horario_preferido'     => trim($post['hora-mentoria'] ?? ''),
                'duracion'              => trim($post['duracion-mentoria'] ?? ''),
                'presupuesto'           => trim($post['presupuesto-mentoria'] ?? ''),
            ];
            $archivoKey = 'archivo-mentoria';
            break;

        case 'proyecto_educativo':
            $campos_especificos = [
                'institucion'            => trim($post['nombre-institucion-proyecto'] ?? ''),
                'tipo_institucion'       => trim($post['tipo-institucion-proyecto'] ?? ''),
                'nivel_educativo'        => trim($post['nivel-estudiantes'] ?? ''),
                'tematicas'              => trim($post['tema-proyecto'] ?? ''),
                'necesidad'              => trim($post['necesidad-proyecto'] ?? ''),
                'objetivos'              => trim($post['resultado-proyecto'] ?? ''),
                'destinatarios'          => trim(($post['nivel-estudiantes'] ?? '') . ' ' . ($post['edad-estudiantes'] ?? '')),
                'cantidad_participantes' => trim($post['cantidad-estudiantes'] ?? ''),
                'duracion_estimada'      => trim($post['duracion-proyecto'] ?? ''),
                'modalidad_proyecto'     => trim($post['modalidad-proyecto'] ?? ''),
                'recursos_disponibles'   => trim($post['recursos-disponibles'] ?? ''),
                'presupuesto'            => trim($post['presupuesto-proyecto'] ?? ''),
            ];
            $archivoKey = 'archivo-proyecto';
            break;

        case 'formacion_institucional':
            $campos_especificos = [
                'organizacion'           => trim($post['nombre-organizacion'] ?? ''),
                'tipo_organizacion'      => trim($post['tipo-organizacion'] ?? ''),
                'rubro'                  => trim($post['rubro-organizacion'] ?? ''),
                'cantidad_participantes' => trim($post['cantidad-participantes'] ?? ''),
                'perfil_participantes'   => trim($post['perfil-participantes'] ?? ''),
                'tematica'               => trim($post['tema-formacion'] ?? ''),
                'objetivo'               => trim($post['objetivo-formacion'] ?? ''),
                'modalidad_preferida'    => trim($post['modalidad-formacion'] ?? ''),
                'cantidad_jornadas'      => trim($post['cantidad-jornadas'] ?? ''),
                'certificacion'          => trim($post['certificacion'] ?? ''),
                'disponibilidad'         => trim(($post['fecha-formacion'] ?? '') . ' ' . ($post['duracion-jornada'] ?? '') . ' ' . ($post['ubicacion-formacion'] ?? '')),
                'presupuesto'            => trim($post['presupuesto-formacion'] ?? ''),
            ];
            break;

        case 'robotica_automatizacion':
            $campos_especificos = [
                'nombre_proyecto' => trim($post['nombre-proyecto-robotica'] ?? ''),
                'idea'            => trim($post['problema-robotica'] ?? ''),
                'objetivo'        => trim($post['resultado-robotica'] ?? ''),
                'nivel_avance'    => trim($post['nivel-avance'] ?? ''),
                'componentes'     => trim($post['tecnologia-disponible'] ?? ''),
                'tecnologias'     => trim($post['tipo-servicio-robotica'] ?? ''),
                'entorno'         => trim($post['modalidad-robotica'] ?? ''),
                'restricciones'   => trim($post['comentarios-generales'] ?? ''),
                'fecha_objetivo'  => trim($post['fecha-robotica'] ?? ''),
                'presupuesto'     => trim($post['presupuesto-robotica'] ?? ''),
            ];
            $archivoKey = 'archivo-robotica';
            break;
    }

    return [
        'detalles'            => array_merge($contacto, $campos_especificos),
        'descripcion_general' => trim($post['comentarios-generales'] ?? ''),
        'archivo_adjunto'     => ($archivoKey && isset($files[$archivoKey])) ? $files[$archivoKey] : ($files['archivo_adjunto'] ?? null),
    ];
}

function procesar_envio_solicitud_servicio(PDO $pdo, int $uid, ?array $servicio, array $post, array $files, string $csrf): array
{
    if (!$servicio) {
        return ['ok' => false, 'mensaje' => 'El servicio solicitado no es válido o no está activo.'];
    }

    $tipo = (string)($servicio['tipo_servicio'] ?? '');
    $datos_solicitud = mapear_campos_formulario_solicitud($tipo, $post, $files);

    $postData = $post;
    $postData['campo'] = $datos_solicitud['detalles'];
    $postData['descripcion_general'] = $datos_solicitud['descripcion_general'];

    $filesData = $files;
    if ($datos_solicitud['archivo_adjunto'] !== null) {
        $filesData['archivo_adjunto'] = $datos_solicitud['archivo_adjunto'];
    }

    return crear_solicitud_servicio($pdo, $uid, $servicio, $postData, $filesData, $csrf);
}

function crear_solicitud_servicio(PDO $pdo, int $uid, array $servicio, array $post, array $files, string $csrf): array
{
    if ($csrf === '' || !hash_equals($csrf, $post['csrf_token'] ?? '')) {
        return ['ok' => false, 'mensaje' => 'La sesión del formulario expiró.'];
    }
    if ((int)$servicio['id_usuario'] === $uid) {
        return ['ok' => false, 'mensaje' => 'No podés solicitar tu propio servicio.'];
    }
    $plantilla = obtener_plantilla_servicio($servicio['tipo_servicio'] ?? null);
    if (!$plantilla) {
        return ['ok' => false, 'mensaje' => 'Este servicio no tiene una plantilla configurada.'];
    }
    $detalles = $post['campo'] ?? [];
    foreach ($plantilla['campos'] as $clave => $campo) {
        $valor = trim((string)($detalles[$clave] ?? ''));
        if (($campo['required'] ?? false) && $valor === '') {
            return ['ok' => false, 'mensaje' => 'Completá el campo: ' . $campo['label'] . '.'];
        }
        $detalles[$clave] = $valor;
    }
    $descripcion = trim($post['descripcion_general'] ?? '') ?: 'Solicitud de ' . $plantilla['nombre'];
    $archivo = null;
    if (isset($files['archivo_adjunto']) && ($files['archivo_adjunto']['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        $r = guardar_archivo_subido($files['archivo_adjunto'], 'solicitudes', 30);
        if (!$r['ok']) {
            return ['ok' => false, 'mensaje' => $r['error']];
        }
        $archivo = $r['ruta'];
    }
    try {
        $q = $pdo->prepare("INSERT INTO solicitudes(titulo, descripcion, estado, detalles_json, archivo_adjunto, precio_propuesto, id_usuario, id_publicacion) VALUES(:t, :d, 'Pendiente', :j, :a, NULL, :u, :p)");
        $q->execute([
            't' => 'Solicitud: ' . $servicio['titulo'],
            'd' => $descripcion,
            'j' => json_encode($detalles, JSON_UNESCAPED_UNICODE),
            'a' => $archivo,
            'u' => $uid,
            'p' => $servicio['id_publicacion']
        ]);
        return ['ok' => true, 'mensaje' => 'Solicitud enviada correctamente.', 'id' => (int)$pdo->lastInsertId()];
    } catch (PDOException $e) {
        error_log('Crear solicitud: ' . $e->getMessage());
        if ($archivo) {
            eliminar_archivo_guardado($archivo);
        }
        return ['ok' => false, 'mensaje' => 'No se pudo enviar la solicitud.'];
    }
}
function obtener_solicitudes_proveedor(PDO $pdo, int $docente, ?string $estado = null): array
{
    $sql = "SELECT s.*, p.titulo AS servicio, p.tipo_servicio, u.nombre, u.apellido, u.email 
            FROM solicitudes s 
            JOIN publicaciones p ON p.id_publicacion=s.id_publicacion 
            JOIN usuarios u ON u.id_usuario=s.id_usuario 
            WHERE p.id_usuario=:d AND p.tipo='Servicio'";
    $pa = ['d' => $docente];
    if ($estado) {
        $sql .= " AND s.estado=:e";
        $pa['e'] = $estado;
    }
    $sql .= " ORDER BY s.fecha_actualizacion DESC, s.fecha_solicitud DESC";
    $q = $pdo->prepare($sql);
    $q->execute($pa);
    return $q->fetchAll();
}

function obtener_solicitud_para_proveedor(PDO $pdo, int $id, int $docente): ?array
{
    $q = $pdo->prepare("SELECT s.*, p.titulo AS servicio, p.tipo_servicio, p.precio AS precio_publicado, u.nombre, u.apellido, u.email 
                        FROM solicitudes s 
                        JOIN publicaciones p ON p.id_publicacion=s.id_publicacion 
                        JOIN usuarios u ON u.id_usuario=s.id_usuario 
                        WHERE s.id_solicitud=:s AND p.id_usuario=:d AND p.tipo='Servicio'");
    $q->execute(['s' => $id, 'd' => $docente]);
    return $q->fetch() ?: null;
}

function transicion_proveedor_valida(string $actual, string $nuevo): bool
{
    $transiciones_permitidas = [
        'Pendiente'    => ['Aceptada', 'Contraoferta', 'Rechazada'],
        'Contraoferta' => ['Contraoferta', 'Rechazada'],
        'Aceptada'     => ['Contraoferta', 'Rechazada'],
        'En Proceso'   => ['Realizada'],
    ];
    return in_array($nuevo, $transiciones_permitidas[$actual] ?? [], true);
}

function procesar_solicitud_proveedor(PDO $pdo, int $id, int $docente, int $uid, array $post, string $csrf): array
{
    if ($csrf === '' || !hash_equals($csrf, $post['csrf_token'] ?? '')) {
        return ['ok' => false, 'mensaje' => 'La sesión del formulario expiró.'];
    }
    $sol = obtener_solicitud_para_proveedor($pdo, $id, $docente);
    if (!$sol) {
        return ['ok' => false, 'mensaje' => 'Solicitud inexistente o sin permisos.'];
    }
    $accion = $post['accion'] ?? '';

    try {
        if ($accion === 'mensaje') {
            $msg = trim($post['mensaje'] ?? '');
            if ($msg === '') {
                return ['ok' => false, 'mensaje' => 'Escribí un mensaje.'];
            }
            $q = $pdo->prepare("INSERT INTO solicitud_mensajes(id_solicitud, id_usuario, mensaje) VALUES(:s, :u, :m)");
            $q->execute(['s' => $id, 'u' => $uid, 'm' => $msg]);
            return ['ok' => true, 'mensaje' => 'Mensaje enviado.'];
        }

        $acciones_a_estados = [
            'aceptar'      => 'Aceptada',
            'rechazar'     => 'Rechazada',
            'contraoferta' => 'Contraoferta',
            'realizada'    => 'Realizada'
        ];

        if (!isset($acciones_a_estados[$accion])) {
            return ['ok' => false, 'mensaje' => 'Acción no válida.'];
        }

        if (!empty($sol['id_contratacion']) && in_array($accion, ['aceptar', 'rechazar', 'contraoferta'], true)) {
            return ['ok' => false, 'mensaje' => 'La propuesta ya generó una contratación y no admite nuevas contraofertas ni rechazo.'];
        }

        $nuevo_estado = $acciones_a_estados[$accion];
        if (!transicion_proveedor_valida($sol['estado'], $nuevo_estado)) {
            return ['ok' => false, 'mensaje' => 'Ese cambio de estado no es válido desde ' . $sol['estado'] . '.'];
        }

        $precio = trim($post['precio_propuesto'] ?? '');
        $precioFinal = ($precio !== '' && is_numeric($precio)) ? (float)$precio : null;
        if (in_array($nuevo_estado, ['Aceptada', 'Contraoferta'], true) && $precioFinal === null) {
            $precioFinal = (float)$sol['precio_publicado'];
        }

        $fecha = trim($post['fecha_hora_propuesta'] ?? '');
        $resp = trim($post['respuesta_proveedor'] ?? '');

        $pdo->beginTransaction();
        $q = $pdo->prepare("UPDATE solicitudes SET estado=:e, precio_propuesto=:p, fecha_hora_propuesta=:f, respuesta_proveedor=:r WHERE id_solicitud=:s");
        $q->execute([
            'e' => $nuevo_estado,
            'p' => $precioFinal,
            'f' => $fecha !== '' ? str_replace('T', ' ', $fecha) . ':00' : null,
            'r' => $resp ?: null,
            's' => $id
        ]);

        if ($resp !== '') {
            $m = $pdo->prepare("INSERT INTO solicitud_mensajes(id_solicitud, id_usuario, mensaje) VALUES(:s, :u, :m)");
            $m->execute(['s' => $id, 'u' => $uid, 'm' => $resp]);
        }

        if ($nuevo_estado === 'Realizada' && !empty($sol['id_contratacion'])) {
            $c = $pdo->prepare("UPDATE contrataciones SET estado='Completada' WHERE id_contratacion=:c");
            $c->execute(['c' => $sol['id_contratacion']]);
        }

        $pdo->commit();
        return ['ok' => true, 'mensaje' => 'Solicitud actualizada a ' . $nuevo_estado . '.'];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Gestionar solicitud: ' . $e->getMessage());
        return ['ok' => false, 'mensaje' => 'No se pudo actualizar la solicitud.'];
    }
}

function obtener_mensajes_solicitud(PDO $pdo, int $id): array
{
    $q = $pdo->prepare("SELECT sm.*, u.nombre, u.apellido FROM solicitud_mensajes sm JOIN usuarios u ON u.id_usuario=sm.id_usuario WHERE sm.id_solicitud=:s ORDER BY sm.fecha_mensaje");
    $q->execute(['s' => $id]);
    return $q->fetchAll();
}

function obtener_solicitudes_cliente(PDO $pdo, int $uid): array
{
    $q = $pdo->prepare("SELECT s.*, p.titulo AS servicio, p.tipo_servicio, u.nombre AS proveedor_nombre, u.apellido AS proveedor_apellido 
                        FROM solicitudes s 
                        JOIN publicaciones p ON p.id_publicacion=s.id_publicacion 
                        JOIN usuarios u ON u.id_usuario=p.id_usuario 
                        WHERE s.id_usuario=:u 
                        ORDER BY s.fecha_actualizacion DESC, s.fecha_solicitud DESC");
    $q->execute(['u' => $uid]);
    return $q->fetchAll();
}

function obtener_solicitud_para_cliente(PDO $pdo, int $id, int $uid): ?array
{
    $q = $pdo->prepare("SELECT s.*, p.titulo AS servicio, p.tipo_servicio, p.precio AS precio_publicado, u.nombre AS proveedor_nombre, u.apellido AS proveedor_apellido 
                        FROM solicitudes s 
                        JOIN publicaciones p ON p.id_publicacion=s.id_publicacion 
                        JOIN usuarios u ON u.id_usuario=p.id_usuario 
                        WHERE s.id_solicitud=:s AND s.id_usuario=:u");
    $q->execute(['s' => $id, 'u' => $uid]);
    return $q->fetch() ?: null;
}

function enviar_mensaje_solicitud_cliente(PDO $pdo, int $id, int $uid, string $mensaje, string $token, string $csrf): array
{
    if ($csrf === '' || !hash_equals($csrf, $token)) {
        return ['ok' => false, 'mensaje' => 'La sesión del formulario expiró.'];
    }
    if (!obtener_solicitud_para_cliente($pdo, $id, $uid)) {
        return ['ok' => false, 'mensaje' => 'Solicitud inexistente o sin permisos.'];
    }
    $mensaje = trim($mensaje);
    if ($mensaje === '') {
        return ['ok' => false, 'mensaje' => 'Escribí un mensaje.'];
    }
    try {
        $q = $pdo->prepare("INSERT INTO solicitud_mensajes(id_solicitud, id_usuario, mensaje) VALUES(:s, :u, :m)");
        $q->execute(['s' => $id, 'u' => $uid, 'm' => $mensaje]);
        return ['ok' => true, 'mensaje' => 'Mensaje enviado.'];
    } catch (PDOException $e) {
        return ['ok' => false, 'mensaje' => 'No se pudo enviar el mensaje.'];
    }
}

function accion_cliente_solicitud(PDO $pdo, int $id, int $uid, string $accion, string $csrf, string $token): array
{
    if ($csrf === '' || !hash_equals($csrf, $token)) {
        return ['ok' => false, 'mensaje' => 'La sesión del formulario expiró.'];
    }
    $s = obtener_solicitud_para_cliente($pdo, $id, $uid);
    if (!$s) {
        return ['ok' => false, 'mensaje' => 'Solicitud inexistente o sin permisos.'];
    }
    if ($accion === 'cancelar') {
        if (!in_array($s['estado'], ['Pendiente', 'Aceptada', 'Contraoferta'], true)) {
            return ['ok' => false, 'mensaje' => 'La solicitud ya no puede cancelarse.'];
        }
        try {
            $pdo->beginTransaction();
            if (!empty($s['id_contratacion'])) {
                $c = $pdo->prepare("SELECT estado FROM contrataciones WHERE id_contratacion=:c AND id_usuario=:u LIMIT 1");
                $c->execute(['c' => $s['id_contratacion'], 'u' => $uid]);
                $estadoContrato = $c->fetchColumn();
                $pg = $pdo->prepare("SELECT COUNT(*) FROM pagos WHERE id_contratacion=:c AND estado_pago='Aprobado'");
                $pg->execute(['c' => $s['id_contratacion']]);
                if ($estadoContrato !== 'Pendiente' || (int)$pg->fetchColumn() > 0) {
                    $pdo->rollBack();
                    return ['ok' => false, 'mensaje' => 'La contratación ya fue procesada y no puede cancelarse desde la solicitud.'];
                }
                $pdo->prepare("UPDATE contrataciones SET estado='Cancelada' WHERE id_contratacion=:c")->execute(['c' => $s['id_contratacion']]);
            }
            $pdo->prepare("UPDATE solicitudes SET estado='Cancelada' WHERE id_solicitud=:s")->execute(['s' => $id]);
            $pdo->commit();
            return ['ok' => true, 'mensaje' => 'Solicitud cancelada.'];
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Cancelar solicitud: ' . $e->getMessage());
            return ['ok' => false, 'mensaje' => 'No se pudo cancelar la solicitud.'];
        }
    }
    if ($accion !== 'aceptar_propuesta' || !in_array($s['estado'], ['Aceptada', 'Contraoferta'], true)) {
        return ['ok' => false, 'mensaje' => 'La propuesta no puede aceptarse en su estado actual.'];
    }
    if (!empty($s['id_contratacion'])) {
        return ['ok' => true, 'mensaje' => 'La contratación ya fue creada.', 'id_contratacion' => (int)$s['id_contratacion']];
    }
    $precio = $s['precio_propuesto'] !== null ? (float)$s['precio_propuesto'] : (float)$s['precio_publicado'];
    if ($precio <= 0) {
        return ['ok' => false, 'mensaje' => 'La propuesta no tiene un precio válido.'];
    }
    try {
        $pdo->beginTransaction();
        $q = $pdo->prepare("INSERT INTO contrataciones(monto_total, estado, id_usuario) VALUES(:m, 'Pendiente', :u)");
        $q->execute(['m' => $precio, 'u' => $uid]);
        $cid = (int)$pdo->lastInsertId();
        $d = $pdo->prepare("INSERT INTO detalles_contratacion(cantidad, precio_unitario, subtotal, id_contratacion, id_publicacion) VALUES(1, :p, :p, :c, :pub)");
        $d->execute(['p' => $precio, 'c' => $cid, 'pub' => $s['id_publicacion']]);
        $u = $pdo->prepare("UPDATE solicitudes SET estado='Aceptada', id_contratacion=:c WHERE id_solicitud=:s");
        $u->execute(['c' => $cid, 's' => $id]);
        $pdo->commit();
        return ['ok' => true, 'mensaje' => 'Propuesta aceptada. Continuá con el pago.', 'id_contratacion' => $cid];
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        error_log('Aceptar propuesta: ' . $e->getMessage());
        return ['ok' => false, 'mensaje' => 'No se pudo generar la contratación.'];
    }
}

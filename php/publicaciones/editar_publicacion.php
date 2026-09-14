<?php

require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/upload_helper.php';
require_once __DIR__ . '/publicacion_helpers.php';

requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], '../../views/usuario.php');

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

$errores=[];
$usuario=usuario_actual();
$uid=(int)($usuario['id_usuario'] ?? 0);
$admin=es_admin();
$categorias=obtener_categorias($pdo);
$plantillas_servicios=plantillas_servicio();
$id_publicacion=(int)($_GET['id'] ?? $_POST['id_publicacion'] ?? 0);

$sql="SELECT * FROM publicaciones WHERE id_publicacion=:id" . ($admin ? '' : ' AND id_usuario=:u');
$stmt=$pdo->prepare($sql);
$params=['id'=>$id_publicacion];
if(!$admin) $params['u']=$uid;
$stmt->execute($params);
$publicacion=$stmt->fetch() ?: null;
if (!$publicacion) $errores[]='No tenés permisos para modificar esta publicación o no existe.';

if ($_SERVER['REQUEST_METHOD']==='POST' && $publicacion) {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        $errores[]='La sesión del formulario expiró.';
    }

    if (isset($_POST['cambiar_estado']) && empty($errores)) {
        $estado=$_POST['cambiar_estado'];
        if (!in_array($estado,['Activo','Pausado','Inactivo','Eliminado'],true)) {
            $errores[]='Estado no válido.';
        } else {
            $q=$pdo->prepare("UPDATE publicaciones SET estado=:estado, eliminado_en=:eliminado WHERE id_publicacion=:id" . ($admin?'':' AND id_usuario=:u'));
            $pp=['estado'=>$estado,'eliminado'=>$estado==='Eliminado'?date('Y-m-d H:i:s'):null,'id'=>$id_publicacion];
            if(!$admin) $pp['u']=$uid;
            $q->execute($pp);
            header('Location: panel-proveedor.php?mensaje=estado_actualizado');
            exit;
        }
    } elseif (empty($errores)) {
        $datos=$_POST;
        $datos['tipo']=$publicacion['tipo'];
        $errores=array_merge($errores,validar_datos_publicacion($datos));

        $estado=$_POST['estado'] ?? $publicacion['estado'];
        if (!in_array($estado,['Activo','Pausado','Inactivo','Eliminado'],true)) $errores[]='Estado no válido.';

        $categoria=['ok'=>false,'id'=>0,'error'=>''];
        if (empty($errores)) {
            try {
                $categoria=resolver_categoria_publicacion($pdo,$uid,(int)($_POST['id_categoria'] ?? 0),$_POST['nueva_categoria'] ?? '',$_POST['descripcion_categoria'] ?? '');
                if(!$categoria['ok']) $errores[]=$categoria['error'];
            } catch(PDOException $e) {
                error_log('Categoría publicación: '.$e->getMessage());
                $errores[]='No se pudo procesar la categoría.';
            }
        }

        $rutaAnterior=$publicacion['imagen'];
        $rutaNueva=$rutaAnterior;
        $archivoNuevo=null;
        if (empty($errores) && isset($_FILES['imagen']) && ($_FILES['imagen']['error'] ?? UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE) {
            $res=guardar_imagen_subida($_FILES['imagen'],'publicaciones',5);
            if($res['ok']) { $archivoNuevo=$res['ruta']; $rutaNueva=$archivoNuevo; }
            else $errores[]=$res['error'];
        } elseif (!empty($_POST['eliminar_imagen'])) {
            $rutaNueva=null;
        }

        if (empty($errores)) {
            try {
                $q=$pdo->prepare("UPDATE publicaciones SET
                    titulo=:titulo, descripcion=:descripcion, precio=:precio, tipo=:tipo,
                    modalidad=:modalidad, nivel_experiencia=:nivel, duracion_horas=:duracion,
                    cupos=:cupos, disponibilidad=:disponibilidad, tipo_servicio=:tipo_servicio,
                    id_categoria=:categoria, estado=:estado, imagen=:imagen, eliminado_en=:eliminado
                    WHERE id_publicacion=:id" . ($admin?'':' AND id_usuario=:u'));
                $pp=[
                    'titulo'=>trim($_POST['titulo']), 'descripcion'=>trim($_POST['descripcion']), 'precio'=>(float)$_POST['precio'],
                    'tipo'=>$publicacion['tipo'], 'modalidad'=>trim($_POST['modalidad'] ?? '') ?: null,
                    'nivel'=>trim($_POST['nivel_experiencia'] ?? '') ?: null,
                    'duracion'=>trim($_POST['duracion_horas'] ?? '') !== '' ? (int)$_POST['duracion_horas'] : null,
                    'cupos'=>trim($_POST['cupos'] ?? '') !== '' ? (int)$_POST['cupos'] : null,
                    'disponibilidad'=>trim($_POST['disponibilidad'] ?? '') ?: null,
                    'tipo_servicio'=>$publicacion['tipo']==='Servicio' ? (trim($_POST['tipo_servicio'] ?? '') ?: null) : null,
                    'categoria'=>$categoria['id'], 'estado'=>$estado, 'imagen'=>$rutaNueva,
                    'eliminado'=>$estado==='Eliminado' ? ($publicacion['eliminado_en'] ?: date('Y-m-d H:i:s')) : null,
                    'id'=>$id_publicacion,
                ];
                if(!$admin) $pp['u']=$uid;
                $q->execute($pp);
                if ($rutaAnterior && $rutaAnterior!==$rutaNueva) eliminar_imagen_subida($rutaAnterior);
                header('Location: panel-proveedor.php?mensaje=actualizada');
                exit;
            } catch(PDOException $e) {
                error_log('Editar publicación: '.$e->getMessage());
                if($archivoNuevo) eliminar_imagen_subida($archivoNuevo);
                $errores[]='No se pudo actualizar la publicación.';
            }
        }
    }
}

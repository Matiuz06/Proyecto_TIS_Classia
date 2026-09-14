<?php
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';

function obtener_curso_del_docente(PDO $pdo, int $id_publicacion, int $id_usuario, bool $admin=false): ?array {
    $sql="SELECT p.*,c.nombre_categoria FROM publicaciones p JOIN categorias c ON c.id_categoria=p.id_categoria WHERE p.id_publicacion=:id AND p.tipo='Curso'";
    $params=['id'=>$id_publicacion]; if(!$admin){$sql.=" AND p.id_usuario=:u";$params['u']=$id_usuario;}
    $s=$pdo->prepare($sql);$s->execute($params);return $s->fetch()?:null;
}
function obtener_contenido_curso(PDO $pdo,int $id_publicacion): array {
    $s=$pdo->prepare("SELECT * FROM curso_modulos WHERE id_publicacion=:id ORDER BY orden,id_modulo");$s->execute(['id'=>$id_publicacion]);$mods=$s->fetchAll();
    foreach($mods as &$m){$u=$pdo->prepare("SELECT * FROM curso_unidades WHERE id_modulo=:id ORDER BY orden,id_unidad");$u->execute(['id'=>$m['id_modulo']]);$uns=$u->fetchAll();foreach($uns as &$un){$r=$pdo->prepare("SELECT * FROM curso_recursos WHERE id_unidad=:id ORDER BY orden,id_recurso");$r->execute(['id'=>$un['id_unidad']]);$un['recursos']=$r->fetchAll();}unset($un);$m['unidades']=$uns;}unset($m);return $mods;
}
function url_recurso_valida(?string $url): bool { if(!$url)return false; $p=parse_url($url); return is_array($p)&&isset($p['scheme'])&&in_array(strtolower($p['scheme']),['http','https'],true); }
function procesar_contenido_curso(PDO $pdo,array $post,array $files,int $id_publicacion,int $id_usuario,bool $admin,string $csrf): array {
    if($csrf===''||!hash_equals($csrf,$post['csrf_token']??''))return['ok'=>false,'mensaje'=>'La sesión del formulario expiró.'];
    if(!obtener_curso_del_docente($pdo,$id_publicacion,$id_usuario,$admin))return['ok'=>false,'mensaje'=>'No tenés permisos para gestionar este curso.'];
    $a=$post['accion']??'';
    try{
        if($a==='agregar_modulo'){
            $t=trim($post['titulo_modulo']??'');if($t==='')return['ok'=>false,'mensaje'=>'El módulo necesita un título.'];$q=$pdo->prepare("INSERT INTO curso_modulos(id_publicacion,titulo,descripcion,orden) VALUES(:p,:t,:d,:o)");$q->execute(['p'=>$id_publicacion,'t'=>$t,'d'=>trim($post['descripcion_modulo']??'')?:null,'o'=>max(1,(int)($post['orden']??1))]);
        }elseif($a==='editar_modulo'){
            $t=trim($post['titulo']??'');if($t==='')return['ok'=>false,'mensaje'=>'El módulo necesita un título.'];$q=$pdo->prepare("UPDATE curso_modulos SET titulo=:t,descripcion=:d,orden=:o WHERE id_modulo=:m AND id_publicacion=:p");$q->execute(['t'=>$t,'d'=>trim($post['descripcion']??'')?:null,'o'=>max(1,(int)($post['orden']??1)),'m'=>(int)$post['id_modulo'],'p'=>$id_publicacion]);
        }elseif($a==='eliminar_modulo'){$q=$pdo->prepare("DELETE FROM curso_modulos WHERE id_modulo=:m AND id_publicacion=:p");$q->execute(['m'=>(int)$post['id_modulo'],'p'=>$id_publicacion]);
        }elseif($a==='agregar_unidad'){
            $m=(int)($post['id_modulo']??0);$c=$pdo->prepare("SELECT COUNT(*) FROM curso_modulos WHERE id_modulo=:m AND id_publicacion=:p");$c->execute(['m'=>$m,'p'=>$id_publicacion]);if(!$c->fetchColumn())return['ok'=>false,'mensaje'=>'El módulo no pertenece a este curso.'];$t=trim($post['titulo_unidad']??'');if($t==='')return['ok'=>false,'mensaje'=>'La unidad necesita un título.'];$q=$pdo->prepare("INSERT INTO curso_unidades(id_modulo,titulo,descripcion,orden) VALUES(:m,:t,:d,:o)");$q->execute(['m'=>$m,'t'=>$t,'d'=>trim($post['descripcion_unidad']??'')?:null,'o'=>max(1,(int)($post['orden']??1))]);
        }elseif($a==='editar_unidad'){
            $t=trim($post['titulo']??'');if($t==='')return['ok'=>false,'mensaje'=>'La unidad necesita un título.'];$q=$pdo->prepare("UPDATE curso_unidades u JOIN curso_modulos m ON m.id_modulo=u.id_modulo SET u.titulo=:t,u.descripcion=:d,u.orden=:o WHERE u.id_unidad=:u AND m.id_publicacion=:p");$q->execute(['t'=>$t,'d'=>trim($post['descripcion']??'')?:null,'o'=>max(1,(int)($post['orden']??1)),'u'=>(int)$post['id_unidad'],'p'=>$id_publicacion]);
        }elseif($a==='eliminar_unidad'){$q=$pdo->prepare("DELETE u FROM curso_unidades u JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE u.id_unidad=:u AND m.id_publicacion=:p");$q->execute(['u'=>(int)$post['id_unidad'],'p'=>$id_publicacion]);
        }elseif(in_array($a,['agregar_recurso','editar_recurso'],true)){
            $rid=(int)($post['id_recurso']??0);$unidad=(int)($post['id_unidad']??0);$actual=null;
            if($a==='editar_recurso'){$q=$pdo->prepare("SELECT r.* FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE r.id_recurso=:r AND m.id_publicacion=:p");$q->execute(['r'=>$rid,'p'=>$id_publicacion]);$actual=$q->fetch();if(!$actual)return['ok'=>false,'mensaje'=>'Recurso inexistente o sin permisos.'];$unidad=(int)$actual['id_unidad'];}
            $c=$pdo->prepare("SELECT COUNT(*) FROM curso_unidades u JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE u.id_unidad=:u AND m.id_publicacion=:p");$c->execute(['u'=>$unidad,'p'=>$id_publicacion]);if(!$c->fetchColumn())return['ok'=>false,'mensaje'=>'La unidad no pertenece a este curso.'];
            $tipo=$post['tipo_recurso']??($actual['tipo']??'Enlace');if(!in_array($tipo,['Archivo','PDF','Imagen','Video','Enlace'],true))return['ok'=>false,'mensaje'=>'Tipo de recurso no válido.'];
            $url=trim($post['url_recurso']??'')?:null;if($url && !url_recurso_valida($url))return['ok'=>false,'mensaje'=>'La URL debe comenzar con http:// o https://.'];
            $archivo=$actual['archivo']??null;$nuevo=null;
            if($tipo!=='Enlace'&&isset($files['archivo_recurso'])&&($files['archivo_recurso']['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){$res=guardar_archivo_subido($files['archivo_recurso'],'cursos',50);if(!$res['ok'])return['ok'=>false,'mensaje'=>$res['error']];$nuevo=$res['ruta'];$archivo=$nuevo;}
            if($tipo==='Enlace'){$archivo=null;if(!$url)return['ok'=>false,'mensaje'=>'Indicá una URL http/https.'];}
            if($tipo!=='Enlace'&&!$archivo&&!$url)return['ok'=>false,'mensaje'=>'Subí un archivo o indicá una URL.'];
            $data=['u'=>$unidad,'t'=>trim($post['titulo_recurso']??'')?:'Recurso','tipo'=>$tipo,'url'=>$url,'a'=>$archivo,'d'=>trim($post['descripcion_recurso']??'')?:null,'o'=>max(1,(int)($post['orden']??1))];
            if($a==='agregar_recurso'){$q=$pdo->prepare("INSERT INTO curso_recursos(id_unidad,titulo,tipo,url,archivo,descripcion,orden) VALUES(:u,:t,:tipo,:url,:a,:d,:o)");$q->execute($data);}else{$data['r']=$rid;$q=$pdo->prepare("UPDATE curso_recursos SET id_unidad=:u,titulo=:t,tipo=:tipo,url=:url,archivo=:a,descripcion=:d,orden=:o WHERE id_recurso=:r");$q->execute($data);if($nuevo&&!empty($actual['archivo'])&&$actual['archivo']!==$nuevo)eliminar_archivo_guardado($actual['archivo']);if($tipo==='Enlace'&&!empty($actual['archivo']))eliminar_archivo_guardado($actual['archivo']);}
        }elseif($a==='eliminar_recurso'){
            $q=$pdo->prepare("SELECT r.archivo FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE r.id_recurso=:r AND m.id_publicacion=:p");$q->execute(['r'=>(int)$post['id_recurso'],'p'=>$id_publicacion]);$ruta=$q->fetchColumn();$d=$pdo->prepare("DELETE r FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo WHERE r.id_recurso=:r AND m.id_publicacion=:p");$d->execute(['r'=>(int)$post['id_recurso'],'p'=>$id_publicacion]);if($ruta)eliminar_archivo_guardado($ruta);
        }else return['ok'=>false,'mensaje'=>'Acción no reconocida.'];
        return['ok'=>true,'mensaje'=>'Contenido actualizado correctamente.'];
    }catch(PDOException $e){error_log('Contenido curso: '.$e->getMessage());return['ok'=>false,'mensaje'=>'No se pudo actualizar el contenido del curso.'];}
}

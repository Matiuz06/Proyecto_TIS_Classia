<?php
require_once __DIR__ . '/../auth/roles.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../utils/file_upload_helper.php';

requerir_autenticacion('../../views/login.php');
$usuario = usuario_actual();
$uid = (int)$usuario['id_usuario'];
$tipo = $_GET['tipo'] ?? '';
$id = (int)($_GET['id'] ?? 0);
$ruta = null;
$nombreDescarga = 'archivo';

if ($tipo === 'recurso' && $id > 0) {
    $stmt = $pdo->prepare("SELECT r.archivo,r.titulo,p.id_usuario AS docente_id,p.id_publicacion FROM curso_recursos r JOIN curso_unidades u ON u.id_unidad=r.id_unidad JOIN curso_modulos m ON m.id_modulo=u.id_modulo JOIN publicaciones p ON p.id_publicacion=m.id_publicacion WHERE r.id_recurso=:id AND p.tipo='Curso' LIMIT 1");
    $stmt->execute(['id'=>$id]);
    $row = $stmt->fetch();
    if ($row) {
        $autorizado = es_admin() || (int)$row['docente_id'] === $uid;
        if (!$autorizado && es_estudiante()) {
            $q=$pdo->prepare("SELECT COUNT(*) FROM detalles_contratacion dc JOIN contrataciones c ON c.id_contratacion=dc.id_contratacion WHERE c.id_usuario=:u AND dc.id_publicacion=:p AND c.estado IN ('En Proceso','Completada')");
            $q->execute(['u'=>$uid,'p'=>$row['id_publicacion']]);
            $autorizado=((int)$q->fetchColumn())>0;
        }
        if ($autorizado) { $ruta=$row['archivo']; $nombreDescarga=$row['titulo']; }
    }
} elseif ($tipo === 'solicitud' && $id > 0) {
    $stmt=$pdo->prepare("SELECT s.archivo_adjunto,s.id_usuario,p.id_usuario docente_id,s.titulo FROM solicitudes s JOIN publicaciones p ON p.id_publicacion=s.id_publicacion WHERE s.id_solicitud=:id LIMIT 1");
    $stmt->execute(['id'=>$id]); $row=$stmt->fetch();
    if ($row && (es_admin() || (int)$row['id_usuario']===$uid || (int)$row['docente_id']===$uid)) {
        $ruta=$row['archivo_adjunto']; $nombreDescarga=$row['titulo'];
    }
}

$abs = ruta_absoluta_archivo_guardado($ruta);
if (!$ruta || !$abs || !is_file($abs)) { http_response_code(404); exit('Archivo no disponible.'); }
$mime = (new finfo(FILEINFO_MIME_TYPE))->file($abs) ?: 'application/octet-stream';
$ext = pathinfo($abs, PATHINFO_EXTENSION);
$seguro = preg_replace('/[^A-Za-z0-9._-]+/u','_', $nombreDescarga) ?: 'archivo';
if ($ext) $seguro .= '.' . $ext;
header('Content-Type: '.$mime);
header('Content-Length: '.filesize($abs));
header('Content-Disposition: attachment; filename="'.$seguro.'"');
header('X-Content-Type-Options: nosniff');
readfile($abs);
exit;

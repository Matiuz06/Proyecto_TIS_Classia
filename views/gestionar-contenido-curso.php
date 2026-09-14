<?php
require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');
require_once '../php/publicaciones/contenido_curso.php';

if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
$usuario=usuario_actual(); $id_usuario=(int)$usuario['id_usuario']; $id_publicacion=(int)($_GET['id'] ?? $_POST['id_publicacion'] ?? 0);
$curso=obtener_curso_del_docente($pdo,$id_publicacion,$id_usuario,es_admin());
$mensaje=''; $error='';
if (!$curso) { http_response_code(404); $error='Curso no encontrado o sin permisos.'; }
elseif ($_SERVER['REQUEST_METHOD']==='POST') {
    $r=procesar_contenido_curso($pdo,$_POST,$_FILES,$id_publicacion,$id_usuario,es_admin(),$_SESSION['csrf_token']);
    if($r['ok'])$mensaje=$r['mensaje']; else $error=$r['mensaje'];
}
$contenido=$curso?obtener_contenido_curso($pdo,$id_publicacion):[];
$title='Contenido del curso'; $description='Gestión de módulos, unidades y recursos.'; $cssPrefix='..'; $jsPrefix='..'; $activePage='panel-proveedor';
include '../includes/header.php';
?>
<main class="provider-editor">
  <header class="section-heading">
    <h1>Contenido: <?= htmlspecialchars($curso['titulo'] ?? 'Curso') ?></h1>
    <p>Organizá módulos, unidades, clases y recursos. Los cambios quedan disponibles en la vista del curso.</p>
  </header>
  <?php if(isset($_GET['mensaje'])&&$_GET['mensaje']==='creada'): ?><div class="alert alert-success">Curso creado. Ahora podés cargar su contenido.</div><?php endif; ?>
  <?php if($mensaje): ?><div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
  <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if($curso): ?>
  <nav class="provider-toolbar"><a class="btn" href="editar-publicacion.php?id=<?= $id_publicacion ?>">Editar datos generales</a><a class="btn" href="vista-previa-publicacion.php?id=<?= $id_publicacion ?>">Vista previa</a><a class="btn" href="panel-proveedor.php">Volver al panel</a></nav>

  <section class="content-card">
    <h2>Agregar módulo</h2>
    <form method="POST" class="form-grid">
      <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="agregar_modulo">
      <label>Título<input type="text" name="titulo_modulo" required></label><label>Orden<input type="number" name="orden" min="1" value="<?= count($contenido)+1 ?>"></label><label class="form-grid-full">Descripción<textarea name="descripcion_modulo" rows="3"></textarea></label><button class="btn btn-primary-action" type="submit">Agregar módulo</button>
    </form>
  </section>

  <?php foreach($contenido as $modulo): ?>
  <details class="course-module" open>
    <summary>Módulo <?= (int)$modulo['orden'] ?> · <?= htmlspecialchars($modulo['titulo']) ?></summary>
    <div class="course-module-body">
      <form method="POST" class="form-grid compact-form">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="editar_modulo"><input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>">
        <label>Título<input name="titulo" value="<?= htmlspecialchars($modulo['titulo']) ?>" required></label><label>Orden<input type="number" min="1" name="orden" value="<?= (int)$modulo['orden'] ?>"></label><label class="form-grid-full">Descripción<textarea name="descripcion" rows="2"><?= htmlspecialchars($modulo['descripcion'] ?? '') ?></textarea></label><button class="btn" type="submit">Guardar módulo</button>
      </form>
      <form method="POST" onsubmit="return confirm('¿Eliminar el módulo y todo su contenido?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="eliminar_modulo"><input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>"><button class="btn-status btn-status-delete" type="submit">Eliminar módulo</button></form>

      <h3>Unidades / clases</h3>
      <form method="POST" class="form-grid compact-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="agregar_unidad"><input type="hidden" name="id_modulo" value="<?= (int)$modulo['id_modulo'] ?>"><label>Título<input name="titulo_unidad" required></label><label>Orden<input type="number" min="1" name="orden" value="<?= count($modulo['unidades'])+1 ?>"></label><label class="form-grid-full">Descripción<textarea name="descripcion_unidad" rows="2"></textarea></label><button class="btn" type="submit">Agregar unidad</button></form>

      <?php foreach($modulo['unidades'] as $unidad): ?>
      <article class="course-unit">
        <form method="POST" class="form-grid compact-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="editar_unidad"><input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>"><label>Título<input name="titulo" value="<?= htmlspecialchars($unidad['titulo']) ?>" required></label><label>Orden<input type="number" min="1" name="orden" value="<?= (int)$unidad['orden'] ?>"></label><label class="form-grid-full">Descripción<textarea name="descripcion" rows="2"><?= htmlspecialchars($unidad['descripcion'] ?? '') ?></textarea></label><button class="btn" type="submit">Guardar unidad</button></form>
        <form method="POST" onsubmit="return confirm('¿Eliminar esta unidad?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="eliminar_unidad"><input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>"><button class="btn-status btn-status-delete">Eliminar unidad</button></form>
        <h4>Recursos</h4>
        <?php if($unidad['recursos']): ?><ul><?php foreach($unidad['recursos'] as $r): ?><li><strong><?= htmlspecialchars($r['titulo']) ?></strong> · <?= htmlspecialchars($r['tipo']) ?> <?php if($r['url']): ?><a href="<?= htmlspecialchars($r['url']) ?>" target="_blank" rel="noopener">Abrir enlace</a><?php endif; ?> <?php if($r['archivo']): ?><a href="../php/descargas/descargar_archivo.php?tipo=recurso&id=<?= (int)$r['id_recurso'] ?>" target="_blank">Abrir archivo</a><?php endif; ?><details><summary>Editar recurso</summary><form method="POST" enctype="multipart/form-data" class="provider-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="editar_recurso"><input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>"><input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>"><label>Título<input name="titulo_recurso" value="<?= htmlspecialchars($r['titulo']) ?>" required></label><label>Tipo<select name="tipo_recurso"><?php foreach(['Archivo','PDF','Imagen','Video','Enlace'] as $tipoR): ?><option value="<?= $tipoR ?>" <?= $r['tipo']===$tipoR?'selected':'' ?>><?= $tipoR ?></option><?php endforeach; ?></select></label><label>URL<input type="url" name="url_recurso" value="<?= htmlspecialchars($r['url'] ?? '') ?>" placeholder="https://..."></label><label>Reemplazar archivo<input type="file" name="archivo_recurso"></label><label>Descripción<textarea name="descripcion_recurso"><?= htmlspecialchars($r['descripcion'] ?? '') ?></textarea></label><label>Orden<input type="number" min="1" name="orden" value="<?= (int)$r['orden'] ?>"></label><button class="btn" type="submit">Guardar recurso</button></form></details><form method="POST" class="inline-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="eliminar_recurso"><input type="hidden" name="id_recurso" value="<?= (int)$r['id_recurso'] ?>"><button class="link-danger" type="submit">Eliminar</button></form></li><?php endforeach; ?></ul><?php else: ?><p class="muted">Sin recursos todavía.</p><?php endif; ?>
        <form method="POST" enctype="multipart/form-data" class="form-grid compact-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>"><input type="hidden" name="id_publicacion" value="<?= $id_publicacion ?>"><input type="hidden" name="accion" value="agregar_recurso"><input type="hidden" name="id_unidad" value="<?= (int)$unidad['id_unidad'] ?>"><label>Título<input name="titulo_recurso" required></label><label>Tipo<select name="tipo_recurso"><option>Enlace</option><option>PDF</option><option>Imagen</option><option>Video</option><option>Archivo</option></select></label><label>URL<input type="url" name="url_recurso" placeholder="https://..."></label><label>Archivo<input type="file" name="archivo_recurso"></label><label>Orden<input type="number" min="1" name="orden" value="<?= count($unidad['recursos'])+1 ?>"></label><label class="form-grid-full">Descripción<textarea name="descripcion_recurso" rows="2"></textarea></label><button class="btn" type="submit">Agregar recurso</button></form>
      </article>
      <?php endforeach; ?>
    </div>
  </details>
  <?php endforeach; ?>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

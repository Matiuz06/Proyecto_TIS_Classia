<?php
require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');
require_once '../php/usuarios/perfil_profesional.php';
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32));
$usuario=usuario_actual(); $uid=(int)$usuario['id_usuario']; $mensaje=''; $error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
    if (!hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'] ?? '')) $error='La sesión del formulario expiró.';
    else {
        try { guardar_perfil_profesional($pdo,$uid,$_POST); $mensaje='Perfil profesional actualizado correctamente.'; }
        catch(PDOException $e){ error_log('Perfil profesional: '.$e->getMessage()); $error='No se pudo guardar el perfil profesional.'; }
    }
}
$perfil=obtener_perfil_profesional($pdo,$uid) ?: [];
$title='Editar perfil profesional'; $description='Configuración del perfil profesional de proveedor.'; $cssPrefix='..'; $jsPrefix='..'; $activePage='panel-proveedor'; include '../includes/header.php';
?>
<main class="provider-editor">
<header class="section-heading"><h1>Perfil profesional</h1><p>Completá la información que ayuda a estudiantes y clientes a entender quién sos, tu experiencia y cómo trabajás.</p></header>
<?php if($mensaje): ?><div class="alert alert-success"><?= htmlspecialchars($mensaje) ?></div><?php endif; ?>
<?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="POST" class="provider-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">
<details open><summary>Presentación profesional</summary><div class="form-grid">
<label>Título profesional<input name="titulo_profesional" maxlength="180" value="<?= htmlspecialchars($perfil['titulo_profesional'] ?? '') ?>" placeholder="Ej: Docente de Informática y especialista en robótica educativa"></label>
<label>Ubicación general<input name="ubicacion" value="<?= htmlspecialchars($perfil['ubicacion'] ?? '') ?>" placeholder="Ej: Salto, Uruguay"></label>
<label class="form-grid-full">Presentación<textarea name="presentacion" rows="6" placeholder="Contá quién sos, qué hacés y qué tipo de propuestas brindás."><?= htmlspecialchars($perfil['presentacion'] ?? '') ?></textarea></label>
</div></details>
<details open><summary>Experiencia y formación</summary><div class="form-grid">
<label class="form-grid-full">Experiencia<textarea name="experiencia" rows="6"><?= htmlspecialchars($perfil['experiencia'] ?? '') ?></textarea></label>
<label class="form-grid-full">Formación académica<textarea name="formacion" rows="5"><?= htmlspecialchars($perfil['formacion'] ?? '') ?></textarea></label>
<label class="form-grid-full">Certificaciones<textarea name="certificaciones" rows="4"><?= htmlspecialchars($perfil['certificaciones'] ?? '') ?></textarea></label>
</div></details>
<details open><summary>Especialidades y forma de trabajo</summary><div class="form-grid">
<label>Idiomas<input name="idiomas" value="<?= htmlspecialchars($perfil['idiomas'] ?? '') ?>" placeholder="Español, inglés..."></label>
<label>Modalidad de trabajo<input name="modalidad_trabajo" value="<?= htmlspecialchars($perfil['modalidad_trabajo'] ?? '') ?>" placeholder="Presencial, virtual, híbrida"></label>
<label>Tiempo de respuesta<input name="tiempo_respuesta" value="<?= htmlspecialchars($perfil['tiempo_respuesta'] ?? '') ?>" placeholder="Ej: Menos de 24 horas"></label>
<label class="form-grid-full">Especialidades<textarea name="especialidades" rows="4" placeholder="Robótica educativa, desarrollo web, impresión 3D..."><?= htmlspecialchars($perfil['especialidades'] ?? '') ?></textarea></label>
<label class="form-grid-full">Habilidades<textarea name="habilidades" rows="4" placeholder="PHP, Arduino, micro:bit, diseño de proyectos..."><?= htmlspecialchars($perfil['habilidades'] ?? '') ?></textarea></label>
</div></details>
<details open><summary>Enlaces y privacidad</summary><div class="form-grid">
<label>Portfolio<input type="url" name="portfolio_url" value="<?= htmlspecialchars($perfil['portfolio_url'] ?? '') ?>"></label>
<label>LinkedIn<input type="url" name="linkedin_url" value="<?= htmlspecialchars($perfil['linkedin_url'] ?? '') ?>"></label>
<label>Quién puede ver el perfil<select name="visibilidad"><option value="Registrados" <?= ($perfil['visibilidad'] ?? 'Registrados')==='Registrados'?'selected':'' ?>>Cualquier usuario registrado</option><option value="Relacionados" <?= ($perfil['visibilidad'] ?? '')==='Relacionados'?'selected':'' ?>>Solo usuarios con cursos o servicios contratados</option></select></label>
</div></details>
<div class="form-actions"><button class="btn btn-primary-action" type="submit">Guardar perfil</button><a class="btn" href="perfil-profesional.php?id=<?= $uid ?>">Vista pública</a><a class="btn" href="panel-proveedor.php">Volver al panel</a></div>
</form>
</main>
<?php include '../includes/footer.php'; ?>

<?php

require_once '../php/auth/roles.php';
requerir_cualquier_rol([ROL_DOCENTE, ROL_ADMIN], 'usuario.php');
require_once '../php/publicaciones/contenido_curso.php';
require_once '../php/publicaciones/EntregaRepository.php';

$usuario = usuario_actual();
$id_usuario = (int)$usuario['id_usuario'];
$id_tarea = (int)($_GET['id'] ?? 0);
$filtro = $_GET['filtro'] ?? 'todos';
$error = '';
$tareaInfo = null;
$filas = [];
$resumen = ['total' => 0, 'entregaron' => 0, 'tardias' => 0, 'sin_entregar' => 0, 'calificadas' => 0, 'sin_calificar' => 0];

try {
    $tareaRepo = new TareaRepository($pdo);
    $tareaInfo = $tareaRepo->obtenerInfoDocente($id_tarea);
    if (!$tareaInfo || (!es_admin() && (int)$tareaInfo['docente_id'] !== $id_usuario)) {
        http_response_code(403);
        $error = 'No tenes permisos para revisar esta tarea.';
    } else {
        $repo = new EntregaRepository($pdo);
        $tarea = Tarea::fromArray($tareaInfo);
        $filas = $repo->listarPorTareaConEstudiantes($id_tarea, (int)$tareaInfo['id_publicacion']);
        $resumen = $repo->obtenerResumenPorTarea($id_tarea, (int)$tareaInfo['id_publicacion'], $tarea);
        $filas = array_values(array_filter($filas, function ($fila) use ($filtro, $tarea) {
            $entrego = !empty($fila['id_entrega']);
            $tardia = $entrego && Entrega::fromArray($fila)->esTardia($tarea);
            $calificada = $entrego && $fila['fecha_calificacion'];
            return match ($filtro) {
                'entregados' => $entrego,
                'tardios' => $tardia,
                'sin_entregar' => !$entrego,
                'sin_calificar' => $entrego && !$calificada,
                'calificados' => $calificada,
                default => true,
            };
        }));
    }
} catch (PDOException $e) {
    error_log('Entregas tarea: ' . $e->getMessage());
    $error = 'No se pudo cargar la tarea.';
}

$title = 'Entregas de tarea';
$description = 'Revision docente de entregas.';
$cssPrefix = '..';
$jsPrefix = '..';
$bodyClass = 'course-builder-page';
$activePage = 'panel-proveedor';
include '../includes/header.php';
?>
<main class="provider-editor course-builder">
  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php else: ?>
    <header class="section-heading course-editor-heading">
      <div>
        <p class="course-eyebrow"><?= htmlspecialchars($tareaInfo['curso_titulo']) ?></p>
        <h1><?= htmlspecialchars($tareaInfo['tarea_titulo']) ?></h1>
        <p><?= htmlspecialchars($tareaInfo['modulo_titulo'] . ' / ' . $tareaInfo['unidad_titulo']) ?></p>
      </div>
      <dl class="course-editor-stats" aria-label="Resumen de entregas">
        <div><dt>Estudiantes</dt><dd><?= $resumen['total'] ?></dd></div>
        <div><dt>Entregaron</dt><dd><?= $resumen['entregaron'] ?></dd></div>
        <div><dt>Tardias</dt><dd><?= $resumen['tardias'] ?></dd></div>
        <div><dt>Pendientes</dt><dd><?= $resumen['sin_entregar'] ?></dd></div>
        <div><dt>Calificadas</dt><dd><?= $resumen['calificadas'] ?></dd></div>
      </dl>
    </header>

    <nav class="provider-toolbar">
      <a class="btn" href="gestionar-contenido-curso.php?id=<?= (int)$tareaInfo['id_publicacion'] ?>">Volver al contenido</a>
      <?php foreach (['todos' => 'Todos', 'entregados' => 'Entregados', 'tardios' => 'Tardios', 'sin_entregar' => 'Sin entregar', 'sin_calificar' => 'Sin calificar', 'calificados' => 'Calificados'] as $key => $label): ?>
        <a class="btn btn-sm<?= $filtro === $key ? ' btn-primary-action' : '' ?>" href="entregas-tarea.php?id=<?= $id_tarea ?>&filtro=<?= $key ?>"><?= $label ?></a>
      <?php endforeach; ?>
    </nav>

    <div class="admin-users-table-wrap">
      <table class="admin-users-table">
        <thead><tr><th>Estudiante</th><th>Estado</th><th>Tipo</th><th>Fecha</th><th>Condicion</th><th>Calificacion</th><th>Accion</th></tr></thead>
        <tbody>
          <?php foreach ($filas as $fila): ?>
            <?php
              $entrego = !empty($fila['id_entrega']);
              $tardia = $entrego && Entrega::fromArray($fila)->esTardia(Tarea::fromArray($tareaInfo));
              $tipos = $fila['tipos_entrega'] ?? [];
            ?>
            <tr>
              <td><?= htmlspecialchars($fila['nombre'] . ' ' . $fila['apellido']) ?><br><small><?= htmlspecialchars($fila['email']) ?></small></td>
              <td><?= $entrego ? 'Entregada' : 'Sin entrega' ?></td>
              <td><?= $entrego ? htmlspecialchars(implode(' + ', $tipos) ?: '-') : '-' ?></td>
              <td><?= $entrego ? date('d/m/Y H:i', strtotime($fila['fecha_entrega'])) : '-' ?></td>
              <td><?= $entrego ? ($tardia ? 'Tardia' : 'En fecha') : 'Pendiente' ?></td>
              <td><?= $fila['calificacion'] !== null ? htmlspecialchars((string)$fila['calificacion']) : '-' ?></td>
              <td><?= $entrego ? '<a class="btn btn-sm" href="revisar-entrega.php?id=' . (int)$fila['id_entrega'] . '">Revisar</a>' : 'Sin entrega' ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</main>
<?php include '../includes/footer.php'; ?>

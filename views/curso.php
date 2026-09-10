<?php
require_once '../php/publicaciones/detalle_curso.php';

$title      = $curso ? htmlspecialchars($curso['titulo']) : 'Curso';
$cssPrefix  = '..';
$jsPrefix    = '..';

$activePage = 'catalogo';
include '../includes/header.php';
?>

    <div class="layout-sidebar">
      <aside class="sidebar">
        <h2>Contenido del curso</h2>

        <h4>Módulo 1 — Introducción</h4>
        <ul>
          <li>Bienvenida al módulo (Video · 2 min)</li>
          <li>Introducción teórica (PDF)</li>
          <li>Actividad práctica 1</li>
        </ul>

        <h4>Módulo 2 — Desarrollo</h4>
        <ul>
          <li>Bienvenida al Módulo 2 (Video · 1 min)</li>
          <li><strong><?= htmlspecialchars($curso ? $curso['titulo'] : 'Introducción a las funciones') ?> (Video · 3 min)</strong></li>
          <li>Crear una práctica básica (Video · 2 min)</li>
          <li>Lectura complementaria (PDF)</li>
          <li>Cuestionario del módulo (Actividad)</li>
        </ul>

        <h4>Módulo 3 — Cierre</h4>
        <ul>
          <li>Proyecto final (Actividad)</li>
          <li>Video de cierre (Video · 4 min)</li>
        </ul>
      </aside>

      <main class="main-content">
        <nav class="breadcrumb" aria-label="Ruta de navegación">
          <a href="catalogo.php?tipo=curso">← Volver al catálogo</a>
        </nav>

        <h1><?= htmlspecialchars($curso ? $curso['titulo'] : 'Introducción a las funciones') ?></h1>

        <?php if ($curso): ?>
          <p class="badge"><?= htmlspecialchars($curso['nombre_categoria']) ?></p>
          <p><strong>Docente:</strong> <?= htmlspecialchars($curso['autor_nombre'] . ' ' . $curso['autor_apellido']) ?> | <strong>Precio:</strong> $<?= number_format($curso['precio'], 2, ',', '.') ?></p>
        <?php endif; ?>

        <?php if (!empty($curso['imagen'])): ?>
          <div class="course-banner-media">
            <img src="../<?= htmlspecialchars($curso['imagen']) ?>" alt="<?= htmlspecialchars($curso['titulo']) ?>" class="course-banner-img" />
          </div>
        <?php endif; ?>

        <video controls aria-label="Video de demostración del curso" class="course-demo-video">
          Tu navegador no soporta video.
        </video>

        <hr />

        <h3>Descripción del curso</h3>
        <p>
          <?= nl2br(htmlspecialchars($curso ? $curso['descripcion'] : 'En este video vamos a explorar el concepto de funciones dentro de la programación. Una función es un bloque de código reutilizable que realiza una tarea específica.')) ?>
        </p>

        <h3>Chat de Consultas</h3>
        <p>Asistente virtual: ¿Tienes alguna duda sobre este módulo?</p>
        <div class="flex-row">
          <input
            type="text"
            aria-label="Pregunta para el chat de consultas"
            placeholder="Escribe tu pregunta..." />
          <button type="button">Enviar</button>
        </div>

        <p class="mt-section">
          <a href="usuario.php" class="btn">Ir a mis cursos</a>
        </p>
      </main>
    </div>

<?php include '../includes/footer.php'; ?>

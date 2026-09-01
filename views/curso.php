<?php
$title      = 'Curso';
$cssPrefix  = '..';
$activePage = 'catalogo';
include '../includes/header.php';
?>

    <div class="layout-sidebar">
      <aside class="sidebar">
        <h2 style="color: white">Contenido del curso</h2>

        <h4>Módulo 1 — Introducción</h4>
        <ul>
          <li>Bienvenida al módulo (Video · 2 min)</li>
          <li>Introducción teórica (PDF)</li>
          <li>Actividad práctica 1</li>
        </ul>

        <h4>Módulo 2 — Desarrollo</h4>
        <ul>
          <li>Bienvenida al Módulo 2 (Video · 1 min)</li>
          <li><strong>Introducción a las funciones (Video · 3 min)</strong></li>
          <li>Crear una función básica (Video · 2 min)</li>
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
        <h1>Introducción a las funciones</h1>

        <video controls aria-label="Video de demostración del curso">
          Tu navegador no soporta video.
        </video>

        <hr />

        <h3>Descripción del material</h3>
        <p>
          En este video vamos a explorar el concepto de funciones dentro de la
          programación. Una función es un bloque de código reutilizable que
          realiza una tarea específica.
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

        <p>
          <a href="usuario.php" class="btn">Continuar aprendizaje</a>
        </p>
      </main>
    </div>

    <footer class="site-footer">
      <p>&copy; 2026 Classia. Todos los derechos reservados.</p>
    </footer>
</body>
</html>

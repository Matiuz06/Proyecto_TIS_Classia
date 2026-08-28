<?php
$title     = 'Perfil de usuario';
$cssPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>

  <div id="panel-admin" hidden>
    <strong>Modo Administrador Activo:</strong>
    <a href="usuario.php">Gestionar Usuarios</a> |
    <a href="usuario.php">Configuración Global</a> |
    <a href="usuario.php">Reportes del Sistema</a>
  </div>

  <main class="motion-entry">
    <section class="profile-hero" aria-labelledby="perfil-usuario">
      <p>Bienvenido/a,</p>
      <h1 id="perfil-usuario">Carlos "Chacho" Ramos</h1>
      <p>Perfil de Usuario (Estudiante, Cliente)</p>
      <p>
        <strong>7</strong> Cursos completados | <strong>2</strong> Servicios
        contratados
      </p>
    </section>

    <nav class="profile-tabs" aria-label="Secciones de la cuenta">
      <button type="button">Perfil</button>
      <button type="button">Mis Cursos Comprados</button>
      <button type="button">Mis Servicios Contratados</button>
      <button type="button">Certificados</button>
      <button type="button">Estadísticas</button>
    </nav>


    <section class="account-section" aria-labelledby="informacion-cuenta">
      <h2 id="informacion-cuenta">Información básica de la cuenta</h2>
      <p>
        <label for="nombre-cuenta">Nombre:*</label><br />
        <input type="text" id="nombre-cuenta" value="Carlos Alberto" readonly />
      </p>
      <p>
        <label for="apellido-cuenta">Apellido:*</label><br />
        <input type="text" id="apellido-cuenta" value="Ramos Cruz" readonly />
      </p>
      <p>
        <label for="idioma-cuenta">Idioma predeterminado:</label><br />
        <select id="idioma-cuenta">
          <option>Español (Spanish)</option>
          <option>Inglés (English)</option>
        </select>
      </p>
      <p>
        <label for="pais-cuenta">País o región:*</label><br />
        <select id="pais-cuenta">
          <option>Uruguay</option>
          <option>Argentina</option>
        </select>
      </p>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="cursos-inscriptos">
      <h2 id="cursos-inscriptos">Mis cursos</h2>
      <p>Cursos en los que estás inscripto:</p>

      <ul>
        <li>
          <strong>Robótica para principiantes</strong> — Progreso: 60% [<a href="curso.php">Continuar curso</a>]
        </li>
      </ul>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="servicios-contratados">
      <h2 id="servicios-contratados">Mis servicios contratados</h2>
      <p>Servicios solicitados a proveedores:</p>

      <ul>
        <li>
          <strong>Modelado e Impresión 3D de Chasis de Robot</strong> —
          Estado: Impresión en proceso (Proveedor: Classia Studio)
        </li>
        <li>
          <strong>Asesoría Técnica en Automatización</strong> — Estado:
          Finalizado (Proveedor: Robótica Uy)
        </li>
      </ul>

      <p><a class="btn" href="catalogo.php">Contratar nuevo servicio</a></p>
    </section>

    <section class="account-section" aria-labelledby="certificados">
      <h2 id="certificados">Mis certificados</h2>
      <p>Certificado de Finalización: Robótica para principiantes (demo)</p>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="estadisticas">
      <h2 id="estadisticas">Estadísticas</h2>
      <p>Resumen de aprendizaje e ingresos por cursos/servicios prestados.</p>
    </section>
  </main>

  <footer class="site-footer">
    <p>&copy; 2026 Classia. Todos los derechos reservados.</p>
  </footer>
</body>
</html>

<?php
require_once '../php/usuarios/perfil.php';

$title      = 'Perfil de usuario';
$cssPrefix  = '..';
$jsPrefix    = '..';

$activePage = 'cuenta';
include '../includes/header.php';
?>

  <?php if (es_admin()): ?>
    <div id="panel-admin" class="alert alert-info role-banner">
      <strong>Modo Administrador Activo:</strong>
      <a href="panel-administrador.php" class="banner-link">Ir al Panel de Administración</a> |
      <a href="catalogo.php">Gestionar Catálogo</a>
    </div>
  <?php elseif (es_docente()): ?>
    <div id="panel-docente" class="alert alert-info role-banner">
      <strong>Modo Docente/Proveedor Activo:</strong>
      <a href="panel-proveedor.php" class="banner-link">Ir a mi Panel de Proveedor</a> |
      <a href="crear-publicacion.php">Publicar nuevo contenido</a>
    </div>
  <?php endif; ?>

  <main class="motion-entry">
    <?php if (!empty($mensaje_acceso)): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($mensaje_acceso); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_error)): ?>
      <div class="alert alert-danger" role="alert">
        <?php echo htmlspecialchars($mensaje_error); ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($mensaje_exito)): ?>
      <div class="alert alert-success" role="status">
        <?php echo htmlspecialchars($mensaje_exito); ?>
      </div>
    <?php endif; ?>

    <section class="profile-hero" aria-labelledby="perfil-usuario">
      <div class="profile-hero-content">
        <div class="profile-avatar-wrapper">
          <?php 
            $fotoPerfilSrc = !empty($userData['foto_perfil']) 
              ? ($cssPrefix . '/' . htmlspecialchars($userData['foto_perfil'])) 
              : ($cssPrefix . '/assets/images/default-avatar.svg');
          ?>
          <img src="<?php echo $fotoPerfilSrc; ?>" alt="Foto de perfil de <?php echo htmlspecialchars($userData['nombre']); ?>" class="profile-avatar-img" />
          
          <div class="profile-avatar-actions">
            <form action="../php/usuarios/foto_perfil.php" method="POST" enctype="multipart/form-data" class="profile-photo-form">
              <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" />
              <input type="hidden" name="accion" value="subir" />
              <label class="btn-avatar-upload" title="Subir nueva foto de perfil">
                <span>Cambiar foto</span>
                <input type="file" name="foto_perfil" accept="image/jpeg,image/png,image/webp,image/gif" onchange="this.form.submit()" class="visually-hidden" />
              </label>
            </form>

            <?php if (!empty($userData['foto_perfil'])): ?>
              <form action="../php/usuarios/foto_perfil.php" method="POST" class="profile-photo-remove-form">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>" />
                <input type="hidden" name="accion" value="eliminar" />
                <button type="submit" class="btn-avatar-remove" onclick="return confirm('¿Seguro que deseas quitar tu foto de perfil?')" title="Quitar foto de perfil">
                  Quitar foto
                </button>
              </form>
            <?php endif; ?>
          </div>
        </div>

        <div class="profile-hero-text">
          <p class="profile-greeting">Bienvenido/a,</p>
          <h1 id="perfil-usuario"><?php echo htmlspecialchars($userData['nombre'] . ' ' . ($userData['apellido'] ?? '')); ?></h1>
          <p>Perfil de Usuario (<?php echo htmlspecialchars($rol_actual); ?>) · <strong><?php echo htmlspecialchars($userData['email']); ?></strong></p>
          <p>
            <strong><?php echo count($cursos_contratados); ?></strong> Cursos contratados | 
            <strong><?php echo count($servicios_contratados); ?></strong> Servicios contratados
          </p>
        </div>
      </div>
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
        <label for="nombre-cuenta">Nombre:</label><br />
        <input type="text" id="nombre-cuenta" value="<?php echo htmlspecialchars($userData['nombre']); ?>" readonly disabled />
      </p>
      <p>
        <label for="apellido-cuenta">Apellido:</label><br />
        <input type="text" id="apellido-cuenta" value="<?php echo htmlspecialchars($userData['apellido'] ?? ''); ?>" readonly disabled />
      </p>
      <p>
        <label for="email-cuenta">Email:</label><br />
        <input type="email" id="email-cuenta" value="<?php echo htmlspecialchars($userData['email']); ?>" readonly disabled />
      </p>
      <p>
        <label for="telefono-cuenta">Número de teléfono:</label><br />
        <input type="text" id="telefono-cuenta" value="<?php echo htmlspecialchars($userData['telefono'] ?? 'No especificado'); ?>" readonly disabled />
      </p>
      <p>
        <label for="fecha-cuenta">Fecha de registro:</label><br />
        <input type="text" id="fecha-cuenta" value="<?php echo !empty($userData['fecha_registro']) ? date('d/m/Y H:i', strtotime($userData['fecha_registro'])) : '-'; ?>" readonly disabled />
      </p>

      <p class="mt-section">
        <a href="editar-perfil.php" class="btn">Modificar datos personales</a>
      </p>

      <div class="account-security-card">
        <h3>Seguridad de la cuenta</h3>
        <p>Podés cambiar tu clave actual o restablecerla en caso de olvido.</p>
        <div class="account-security-actions">
          <a href="cambiar-contrasena.php" class="btn">Cambiar contraseña</a>
          <a href="restablecer-contrasena.php" class="btn btn-ghost">Restablecer contraseña</a>
        </div>
      </div>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="cursos-inscriptos">
      <h2 id="cursos-inscriptos">Mis cursos contratados</h2>
      <?php if (empty($cursos_contratados)): ?>
        <p>Aún no estás inscripto en ningún curso.</p>
        <p><a class="btn" href="catalogo.php?tipo=curso">Ver catálogo de cursos</a></p>
      <?php else: ?>
        <p>Cursos en los que estás inscripto:</p>
        <ul>
          <?php foreach ($cursos_contratados as $curso): ?>
            <li class="account-item">
              <strong><?php echo htmlspecialchars($curso['titulo']); ?></strong>
              — Estado: <em><?php echo htmlspecialchars($curso['estado']); ?></em>
              [<a href="curso.php?id=<?php echo (int)$curso['id_publicacion']; ?>">Continuar curso</a>]

              <?php if (!empty($curso['id_valoracion'])): ?>
                <div class="account-item-rating">
                  <span class="rating-stars">★ Tu valoración: <?php echo (int) $curso['puntuacion']; ?>/5</span>
                  <?php if (!empty($curso['comentario'])): ?>
                    <span>— "<?php echo htmlspecialchars($curso['comentario']); ?>"</span>
                  <?php endif; ?>
                </div>
              <?php elseif (in_array($curso['estado'], ['Completada', 'En Proceso'], true)): ?>
                <span>[<a href="valoracion.php?contratacion=<?php echo (int) $curso['id_contratacion']; ?>">Valorar curso</a>]</span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="servicios-contratados">
      <h2 id="servicios-contratados">Mis servicios contratados</h2>
      <?php if (empty($servicios_contratados)): ?>
        <p>No tenés servicios solicitados a proveedores en este momento.</p>
        <p><a class="btn" href="catalogo.php?tipo=servicio">Contratar nuevo servicio</a></p>
      <?php else: ?>
        <p>Servicios solicitados a proveedores:</p>
        <ul>
          <?php foreach ($servicios_contratados as $servicio): ?>
            <li class="account-item">
              <strong><?php echo htmlspecialchars($servicio['titulo']); ?></strong>
              — Estado: <em><?php echo htmlspecialchars($servicio['estado']); ?></em>
              [<a href="servicio-detalle.php?id=<?php echo (int)$servicio['id_publicacion']; ?>">Ver detalles</a>]

              <?php if (!empty($servicio['id_valoracion'])): ?>
                <div class="account-item-rating">
                  <span class="rating-stars">★ Tu valoración: <?php echo (int) $servicio['puntuacion']; ?>/5</span>
                  <?php if (!empty($servicio['comentario'])): ?>
                    <span>— "<?php echo htmlspecialchars($servicio['comentario']); ?>"</span>
                  <?php endif; ?>
                </div>
              <?php elseif (in_array($servicio['estado'], ['Completada', 'En Proceso'], true)): ?>
                <span>[<a href="valoracion.php?contratacion=<?php echo (int) $servicio['id_contratacion']; ?>">Valorar servicio</a>]</span>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
        <p><a class="btn" href="catalogo.php?tipo=servicio">Contratar nuevo servicio</a></p>
      <?php endif; ?>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="certificados">
      <h2 id="certificados">Mis certificados</h2>
      <p>Certificados disponibles según tus cursos finalizados.</p>
    </section>

    <hr />

    <section class="account-section" aria-labelledby="estadisticas">
      <h2 id="estadisticas">Estadísticas</h2>
      <p>Resumen de aprendizaje e historial de participación en la plataforma.</p>
    </section>
  </main>

<?php include '../includes/footer.php'; ?>

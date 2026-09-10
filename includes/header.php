<?php
require_once __DIR__ . '/../php/auth/session.php';
iniciar_sesion();

require_once __DIR__ . '/../php/auth/roles.php';

$title       = $title       ?? 'Classia';
$description = $description ?? 'Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada.';
$cssPrefix   = $cssPrefix   ?? '..';
$activePage  = $activePage  ?? '';

$indexHref    = ($cssPrefix === '.') ? 'index.php'           : '../index.php';
$catalogoHref = ($cssPrefix === '.') ? 'views/catalogo.php'  : 'catalogo.php';
$carritoHref  = ($cssPrefix === '.') ? 'views/carrito.php'   : 'carrito.php';
$loginHref    = ($cssPrefix === '.') ? 'views/login.php'     : 'login.php';
$registroHref = ($cssPrefix === '.') ? 'views/registro.php'  : 'registro.php';
$cuentaHref   = ($cssPrefix === '.') ? 'views/usuario.php'   : 'usuario.php';
$logoutHref   = ($cssPrefix === '.') ? 'php/auth/logout.php' : '../php/auth/logout.php';
$solicitarDocenteHref = ($cssPrefix === '.') ? 'views/solicitar-docente.php' : 'solicitar-docente.php';
$panelProveedorHref   = ($cssPrefix === '.') ? 'views/panel-proveedor.php' : 'panel-proveedor.php';
$panelAdminHref       = ($cssPrefix === '.') ? 'views/panel-administrador.php' : 'panel-administrador.php';

$isAuth = esta_autenticado();
$currentUser = usuario_actual();
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars($description) ?>" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="<?= $cssPrefix ?>/css/animation.css" />
  <link rel="stylesheet" href="<?= $cssPrefix ?>/css/style.css" />
  <link rel="icon" type="image/png" href="<?= $cssPrefix ?>/assets/images/favicon.png" />
</head>
<body<?= ($bodyClass ?? '') ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
  <header class="site-header">
    <div class="site-header__inner">
      <a class="site-brand" href="<?= $indexHref ?>">
        <img src="<?= $cssPrefix ?>/assets/images/logo-classia.png" alt="Classia" />
      </a>
      <nav class="site-nav" aria-label="Navegación principal">
        <a href="<?= $indexHref ?>"<?= $activePage === 'inicio' ? ' aria-current="page"' : '' ?>>Inicio</a>
        <a href="<?= $catalogoHref ?>"<?= $activePage === 'catalogo' ? ' aria-current="page"' : '' ?>>Catálogo</a>
        <a href="<?= $carritoHref ?>"<?= $activePage === 'carrito' ? ' aria-current="page"' : '' ?>>Carrito</a>

        <?php if (!$isAuth): ?>
          <div class="user-nav-dropdown">
            <a href="<?= $loginHref ?>" class="user-nav-trigger" title="Acceder a tu cuenta">
              <span class="user-nav-name">Cuenta</span>
              <img src="<?= $cssPrefix ?>/assets/images/default-avatar.svg" alt="Cuenta" class="user-nav-avatar" />
              <span class="user-nav-caret" aria-hidden="true">&#9662;</span>
            </a>

            <div class="user-dropdown-menu" role="menu" aria-label="Opciones de acceso">
              <a href="<?= $loginHref ?>" role="menuitem" class="user-dropdown-item<?= in_array($activePage, ['login', 'cuenta'], true) ? ' active' : '' ?>">
                Iniciar sesión
              </a>
              <a href="<?= $registroHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'registro' ? ' active' : '' ?>">
                Registrarse
              </a>
            </div>
          </div>
        <?php else: ?>
          <?php
            $navAvatarSrc = !empty($currentUser['foto_perfil'])
              ? ($cssPrefix . '/' . htmlspecialchars($currentUser['foto_perfil']))
              : ($cssPrefix . '/assets/images/default-avatar.svg');
            $primerNombre = htmlspecialchars(explode(' ', trim($currentUser['nombre'] ?? 'Usuario'))[0]);
          ?>
          <div class="user-nav-dropdown">
            <a href="<?= $cuentaHref ?>" class="user-nav-trigger" title="Ir a mi cuenta (<?= $primerNombre ?>)">
              <span class="user-nav-name"><?= $primerNombre ?></span>
              <img src="<?= $navAvatarSrc ?>" alt="Avatar de <?= $primerNombre ?>" class="user-nav-avatar" />
              <span class="user-nav-caret" aria-hidden="true">&#9662;</span>
            </a>

            <div class="user-dropdown-menu" role="menu" aria-label="Opciones de usuario">
              <a href="<?= $cuentaHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'cuenta' ? ' active' : '' ?>">
                Mi cuenta
              </a>

              <?php if (es_estudiante()): ?>
                <a href="<?= $solicitarDocenteHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'solicitar-docente' ? ' active' : '' ?>">
                  Solicitar ser docente
                </a>
              <?php elseif (es_docente()): ?>
                <a href="<?= $panelProveedorHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'panel-proveedor' ? ' active' : '' ?>">
                  Panel proveedor
                </a>
                <a href="<?= ($cssPrefix === '.') ? 'views/crear-publicacion.php' : 'crear-publicacion.php' ?>" role="menuitem" class="user-dropdown-item">
                  Crear publicación
                </a>
              <?php elseif (es_admin()): ?>
                <a href="<?= $panelAdminHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'panel-administrador' ? ' active' : '' ?>">
                  Panel administrador
                </a>
                <a href="<?= ($cssPrefix === '.') ? 'views/solicitudes-docente.php' : 'solicitudes-docente.php' ?>" role="menuitem" class="user-dropdown-item">
                  Solicitudes docentes
                </a>
                <a href="<?= $panelProveedorHref ?>" role="menuitem" class="user-dropdown-item">
                  Panel proveedor
                </a>
              <?php endif; ?>

              <div class="user-dropdown-divider" role="separator"></div>
              <a href="<?= $logoutHref ?>" role="menuitem" class="user-dropdown-item user-dropdown-item--logout">
                Cerrar sesión
              </a>
            </div>
          </div>
        <?php endif; ?>
      </nav>
    </div>
  </header>

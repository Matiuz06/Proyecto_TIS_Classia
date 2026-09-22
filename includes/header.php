<?php
require_once __DIR__ . '/../php/auth/sesion.php';
iniciar_sesion();

require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../php/auth/guardia_onboarding.php';
require_once __DIR__ . '/../php/utils/i18n.php';

$current_lang = inicializar_i18n();

$title       = $title       ?? 'Classia';
$description = $description ?? 'Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada.';
$cssPrefix   = $cssPrefix   ?? '..';
$jsPrefix    = $jsPrefix    ?? '..';
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
$misSolicitudesHref = ($cssPrefix === '.') ? 'views/mis-solicitudes-servicios.php' : 'mis-solicitudes-servicios.php';
$solicitudesProveedorHref = ($cssPrefix === '.') ? 'views/solicitudes-servicios.php' : 'solicitudes-servicios.php';
$perfilProfesionalHref = ($cssPrefix === '.') ? 'views/perfil-profesional.php' : 'perfil-profesional.php';

$isAuth = esta_autenticado();
$currentUser = usuario_actual();
$onboardingHref = ($cssPrefix === '.') ? 'views/primeros-pasos.php' : 'primeros-pasos.php';
requerir_onboarding_completo($onboardingHref);

$cartItems = array_unique(array_filter($_SESSION['carrito_publicaciones'] ?? []));
$cartCount = count($cartItems);


?>
<!doctype html>
<html lang="<?= htmlspecialchars($current_lang) ?>">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="<?= htmlspecialchars($description) ?>" />
  <title><?= htmlspecialchars($title) ?></title>
  <link rel="stylesheet" href="<?= $cssPrefix ?>/css/animation.css?v=<?= filemtime(__DIR__ . '/../css/animation.css') ?>" />
  <link rel="stylesheet" href="<?= $cssPrefix ?>/css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>" />
  <link rel="icon" type="image/png" href="<?= $cssPrefix ?>/assets/images/favicon.png" />
</head>
<body<?= ($bodyClass ?? '') ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
  <header class="site-header">
    <div class="site-header__inner">
      <a class="site-brand" href="<?= $indexHref ?>">
        <img src="<?= $cssPrefix ?>/assets/images/logo-classia.png" alt="Classia" />
      </a>
      <nav class="site-nav" aria-label="Navegación principal">
        <a href="<?= $indexHref ?>"<?= $activePage === 'inicio' ? ' aria-current="page"' : '' ?>><?= __t('nav_inicio') ?></a>
        <a href="<?= $catalogoHref ?>"<?= $activePage === 'catalogo' ? ' aria-current="page"' : '' ?>><?= __t('nav_catalogo') ?></a>
        
        <?php if ($isAuth): ?>
        <a href="<?= $carritoHref ?>" class="nav-cart-btn<?= $activePage === 'carrito' ? ' active' : '' ?>" aria-label="<?= __t('nav_carrito') ?> (<?= $cartCount ?> items)">
          <svg class="nav-cart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <span class="nav-cart-text"><?= __t('nav_carrito') ?></span>
          <?php if ($cartCount > 0): ?>
            <span class="nav-cart-badge" id="cart-item-count"><?= $cartCount ?></span>
          <?php endif; ?>
        </a>
        <?php endif; ?>


        <?php if (!$isAuth): ?>
          <div class="user-nav-dropdown">
            <a href="<?= $loginHref ?>" class="user-nav-trigger" title="Acceder a tu cuenta">
              <span class="user-nav-name"><?= __t('nav_cuenta') ?></span>
              <img src="<?= $cssPrefix ?>/assets/images/default-avatar.svg" alt="Cuenta" class="user-nav-avatar" />
              <span class="user-nav-caret" aria-hidden="true">&#9662;</span>
            </a>

            <div class="user-dropdown-menu" role="menu" aria-label="Opciones de acceso">
              <a href="<?= $loginHref ?>" role="menuitem" class="user-dropdown-item<?= in_array($activePage, ['login', 'cuenta'], true) ? ' active' : '' ?>">
                <?= __t('nav_iniciar_sesion') ?>
              </a>
              <a href="<?= $registroHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'registro' ? ' active' : '' ?>">
                <?= __t('nav_registrarse') ?>
              </a>
            </div>
          </div>
        <?php else: ?>
          <?php
            $fotoPerfil = (string) ($currentUser['foto_perfil'] ?? '');
            $navAvatarSrc = $fotoPerfil !== ''
              ? (preg_match('#^https?://#i', $fotoPerfil) ? $fotoPerfil : $cssPrefix . '/' . ltrim($fotoPerfil, '/'))
              : ($cssPrefix . '/assets/images/default-avatar.svg');
            $primerNombre = htmlspecialchars(explode(' ', trim($currentUser['nombre'] ?? 'Usuario'))[0]);
          ?>
          <div class="user-nav-dropdown">
            <a href="<?= $cuentaHref ?>" class="user-nav-trigger" title="Ir a mi cuenta (<?= $primerNombre ?>)">
              <span class="user-nav-name"><?= $primerNombre ?></span>
              <img src="<?= htmlspecialchars($navAvatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= $primerNombre ?>" class="user-nav-avatar" />
              <span class="user-nav-caret" aria-hidden="true">&#9662;</span>
            </a>

            <div class="user-dropdown-menu" role="menu" aria-label="Opciones de usuario">
              <a href="<?= $cuentaHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'cuenta' ? ' active' : '' ?>">
                <?= __t('nav_mi_cuenta') ?>
              </a>

              <?php if (es_estudiante()): ?>
                <a href="<?= $misSolicitudesHref ?>" role="menuitem" class="user-dropdown-item"><?= __t('nav_mis_solicitudes', 'Mis solicitudes de servicios') ?></a>
                <a href="<?= $solicitarDocenteHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'solicitar-docente' ? ' active' : '' ?>">
                  <?= __t('nav_solicitar_docente') ?>
                </a>
              <?php elseif (es_docente()): ?>
                <a href="<?= $panelProveedorHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'panel-proveedor' ? ' active' : '' ?>">
                  <?= __t('nav_panel_docente') ?>
                </a>
                <a href="<?= ($cssPrefix === '.') ? 'views/crear-publicacion.php' : 'crear-publicacion.php' ?>" role="menuitem" class="user-dropdown-item">
                  <?= __t('nav_crear_publicacion', 'Crear publicación') ?>
                </a>
                <a href="<?= $solicitudesProveedorHref ?>" role="menuitem" class="user-dropdown-item">
                  <?= __t('nav_solicitudes_servicios', 'Solicitudes de servicios') ?>
                </a>
                <a href="<?= $perfilProfesionalHref ?>" role="menuitem" class="user-dropdown-item">
                  <?= __t('nav_perfil_profesional', 'Perfil profesional') ?>
                </a>
              <?php elseif (es_admin()): ?>
                <a href="<?= $panelAdminHref ?>"
                role="menuitem"
                class="user-dropdown-item<?= $activePage === 'panel-administrador' ? ' active' : '' ?>">
                <?= __t('nav_panel_admin') ?>
                </a>

                <a href="<?= ($cssPrefix === '.') ? 'views/solicitudes-docente.php' : 'solicitudes-docente.php' ?>"
                  role="menuitem"
                  class="user-dropdown-item<?= $activePage === 'solicitudes-docente' ? ' active' : '' ?>">
                  <?= __t('nav_solicitudes_docentes', 'Solicitudes docentes') ?>
                </a>

                <a href="<?= $panelProveedorHref ?>"
                  role="menuitem"
                  class="user-dropdown-item<?= $activePage === 'panel-proveedor' ? ' active' : '' ?>">
                  <?= __t('nav_panel_proveedor', 'Panel proveedor') ?>
                </a>

                <?php endif; ?>

              <div class="user-dropdown-divider" role="separator"></div>
              <a href="<?= $logoutHref ?>" role="menuitem" class="user-dropdown-item user-dropdown-item--logout">
                <?= __t('nav_cerrar_sesion') ?>
              </a>
            </div>
          </div>
        <?php endif; ?>
      </nav>
    </div>
  </header>

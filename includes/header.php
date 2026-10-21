<?php

/**
 * Responsabilidad: Encabezado global, navegación por rol y control de sesión.
 */

require_once __DIR__ . '/../php/auth/sesion.php';
iniciar_sesion();

require_once __DIR__ . '/../php/auth/roles.php';
require_once __DIR__ . '/../php/auth/guardia_onboarding.php';
require_once __DIR__ . '/../php/utils/i18n.php';
require_once __DIR__ . '/../php/utils/avatar_helper.php';

$current_lang = inicializar_i18n();

$title       = $title       ?? 'Classia';
$description = $description ?? 'Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada.';
$cssPrefix   = $cssPrefix   ?? '..';
$jsPrefix    = $jsPrefix    ?? '..';
$activePage  = $activePage  ?? '';
$publicBase  = isset($publicBase) ? rtrim((string)$publicBase, '/') : null;
$assetPrefix = $publicBase ?? $cssPrefix;

if ($publicBase !== null) {
    $indexHref    = $publicBase . '/index.php';
    $catalogoHref = $publicBase . '/views/catalogo.php';
    $carritoHref  = $publicBase . '/views/carrito.php';
    $loginHref    = $publicBase . '/views/login.php';
    $registroHref = $publicBase . '/views/registro.php';
    $cuentaHref   = $publicBase . '/views/usuario.php';
    $logoutHref   = $publicBase . '/php/auth/logout.php';
    $solicitarDocenteHref = $publicBase . '/views/solicitar-docente.php';
    $panelProveedorHref   = $publicBase . '/views/panel-proveedor.php';
    $panelAdminHref       = $publicBase . '/views/panel-administrador.php';
    $misSolicitudesHref = $publicBase . '/views/mis-solicitudes-servicios.php';
    $solicitudesProveedorHref = $publicBase . '/views/solicitudes-servicios.php';
    $perfilProfesionalHref = $publicBase . '/views/perfil-profesional.php';
    $eventosHref  = $publicBase . '/views/eventos.php';
    $noticiasHref = $publicBase . '/views/noticias.php';
    $notificacionesHref = $publicBase . '/views/notificaciones.php';
    $notificacionesBase = $publicBase . '/php/notificaciones';
} else {
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
    $eventosHref  = ($cssPrefix === '.') ? 'views/eventos.php'   : 'eventos.php';
    $noticiasHref = ($cssPrefix === '.') ? 'views/noticias.php'  : 'noticias.php';
    $notificacionesHref = ($cssPrefix === '.') ? 'views/notificaciones.php' : 'notificaciones.php';
    $notificacionesBase = ($cssPrefix === '.') ? 'php/notificaciones' : '../php/notificaciones';
}

$isAuth = esta_autenticado();
$currentUser = usuario_actual();
$defaultAvatarSrc = $publicBase !== null
    ? $publicBase . '/assets/images/default-avatar.svg'
    : obtener_avatar_default($assetPrefix);
$onboardingHref = $publicBase !== null ? $publicBase . '/views/primeros-pasos.php' : (($cssPrefix === '.') ? 'views/primeros-pasos.php' : 'primeros-pasos.php');
if (empty($skipOnboardingGuard)) {
    requerir_onboarding_completo($onboardingHref);
}

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
  <link rel="stylesheet" href="<?= $assetPrefix ?>/css/animation.css?v=<?= filemtime(__DIR__ . '/../css/animation.css') ?>" />
  <link rel="stylesheet" href="<?= $assetPrefix ?>/css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>" />
  <link rel="stylesheet" href="<?= $assetPrefix ?>/css/header-footer.css?v=<?= filemtime(__DIR__ . '/../css/header-footer.css') ?>" />
  <link rel="stylesheet" href="<?= $assetPrefix ?>/css/notificaciones.css?v=<?= filemtime(__DIR__ . '/../css/notificaciones.css') ?>" />
  <link rel="icon" type="image/png" href="<?= $assetPrefix ?>/assets/images/favicon.png" />
  <!-- Anti-FOUC: aplica el tema antes del primer paint -->
  <script>
    (function () {
      try {
        var saved = localStorage.getItem('classia-theme');
        if (saved === 'dark') {
          document.documentElement.setAttribute('data-theme', 'dark');
        } else if (saved === 'light') {
          document.documentElement.setAttribute('data-theme', 'light');
        }
        // Sin valor guardado: se usa prefers-color-scheme via CSS
      } catch (e) {}
    })();
  </script>
</head>
<body<?= ($bodyClass ?? '') ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
  <!-- Skip-link: acceso directo al contenido principal (WCAG 2.4.1) -->
  <a class="skip-link" href="#main-content">Saltar al contenido principal</a>
  <header class="site-header">
    <div class="site-header__inner">
      <a class="site-brand" href="<?= $indexHref ?>">
        <img src="<?= $assetPrefix ?>/assets/images/logo-classia.png" alt="Classia" />
      </a>
      <nav class="site-nav" aria-label="Navegación principal">
        <a href="<?= $indexHref ?>"<?= $activePage === 'inicio' ? ' aria-current="page"' : '' ?>><?= __t('nav_inicio') ?></a>
        <a href="<?= $catalogoHref ?>"<?= $activePage === 'catalogo' ? ' aria-current="page"' : '' ?>><?= __t('nav_catalogo') ?></a>
        <a href="<?= $eventosHref ?>"<?= $activePage === 'eventos' ? ' aria-current="page"' : '' ?>><?= __t('nav_eventos', 'Eventos') ?></a>
        <a href="<?= $noticiasHref ?>"<?= $activePage === 'noticias' ? ' aria-current="page"' : '' ?>><?= __t('nav_noticias', 'Noticias') ?></a>
        
        <?php if ($isAuth): ?>
        <a href="<?= $carritoHref ?>" class="nav-cart-btn<?= $activePage === 'carrito' ? ' active' : '' ?>" aria-label="<?= __t('nav_carrito') ?> (<?= $cartCount ?> items)">
          <svg class="nav-cart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="9" cy="21" r="1"></circle>
            <circle cx="20" cy="21" r="1"></circle>
            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
          </svg>
          <?php if ($cartCount > 0): ?>
            <span class="nav-cart-badge" id="cart-item-count"><?= $cartCount ?></span>
          <?php endif; ?>
        </a>
        <?php endif; ?>

        <?php if ($isAuth): ?>
          <div
            class="notifications-nav"
            data-notifications-root
            data-notifications-base="<?= htmlspecialchars($notificacionesBase, ENT_QUOTES, 'UTF-8') ?>"
            data-notifications-history="<?= htmlspecialchars($notificacionesHref, ENT_QUOTES, 'UTF-8') ?>"
            data-csrf-token="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
            <button
              class="notifications-toggle"
              type="button"
              aria-label="Notificaciones"
              aria-controls="notifications-panel"
              aria-expanded="false">
              <svg class="notifications-bell-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"></path>
                <path d="M10 21h4"></path>
              </svg>
              <span class="notifications-badge" data-notifications-count hidden>0</span>
            </button>
            <section class="notifications-panel" id="notifications-panel" aria-label="Notificaciones recientes" hidden>
              <div class="notifications-panel__header">
                <h2>Notificaciones</h2>
                <button type="button" class="notifications-mark-all" data-notifications-mark-all>Marcar todas como leídas</button>
              </div>
              <div class="notifications-list" data-notifications-list aria-live="polite">
                <p class="notifications-state">Cargando notificaciones...</p>
              </div>
              <a class="notifications-history-link" href="<?= htmlspecialchars($notificacionesHref, ENT_QUOTES, 'UTF-8') ?>">Ver todas las notificaciones</a>
            </section>
          </div>
        <?php endif; ?>

        <?php if (!$isAuth): ?>
          <div class="user-nav-dropdown">
            <a href="<?= $loginHref ?>" class="user-nav-trigger" title="Acceder a tu cuenta">
              <span class="user-nav-name"><?= __t('nav_cuenta') ?></span>
              <img src="<?= htmlspecialchars($defaultAvatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Cuenta" class="user-nav-avatar" />
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
            $navAvatarSrc = $publicBase !== null
                ? obtener_avatar_usuario($currentUser ?? [], $publicBase, 64)
                : obtener_avatar_usuario($currentUser ?? [], $cssPrefix, 64);
            if ($publicBase !== null && !preg_match('#^(?:https?://|/)#i', $navAvatarSrc)) {
                $navAvatarSrc = '/' . $navAvatarSrc;
            }
            $primerNombre = htmlspecialchars(explode(' ', trim($currentUser['nombre'] ?? 'Usuario'))[0]);
          ?>
          <div class="user-nav-dropdown">
            <a href="<?= $cuentaHref ?>" class="user-nav-trigger" title="Ir a mi cuenta (<?= $primerNombre ?>)">
              <span class="user-nav-name"><?= $primerNombre ?></span>
              <img src="<?= htmlspecialchars($navAvatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= $primerNombre ?>" class="user-nav-avatar" onerror="this.onerror=null;this.src='<?= htmlspecialchars($defaultAvatarSrc, ENT_QUOTES, 'UTF-8') ?>';" />
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
                <a href="<?= $perfilProfesionalHref ?>" role="menuitem" class="user-dropdown-item<?= $activePage === 'perfil-profesional' ? ' active' : '' ?>">
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
                <a href="<?= $eventosHref ?>" role="menuitem" class="user-dropdown-item">
                  <?= __t('nav_admin_eventos', 'Gestión de Eventos') ?>
                </a>
                <a href="<?= $noticiasHref ?>" role="menuitem" class="user-dropdown-item">
                  <?= __t('nav_admin_noticias', 'Gestión de Noticias') ?>
                </a>

                <?php endif; ?>

              <div class="user-dropdown-divider" role="separator"></div>
              <a href="<?= $logoutHref ?>" role="menuitem" class="user-dropdown-item user-dropdown-item--logout">
                <?= __t('nav_cerrar_sesion') ?>
              </a>
            </div>
          </div>
        <?php endif; ?>
        <!-- Botón de modo oscuro (RNF-17) -->
        <button
          class="btn-theme-toggle"
          id="theme-toggle-btn"
          type="button"
          aria-label="Cambiar a modo oscuro"
          title="Alternar modo claro/oscuro"
        >
          <!-- Sol (visible en modo oscuro) -->
          <svg class="icon-sun" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               aria-hidden="true" focusable="false">
            <circle cx="12" cy="12" r="5"/>
            <line x1="12" y1="1" x2="12" y2="3"/>
            <line x1="12" y1="21" x2="12" y2="23"/>
            <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
            <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
            <line x1="1" y1="12" x2="3" y2="12"/>
            <line x1="21" y1="12" x2="23" y2="12"/>
            <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
            <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
          </svg>
          <!-- Luna (visible en modo claro) -->
          <svg class="icon-moon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
               aria-hidden="true" focusable="false">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
          </svg>
        </button>
      </nav>
    </div>
  </header>

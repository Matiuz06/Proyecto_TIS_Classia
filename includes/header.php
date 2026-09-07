<?php

require_once __DIR__ . '/../php/auth/roles.php';

$title       = $title       ?? 'Classia';
$description = $description ?? 'Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada.';
$cssPrefix   = $cssPrefix   ?? '..';
$activePage  = $activePage  ?? '';

$indexHref    = ($cssPrefix === '.') ? 'index.php'           : '../index.php';
$catalogoHref = ($cssPrefix === '.') ? 'views/catalogo.php'  : 'catalogo.php';
$loginHref    = ($cssPrefix === '.') ? 'views/login.php'     : 'login.php';
$registroHref = ($cssPrefix === '.') ? 'views/registro.php'  : 'registro.php';
$cuentaHref   = ($cssPrefix === '.') ? 'views/usuario.php'   : 'usuario.php';
$logoutHref   = ($cssPrefix === '.') ? 'php/auth/logout.php' : '../php/auth/logout.php';
$solicitarDocenteHref = ($cssPrefix === '.') ? 'views/solicitar-docente.php' : 'solicitar-docente.php';
$panelProveedorHref   = ($cssPrefix === '.') ? 'views/panel-proveedor.php' : 'panel-proveedor.php';
$panelAdminHref       = ($cssPrefix === '.') ? 'views/panel-administrador.php' : 'panel-administrador.php';
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
      <nav class="site-nav" aria-label="Navegacion principal">
        <a href="<?= $indexHref ?>"<?= $activePage === 'inicio' ? ' aria-current="page"' : '' ?>>Inicio</a>
        <a href="<?= $catalogoHref ?>"<?= $activePage === 'catalogo' ? ' aria-current="page"' : '' ?>>Catalogo</a>

        <?php if (!esta_autenticado()): ?>
          <a href="<?= $loginHref ?>"<?= $activePage === 'cuenta' ? ' aria-current="page"' : '' ?>>Iniciar sesion</a>
          <a href="<?= $registroHref ?>">Registrarse</a>
        <?php else: ?>
          <a href="<?= $cuentaHref ?>"<?= $activePage === 'cuenta' ? ' aria-current="page"' : '' ?>>Mi cuenta</a>

          <?php if (es_estudiante()): ?>
            <a href="<?= $solicitarDocenteHref ?>"<?= $activePage === 'solicitar-docente' ? ' aria-current="page"' : '' ?>>Solicitar ser docente</a>
          <?php elseif (es_docente()): ?>
            <a href="<?= $panelProveedorHref ?>"<?= $activePage === 'panel-proveedor' ? ' aria-current="page"' : '' ?>>Panel proveedor</a>
          <?php elseif (es_admin()): ?>
            <a href="<?= $panelAdminHref ?>"<?= $activePage === 'panel-administrador' ? ' aria-current="page"' : '' ?>>Panel administrador</a>
          <?php endif; ?>

          <a href="<?= $logoutHref ?>">Cerrar sesion</a>
        <?php endif; ?>
      </nav>
    </div>
  </header>

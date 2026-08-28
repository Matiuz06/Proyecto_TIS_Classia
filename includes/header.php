<?php

$title       = $title       ?? 'Classia';
$description = $description ?? 'Classia conecta clientes, proveedores y administradores en una plataforma educativa clara y organizada.';
$cssPrefix   = $cssPrefix   ?? '..';
$activePage  = $activePage  ?? '';

$indexHref    = ($cssPrefix === '.') ? 'index.php'           : '../index.php';
$catalogoHref = ($cssPrefix === '.') ? 'html/catalogo.php'   : 'catalogo.php';
$carritoHref  = ($cssPrefix === '.') ? 'html/carrito.php'    : 'carrito.php';
$loginHref    = ($cssPrefix === '.') ? 'html/login.php'      : 'login.php';
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
  <link rel="icon" type="image/png" href="<?= $cssPrefix ?>/assets/favicon.png" />
</head>
<body<?= ($bodyClass ?? '') ? ' class="' . htmlspecialchars($bodyClass) . '"' : '' ?>>
  <header class="site-header">
    <div class="site-header__inner">
      <a class="site-brand" href="<?= $indexHref ?>">
        <img src="<?= $cssPrefix ?>/assets/logoC.png" alt="Classia" />
      </a>
      <nav class="site-nav" aria-label="Navegación principal">
        <a href="<?= $indexHref ?>"<?= $activePage === 'inicio'   ? ' aria-current="page"' : '' ?>>Inicio</a>
        <a href="<?= $catalogoHref ?>"<?= $activePage === 'catalogo' ? ' aria-current="page"' : '' ?>>Catálogo</a>
        <a href="<?= $carritoHref ?>"<?= $activePage === 'carrito'  ? ' aria-current="page"' : '' ?>>Carrito</a>
        <a href="<?= $loginHref ?>"<?= $activePage === 'cuenta'   ? ' aria-current="page"' : '' ?>>Mi cuenta</a>
      </nav>
    </div>
  </header>

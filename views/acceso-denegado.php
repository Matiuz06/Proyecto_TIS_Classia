<?php
require_once '../php/auth/session.php';

requerir_autenticacion('login.php');

$title = 'Acceso denegado';
$description = 'Aviso de permisos insuficientes en Classia.';
$cssPrefix = '..';
$activePage = 'cuenta';
include '../includes/header.php';
?>

<main class="card-container motion-entry">
  <img style="width: 200px;" src="<?= $cssPrefix ?>/assets/images/logo-classia.png" alt="Classia" />
  <br>
  <h1>No tenes permisos para acceder a esta pagina.</h1>
  <p>
    La cuenta con la que iniciaste sesion no tiene autorizacion para utilizar
    esta seccion.
  </p>
  <p>
    <a class="btn" href="../index.php">Volver al inicio</a>
  </p>
</main>

<?php include '../includes/footer.php'; ?>

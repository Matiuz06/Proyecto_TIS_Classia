<?php

require_once __DIR__ . '/../php/auth/sesion.php';
iniciar_sesion();

$title = 'Notificaciones';
$description = 'Historial de notificaciones de Classia.';
$cssPrefix = '..';
$jsPrefix = '..';
$activePage = 'notificaciones';

include '../includes/header.php';
?>

<main id="main-content" class="notifications-page motion-entry" data-notifications-history data-page="1" data-total="0">
  <header class="notifications-page__header">
    <div>
      <h1>Notificaciones</h1>
      <p>Consultá tus avisos recientes y accedé directamente al contenido relacionado.</p>
    </div>
    <button type="button" class="btn btn-primary" data-notifications-mark-all>Marcar todas como leídas</button>
  </header>

  <div class="notifications-page__tools">
    <label for="notifications-filter">Filtrar notificaciones</label>
    <select id="notifications-filter" class="notifications-filter" data-notifications-filter>
      <option value="all">Todas</option>
      <option value="unread">No leídas</option>
      <option value="read">Leídas</option>
    </select>
  </div>

  <section class="notifications-history-list" data-notifications-history-list aria-live="polite">
    <p class="notifications-state">Cargando notificaciones...</p>
  </section>

  <nav class="notifications-pagination" aria-label="Paginación de notificaciones">
    <button type="button" data-notifications-previous disabled>Anterior</button>
    <span data-notifications-page-label>Página 1</span>
    <button type="button" data-notifications-next disabled>Siguiente</button>
  </nav>
</main>

<?php include '../includes/footer.php'; ?>

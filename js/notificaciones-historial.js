(function () {
  'use strict';

  function initHistory() {
    const history = document.querySelector('[data-notifications-history]');
    if (!history) return;

    const shared = window.ClassiaNotifications;
    const header = document.querySelector('[data-notifications-root]');
    const base = header?.dataset.notificationsBase || new URL('../php/notificaciones', document.baseURI).href;
    const csrfToken = header?.dataset.csrfToken || '';
    const api = shared?.createApi?.(base, csrfToken);
    const helpers = shared?.helpers;
    const list = history.querySelector('[data-notifications-history-list]');

    if (!api || !helpers || !list) {
      console.error('No se pudo inicializar el historial de notificaciones.');
      if (list) showState(list, 'No se pudo cargar el historial.');
      return;
    }

    let loading = false;

    function showState(container, message) {
      const state = document.createElement('p');
      state.className = 'notifications-state';
      state.textContent = message;
      container.replaceChildren(state);
    }

    function showError() {
      const state = document.createElement('div');
      state.className = 'notifications-state';
      const message = document.createElement('p');
      message.textContent = 'No se pudo cargar el historial.';
      const retry = document.createElement('button');
      retry.type = 'button';
      retry.className = 'btn';
      retry.textContent = 'Reintentar';
      retry.addEventListener('click', loadHistory);
      state.append(message, retry);
      list.replaceChildren(state);
    }

    function setCount(value) {
      const count = header?.querySelector('[data-notifications-count]');
      const total = Math.max(0, Number(value) || 0);
      if (count) {
        count.textContent = total > 99 ? '99+' : String(total);
        count.hidden = total === 0;
      }
    }

    function createHistoryItem(item) {
      const element = item.url_destino ? document.createElement('a') : document.createElement('article');
      element.className = `notification-item${Number(item.leida) ? '' : ' notification-item--unread'}`;

      if (item.url_destino) {
        element.href = helpers.resolveInternalUrl(item.url_destino, base);
        element.addEventListener('click', function (event) {
          event.preventDefault();
          const destination = element.href;
          api.markRead(item.id_notificacion)
            .catch((error) => console.error('No se pudo marcar la notificacion:', error))
            .finally(() => window.location.assign(destination));
        });
      }

      const icon = document.createElement('span');
      icon.className = 'notification-item__icon';
      icon.appendChild(helpers.iconFor(item.categoria));
      const content = document.createElement('span');
      const title = document.createElement('strong');
      title.className = 'notification-item__title';
      title.textContent = item.titulo || 'Notificacion';
      const message = document.createElement('span');
      message.className = 'notification-item__message';
      message.textContent = item.mensaje || '';
      const date = document.createElement('time');
      date.className = 'notification-item__date';
      date.dateTime = item.fecha_creacion || '';
      date.title = helpers.fullDate(item.fecha_creacion);
      date.textContent = helpers.formatDate(item.fecha_creacion);
      content.append(title, message, date);
      element.append(icon, content);

      if (!Number(item.leida)) {
        const action = document.createElement('button');
        action.type = 'button';
        action.className = 'notification-action';
        action.textContent = 'Marcar como leida';
        action.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          api.markRead(item.id_notificacion)
            .then((data) => {
              setCount(data.unread_count);
              loadHistory();
            })
            .catch((error) => console.error('No se pudo marcar la notificacion:', error));
        });
        element.appendChild(action);
      }
      return element;
    }

    function updatePagination(pagination) {
      const page = Number(pagination.page || 1);
      const total = Number(pagination.total || 0);
      const perPage = Number(pagination.per_page || 20);
      const pages = Math.max(1, Math.ceil(total / perPage));
      const previous = history.querySelector('[data-notifications-previous]');
      const next = history.querySelector('[data-notifications-next]');
      const label = history.querySelector('[data-notifications-page-label]');
      if (previous) previous.disabled = page <= 1;
      if (next) next.disabled = page >= pages;
      if (label) label.textContent = `Pagina ${page} de ${pages}`;
    }

    async function loadHistory() {
      if (loading) return;
      loading = true;
      showState(list, 'Cargando notificaciones...');
      const page = Number(history.dataset.page || 1);
      const filter = history.querySelector('[data-notifications-filter]')?.value || 'all';

      try {
        const data = await api.getJson(`${api.endpoints.obtener}?limite=20&pagina=${page}&filtro=${encodeURIComponent(filter)}`);
        const items = Array.isArray(data.items) ? data.items : [];
        setCount(data.unread_count);
        list.replaceChildren();
        if (!items.length) showState(list, 'No tenes notificaciones para mostrar.');
        else items.forEach((item) => list.appendChild(createHistoryItem(item)));
        updatePagination(data.pagination || {});
      } catch (error) {
        console.error('No se pudo cargar el historial de notificaciones:', error);
        showError();
      } finally {
        loading = false;
      }
    }

    async function markAll() {
      try {
        const data = await api.markAll();
        setCount(data.unread_count);
        loadHistory();
      } catch (error) {
        console.error('No se pudieron marcar todas las notificaciones:', error);
      }
    }

    history.querySelector('[data-notifications-filter]')?.addEventListener('change', function () {
      history.dataset.page = '1';
      loadHistory();
    });
    history.querySelector('[data-notifications-previous]')?.addEventListener('click', function () {
      history.dataset.page = String(Math.max(1, Number(history.dataset.page || 1) - 1));
      loadHistory();
    });
    history.querySelector('[data-notifications-next]')?.addEventListener('click', function () {
      history.dataset.page = String(Number(history.dataset.page || 1) + 1);
      loadHistory();
    });
    history.querySelector('[data-notifications-mark-all]')?.addEventListener('click', markAll);
    loadHistory();
  }

  function startHistory() {
    if (window.ClassiaNotifications?.createApi) {
      initHistory();
    } else {
      document.addEventListener('classia-notifications-ready', initHistory, { once: true });
    }
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', startHistory, { once: true });
  } else {
    startHistory();
  }
}());

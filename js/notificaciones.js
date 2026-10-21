(function () {
  'use strict';

  function initNotifications() {
    const root = document.querySelector('[data-notifications-root]');
    if (!root) return;

    const base = root.dataset.notificationsBase;
    const historyHref = root.dataset.notificationsHistory;
    const csrfToken = root.dataset.csrfToken || '';
    const toggle = root.querySelector('.notifications-toggle');
    const panel = root.querySelector('.notifications-panel');
    const list = root.querySelector('[data-notifications-list]');
    const count = root.querySelector('[data-notifications-count]');
    const markAllButton = root.querySelector('[data-notifications-mark-all]');
    const history = document.querySelector('[data-notifications-history]');
    let loading = false;
    let pollTimer = null;
    let recentItems = [];

    const endpoints = {
      obtener: `${base}/obtener.php`,
      contador: `${base}/contador.php`,
      marcar: `${base}/marcar_leida.php`,
      marcarTodas: `${base}/marcar_todas_leidas.php`,
    };

    function resolveInternalUrl(path) {
      const projectRoot = new URL('../..', `${new URL(`${base}/`, document.baseURI)}`);
      return new URL(String(path || '').replace(/^\/+/, ''), projectRoot).href;
    }

    function setCount(value) {
      const total = Math.max(0, Number(value) || 0);
      count.textContent = total > 99 ? '99+' : String(total);
      count.hidden = total === 0;
      toggle.setAttribute('aria-label', total > 0
        ? `Notificaciones, ${total} sin leer`
        : 'Notificaciones');
    }

    function parseDate(value) {
      if (!value) return null;
      const normalized = String(value).replace(' ', 'T');
      const date = new Date(normalized);
      return Number.isNaN(date.getTime()) ? null : date;
    }

    function formatDate(value) {
      const date = parseDate(value);
      if (!date) return '';
      const diffSeconds = Math.round((date.getTime() - Date.now()) / 1000);
      const absolute = Math.abs(diffSeconds);
      const unit = absolute < 60 ? 'second' : absolute < 3600 ? 'minute' : absolute < 86400 ? 'hour' : 'day';
      const divisor = unit === 'second' ? 1 : unit === 'minute' ? 60 : unit === 'hour' ? 3600 : 86400;
      const amount = Math.round(diffSeconds / divisor);
      let relative = '';
      try {
        relative = new Intl.RelativeTimeFormat('es', { numeric: 'auto' }).format(amount, unit);
      } catch (error) {
        relative = date.toLocaleString('es-UY');
      }
      return relative;
    }

    function fullDate(value) {
      const date = parseDate(value);
      return date ? date.toLocaleString('es-UY', { dateStyle: 'medium', timeStyle: 'short' }) : '';
    }

    function iconFor(category) {
      const paths = {
        tareas: 'M6 3h12v18H6z M9 7h6 M9 11h6 M9 15h4',
        compras: 'M3 4h2l2.5 11h9L19 8H6 M10 19a1 1 0 1 0 0 .01 M17 19a1 1 0 1 0 0 .01',
        solicitudes: 'M5 4h14v16H5z M8 8h8 M8 12h8 M8 16h5',
        foro: 'M4 5h16v11H8l-4 4z',
        sistema: 'M12 3l1.8 5.4H19l-4.2 3.1 1.6 5.5-4.4-3.2-4.4 3.2 1.6-5.5L5 8.4h5.2z',
      };
      const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
      svg.setAttribute('viewBox', '0 0 24 24');
      svg.setAttribute('fill', 'none');
      svg.setAttribute('stroke', 'currentColor');
      svg.setAttribute('stroke-width', '1.8');
      svg.setAttribute('stroke-linecap', 'round');
      svg.setAttribute('stroke-linejoin', 'round');
      svg.setAttribute('aria-hidden', 'true');
      const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
      path.setAttribute('d', paths[category] || 'M12 3a9 9 0 1 0 9 9');
      svg.appendChild(path);
      return svg;
    }

    function buildItem(item, options) {
      const isHistory = Boolean(options && options.history);
      const element = item.url_destino ? document.createElement('a') : document.createElement('article');
      element.className = `notification-item${Number(item.leida) ? '' : ' notification-item--unread'}`;
      if (item.url_destino) {
        element.href = resolveInternalUrl(item.url_destino);
        element.addEventListener('click', function (event) {
          event.preventDefault();
          const destination = element.href;
          markRead(item.id_notificacion, false, { silent: true }).finally(() => {
            window.location.assign(destination);
          });
        });
      }

      const icon = document.createElement('span');
      icon.className = 'notification-item__icon';
      icon.appendChild(iconFor(item.categoria));

      const content = document.createElement('span');
      const title = document.createElement('strong');
      title.className = 'notification-item__title';
      title.textContent = item.titulo || 'Notificación';
      const message = document.createElement('span');
      message.className = 'notification-item__message';
      message.textContent = item.mensaje || '';
      const date = document.createElement('time');
      date.className = 'notification-item__date';
      date.dateTime = item.fecha_creacion || '';
      date.title = fullDate(item.fecha_creacion);
      date.textContent = formatDate(item.fecha_creacion);
      content.append(title, message, date);
      element.append(icon, content);

      if (isHistory && !Number(item.leida)) {
        const action = document.createElement('button');
        action.type = 'button';
        action.className = 'notification-action';
        action.textContent = 'Marcar como leída';
        action.addEventListener('click', function (event) {
          event.preventDefault();
          event.stopPropagation();
          markRead(item.id_notificacion, true);
        });
        element.appendChild(action);
      }
      return element;
    }

    function showState(container, message) {
      const state = document.createElement('p');
      state.className = 'notifications-state';
      state.textContent = message;
      container.replaceChildren(state);
    }

    function renderRecent(items) {
      if (!list) return;
      list.replaceChildren();
      if (!items.length) {
        showState(list, 'No tenés notificaciones nuevas.');
        return;
      }
      items.slice(0, 5).forEach((item) => list.appendChild(buildItem(item, { history: false })));
    }

    async function getJson(url) {
      const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const raw = await response.text();
      let data;
      try {
        data = JSON.parse(raw);
      } catch (error) {
        throw new Error('Respuesta inválida del servidor.');
      }
      if (!response.ok || !data.success) throw new Error(data.message || 'No se pudo completar la operación.');
      return data;
    }

    async function loadRecent() {
      if (loading || !list) return;
      loading = true;
      try {
        const data = await getJson(`${endpoints.obtener}?limite=5&pagina=1`);
        recentItems = Array.isArray(data.items) ? data.items : [];
        renderRecent(recentItems);
        setCount(data.unread_count);
      } catch (error) {
        showState(list, 'No se pudieron cargar las notificaciones.');
      } finally {
        loading = false;
      }
    }

    async function refreshCount() {
      try {
        const data = await getJson(endpoints.contador);
        setCount(data.unread_count);
      } catch (error) {
        // El contador no debe interrumpir el resto de la navegación.
      }
    }

    async function markRead(id, reloadHistory, options = {}) {
      try {
        const response = await fetch(endpoints.marcar, {
          method: 'POST',
          credentials: 'same-origin',
          keepalive: true,
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': csrfToken },
          body: JSON.stringify({ id_notificacion: Number(id), csrf_token: csrfToken }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'No se pudo actualizar la notificación.');
        setCount(data.unread_count);
        if (reloadHistory && history) loadHistory();
        else loadRecent();
      } catch (error) {
        console.error('No se pudo marcar la notificación como leída:', error);
        if (!options.silent && window.ClassiaToast) {
          window.ClassiaToast.error({ title: 'Notificaciones', description: error.message });
        }
      }
    }

    async function markAllNotifications() {
      try {
        const response = await fetch(endpoints.marcarTodas, {
          method: 'POST',
          credentials: 'same-origin',
          headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': csrfToken },
          body: JSON.stringify({ csrf_token: csrfToken }),
        });
        const data = await response.json();
        if (!response.ok || !data.success) throw new Error(data.message || 'No se pudieron marcar las notificaciones.');
        setCount(data.unread_count);
        recentItems = recentItems.map((item) => ({ ...item, leida: 1 }));
        renderRecent(recentItems);
        if (history) loadHistory();
      } catch (error) {
        if (window.ClassiaToast) window.ClassiaToast.error({ title: 'Notificaciones', description: error.message });
      }
    }

    function closePanel() {
      if (!panel || panel.hidden) return;
      panel.hidden = true;
      toggle.setAttribute('aria-expanded', 'false');
    }

    async function loadHistory() {
      if (!history) return;
      const page = Number(history.dataset.page || 1);
      const filter = history.querySelector('[data-notifications-filter]')?.value || 'all';
      const historyList = history.querySelector('[data-notifications-history-list]');
      if (!historyList) return;
      showState(historyList, 'Cargando notificaciones...');
      try {
        const data = await getJson(`${endpoints.obtener}?limite=20&pagina=${page}`);
        const items = (Array.isArray(data.items) ? data.items : []).filter((item) => (
          filter === 'all' || (filter === 'unread' && !Number(item.leida)) || (filter === 'read' && Number(item.leida))
        ));
        history.dataset.total = String(data.pagination?.total || 0);
        setCount(data.unread_count);
        historyList.replaceChildren();
        if (!items.length) showState(historyList, 'No hay notificaciones para este filtro.');
        else items.forEach((item) => historyList.appendChild(buildItem(item, { history: true })));
        updatePagination(history, data.pagination || {});
      } catch (error) {
        showState(historyList, 'No se pudo cargar el historial.');
      }
    }

    function updatePagination(container, pagination) {
      const page = Number(pagination.page || 1);
      const total = Number(pagination.total || 0);
      const perPage = Number(pagination.per_page || 20);
      const pages = Math.max(1, Math.ceil(total / perPage));
      const previous = container.querySelector('[data-notifications-previous]');
      const next = container.querySelector('[data-notifications-next]');
      const label = container.querySelector('[data-notifications-page-label]');
      if (previous) previous.disabled = page <= 1;
      if (next) next.disabled = page >= pages;
      if (label) label.textContent = `Página ${page} de ${pages}`;
    }

    toggle?.addEventListener('click', function () {
      const shouldOpen = panel.hidden;
      panel.hidden = !shouldOpen;
      toggle.setAttribute('aria-expanded', String(shouldOpen));
      if (shouldOpen) loadRecent();
    });

    markAllButton?.addEventListener('click', markAllNotifications);
    document.addEventListener('click', function (event) {
      if (!root.contains(event.target)) closePanel();
    });
    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') closePanel();
    });

    history?.querySelector('[data-notifications-filter]')?.addEventListener('change', function () {
      history.dataset.page = '1';
      loadHistory();
    });
    history?.querySelector('[data-notifications-previous]')?.addEventListener('click', function () {
      history.dataset.page = String(Math.max(1, Number(history.dataset.page || 1) - 1));
      loadHistory();
    });
    history?.querySelector('[data-notifications-next]')?.addEventListener('click', function () {
      history.dataset.page = String(Number(history.dataset.page || 1) + 1);
      loadHistory();
    });
    history?.querySelector('[data-notifications-mark-all]')?.addEventListener('click', markAllNotifications);

    function schedulePoll() {
      window.clearTimeout(pollTimer);
      pollTimer = window.setTimeout(async function () {
        if (!document.hidden) await refreshCount();
        schedulePoll();
      }, 60000);
    }

    document.addEventListener('visibilitychange', function () {
      if (!document.hidden) refreshCount();
    });

    setCount(0);
    refreshCount();
    if (history) loadHistory();
    schedulePoll();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initNotifications);
  else initNotifications();
}());

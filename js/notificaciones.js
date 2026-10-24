(function () {
  'use strict';

  function createNotificationsApi(base, csrfToken) {
    const endpoints = {
      obtener: `${base}/obtener.php`,
      contador: `${base}/contador.php`,
      marcar: `${base}/marcar_leida.php`,
      marcarTodas: `${base}/marcar_todas_leidas.php`,
    };

    async function getJson(url) {
      const response = await fetch(url, { credentials: 'same-origin', headers: { Accept: 'application/json' } });
      const raw = await response.text();
      let data;
      try {
        data = JSON.parse(raw);
      } catch (error) {
        const parseError = new Error('Respuesta invalida del servidor.');
        parseError.status = response.status;
        throw parseError;
      }
      if (!response.ok || !data.success) {
        const requestError = new Error(data.message || 'No se pudo completar la operacion.');
        requestError.status = response.status;
        throw requestError;
      }
      return data;
    }

    async function markRead(id) {
      const response = await fetch(endpoints.marcar, {
        method: 'POST', credentials: 'same-origin', keepalive: true,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ id_notificacion: Number(id), csrf_token: csrfToken }),
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'No se pudo actualizar la notificacion.');
      return data;
    }

    async function markAll() {
      const response = await fetch(endpoints.marcarTodas, {
        method: 'POST', credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ csrf_token: csrfToken }),
      });
      const data = await response.json();
      if (!response.ok || !data.success) throw new Error(data.message || 'No se pudieron marcar las notificaciones.');
      return data;
    }

    return { endpoints, getJson, markRead, markAll };
  }

  window.ClassiaNotifications = window.ClassiaNotifications || {};
  window.ClassiaNotifications.createApi = createNotificationsApi;

  function parseDate(value) {
    if (!value) return null;
    const date = new Date(String(value).replace(' ', 'T'));
    return Number.isNaN(date.getTime()) ? null : date;
  }

  function formatDate(value) {
    const date = parseDate(value);
    if (!date) return '';
    const seconds = Math.round((date.getTime() - Date.now()) / 1000);
    const absolute = Math.abs(seconds);
    const unit = absolute < 60 ? 'second' : absolute < 3600 ? 'minute' : absolute < 86400 ? 'hour' : 'day';
    const divisor = unit === 'second' ? 1 : unit === 'minute' ? 60 : unit === 'hour' ? 3600 : 86400;
    const amount = Math.round(seconds / divisor);
    try {
      return new Intl.RelativeTimeFormat('es', { numeric: 'auto' }).format(amount, unit);
    } catch (error) {
      return date.toLocaleString('es-UY');
    }
  }

  function fullDate(value) {
    const date = parseDate(value);
    return date ? date.toLocaleString('es-UY', { dateStyle: 'medium', timeStyle: 'short' }) : '';
  }

  function notificationIconFile(category, title, entityType) {
    const normalizedCategory = String(category || '').toLocaleLowerCase();
    const normalizedTitle = String(title || '').toLocaleLowerCase();
    const normalizedEntityType = String(entityType || '').toLocaleLowerCase();

    if (normalizedCategory === 'valoraciones' || normalizedEntityType === 'valoracion') return 'valoraciones.svg';
    if (normalizedEntityType === 'solicitud_docente' || normalizedTitle.includes('docente')) return 'solicitudes_docentes.svg';
    if (normalizedEntityType === 'solicitud_mensaje' || normalizedEntityType === 'foro_mensaje' || normalizedCategory === 'foro' || normalizedTitle.includes('mensaje')) return 'mensajes.svg';
    if (normalizedCategory === 'compras' || normalizedTitle.includes('servicio contratado') || normalizedTitle.includes('pago')) return 'pagos.svg';
    if (normalizedCategory === 'tareas') {
      return normalizedTitle.includes('correg') || normalizedTitle.includes('devoluc') || normalizedTitle.includes('calific')
        ? 'correcciones.svg'
        : 'tareas.svg';
    }
    if (normalizedEntityType === 'solicitud_servicio' || normalizedCategory === 'solicitudes') return 'solicitudes_servicios.svg';
    if (normalizedCategory === 'cursos') return 'cursos.svg';

    return 'cursos.svg';
  }

  function iconFor(category, title, entityType, base) {
    const projectRoot = new URL('../..', `${new URL(`${base}/`, document.baseURI)}`);
    const image = document.createElement('img');
    image.src = new URL(`assets/images/icons/${notificationIconFile(category, title, entityType)}`, projectRoot).href;
    image.alt = '';
    image.setAttribute('aria-hidden', 'true');
    return image;
  }

  function showState(container, message) {
    const state = document.createElement('p');
    state.className = 'notifications-state';
    state.textContent = message;
    container.replaceChildren(state);
  }

  function resolveInternalUrl(path, base) {
    const projectRoot = new URL('../..', `${new URL(`${base}/`, document.baseURI)}`);
    return new URL(String(path || '').replace(/^\/+/, ''), projectRoot).href;
  }

  function createItem(item, api, base) {
    const element = item.url_destino ? document.createElement('a') : document.createElement('article');
    element.className = `notification-item${Number(item.leida) ? '' : ' notification-item--unread'}`;
    if (item.url_destino) {
      element.href = resolveInternalUrl(item.url_destino, base);
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
    icon.appendChild(iconFor(item.categoria, item.titulo, item.tipo_entidad, base));
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
    date.title = fullDate(item.fecha_creacion);
    date.textContent = formatDate(item.fecha_creacion);
    content.append(title, message, date);
    element.append(icon, content);

    return element;
  }

  window.ClassiaNotifications.helpers = {
    formatDate,
    fullDate,
    iconFor,
    resolveInternalUrl,
  };
  document.dispatchEvent(new Event('classia-notifications-ready'));

  function initNotifications() {
    const header = document.querySelector('[data-notifications-root]');
    if (!header) return;

    const base = header?.dataset.notificationsBase || new URL('../php/notificaciones', document.baseURI).href;
    const api = createNotificationsApi(base, header?.dataset.csrfToken || '');
    const count = header?.querySelector('[data-notifications-count]');
    const toggle = header?.querySelector('.notifications-toggle');
    const panel = header?.querySelector('.notifications-panel');
    const recentList = header?.querySelector('[data-notifications-list]');
    let recentItems = [];
    let recentLoading = false;
    let countLoading = false;
    let pollingTimer = null;
    let pollingStopped = false;
    let currentUnreadCount = null;
    let stateRevision = 0;
    let recentRequestId = 0;

    function setCount(value) {
      const total = Math.max(0, Number(value) || 0);
      currentUnreadCount = total;
      if (count) {
        count.textContent = total > 99 ? '99+' : String(total);
        count.hidden = total === 0;
      }
      if (toggle) toggle.setAttribute('aria-label', total > 0 ? `Notificaciones, ${total} sin leer` : 'Notificaciones');
    }

    function renderRecent() {
      if (!recentList) return;
      recentList.replaceChildren();
      if (!recentItems.length) {
        showState(recentList, 'No tenes notificaciones nuevas.');
        return;
      }
      recentItems.slice(0, 5).forEach((item) => recentList.appendChild(createItem(item, api, base)));
    }

    async function loadRecent() {
      if (!recentList || recentLoading) return;
      recentLoading = true;
      const requestId = ++recentRequestId;
      const revision = stateRevision;
      try {
        const data = await api.getJson(`${api.endpoints.obtener}?limite=5&pagina=1`);
        if (requestId !== recentRequestId || revision !== stateRevision) return;
        recentItems = Array.isArray(data.items) ? data.items : [];
        renderRecent();
        setCount(data.unread_count);
      } catch (error) {
        if (error.status === 401 || error.status === 403) stopPolling();
        if (requestId !== recentRequestId || revision !== stateRevision) return;
        console.error('No se pudieron cargar las notificaciones recientes:', error);
        showState(recentList, 'No se pudieron cargar las notificaciones.');
      } finally {
        recentLoading = false;
      }
    }

    async function refreshCount() {
      if (countLoading || pollingStopped) return;
      countLoading = true;
      const revision = stateRevision;
      try {
        const data = await api.getJson(api.endpoints.contador);
        if (revision !== stateRevision) return;
        const nextUnreadCount = Math.max(0, Number(data.unread_count) || 0);
        const countChanged = currentUnreadCount !== null && nextUnreadCount !== currentUnreadCount;
        setCount(nextUnreadCount);
        if (countChanged && panel && !panel.hidden) loadRecent();
      } catch (error) {
        if (error.status === 401 || error.status === 403) stopPolling();
        console.error('No se pudo actualizar el contador de notificaciones:', error);
      } finally {
        countLoading = false;
      }
    }

    function stopPolling() {
      pollingStopped = true;
      if (pollingTimer !== null) {
        window.clearInterval(pollingTimer);
        pollingTimer = null;
      }
    }

    async function markAll() {
      stateRevision += 1;
      recentRequestId += 1;
      try {
        const data = await api.markAll();
        setCount(data.unread_count);
        recentItems = recentItems.map((item) => ({ ...item, leida: 1 }));
        renderRecent();
      } catch (error) {
        console.error('No se pudieron marcar todas las notificaciones:', error);
      }
    }

    if (header) {
      const markAllButton = header.querySelector('[data-notifications-mark-all]');
      toggle?.addEventListener('click', function () {
        const shouldOpen = panel.hidden;
        panel.hidden = !shouldOpen;
        toggle.setAttribute('aria-expanded', String(shouldOpen));
        if (shouldOpen) loadRecent();
      });
      markAllButton?.addEventListener('click', markAll);
      document.addEventListener('click', function (event) {
        if (!header.contains(event.target) && panel && !panel.hidden) {
          panel.hidden = true;
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
      document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && panel && !panel.hidden) {
          panel.hidden = true;
          toggle.setAttribute('aria-expanded', 'false');
        }
      });
      refreshCount();
      pollingTimer = window.setInterval(function () {
        if (!document.hidden) refreshCount();
      }, 10000);
      document.addEventListener('visibilitychange', function () {
        if (!document.hidden && !pollingStopped) refreshCount();
      });
    }

  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initNotifications, { once: true });
  } else {
    initNotifications();
  }
}());

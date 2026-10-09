(function () {
  "use strict";

  const ICONS = {
    success: ["M20 6 9 17l-5-5"],
    error: ["M18 6 6 18", "M6 6l12 12"],
    warning: ["M12 7v7", "M12 17h.01", "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"],
    info: ["M12 16v-4", "M12 8h.01", "M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z"],
    file: ["M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z", "M14 2v6h6"],
    upload: ["M12 16V4", "M7 9l5-5 5 5", "M5 20h14"],
    download: ["M12 4v12", "M7 11l5 5 5-5", "M5 20h14"],
    save: ["M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z", "M17 21v-8H7v8", "M7 3v5h8"]
  };
  const DURATIONS = { success: 4500, info: 5000, warning: 6000, error: 7000, action: 5000, custom: 5000 };
  const SAFE_ICONS = new Set(["file", "upload", "download", "save", "success", "error", "warning", "info"]);
  const toasts = new Map();
  let seq = 0;

  function getContainer() {
    let container = document.getElementById("classia-toast-container");
    if (!container) {
      container = document.createElement("div");
      container.id = "classia-toast-container";
      container.className = "classia-toast-container";
      container.setAttribute("aria-live", "polite");
      container.setAttribute("aria-atomic", "false");
      document.body.appendChild(container);
    }
    return container;
  }

  function iconNode(name, loading) {
    if (loading) {
      const spinner = document.createElement("span");
      spinner.className = "classia-toast__spinner";
      return spinner;
    }
    const svg = document.createElementNS("http://www.w3.org/2000/svg", "svg");
    svg.setAttribute("viewBox", "0 0 24 24");
    svg.setAttribute("aria-hidden", "true");
    svg.setAttribute("focusable", "false");
    (ICONS[name] || ICONS.info).forEach((d) => {
      const path = document.createElementNS("http://www.w3.org/2000/svg", "path");
      path.setAttribute("d", d);
      svg.appendChild(path);
    });
    return svg;
  }

  function normalize(input) {
    if (typeof input === "string") return { title: input };
    return input && typeof input === "object" ? input : {};
  }

  function requireTitle(opts) {
    if (!opts.title) throw new TypeError("ClassiaToast requiere title.");
  }

  function safeHref(href) {
    const value = String(href || "");
    return /^\s*javascript:/i.test(value) ? "#" : value;
  }

  function setTimer(id, duration) {
    const data = toasts.get(id);
    if (!data || duration === 0 || data.loading) return;
    data.remaining = duration;
    data.startedAt = Date.now();
    data.timer = window.setTimeout(() => dismiss(id), duration);
  }

  function pause(id) {
    const data = toasts.get(id);
    if (!data || !data.timer) return;
    window.clearTimeout(data.timer);
    data.timer = null;
    data.remaining = Math.max(0, data.remaining - (Date.now() - data.startedAt));
  }

  function resume(id) {
    const data = toasts.get(id);
    if (!data || data.timer || data.loading || data.duration === 0) return;
    if (data.remaining <= 0) {
      dismiss(id);
      return;
    }
    setTimer(id, data.remaining || data.duration);
  }

  function dismiss(id) {
    const data = toasts.get(id);
    if (!data) return;
    if (data.timer) window.clearTimeout(data.timer);
    if (data.leaveTimer) window.clearTimeout(data.leaveTimer);
    data.node.classList.add("is-leaving");
    data.leaveTimer = window.setTimeout(() => {
      data.node.remove();
      toasts.delete(id);
    }, 220);
  }

  function render(id, type, input, existing) {
    const opts = normalize(input);
    requireTitle(opts);

    const finalType = type === "action" ? (opts.type || "info") : type;
    const iconName = opts.icon && SAFE_ICONS.has(opts.icon) ? opts.icon : finalType;
    const hasBody = Boolean(opts.description || opts.action);
    const loading = finalType === "loading";
    const duration = loading ? 0 : (opts.duration ?? DURATIONS[type] ?? DURATIONS[finalType] ?? 5000);
    const role = finalType === "error" || finalType === "warning" ? "alert" : "status";
    const toast = existing || document.createElement("article");

    toast.className = [
      "classia-toast",
      `classia-toast--${finalType}`,
      hasBody ? "classia-toast--expanded" : "classia-toast--compact",
      opts.action ? "classia-toast--has-action" : ""
    ].filter(Boolean).join(" ");
    toast.setAttribute("role", role);
    toast.setAttribute("aria-busy", loading ? "true" : "false");
    toast.replaceChildren();

    const head = document.createElement("div");
    head.className = "classia-toast__head";

    const icon = document.createElement("span");
    icon.className = "classia-toast__icon";
    icon.appendChild(iconNode(iconName, loading));

    const title = document.createElement("strong");
    title.className = "classia-toast__title";
    title.textContent = String(opts.title);

    const close = document.createElement("button");
    close.type = "button";
    close.className = "classia-toast__close";
    close.setAttribute("aria-label", "Cerrar notificacion");
    close.textContent = "×";
    close.addEventListener("click", () => dismiss(id));

    head.append(icon, title, close);
    toast.appendChild(head);

    if (hasBody) {
      const body = document.createElement("div");
      body.className = "classia-toast__body";
      if (opts.description) {
        const description = document.createElement("p");
        description.className = "classia-toast__description";
        description.textContent = String(opts.description);
        body.appendChild(description);
      }
      if (opts.action && opts.action.label) {
        const action = opts.action.href ? document.createElement("a") : document.createElement("button");
        action.className = "classia-toast__action";
        action.textContent = String(opts.action.label);
        if (opts.action.href) {
          action.href = safeHref(opts.action.href);
        } else {
          action.type = "button";
          action.addEventListener("click", () => {
            if (typeof opts.action.onClick === "function") opts.action.onClick();
          });
        }
        body.appendChild(action);
      }
      toast.appendChild(body);
    }

    let data = toasts.get(id);
    if (data?.timer) {
      window.clearTimeout(data.timer);
      data.timer = null;
    }
    if (data?.leaveTimer) {
      window.clearTimeout(data.leaveTimer);
      data.leaveTimer = null;
    }
    toast.classList.remove("is-leaving");

    if (!data) {
      data = { node: toast };
      toasts.set(id, data);
      toast.addEventListener("mouseenter", () => pause(id));
      toast.addEventListener("mouseleave", () => resume(id));
      toast.addEventListener("focusin", () => pause(id));
      toast.addEventListener("focusout", () => resume(id));
      getContainer().appendChild(toast);
      requestAnimationFrame(() => toast.classList.add("is-visible"));
    } else {
      toast.classList.add("is-visible");
    }

    data.node = toast;
    data.duration = duration;
    data.loading = loading;
    data.remaining = duration;
    data.startedAt = 0;
    if (!loading) setTimer(id, duration);
    return id;
  }

  function create(type, opts) {
    const id = `classia-toast-${Date.now()}-${++seq}`;
    return render(id, type, opts);
  }

  function promise(task, states) {
    const id = create("loading", states.loading || { title: "Cargando..." });
    Promise.resolve(task)
      .then((value) => {
        render(id, "success", states.success || { title: "Listo" }, toasts.get(id)?.node);
        return value;
      })
      .catch((error) => {
        render(id, "error", states.error || { title: "No se pudo completar" }, toasts.get(id)?.node);
        return error;
      });
    return id;
  }

  window.ClassiaToast = {
    success: (opts) => create("success", opts),
    error: (opts) => create("error", opts),
    warning: (opts) => create("warning", opts),
    info: (opts) => create("info", opts),
    action: (opts) => create("action", opts),
    custom: (opts) => create("custom", opts),
    promise,
    dismiss,
    dismissAll: () => Array.from(toasts.keys()).forEach(dismiss)
  };
})();

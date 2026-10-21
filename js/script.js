document.addEventListener("DOMContentLoaded", () => {
  // =========================================================
  // MODO OSCURO — RNF-17 (Accesibilidad) y preferencia de tema
  // =========================================================
  const themeToggleBtn = document.getElementById("theme-toggle-btn");

  if (themeToggleBtn) {
    /**
     * Actualiza el estado visual y semántico del botón según el tema activo.
     * @param {string} theme - "dark" | "light"
     */
    const applyTheme = (theme) => {
      const isDark = theme === "dark";
      document.documentElement.setAttribute("data-theme", theme);
      themeToggleBtn.setAttribute(
        "aria-label",
        isDark ? "Cambiar a modo claro" : "Cambiar a modo oscuro"
      );
      try {
        localStorage.setItem("classia-theme", theme);
      } catch (e) {
        // localStorage no disponible (modo privado, etc.)
      }
    };

    // Determinar el tema inicial: localStorage > preferencia del SO
    const getInitialTheme = () => {
      try {
        const saved = localStorage.getItem("classia-theme");
        if (saved === "dark" || saved === "light") return saved;
      } catch (e) {}
      // Fallback: prefers-color-scheme
      return window.matchMedia("(prefers-color-scheme: dark)").matches
        ? "dark"
        : "light";
    };

    // Aplicar tema inicial (el anti-FOUC ya lo aplicó, esto sincroniza el aria-label)
    const initialTheme = getInitialTheme();
    applyTheme(initialTheme);

    // Toggle al hacer clic
    themeToggleBtn.addEventListener("click", () => {
      const current = document.documentElement.getAttribute("data-theme");
      applyTheme(current === "dark" ? "light" : "dark");
    });

    // Sincronizar si el usuario cambia la preferencia del SO mientras la página está abierta
    window.matchMedia("(prefers-color-scheme: dark)").addEventListener("change", (e) => {
      // Solo sincronizar si el usuario no tiene preferencia guardada
      try {
        if (!localStorage.getItem("classia-theme")) {
          applyTheme(e.matches ? "dark" : "light");
        }
      } catch (err) {
        applyTheme(e.matches ? "dark" : "light");
      }
    });
  }

  // Menú lateral del curso: en móvil se cierra al navegar a una unidad.
  const PASSWORD_ICON_EYE = `
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z" />
      <circle cx="12" cy="12" r="3" />
    </svg>
  `;
  const PASSWORD_ICON_EYE_OFF = `
    <svg viewBox="0 0 24 24" aria-hidden="true" focusable="false">
      <path d="M3 3l18 18" />
      <path d="M10.7 5.2A10.7 10.7 0 0 1 12 5c6 0 9.5 7 9.5 7a15.5 15.5 0 0 1-3.1 3.9" />
      <path d="M6.4 6.5A16 16 0 0 0 2.5 12s3.5 7 9.5 7a10.4 10.4 0 0 0 4.1-.8" />
      <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2" />
    </svg>
  `;

  const syncPasswordToggle = (input, button) => {
    const visible = input.type === "text";
    button.setAttribute("aria-label", visible ? "Ocultar contraseña" : "Mostrar contraseña");
    button.innerHTML = visible ? PASSWORD_ICON_EYE_OFF : PASSWORD_ICON_EYE;
    button.classList.toggle("is-visible", visible);
  };

  const initPasswordToggles = (root = document) => {
    root.querySelectorAll('input[type="password"]').forEach((input) => {
      if (input.dataset.passwordToggleReady === "true") return;

      let wrapper = input.closest(".password-field");
      if (!wrapper) {
        wrapper = document.createElement("span");
        wrapper.className = "password-field";
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);
      }

      let button = wrapper.querySelector(":scope > .password-toggle");
      if (!button) {
        button = document.createElement("button");
        button.type = "button";
        button.className = "password-toggle";
        wrapper.appendChild(button);
      }

      input.dataset.passwordToggleReady = "true";
      syncPasswordToggle(input, button);

      button.addEventListener("click", () => {
        const start = input.selectionStart;
        const end = input.selectionEnd;
        input.type = input.type === "password" ? "text" : "password";
        syncPasswordToggle(input, button);
        input.focus();
        if (start !== null && end !== null) {
          input.setSelectionRange(start, end);
        }
      });
    });
  };

  initPasswordToggles();

  const sidebarToggle = document.querySelector("[data-course-sidebar-toggle]");
  const courseSidebar = document.querySelector("[data-course-sidebar]");

  if (sidebarToggle && courseSidebar) {
    sidebarToggle.addEventListener("click", () => {
      const expanded = sidebarToggle.getAttribute("aria-expanded") === "true";
      sidebarToggle.setAttribute("aria-expanded", String(!expanded));
      courseSidebar.classList.toggle("is-open", !expanded);
    });

    courseSidebar.addEventListener("click", (event) => {
      if (event.target.closest("a") && window.matchMedia("(max-width: 900px)").matches) {
        sidebarToggle.setAttribute("aria-expanded", "false");
        courseSidebar.classList.remove("is-open");
      }
    });
  }

  let lastDialogTrigger = null;

  // Delegación de eventos para diálogos de recursos y limpieza de adjuntos.
  document.addEventListener("click", (event) => {
    const openTrigger = event.target.closest("[data-dialog-open]");

    if (openTrigger) {
      event.preventDefault();
      const dialog = document.getElementById(openTrigger.dataset.dialogOpen);

      if (!dialog) {
        console.error(`Dialog not found: ${openTrigger.dataset.dialogOpen}`);
        return;
      }

      if (typeof dialog.showModal !== "function") {
        console.error("Native dialog.showModal() is not supported in this browser.");
        return;
      }

      lastDialogTrigger = openTrigger;
      document.body.classList.add("course-dialog-open");
      if (!dialog.open) dialog.showModal();

      const typeSelect = dialog.querySelector('select[name="tipo_recurso"]');
      if (typeSelect) {
        syncResourceFields(typeSelect);
      } else {
        const resourceForm = dialog.querySelector("form");
        if (resourceForm) updateResourceMutualExclusivity(resourceForm);
      }

      dialog.querySelector("input, textarea, select")?.focus();

      return;
    }

    const closeTrigger = event.target.closest("[data-dialog-close]");

    if (closeTrigger) {
      event.preventDefault();
      closeTrigger.closest("dialog")?.close();
    }

    const clearFileTrigger = event.target.closest("[data-clear-file-btn]");
    if (clearFileTrigger) {
      event.preventDefault();
      const form = clearFileTrigger.closest("form");
      if (form) {
        const fileInput = form.querySelector('input[name="archivo_recurso"]');
        if (fileInput) fileInput.value = "";
        updateResourceMutualExclusivity(form);
        fileInput?.focus();
      }
    }
  });

  document.addEventListener("close", (event) => {
    if (event.target.matches(".course-dialog")) {
      document.body.classList.remove("course-dialog-open");
      if (lastDialogTrigger?.isConnected) lastDialogTrigger.focus();
      lastDialogTrigger = null;
    }
  }, true);

  const teacherRequestDialog = document.getElementById("dialog-solicitud-docente");
  const teacherRequestData = document.getElementById("teacher-request-dialog-data");

  if (teacherRequestDialog && teacherRequestData) {
    let requests = {};
    let lastTeacherRequestTrigger = null;

    try {
      requests = JSON.parse(teacherRequestData.textContent || "{}");
    } catch (error) {
      console.error("No se pudieron cargar los datos de la solicitud docente.", error);
    }

    const setDialogText = (id, value) => {
      const element = document.getElementById(id);
      if (element) element.textContent = value || "-";
    };

    const renderTeacherRequest = (request) => {
      setDialogText("dialog-solicitud-nombre", request.nombre);
      setDialogText("dialog-solicitud-email", request.email);
      setDialogText("dialog-solicitud-fecha", request.fecha_solicitud);
      setDialogText("dialog-solicitud-motivo", request.motivo || "Sin motivo indicado.");
      setDialogText("dialog-solicitud-estado", request.estado || "Pendiente");
      setDialogText("dialog-documentacion-nombre", request.documentacion_nombre);
      setDialogText("dialog-documentacion-mime", request.documentacion_mime);
      setDialogText("dialog-documentacion-tamano", request.documentacion_tamano ? `${request.documentacion_tamano} MB` : "-");
      setDialogText("dialog-documentacion-fecha", request.documentacion_fecha);

      const id = Number(request.id);
      const idInput = document.getElementById("dialog-solicitud-id");
      if (idInput) idInput.value = String(id);

      const status = document.getElementById("dialog-solicitud-estado");
      if (status) {
        status.classList.remove("teacher-request-status-badge--pending", "teacher-request-status-badge--approved", "teacher-request-status-badge--rejected");
        const stateClass = String(request.estado || "Pendiente").toLowerCase() === "aprobada"
          ? "teacher-request-status-badge--approved"
          : String(request.estado || "Pendiente").toLowerCase() === "rechazada"
            ? "teacher-request-status-badge--rejected"
            : "teacher-request-status-badge--pending";
        status.classList.add(stateClass);
      }

      const documentacion = document.getElementById("dialog-solicitud-documentacion");
      const emptyDocumentation = document.getElementById("dialog-solicitud-documentacion-vacia");
      const documentationActions = document.getElementById("dialog-documentacion-actions");
      const hasDocumentation = Boolean(request.tiene_documentacion);
      if (documentacion) documentacion.hidden = !hasDocumentation;
      if (emptyDocumentation) emptyDocumentation.hidden = hasDocumentation;
      if (documentationActions) documentationActions.hidden = !hasDocumentation;

      if (hasDocumentation) {
        const inlineLink = document.getElementById("dialog-documentacion-ver");
        const downloadLink = document.getElementById("dialog-documentacion-descargar");
        const baseUrl = `../php/solicitudes/descargar_documentacion_docente.php?id=${encodeURIComponent(id)}`;
        if (inlineLink) inlineLink.href = `${baseUrl}&modo=inline`;
        if (downloadLink) downloadLink.href = baseUrl;
      }
    };

    document.querySelectorAll(".teacher-request-details-trigger").forEach((trigger) => {
      if (trigger.dataset.dialogReady === "true") return;
      trigger.dataset.dialogReady = "true";
      trigger.addEventListener("click", () => {
        const request = requests[String(trigger.dataset.solicitudId)];
        if (!request || typeof teacherRequestDialog.showModal !== "function") {
          console.error("No se pudo abrir el detalle de la solicitud docente.");
          return;
        }

        lastTeacherRequestTrigger = trigger;
        trigger.setAttribute("aria-expanded", "true");
        renderTeacherRequest(request);
        document.body.classList.add("course-dialog-open");
        if (!teacherRequestDialog.open) teacherRequestDialog.showModal();
        teacherRequestDialog.querySelector("[data-dialog-close]")?.focus();
      });
    });

    teacherRequestDialog.addEventListener("close", () => {
      if (lastTeacherRequestTrigger?.isConnected) {
        lastTeacherRequestTrigger.setAttribute("aria-expanded", "false");
        lastTeacherRequestTrigger.focus();
      }
      lastTeacherRequestTrigger = null;
      setDialogText("dialog-solicitud-nombre", "");
      setDialogText("dialog-solicitud-email", "");
      setDialogText("dialog-solicitud-motivo", "");
    });
  }

  const HINTS_RECURSOS = {
    "Archivo": "Sube un documento, plantilla o archivo complementario para la clase.",
    "Foro": "Espacio de intercambio: detalla el tema o consigna de debate para los estudiantes.",
    "Entrega de Tareas": "Actividad práctica: especifica las consignas, pautas de evaluación y fecha de entrega.",
    "Video": "Pega un enlace de YouTube / Vimeo o subí un video explicativo (.mp4, .webm).",
    "PDF": "Documento de lectura o guía de estudio en formato PDF.",
    "Imagen": "Infografía, diagrama o imagen ilustrativa para la clase.",
    "Enlace": "Enlace externo o recurso web de interés para los alumnos."
  };

  const updateResourceMutualExclusivity = (form) => {
    // Un recurso puede tener enlace o archivo, salvo el tipo "Enlace", que fuerza URL.
    if (!form) return;
    const tipoSelect = form.querySelector('select[name="tipo_recurso"]');
    const tipo = tipoSelect ? tipoSelect.value : "";
    const urlLabel = form.querySelector('[data-field-container="url"]');
    const fileLabel = form.querySelector('[data-field-container="archivo"]');
    const urlInput = urlLabel ? urlLabel.querySelector('input[name="url_recurso"]') : null;
    const fileInput = fileLabel ? fileLabel.querySelector('input[name="archivo_recurso"]') : null;
    const clearFileBtn = form.querySelector('[data-clear-file-btn]');

    if (!urlInput || !fileInput) return;

    if (tipo === "Enlace") {
      fileInput.disabled = true;
      fileInput.value = "";
      if (fileLabel) {
        fileLabel.hidden = true;
        fileLabel.classList.remove("is-disabled");
        fileLabel.removeAttribute("title");
      }
      urlInput.disabled = false;
      if (urlLabel) {
        urlLabel.hidden = false;
        urlLabel.classList.remove("is-disabled");
        urlLabel.removeAttribute("title");
      }
      if (clearFileBtn) clearFileBtn.hidden = true;
      return;
    }

    if (fileLabel) fileLabel.hidden = false;
    if (urlLabel) urlLabel.hidden = false;

    const hasUrl = urlInput.value.trim().length > 0;
    const hasFile = fileInput.files && fileInput.files.length > 0;

    if (clearFileBtn) {
      clearFileBtn.hidden = !hasFile;
    }

    if (hasUrl) {
      fileInput.disabled = true;
      fileInput.value = "";
      if (clearFileBtn) clearFileBtn.hidden = true;
      if (fileLabel) {
        fileLabel.classList.add("is-disabled");
        fileLabel.title = "Deshabilitado porque se ingresó un enlace";
      }
      urlInput.disabled = false;
      if (urlLabel) {
        urlLabel.classList.remove("is-disabled");
        urlLabel.removeAttribute("title");
      }
    } else if (hasFile) {
      urlInput.disabled = true;
      urlInput.value = "";
      if (urlLabel) {
        urlLabel.classList.add("is-disabled");
        urlLabel.title = "Deshabilitado porque se seleccionó un archivo adjunto";
      }
      fileInput.disabled = false;
      if (fileLabel) {
        fileLabel.classList.remove("is-disabled");
        fileLabel.removeAttribute("title");
      }
    } else {
      urlInput.disabled = false;
      fileInput.disabled = false;
      if (urlLabel) {
        urlLabel.classList.remove("is-disabled");
        urlLabel.removeAttribute("title");
      }
      if (fileLabel) {
        fileLabel.classList.remove("is-disabled");
        fileLabel.removeAttribute("title");
      }
    }
  };

  const syncResourceFields = (select) => {
    // Ajusta ayudas y placeholders según el tipo de recurso seleccionado.
    const form = select.closest("form");
    if (!form) return;

    const tipo = select.value;
    const urlLabel = form.querySelector('[data-field-container="url"]');
    const descLabel = form.querySelector('[data-field-container="descripcion"]');
    const hintElem = form.querySelector("[data-resource-hint]");
    const taskConfig = form.querySelector("[data-task-config]");

    if (hintElem) {
      hintElem.textContent = HINTS_RECURSOS[tipo] || "";
    }

    const isLink = tipo === "Enlace";
    const isVideo = tipo === "Video";
    const isForum = tipo === "Foro";
    const isTask = tipo === "Entrega de Tareas";

    if (taskConfig) {
      taskConfig.hidden = !isTask;
      taskConfig.querySelectorAll("input, select, textarea").forEach((control) => {
        control.disabled = !isTask;
      });
    }

    if (urlLabel) {
      const urlInput = urlLabel.querySelector("input");
      if (urlInput) {
        if (isVideo) {
          urlInput.placeholder = "https://www.youtube.com/watch?v=... o https://vimeo.com/...";
        } else if (isLink) {
          urlInput.placeholder = "https://...";
        } else {
          urlInput.placeholder = "https://... (opcional)";
        }
      }
    }

    if (descLabel) {
      const descInput = descLabel.querySelector("textarea");
      if (descInput) {
        if (isForum) {
          descInput.placeholder = "Escribí la consigna de debate, preguntas guía o pautas de participación...";
        } else if (isTask) {
          descInput.placeholder = "Detallá las instrucciones de la entrega, criterios de evaluación y fecha límite...";
        } else {
          descInput.placeholder = "Breve descripción o notas para el alumno (opcional)...";
        }
      }
    }

    updateResourceMutualExclusivity(form);
  };

  document.querySelectorAll('.course-builder-page select[name="tipo_recurso"]').forEach(syncResourceFields);

  document.addEventListener("change", (event) => {
    const resourceType = event.target.closest('.course-builder-page select[name="tipo_recurso"]');
    if (resourceType) {
      syncResourceFields(resourceType);
      return;
    }

    const fileInput = event.target.closest('.course-builder-page input[name="archivo_recurso"]');
    if (fileInput) {
      updateResourceMutualExclusivity(fileInput.closest("form"));
      return;
    }

    const urlInput = event.target.closest('.course-builder-page input[name="url_recurso"]');
    if (urlInput) {
      updateResourceMutualExclusivity(urlInput.closest("form"));
      return;
    }
  });

  document.addEventListener("input", (event) => {
    const urlInput = event.target.closest('.course-builder-page input[name="url_recurso"]');
    if (urlInput) {
      updateResourceMutualExclusivity(urlInput.closest("form"));
    }
  });

  const onboardingForm = document.querySelector(".onboarding-page form");
  if (onboardingForm) {
    // Precarga respuestas guardadas para que el onboarding pueda retomarse.
    const dataScript = document.getElementById("onboarding-data");
    if (dataScript) {
      try {
        const datos = JSON.parse(dataScript.textContent || "{}");
        Object.entries(datos).forEach(([clave, valor]) => {
          const controles = onboardingForm.querySelectorAll(`[name="${clave}"], [name="${clave}[]"]`);
          controles.forEach((control) => {
            if (control.type === "checkbox" || control.type === "radio") {
              control.checked = Array.isArray(valor) ? valor.includes(control.value) : valor === control.value;
            } else {
              control.value = valor;
            }
          });
        });
      } catch (err) {
        console.error("Error al cargar datos de onboarding:", err);
      }
    }

    const profesion = document.getElementById("profesion");
    const otraProfesion = document.getElementById("otra-profesion");

    const actualizarOtraProfesion = () => {
      if (!profesion || !otraProfesion) return;
      const habilitada = profesion.value === "otro";
      otraProfesion.disabled = !habilitada;
      otraProfesion.required = habilitada;
      if (!habilitada) {
        otraProfesion.value = "";
      }
    };

    if (profesion && otraProfesion) {
      profesion.addEventListener("change", actualizarOtraProfesion);
      actualizarOtraProfesion();
    }

    const ciInput = document.getElementById("cedula_identidad");
    if (ciInput) {
      ciInput.addEventListener("input", function () {
        this.value = this.value.replace(/\D/g, "").slice(0, 8);
      });
    }
  }

  const publicationForm = document.querySelector("form.publication-form");
  if (publicationForm) {
    const publicationType = publicationForm.dataset.publicationType || publicationForm.querySelector('[name="tipo"]')?.value || "";
    const serviceType = publicationForm.querySelector('[name="tipo_servicio"]');
    const serviceContexts = {
      mentoria: { duration: "Duración de la sesión", durationHelp: "Tiempo estimado de cada sesión o del acompañamiento.", availability: "Disponibilidad para agendar", availabilityHelp: "Indicá días y horarios disponibles para coordinar." },
      formacion_institucional: { duration: "Duración estimada de la formación", durationHelp: "Cantidad estimada de horas o jornadas de formación.", availability: "Disponibilidad para nuevas formaciones", availabilityHelp: "Indicá períodos o condiciones para recibir solicitudes." },
      impresion_3d: { duration: "Tiempo estimado de producción", durationHelp: "Cantidad estimada de horas para producir el pedido.", availability: "Disponibilidad del servicio", availabilityHelp: "Indicá cuándo podés recibir nuevos pedidos." },
      proyecto_educativo: { duration: "Duración estimada del trabajo", durationHelp: "Tiempo estimado para desarrollar el proyecto educativo.", availability: "Disponibilidad para nuevos proyectos", availabilityHelp: "Indicá cuándo podés comenzar nuevos proyectos." },
      robotica_automatizacion: { duration: "Duración estimada del proyecto", durationHelp: "Tiempo estimado para desarrollar y entregar el proyecto.", availability: "Disponibilidad para nuevos proyectos", availabilityHelp: "Indicá cuándo podés comenzar nuevos proyectos." }
    };

    const setPublicationField = (name, visible) => {
      const field = publicationForm.querySelector(`[data-publication-field="${name}"]`);
      if (!field) return;
      field.hidden = !visible;
      field.querySelectorAll("input, select, textarea").forEach((control) => {
        control.disabled = !visible;
      });
    };

    const refreshPublicationContext = () => {
      const isCourse = publicationType === "Curso";
      const context = serviceContexts[serviceType?.value] || {};
      setPublicationField("nivel_experiencia", isCourse);
      setPublicationField("cupos", isCourse);
      const priceLabel = publicationForm.querySelector('[data-publication-field="precio"] .publication-field__label');
      const modalityLabel = publicationForm.querySelector('[data-publication-field="modalidad"] .publication-field__label');
      const durationLabel = publicationForm.querySelector('[data-publication-field="duracion_horas"] .publication-field__label');
      const durationHelp = publicationForm.querySelector('[data-publication-help="duracion_horas"]');
      const availabilityLabel = publicationForm.querySelector('[data-publication-field="disponibilidad"] .publication-field__label');
      const availabilityHelp = publicationForm.querySelector('[data-publication-help="disponibilidad"]');
      if (priceLabel) priceLabel.textContent = isCourse ? "Precio del curso ($ UYU)" : "Precio del servicio ($ UYU)";
      if (modalityLabel) modalityLabel.textContent = isCourse ? "Modalidad del curso" : "Modalidad del servicio";
      if (durationLabel) durationLabel.textContent = isCourse ? "Duración del curso" : (context.duration || "Duración estimada");
      if (durationHelp) durationHelp.textContent = isCourse ? "Cantidad estimada de horas del curso." : (context.durationHelp || "Cantidad estimada de horas del servicio.");
      if (availabilityLabel) availabilityLabel.textContent = isCourse ? "Disponibilidad del curso" : (context.availability || "Disponibilidad del servicio");
      if (availabilityHelp) availabilityHelp.textContent = isCourse ? "Indicá horarios, frecuencia o coordinación esperada." : (context.availabilityHelp || "Indicá cuándo podés recibir nuevas solicitudes.");
    };

    if (serviceType) serviceType.addEventListener("change", refreshPublicationContext);
    refreshPublicationContext();
  }

  const selectCat = document.querySelector('select[name="id_categoria"]');
  const inputNueva = document.querySelector('input[name="nueva_categoria"]');
  const txtDesc = document.querySelector('textarea[name="descripcion_categoria"]');

  if (publicationForm) {
    let hasUnsavedPublicationData = false;
    publicationForm.addEventListener("input", () => {
      hasUnsavedPublicationData = true;
    });
    publicationForm.addEventListener("change", () => {
      hasUnsavedPublicationData = true;
    });
    document.querySelectorAll("[data-publication-type-change]").forEach((link) => {
      link.addEventListener("click", (event) => {
        if (hasUnsavedPublicationData && !window.confirm("Se perderán los datos no guardados. ¿Querés cambiar el tipo de publicación?")) {
          event.preventDefault();
        }
      });
    });
  }

  if (selectCat && inputNueva) {
    const nuevaChoiceBox = inputNueva.closest(".publication-choice");

    const sincronizarCategorias = () => {
      // Evita enviar categoría existente y categoría nueva en el mismo formulario.
      const tieneSeleccion = selectCat.value.trim() !== "";
      if (tieneSeleccion) {
        inputNueva.disabled = true;
        if (txtDesc) txtDesc.disabled = true;
        inputNueva.value = "";
        if (txtDesc) txtDesc.value = "";
        if (nuevaChoiceBox) {
          nuevaChoiceBox.style.opacity = "0.5";
          nuevaChoiceBox.style.pointerEvents = "none";
        }
        inputNueva.placeholder = "Deselecciona la categoría existente para crear una nueva";
      } else {
        inputNueva.disabled = false;
        if (txtDesc) txtDesc.disabled = false;
        if (nuevaChoiceBox) {
          nuevaChoiceBox.style.opacity = "1";
          nuevaChoiceBox.style.pointerEvents = "auto";
        }
        inputNueva.placeholder = "Solo si ninguna categoria aplica";
      }
    };

    selectCat.addEventListener("change", sincronizarCategorias);
    sincronizarCategorias();
  }

  const serviceForm = document.querySelector("form[data-service-form]");
  if (serviceForm) {
    // Muestra solo la sección técnica correspondiente al tipo de servicio.
    const tipo = serviceForm.dataset.tipoServicio || "";
    const ids = {
      impresion_3d: "solicitud-diseno-3d",
      mentoria: "solicitud-mentoria",
      proyecto_educativo: "solicitud-proyecto-educativo",
      formacion_institucional: "solicitud-formacion",
      robotica_automatizacion: "solicitud-robotica"
    };
    Object.values(ids).forEach((id) => {
      const heading = document.getElementById(id);
      if (heading) {
        const section = heading.closest("section");
        if (section) section.hidden = id !== ids[tipo];
      }
    });
    const selector = document.getElementById("tipo-servicio");
    if (selector) {
      selector.value = tipo;
      selector.disabled = true;
    }
  }

  // Tabs de perfil: un único comportamiento reutilizable, con fallback de contenido visible sin JS.
  document.querySelectorAll(".profile-tabs").forEach((tablist) => {
    if (tablist.dataset.tabsReady === "true") return;

    const buttons = Array.from(tablist.querySelectorAll("button"));
    const panels = Array.from(tablist.parentElement.querySelectorAll(":scope > .account-section"));
    if (!buttons.length || buttons.length !== panels.length) return;

    tablist.dataset.tabsReady = "true";
    tablist.setAttribute("role", "tablist");

    const activateTab = (index, moveFocus = false) => {
      buttons.forEach((button, buttonIndex) => {
        const active = buttonIndex === index;
        const panel = panels[buttonIndex];
        const panelId = panel.id || `profile-panel-${buttonIndex + 1}`;

        panel.id = panelId;
        panel.setAttribute("role", "tabpanel");
        panel.setAttribute("aria-labelledby", button.id || `profile-tab-${buttonIndex + 1}`);
        button.id = button.id || `profile-tab-${buttonIndex + 1}`;
        button.setAttribute("role", "tab");
        button.setAttribute("aria-controls", panelId);
        button.setAttribute("aria-selected", String(active));
        button.tabIndex = active ? 0 : -1;
        panel.hidden = !active;

        if (active && moveFocus) button.focus();
      });
    };

    buttons.forEach((button, index) => {
      button.addEventListener("click", () => activateTab(index));
      button.addEventListener("keydown", (event) => {
        if (!["ArrowRight", "ArrowDown", "ArrowLeft", "ArrowUp", "Home", "End"].includes(event.key)) return;
        event.preventDefault();
        let nextIndex = index;
        if (event.key === "ArrowRight" || event.key === "ArrowDown") nextIndex = (index + 1) % buttons.length;
        if (event.key === "ArrowLeft" || event.key === "ArrowUp") nextIndex = (index - 1 + buttons.length) % buttons.length;
        if (event.key === "Home") nextIndex = 0;
        if (event.key === "End") nextIndex = buttons.length - 1;
        activateTab(nextIndex, true);
      });
    });

    const activeIndex = buttons.findIndex((button) => button.classList.contains("active"));
    activateTab(activeIndex >= 0 ? activeIndex : 0);
  });

  // Acordeones nativos: conservan apertura sin JS y sincronizan su estado accesible.
  document.querySelectorAll("details[data-accordion], details.course-nav-module, details.course-deliveries-task").forEach((accordion, index) => {
    if (accordion.dataset.accordionReady === "true") return;

    const summary = accordion.querySelector(":scope > summary");
    if (!summary) return;

    let panel = accordion.querySelector(":scope > .accordion-panel");
    if (!panel && accordion.dataset.accordion === "true") {
      panel = document.createElement("div");
      panel.className = "accordion-panel";
      Array.from(accordion.children).forEach((child) => {
        if (child !== summary) panel.appendChild(child);
      });
      accordion.appendChild(panel);
    }

    if (!panel) return;
    const panelId = panel.id || `accordion-panel-${index + 1}`;
    panel.id = panelId;
    summary.setAttribute("aria-controls", panelId);
    summary.setAttribute("aria-expanded", String(accordion.open));
    accordion.dataset.accordionReady = "true";
    accordion.addEventListener("toggle", () => {
      summary.setAttribute("aria-expanded", String(accordion.open));
    });
  });

  document.querySelectorAll("form[data-add-to-cart-form]").forEach((form) => {
    if (form.dataset.cartReady === "true") return;
    form.dataset.cartReady = "true";

    form.addEventListener("submit", async (event) => {
      event.preventDefault();
      const button = form.querySelector('button[type="submit"]');
      if (!button || button.disabled) return;

      button.disabled = true;
      try {
        const response = await fetch(form.action, {
          method: "POST",
          body: new FormData(form),
          headers: { Accept: "application/json" },
          credentials: "same-origin",
        });
        const rawResponse = await response.text();
        let data;

        try {
          data = JSON.parse(rawResponse);
        } catch (parseError) {
          throw new Error(`El servidor devolvió una respuesta no válida (${response.status}).`);
        }

        if (!response.ok || !data.success) {
          throw new Error(data.message || "No se pudo agregar el curso al carrito.");
        }

        const badge = document.getElementById("cart-item-count");
        if (badge) {
          badge.textContent = String(data.cart_count ?? 0);
        } else if ((data.cart_count ?? 0) > 0) {
          const cartLink = document.querySelector(".nav-cart-btn");
          if (cartLink) {
            const newBadge = document.createElement("span");
            newBadge.id = "cart-item-count";
            newBadge.className = "nav-cart-badge";
            newBadge.textContent = String(data.cart_count);
            cartLink.appendChild(newBadge);
          }
        }

        if (window.ClassiaToast) {
          window.ClassiaToast[data.already_exists ? "info" : "success"]({ title: data.message });
        }
        button.textContent = data.already_exists ? "Ya está en el carrito" : "Agregado al carrito";
      } catch (error) {
        console.error("Error al agregar el curso al carrito:", error);
        if (window.ClassiaToast) {
          window.ClassiaToast.error({ title: error.message || "No se pudo agregar el curso al carrito." });
        }
      } finally {
        button.disabled = false;
      }
    });
  });
});

/**
 * Copia una URL al portapapeles y actualiza el botón de feedback (noticias).
 * @param {string} url
 * @param {HTMLElement} btn
 */
function copiarAlPortapapeles(url, btn) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(function() {
      var original = btn.innerText;
      btn.innerText = '✓ ¡Copiado!';
      setTimeout(function() { btn.innerText = original; }, 2000);
    });
  } else {
    prompt('Copia este enlace para compartir:', url);
  }
}

/**
 * Copia el enlace de un evento al portapapeles y actualiza estilos de feedback.
 * @param {string} url
 * @param {HTMLElement} btn
 */
function copiarEnlaceEvento(url, btn) {
  if (navigator.clipboard) {
    navigator.clipboard.writeText(url).then(function() {
      var textoOriginal = btn.innerText;
      btn.innerText = '✓ ¡Copiado!';
      btn.classList.remove('btn-secondary');
      btn.classList.add('btn-primary');
      setTimeout(function() {
        btn.innerText = textoOriginal;
        btn.classList.remove('btn-primary');
        btn.classList.add('btn-secondary');
      }, 2200);
    }).catch(function() {
      prompt('Copia este enlace:', url);
    });
  } else {
    prompt('Copia este enlace:', url);
  }
}



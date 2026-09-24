document.addEventListener("DOMContentLoaded", () => {
  // Menú lateral del curso: en móvil se cierra al navegar a una unidad.
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

    if (hintElem) {
      hintElem.textContent = HINTS_RECURSOS[tipo] || "";
    }

    const isLink = tipo === "Enlace";
    const isVideo = tipo === "Video";
    const isForum = tipo === "Foro";
    const isTask = tipo === "Entrega de Tareas";

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

  const selectCat = document.querySelector('select[name="id_categoria"]');
  const inputNueva = document.querySelector('input[name="nueva_categoria"]');
  const txtDesc = document.querySelector('textarea[name="descripcion_categoria"]');

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



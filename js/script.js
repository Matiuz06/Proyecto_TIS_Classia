document.addEventListener("DOMContentLoaded", () => {
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
      dialog.querySelector("input, textarea, select")?.focus();

      return;
    }

    const closeTrigger = event.target.closest("[data-dialog-close]");

    if (closeTrigger) {
      event.preventDefault();
      closeTrigger.closest("dialog")?.close();
    }
  });

  document.addEventListener("close", (event) => {
    if (event.target.matches(".course-dialog")) {
      document.body.classList.remove("course-dialog-open");
      if (lastDialogTrigger?.isConnected) lastDialogTrigger.focus();
      lastDialogTrigger = null;
    }
  }, true);

  const syncResourceFields = (select) => {
    const form = select.closest("form");
    if (!form) return;

    const urlField = form.querySelector('input[name="url_recurso"]')?.closest("label");
    const fileField = form.querySelector('input[name="archivo_recurso"]')?.closest("label");
    const isLink = select.value === "Enlace";

    if (urlField) urlField.hidden = !isLink;
    if (fileField) fileField.hidden = isLink;
  };

  document.querySelectorAll('.course-builder-page select[name="tipo_recurso"]').forEach(syncResourceFields);

  document.addEventListener("change", (event) => {
    const resourceType = event.target.closest('.course-builder-page select[name="tipo_recurso"]');
    if (resourceType) syncResourceFields(resourceType);
  });
});

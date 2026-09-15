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
      if (event.target.closest("a") && window.matchMedia("(max-width: 760px)").matches) {
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

      if (!dialog || typeof dialog.showModal !== "function") return;
      lastDialogTrigger = openTrigger;
      document.body.classList.add("course-dialog-open");
      dialog.showModal();
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
      if (lastDialogTrigger) lastDialogTrigger.focus();
    }
  }, true);
});

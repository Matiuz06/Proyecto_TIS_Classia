/**
 * Responsabilidad: Interacciones y modales del panel de administración.
 */

document.addEventListener("DOMContentLoaded", () => {
  // Dropdown de navegación "Seleccionar apartado"
  const trigger = document.getElementById("admin-panel-trigger");
  const menu = document.getElementById("admin-panel-menu");

  if (trigger && menu) {
    trigger.addEventListener("click", () => {
      const open = trigger.getAttribute("aria-expanded") === "true";
      trigger.setAttribute("aria-expanded", String(!open));
      menu.classList.toggle("is-open", !open);
    });

    // Cerrar al hacer click fuera
    document.addEventListener("click", (e) => {
      if (!trigger.contains(e.target) && !menu.contains(e.target)) {
        trigger.setAttribute("aria-expanded", "false");
        menu.classList.remove("is-open");
      }
    });

    // Cerrar al seleccionar un ítem y actualizar el texto del botón
    menu.querySelectorAll(".admin-panel-item").forEach((item) => {
      item.addEventListener("click", () => {
        const span = trigger.querySelector("span:first-child");
        if (span) {
          span.textContent = item.textContent.trim().split("\n")[0].trim();
        }
        trigger.setAttribute("aria-expanded", "false");
        menu.classList.remove("is-open");
      });
    });
  }
});

/**
 * Abre el diálogo de bloqueo de usuario y asigna los datos al formulario.
 * @param {number} idUsuario - ID del usuario a bloquear
 * @param {string} nombreUsuario - Nombre para el mensaje de confirmación
 */
function abrirModalBloqueo(idUsuario, nombreUsuario) {
  const dialog = document.getElementById("dialog-bloqueo-admin");
  const inputId = document.getElementById("bloqueo_id_usuario");
  const spanNombre = document.getElementById("bloqueo_nombre_usuario");
  const textareaMotivo = document.getElementById("bloqueo_motivo");

  if (inputId) inputId.value = idUsuario;
  if (spanNombre) spanNombre.textContent = nombreUsuario;
  if (textareaMotivo) textareaMotivo.value = "";

  if (dialog && typeof dialog.showModal === "function") {
    dialog.showModal();
  } else if (dialog) {
    dialog.setAttribute("open", "");
  }
}

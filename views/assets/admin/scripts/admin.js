/* TechOS — Admin layout: sidebar toggle, user menu, busca global */
(function () {
  "use strict";

  const toggle = document.querySelector("[data-sidebar-toggle]");
  const sidebar = document.querySelector("[data-sidebar]");
  if (toggle && sidebar) {
    toggle.addEventListener("click", function () {
      const isOpen = sidebar.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", String(isOpen));
    });
  }

  const userMenu = document.querySelector("[data-user-menu]");
  if (userMenu) {
    userMenu.addEventListener("click", function () {
      // TODO: substituir por dropdown real com HttpClientBase.js
      alert("Perfil · Configurações · Sair");
    });
  }

  const search = document.querySelector("[data-global-search]");
  if (search) {
    search.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        // TODO: substituir por chamada à API /api/admin/busca
        console.info("Busca admin:", search.value);
      }
    });
  }

  // Fecha sidebar ao clicar fora em mobile
  document.addEventListener("click", function (event) {
    if (!sidebar || !toggle) return;
    const clickedInsideSidebar = sidebar.contains(event.target);
    const clickedToggle = toggle.contains(event.target);
    if (!clickedInsideSidebar && !clickedToggle && sidebar.classList.contains("is-open")) {
      sidebar.classList.remove("is-open");
      toggle.setAttribute("aria-expanded", "false");
    }
  });
})();

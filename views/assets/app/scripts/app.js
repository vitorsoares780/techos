/* TechOS — App layout: sidebar toggle, user menu, busca global */
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

  
  const search = document.querySelector("[data-global-search]");
  if (search) {
    search.addEventListener("keydown", function (event) {
      if (event.key === "Enter") {
        event.preventDefault();
        // TODO: substituir por chamada à API /api/busca
        console.info("Busca:", search.value);
      }
    });
  }
})();

/* TechOS — Admin layout and logged-in user chip */
(function () {
  "use strict";

  const toggle = document.querySelector("[data-sidebar-toggle]");
  const sidebar = document.querySelector("[data-sidebar]");

  document.querySelectorAll('a[href="ordem-nova.html"]').forEach(link => link.remove());
  document.querySelectorAll('a[href="configuracoes.html"]').forEach(link => {
    if (link.matches("[data-user-chip]")) {
      link.href = "usuarios.html";
      link.setAttribute("aria-label", "Usuários");
      return;
    }
    link.remove();
  });

  if (toggle && sidebar) {
    toggle.addEventListener("click", function () {
      const isOpen = sidebar.classList.toggle("is-open");
      toggle.setAttribute("aria-expanded", String(isOpen));
    });
  }

  function readUser() {
    try {
      const user = JSON.parse(localStorage.getItem("user") || "{}");
      return user && typeof user === "object" && !Array.isArray(user) ? user : {};
    } catch (error) {
      console.warn("Não foi possível ler o usuário da sessão:", error);
      return {};
    }
  }

  function renderUser(user = readUser()) {
    const chip = document.querySelector("[data-user-chip]");
    if (!chip) return;

    const nameElement = chip.querySelector("[data-user-name]");
    const avatar = chip.querySelector("[data-user-avatar]");
    const name = typeof user.name === "string" && user.name.trim()
      ? user.name.trim()
      : "Administrador";
    const initial = Array.from(name)[0].toLocaleUpperCase("pt-BR");

    if (nameElement) nameElement.textContent = name;
    if (!avatar) return;

    const showInitial = () => {
      avatar.replaceChildren(document.createTextNode(initial));
      avatar.style.background = "";
    };

    if (typeof user.photo !== "string" || !user.photo.trim()) {
      showInitial();
      return;
    }

    const image = document.createElement("img");
    image.src = user.photo;
    image.alt = "";
    image.addEventListener("error", showInitial, { once: true });
    avatar.replaceChildren(image);
    avatar.style.background = "none";
  }

  renderUser();
  window.addEventListener("user:updated", event => renderUser(event.detail));

  const adminName = document.querySelector("[data-admin-name]");
  if (adminName) adminName.textContent = readUser().name || "Administrador";

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

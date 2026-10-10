(function () {
  "use strict";

  const chip = document.querySelector("[data-user-chip]");
  if (!chip) return;

  const avatar = chip.querySelector("[data-user-avatar]");
  const nameElement = chip.querySelector("[data-user-name]");
  function readStoredUser() {
    try {
      const storedUser = JSON.parse(localStorage.getItem("user") || "{}");
      if (storedUser && typeof storedUser === "object" && !Array.isArray(storedUser)) {
        return storedUser;
      }
    } catch (error) {
      console.warn("Erro ao carregar dados do usuário:", error);
    }
    return {};
  }

  function renderUser(user) {
    const name = typeof user.name === "string" && user.name.trim()
      ? user.name.trim()
      : "Usuário";
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

  renderUser(readStoredUser());
  window.addEventListener("user:updated", event => {
    renderUser(event.detail || readStoredUser());
  });
})();

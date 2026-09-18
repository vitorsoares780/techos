(function () {
  "use strict";

  const userData = localStorage.getItem("user");
  if (!userData) return;

  try {
    const user = JSON.parse(userData);
    const chip = document.querySelector(".user-chip");
    if (!chip) return;

    const avatar = chip.querySelector(".avatar");
    const nameSpan = chip.querySelector("span:last-child");

    if (nameSpan) {
      nameSpan.textContent = user.name || "Usuário";
    }

    if (avatar) {
      if (user.photo) {
        avatar.innerHTML = `<img src="${user.photo}" alt="${user.name}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
        avatar.style.background = "none";
      } else {
        const initials = user.name
          ? user.name.split(" ").map(w => w[0]).join("").substring(0, 2).toUpperCase()
          : "U";
        avatar.textContent = initials;
      }
    }
  } catch (e) {
    console.warn("Erro ao carregar dados do usuário:", e);
  }
})();

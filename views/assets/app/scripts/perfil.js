/* TechOS — Perfil: salvar dados e trocar senha */
(function () {
  "use strict";

  const perfil = document.querySelector("[data-perfil-form]");
  if (perfil) {
    perfil.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!perfil.checkValidity()) { perfil.reportValidity(); return; }
      // TODO: substituir por HttpClientBase.js → PUT /api/perfil
      alert("Perfil atualizado (demo).");
    });
  }

  const senha = document.querySelector("[data-senha-form]");
  if (senha) {
    senha.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!senha.checkValidity()) { senha.reportValidity(); return; }
      if (senha.elements.nova.value !== senha.elements.confirmar.value) {
        alert("A confirmação não coincide com a nova senha.");
        return;
      }
      // TODO: substituir por HttpClientBase.js → POST /api/perfil/senha
      alert("Senha atualizada (demo).");
      senha.reset();
    });
  }
})();

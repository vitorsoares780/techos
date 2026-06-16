/* TechOS — Perfil do cliente: dados pessoais e senha */
(function () {
  "use strict";

  const perfilForm = document.querySelector("[data-perfil-form]");
  if (perfilForm) {
    perfilForm.addEventListener("submit", function (event) {
      event.preventDefault();
      alert("Dados atualizados com sucesso!");
    });
  }

  const senhaForm = document.querySelector("[data-senha-form]");
  if (senhaForm) {
    senhaForm.addEventListener("submit", function (event) {
      event.preventDefault();
      const nova = document.querySelector("[data-senha-nova]");
      const confirma = document.querySelector("[data-senha-confirma]");
      if (nova.value !== confirma.value) {
        alert("As senhas não conferem.");
        return;
      }
      alert("Senha atualizada com sucesso!");
      senhaForm.reset();
    });
  }
})();
/* TechOS — Nova OS: envio simplificado para cliente */
(function () {
  "use strict";

  const form = document.querySelector("[data-os-form]");
  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      // TODO: substituir por HttpClientBase.js → POST /api/ordens
      alert("Solicitação enviada com sucesso! Aguarde a aprovação da assistência.");
      window.location.href = "ordens.html";
    });
  }
})();
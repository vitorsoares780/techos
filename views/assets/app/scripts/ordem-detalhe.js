/* TechOS — Detalhe da OS: troca de status + concluir */
(function () {
  "use strict";

  const select = document.querySelector("[data-status-select]");
  if (select) {
    select.addEventListener("change", function () {
      // TODO: substituir por HttpClientBase.js → PATCH /api/ordens/:id
      console.info("Status alterado para:", select.value);
    });
  }

  const finalizar = document.querySelector("[data-finalizar]");
  if (finalizar) {
    finalizar.addEventListener("click", function () {
      if (confirm("Concluir esta OS e notificar o cliente?")) {
        // TODO: substituir por HttpClientBase.js → POST /api/ordens/:id/concluir
        alert("OS concluída (demo).");
      }
    });
  }
})();

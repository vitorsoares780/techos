/* TechOS — Detalhe da OS: troca de status + concluir + data de conclusão */
(function () {
  "use strict";

  const select = document.querySelector("[data-status-select]");
  const conclusao = document.querySelector("[data-conclusao]");

  function toggleConclusao() {
    if (!conclusao) return;
    if (select && select.value === "concluida") {
      conclusao.value = new Date().toISOString().slice(0, 10);
      conclusao.disabled = true;
    } else if (conclusao) {
      conclusao.disabled = false;
    }
  }

  if (select) {
    select.addEventListener("change", function () {
      // TODO: substituir por HttpClientBase.js → PATCH /api/ordens/:id
      console.info("Status alterado para:", select.value);
      toggleConclusao();
    });
  }

  // Run on page load in case status is already "concluida"
  toggleConclusao();

  const finalizar = document.querySelector("[data-finalizar]");
  if (finalizar) {
    finalizar.addEventListener("click", function () {
      if (confirm("Concluir esta OS e notificar o cliente?")) {
        // TODO: substituir por HttpClientBase.js → POST /api/ordens/:id/concluir
        alert("OS concluída (demo).");
        if (select) { select.value = "concluida"; toggleConclusao(); }
      }
    });
  }
})();

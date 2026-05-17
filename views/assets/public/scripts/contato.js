/* ============================================================
   TechOS — Comportamentos da página de Contato
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-contact-form]");
  const feedback = document.querySelector("[data-form-feedback]");

  if (!form || !feedback) {
    return;
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    // TODO: substituir por chamada real via HttpClientBase.js
    feedback.hidden = false;
    feedback.textContent = "Mensagem enviada! Entraremos em contato em breve.";
    form.reset();
  });
})();

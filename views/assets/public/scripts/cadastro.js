/* ============================================================
   TechOS — Tela de Cadastro de empresa
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-signup-form]");
  const cnpjField = document.querySelector("[data-cnpj]");

  if (cnpjField) {
    cnpjField.addEventListener("input", function (event) {
      const digits = event.target.value.replace(/\D/g, "").slice(0, 14);
      const masked = digits
        .replace(/^(\d{2})(\d)/, "$1.$2")
        .replace(/^(\d{2})\.(\d{3})(\d)/, "$1.$2.$3")
        .replace(/\.(\d{3})(\d)/, ".$1/$2")
        .replace(/(\d{4})(\d)/, "$1-$2");
      event.target.value = masked;
    });
  }

  if (!form) {
    return;
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    // TODO: substituir por requisição real via HttpClientBase.js
    window.alert("Cadastro enviado (demonstração).");
  });
})();

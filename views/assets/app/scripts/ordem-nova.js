/* TechOS — Nova OS: máscara de telefone + envio */
(function () {
  "use strict";

  const tel = document.getElementById("os-telefone");
  if (tel) {
    tel.addEventListener("input", function () {
      let v = tel.value.replace(/\D/g, "").slice(0, 11);
      if (v.length > 6) {
        tel.value = "(" + v.slice(0, 2) + ") " + v.slice(2, 7) + "-" + v.slice(7);
      } else if (v.length > 2) {
        tel.value = "(" + v.slice(0, 2) + ") " + v.slice(2);
      } else {
        tel.value = v;
      }
    });
  }

  const form = document.querySelector("[data-os-form]");
  if (form) {
    form.addEventListener("submit", function (event) {
      event.preventDefault();
      if (!form.checkValidity()) {
        form.reportValidity();
        return;
      }
      // TODO: substituir por HttpClientBase.js → POST /api/ordens
      alert("OS criada com sucesso (demo). Redirecionando…");
      window.location.href = "ordens.html";
    });
  }
})();

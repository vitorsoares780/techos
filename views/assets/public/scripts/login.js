/* ============================================================
   TechOS — Tela de Login (validação simples no front)
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-login-form]");
  if (!form) {
    return;
  }

  form.addEventListener("submit", function (event) {
    event.preventDefault();
    
    // fazer requisição para PHP
  
    window.location.href = "../app/dashboard.html";
  });
})();

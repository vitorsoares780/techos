(function () {
  "use strict";

  const form = document.querySelector('[data-admin-login-form]');
  if (!form) {
    return;
  }

  form.addEventListener('submit', function (event) {
    event.preventDefault();

    const email = document.querySelector('#admin-email');
    const password = document.querySelector('#admin-password');

    if (!email || !password) {
      return;
    }

    if (!email.value || !password.value) {
      alert('Preencha e-mail e senha para continuar.');
      return;
    }

    // TODO: substituir por autenticação real via API
    window.location.href = 'painel.html';
  });
})();

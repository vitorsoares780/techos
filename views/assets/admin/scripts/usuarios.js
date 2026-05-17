(function () {
  "use strict";

  const modal = document.querySelector('[data-modal-usuario]');
  const btnNew = document.querySelector('[data-novo-usuario]');
  const closeButtons = document.querySelectorAll('[data-fechar-modal]');
  const saveButton = document.querySelector('[data-salvar-usuario]');

  if (btnNew && modal) {
    btnNew.addEventListener('click', function () {
      modal.classList.add('is-open');
    });
  }

  closeButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      if (modal) {
        modal.classList.remove('is-open');
      }
    });
  });

  if (saveButton) {
    saveButton.addEventListener('click', function () {
      // TODO: chamar API para convidar usuário
      console.info('Convidar usuário');
      if (modal) {
        modal.classList.remove('is-open');
      }
    });
  }
})();

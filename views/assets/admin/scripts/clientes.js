(function () {
  "use strict";

  const modal = document.querySelector('[data-modal-cliente]');
  const btnNew = document.querySelector('[data-novo-cliente]');
  const btnExport = document.querySelector('[data-export-csv]');
  const closeButtons = document.querySelectorAll('[data-fechar-modal]');
  const saveButton = document.querySelector('[data-salvar-cliente]');

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
      // TODO: enviar dados reais ao back-end via API
      console.info('Salvar cliente clicado');
      if (modal) {
        modal.classList.remove('is-open');
      }
    });
  }

  if (btnExport) {
    btnExport.addEventListener('click', function () {
      // TODO: exportar CSV real de clientes
      console.info('Exportar clientes CSV');
    });
  }
})();

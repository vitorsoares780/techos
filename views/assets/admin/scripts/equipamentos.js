(function () {
  "use strict";

  const newButton = document.querySelector('[data-novo-equipamento]');
  const filterForm = document.querySelector('[data-filter-form]');

  if (newButton) {
    newButton.addEventListener('click', function () {
      // TODO: implementar modal de cadastro de equipamento
      console.info('Novo equipamento clicado');
      alert('Função de novo equipamento ainda será implementada.');
    });
  }

  if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
      event.preventDefault();
      const formData = new FormData(filterForm);
      console.info('Filtros de equipamentos:', Object.fromEntries(formData.entries()));
    });
  }
})();

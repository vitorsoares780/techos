(function () {
  "use strict";

  const periodSelect = document.querySelector('select[aria-label="Período"]');
  const exportButton = document.querySelector('[data-export-pdf]');

  if (periodSelect) {
    periodSelect.addEventListener('change', function () {
      // TODO: atualizar view com dados reais da API
      console.info('Período selecionado:', periodSelect.value);
    });
  }

  if (exportButton) {
    exportButton.addEventListener('click', function () {
      // TODO: implementar exportação real de relatório
      console.info('Exportando relatório PDF');
    });
  }
})();

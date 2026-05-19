(function () {
  "use strict";

  const exportButton = document.querySelector('[data-export-pdf]');
  if (exportButton) {
    exportButton.addEventListener('click', function () {
      // TODO: gerar relatório real via API ou serviço de exportação
      console.info('Exportar relatório admin');
    });
  }
})();

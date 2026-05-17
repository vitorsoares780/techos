(function () {
  "use strict";

  const saveButton = document.querySelector('[data-save-company]');
  if (!saveButton) return;

  saveButton.addEventListener('click', function () {
    // TODO: enviar dados da empresa para API
    console.info('Salvar dados da empresa');
    alert('Dados da empresa salvos (mock).');
  });
})();

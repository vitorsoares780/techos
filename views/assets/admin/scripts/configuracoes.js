(function () {
  "use strict";

  const saveButton = document.querySelector('[data-save-settings]');
  if (!saveButton) return;

  saveButton.addEventListener('click', function () {
    // TODO: salvar configurações no back-end
    console.info('Salvar configurações do admin');
    alert('Configurações salvas (mock).');
  });
})();

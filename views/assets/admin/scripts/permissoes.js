(function () {
  "use strict";

  const saveButton = document.querySelector('[data-salvar-permissoes]');
  if (!saveButton) return;

  saveButton.addEventListener('click', function () {
    const toggles = Array.from(document.querySelectorAll('.permission-item input[type="checkbox"]'));
    const data = toggles.map(function (input) {
      return { id: input.id, checked: input.checked };
    });
    // TODO: enviar permissões ao back-end
    console.info('Salvar permissões:', data);
    alert('Permissões atualizadas (mock).');
  });
})();

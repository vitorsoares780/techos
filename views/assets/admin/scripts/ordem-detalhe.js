(function () {
  "use strict";

  const btnPrint = document.querySelector('[data-imprimir]');
  const btnSend = document.querySelector('[data-enviar-cliente]');
  const btnConclude = document.querySelector('[data-concluir]');
  const updateStatus = document.querySelector('[data-atualizar-status]');
  const statusSelect = document.querySelector('[data-status-select]');

  function showToast(message) {
    console.info(message);
    alert(message);
  }

  if (btnPrint) {
    btnPrint.addEventListener('click', function () {
      showToast('Imprimir OS selecionada');
    });
  }

  if (btnSend) {
    btnSend.addEventListener('click', function () {
      showToast('Enviar OS ao cliente');
    });
  }

  if (btnConclude) {
    btnConclude.addEventListener('click', function () {
      showToast('OS concluída');
    });
  }

  if (updateStatus && statusSelect) {
    updateStatus.addEventListener('click', function () {
      const status = statusSelect.value;
      showToast('Status atualizado para: ' + status);
    });
  }
})();

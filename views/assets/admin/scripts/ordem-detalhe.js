(function () {
  "use strict";

  const btnPrint = document.querySelector('[data-imprimir]');
  const btnSend = document.querySelector('[data-enviar-cliente]');
  const btnConclude = document.querySelector('[data-concluir]');
  const updateStatus = document.querySelector('[data-atualizar-status]');
  const statusSelect = document.querySelector('[data-status-select]');
  const conclusao = document.querySelector('[data-conclusao]');

  function toggleConclusao() {
    if (!conclusao) return;
    if (statusSelect && statusSelect.value === "concluida") {
      conclusao.value = new Date().toISOString().slice(0, 10);
      conclusao.disabled = true;
    } else if (conclusao) {
      conclusao.disabled = false;
    }
  }

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
      if (statusSelect) { statusSelect.value = "concluida"; toggleConclusao(); }
    });
  }

  if (updateStatus && statusSelect) {
    updateStatus.addEventListener('click', function () {
      const status = statusSelect.value;
      showToast('Status atualizado para: ' + status);
      toggleConclusao();
    });
  }

  // Watch for status select changes directly
  if (statusSelect) {
    statusSelect.addEventListener('change', toggleConclusao);
  }

  // Run on page load in case status is already "concluida"
  toggleConclusao();
})();

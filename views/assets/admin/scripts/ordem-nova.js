(function () {
  "use strict";

  const modalCliente = document.querySelector('[data-modal-novo-cliente]');
  const btnNovoCliente = document.querySelector('[data-novo-cliente]');
  const closeClienteButtons = document.querySelectorAll('[data-fechar-modal]');
  const saveClienteBtn = document.querySelector('[data-salvar-cliente]');

  const modalItem = document.querySelector('[data-modal-item]');
  const btnAddItem = document.querySelector('[data-add-item]');
  const closeItemButtons = document.querySelectorAll('[data-fechar-modal-item]');
  const saveItemBtn = document.querySelector('[data-salvar-item]');
  const itensTable = document.querySelector('[data-itens-table] tbody');
  const itemEmpty = document.querySelector('[data-item-vazio]');
  const totalField = document.querySelector('[data-total]');

  function toggleModal(modal, open) {
    if (!modal) return;
    modal.classList.toggle('is-open', open);
  }

  function formatMoney(value) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value);
  }

  function updateTotal() {
    if (!itensTable || !totalField) return;
    let total = 0;
    itensTable.querySelectorAll('tr[data-item]').forEach(function (row) {
      const subtotal = Number(row.dataset.subtotal || '0');
      total += subtotal;
    });
    totalField.textContent = formatMoney(total);
  }

  if (btnNovoCliente) {
    btnNovoCliente.addEventListener('click', function () {
      toggleModal(modalCliente, true);
    });
  }

  closeClienteButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      toggleModal(modalCliente, false);
    });
  });

  if (saveClienteBtn) {
    saveClienteBtn.addEventListener('click', function () {
      // TODO: cadastrar cliente real via API
      console.info('Salvar novo cliente');
      toggleModal(modalCliente, false);
    });
  }

  if (btnAddItem) {
    btnAddItem.addEventListener('click', function () {
      toggleModal(modalItem, true);
    });
  }

  closeItemButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      toggleModal(modalItem, false);
    });
  });

  if (saveItemBtn) {
    saveItemBtn.addEventListener('click', function () {
      const desc = document.querySelector('#item-desc');
      const qtd = document.querySelector('#item-qtd');
      const valor = document.querySelector('#item-valor');
      if (!desc || !qtd || !valor || !itensTable) return;

      const amount = Number(valor.value.replace(',', '.')) || 0;
      const quantity = Number(qtd.value) || 1;
      const subtotal = amount * quantity;

      const row = document.createElement('tr');
      row.dataset.item = 'true';
      row.dataset.subtotal = String(subtotal);
      row.innerHTML = `
        <td>${desc.value}</td>
        <td>${quantity}</td>
        <td>${formatMoney(amount)}</td>
        <td>${formatMoney(subtotal)}</td>
        <td><span class="row-actions"><button type="button" class="danger" data-remove-item>✕</button></span></td>
      `;
      if (itemEmpty) {
        itemEmpty.remove();
      }
      itensTable.appendChild(row);
      updateTotal();
      toggleModal(modalItem, false);
      desc.value = '';
      qtd.value = '1';
      valor.value = '';
    });
  }

  if (itensTable) {
    itensTable.addEventListener('click', function (event) {
      const button = event.target.closest('[data-remove-item]');
      if (!button) return;
      const row = button.closest('tr');
      if (row) {
        row.remove();
        updateTotal();
      }
    });
  }
})();

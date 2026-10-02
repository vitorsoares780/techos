// ============================================================================
// ordem-nova.js - Script para a página de Nova Ordem de Serviço (App/Cliente)
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
    console.log('Página Nova OS (App) carregada');

    const form = document.getElementById('formNovaOS');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnCancel = document.getElementById('btnCancel');

    // Carregar dispositivos do cliente logado
    carregarMeusDispositivos();

    // Event listeners
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', () => window.location.href = 'index.html');
    }
});

// ============================================================================
// Carregar dispositivos do cliente
// ============================================================================

async function carregarMeusDispositivos() {
    try {
        const token = localStorage.getItem('authToken');
        const userId = localStorage.getItem('userId'); // Assumindo que o userId está salvo no login

        const response = await fetch(`${window.API_BASE_URL}/devices/list`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();

        if (data.success && data.data) {
            const select = document.getElementById('deviceId');
            // Filtrar apenas dispositivos do usuário logado
            const meusDispositivos = data.data.filter(d => d.user_id == userId);

            meusDispositivos.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.textContent = `${device.brand} ${device.model} (${device.serialNumber})`;
                select.appendChild(option);
            });

            // Se só tem um dispositivo, selecionar automaticamente
            if (meusDispositivos.length === 1) {
                select.value = meusDispositivos[0].id;
            }
        }
    } catch (error) {
        console.error('Erro ao carregar dispositivos:', error);
    }
}

// ============================================================================
// Handle Submit
// ============================================================================

async function handleSubmit(event) {
    event.preventDefault();

    const btnSubmit = document.getElementById('btnSubmit');
    const originalText = btnSubmit.textContent;
    btnSubmit.disabled = true;
    btnSubmit.textContent = 'Enviando...';

    // Coletar dados do formulário
    const formData = new FormData(event.target);
    const osData = {};

    for (const [key, value] of formData.entries()) {
        if (value !== '') {
            osData[key] = value;
        }
    }

    // Adicionar user_id do usuário logado
    const userId = localStorage.getItem('userId');
    if (userId) {
        osData.userId = userId;
    }

    // Status padrão para nova OS do cliente
    osData.status = 'aguardando';

    try {
        const token = localStorage.getItem('authToken');
        const response = await fetch(`${window.API_BASE_URL}/serviceOrders`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${token}`
            },
            body: JSON.stringify(osData)
        });
        const result = await response.json();

        if (result.success) {
            showSuccess('Ordem de Serviço criada com sucesso! Entraremos em contato em breve.');
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 2000);
        } else {
            showError(result.message || 'Erro ao criar OS');
        }
    } catch (error) {
        console.error('Erro ao criar OS:', error);
        showError('Erro ao criar OS: ' + error.message);
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.textContent = originalText;
    }
}

// ============================================================================
// Helpers
// ============================================================================

function showError(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-danger alert-dismissible fade show mt-3';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    const container = document.querySelector('.card-body') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
    setTimeout(() => alertDiv.remove(), 5000);
}

function showSuccess(message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = 'alert alert-success alert-dismissible fade show mt-3';
    alertDiv.role = 'alert';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    `;
    const container = document.querySelector('.card-body') || document.body;
    container.insertBefore(alertDiv, container.firstChild);
}
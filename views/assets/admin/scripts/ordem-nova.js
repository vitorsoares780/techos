// ============================================================================
// ordem-nova.js - Script para a página de Nova Ordem de Serviço (Admin)
// ============================================================================

document.addEventListener('DOMContentLoaded', function () {
    console.log('Página Nova OS (Admin) carregada');

    const form = document.getElementById('formNovaOS');
    const btnSubmit = document.getElementById('btnSubmit');
    const btnCancel = document.getElementById('btnCancel');

    // Carregar dados para selects
    carregarUsuarios();
    carregarDispositivos();
    carregarEmpresas();
    carregarPlanos();

    // Event listeners
    if (form) {
        form.addEventListener('submit', handleSubmit);
    }
    if (btnCancel) {
        btnCancel.addEventListener('click', () => window.location.href = 'ordens-servico.html');
    }
});

// ============================================================================
// Carregar dados para os selects
// ============================================================================

async function carregarUsuarios() {
    try {
        const token = localStorage.getItem('authToken');
        const response = await fetch(`${window.API_BASE_URL}/users/list`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        if (data.success && data.data) {
            const select = document.getElementById('userId');
            data.data.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.name} (${user.email})`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar usuários:', error);
    }
}

async function carregarDispositivos() {
    try {
        const token = localStorage.getItem('authToken');
        const response = await fetch(`${window.API_BASE_URL}/devices/list`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        if (data.success && data.data) {
            const select = document.getElementById('deviceId');
            data.data.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.textContent = `${device.brand} ${device.model} - ${device.serialNumber}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar dispositivos:', error);
    }
}

async function carregarEmpresas() {
    try {
        const token = localStorage.getItem('authToken');
        const response = await fetch(`${window.API_BASE_URL}/companies/list`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        if (data.success && data.data) {
            const select = document.getElementById('companyId');
            data.data.forEach(company => {
                const option = document.createElement('option');
                option.value = company.id;
                option.textContent = company.name;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar empresas:', error);
    }
}

async function carregarPlanos() {
    try {
        const token = localStorage.getItem('authToken');
        const response = await fetch(`${window.API_BASE_URL}/plans/list`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        const data = await response.json();
        if (data.success && data.data) {
            const select = document.getElementById('planId');
            data.data.forEach(plan => {
                const option = document.createElement('option');
                option.value = plan.id;
                option.textContent = `${plan.name} - R$ ${plan.price}`;
                select.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erro ao carregar planos:', error);
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
    btnSubmit.textContent = 'Salvando...';

    // Coletar dados do formulário
    const formData = new FormData(event.target);
    const data = {};

    // Separar dados da OS e dados do dispositivo
    const osData = {};
    const deviceData = {};

    for (const [key, value] of formData.entries()) {
        if (value === '') continue;

        // Campos do dispositivo
        if (['brand', 'model', 'serialNumber', 'categoryId', 'color', 'accessories', 'imei1', 'imei2', 'deviceObservations'].includes(key)) {
            deviceData[key] = value;
        }
        // Campos da OS
        else {
            osData[key] = value;
        }
    }

    // Se há dados do dispositivo, criar dispositivo primeiro
    let deviceId = osData.deviceId;

    if (Object.keys(deviceData).length > 0) {
        try {
            const token = localStorage.getItem('authToken');
            const response = await fetch(`${window.API_BASE_URL}/devices`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Authorization': `Bearer ${token}`
                },
                body: JSON.stringify(deviceData)
            });
            const result = await response.json();
            if (result.success && result.data) {
                deviceId = result.data.id || result.data[0]?.id;
                osData.deviceId = deviceId;
            } else {
                throw new Error(result.message || 'Erro ao criar dispositivo');
            }
        } catch (error) {
            console.error('Erro ao criar dispositivo:', error);
            showError('Erro ao criar dispositivo: ' + error.message);
            btnSubmit.disabled = false;
            btnSubmit.textContent = originalText;
            return;
        }
    }

    // Criar a OS
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
            showSuccess('Ordem de Serviço criada com sucesso!');
            setTimeout(() => {
                window.location.href = 'ordens-servico.html';
            }, 1500);
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
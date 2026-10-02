import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const serviceOrder = new ServiceOrder();

const token = localStorage.getItem("token");
if (token) {
    serviceOrder.setAuthToken(token);
}

const form = document.querySelector("[data-filter-form]");
const tableBody = document.querySelector("[data-os-table] tbody");
const summary = document.querySelector("[data-os-summary]");

let orders = [];

function getOrders(response) {
    if (Array.isArray(response)) {
        return response;
    }

    if (response && Array.isArray(response.data)) {
        return response.data;
    }

    return [];
}

function getDevice(order) {
    const device = order.device || order.Device || order.equipment;

    if (device) {
        const name = device.name || device.type || "";
        const brand = device.brand || "";
        const model = device.model || "";
        const serial = device.serial_number || device.serialNumber || "";

        return [name, brand, model, serial]
            .filter(Boolean)
            .join(" ")
            .trim() || `Dispositivo #${order.device_id ?? "—"}`;
    }

    return order.device_name || order.equipment || `Dispositivo #${order.device_id ?? "—"}`;
}

function getStatusClass(status) {
    const normalized = String(status || "").toLowerCase();

    const map = {
        aberta: "aberta",
        aberto: "aberta",
        andamento: "andamento",
        "em andamento": "andamento",
        aguardando: "aguardando",
        "aguardando peça": "aguardando",
        concluida: "concluida",
        concluída: "concluida",
        cancelada: "cancelada"
    };

    return map[normalized] || "aguardando";
}

function getStatusLabel(status) {
    const normalized = String(status || "").toLowerCase();

    const map = {
        aberta: "Aberta",
        aberto: "Aberta",
        andamento: "Em andamento",
        "em andamento": "Em andamento",
        aguardando: "Aguardando peça",
        "aguardando peça": "Aguardando peça",
        concluida: "Concluída",
        concluída: "Concluída",
        cancelada: "Cancelada"
    };

    return map[normalized] || status || "Aguardando";
}

function formatDate(value) {
    if (!value) {
        return "—";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return "—";
    }

    return date.toLocaleDateString("pt-BR");
}

function escapeHtml(value) {
    return String(value ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function renderOrders(list) {
    tableBody.innerHTML = "";

    if (list.length === 0) {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center">Nenhuma ordem de serviço encontrada.</td>
            </tr>
        `;
        updateSummary(0);
        return;
    }

    list.forEach(order => {
        const statusClass = getStatusClass(order.status);
        const statusLabel = getStatusLabel(order.status);
        const device = getDevice(order);

        const row = document.createElement("tr");

        row.innerHTML = `
            <td>#${escapeHtml(order.id)}</td>
            <td>${escapeHtml(device)}</td>
            <td><span class="pill ${statusClass}">${escapeHtml(statusLabel)}</span></td>
            <td>${escapeHtml(formatDate(order.creation_time || order.created_at))}</td>
            <td>
                <span class="row-actions">
                    <a href="ordem-detalhe.html?id=${encodeURIComponent(order.id)}">Abrir</a>
                    <button type="button" data-order-delete="${escapeHtml(order.id)}">Excluir</button>
                </span>
            </td>
        `;

        tableBody.appendChild(row);
    });

    updateSummary(list.length);
}

function updateSummary(total) {
    if (summary) {
        summary.textContent = `Mostrando ${total} ${total === 1 ? "ordem" : "ordens"}`;
    }
}

function applyFilters() {
    const search = String(form?.elements.q?.value || "").trim().toLowerCase();
    const status = String(form?.elements.status?.value || "").trim().toLowerCase();
    const from = form?.elements.from?.value || "";
    const to = form?.elements.to?.value || "";

    const filtered = orders.filter(order => {
        const device = getDevice(order).toLowerCase();
        const id = String(order.id || "").toLowerCase();
        const orderStatus = getStatusClass(order.status);
        const dateValue = order.creation_time || order.created_at;
        const date = dateValue ? new Date(dateValue) : null;
        const dateString = date && !Number.isNaN(date.getTime())
            ? date.toISOString().slice(0, 10)
            : "";

        const matchSearch = !search || device.includes(search) || id.includes(search);
        const matchStatus = !status || orderStatus === status;
        const matchFrom = !from || (dateString && dateString >= from);
        const matchTo = !to || (dateString && dateString <= to);

        return matchSearch && matchStatus && matchFrom && matchTo;
    });

    renderOrders(filtered);
}

async function loadOrders() {
    tableBody.innerHTML = `
        <tr>
            <td colspan="5" style="text-align:center">Carregando ordens...</td>
        </tr>
    `;

    try {
        const response = await serviceOrder.listAll();
        orders = getOrders(response);
        applyFilters();
    } catch (error) {
        console.error("Erro ao carregar ordens:", error);
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align:center">Não foi possível carregar as ordens.</td>
            </tr>
        `;
        toastPrincipal.error({
            message: error.message || "Erro ao carregar as ordens."
        });
    }
}

async function deleteOrder(id) {
    if (!window.confirm(`Deseja excluir a ordem #${id}?`)) {
        return;
    }

    try {
        const response = await serviceOrder.remove(id);

        toastPrincipal.success(
            response?.message
                ? response
                : { message: "Ordem de serviço excluída com sucesso." }
        );

        await loadOrders();
    } catch (error) {
        console.error("Erro ao excluir ordem:", error);
        toastPrincipal.error({
            message: error.message || "Erro ao excluir a ordem de serviço."
        });
    }
}

if (form) {
    form.addEventListener("input", applyFilters);
    form.addEventListener("change", applyFilters);
}

tableBody?.addEventListener("click", event => {
    const button = event.target.closest("[data-order-delete]");

    if (!button) {
        return;
    }

    deleteOrder(button.dataset.orderDelete);
});

loadOrders();

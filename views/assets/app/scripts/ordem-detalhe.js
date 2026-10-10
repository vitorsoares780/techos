import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const serviceOrder = new ServiceOrder();
const token = localStorage.getItem("token");
const params = new URLSearchParams(window.location.search);
const orderId = params.get("id");

if (token) {
    serviceOrder.setAuthToken(token);
}

function getStatusClass(status) {
    const normalized = String(status || "").toLowerCase().replaceAll("_", " ");
    const classes = {
        aberta: "aberta",
        aberto: "aberta",
        andamento: "andamento",
        "em andamento": "andamento",
        aguardando: "aguardando",
        "aguardando peca": "aguardando",
        "aguardando peça": "aguardando",
        concluida: "concluida",
        "concluída": "concluida",
        cancelada: "cancelada"
    };

    return classes[normalized] || "aguardando";
}

function getStatusLabel(status) {
    const labels = {
        aberta: "Aberta",
        aberto: "Aberta",
        andamento: "Em andamento",
        "em andamento": "Em andamento",
        aguardando: "Aguardando peça",
        "aguardando peca": "Aguardando peça",
        "aguardando peça": "Aguardando peça",
        concluida: "Concluída",
        "concluída": "Concluída",
        cancelada: "Cancelada"
    };
    const normalized = String(status || "").toLowerCase().replaceAll("_", " ");

    return labels[normalized] || status || "Aguardando";
}

function formatDate(value) {
    if (!value) return "—";

    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? "—" : date.toLocaleString("pt-BR");
}

function formatPrice(value) {
    const price = Number(value);
    if (!Number.isFinite(price)) return "—";

    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL"
    }).format(price);
}

function renderOrder(order) {
    const statusLabel = getStatusLabel(order.status);
    const deviceName = params.get("device") || order.device_name || order.device_model || `Dispositivo #${order.device_id ?? "—"}`;
    const date = formatDate(order.creation_time || order.created_at);
    const statusElement = document.querySelector("[data-detail-status]");

    document.querySelector("[data-detail-title]").textContent = `OS #${order.id} · ${deviceName}`;
    document.querySelector("[data-detail-meta]").textContent = `Aberta em ${date} · ${statusLabel}`;
    document.querySelector("[data-detail-defect]").textContent = order.defect || "—";
    document.querySelector("[data-detail-diagnosis]").textContent = order.diagnosis || "—";
    document.querySelector("[data-detail-price]").textContent = formatPrice(order.price);
    document.querySelector("[data-detail-device]").textContent = deviceName;
    statusElement.className = `pill ${getStatusClass(order.status)}`;
    statusElement.textContent = statusLabel;
}

async function loadOrder() {
    if (!orderId || !/^\d+$/.test(orderId)) {
        toastPrincipal.error({ message: "ID da ordem de serviço inválido." });
        document.querySelector("[data-detail-title]").textContent = "Ordem não encontrada";
        return;
    }

    try {
        const response = await serviceOrder.listById(orderId);
        if (response?.type === "error" || response?.code >= 400) {
            throw new Error(response.message || "Não foi possível carregar a ordem de serviço.");
        }

        const order = Array.isArray(response?.data) ? response.data[0] : response?.data;
        if (!order) {
            throw new Error("Ordem de serviço não encontrada.");
        }

        renderOrder(order);
    } catch (error) {
        console.error("Erro ao carregar ordem de serviço:", error);
        document.querySelector("[data-detail-title]").textContent = "Não foi possível carregar a ordem";
        document.querySelector("[data-detail-meta]").textContent = "Verifique o acesso e tente novamente.";
        toastPrincipal.error({
            message: error.message || "Erro ao carregar a ordem de serviço."
        });
    }
}

loadOrder();
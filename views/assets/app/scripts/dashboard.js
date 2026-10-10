import ServiceOrder from "../../_common/classes/ServiceOrder.js";

const serviceOrder = new ServiceOrder();
const token = localStorage.getItem("token");
const tableBody = document.querySelector("tbody[data-dashboard-orders]");
const openCount = document.querySelector("[data-os-open]");
const inProgressCount = document.querySelector("[data-os-progress]");
const completedCount = document.querySelector("[data-os-completed]");

if (token) {
  serviceOrder.setAuthToken(token);
}

function unwrapOrders(response) {
  if (Array.isArray(response)) {
    return response;
  }

  return Array.isArray(response?.data) ? response.data : [];
}

function normalizeStatus(status) {
  return String(status || "")
    .toLowerCase()
    .replaceAll("_", " ");
}

function getStatusClass(status) {
  const normalized = normalizeStatus(status);

  if (["aberta", "aberto"].includes(normalized)) return "aberta";
  if (["em andamento", "andamento"].includes(normalized)) return "andamento";
  if (["concluida", "concluída"].includes(normalized)) return "concluida";
  if (normalized === "cancelada") return "cancelada";
  return "aguardando";
}

function getStatusLabel(status) {
  const labels = {
    aberta: "Aberta",
    aberto: "Aberta",
    andamento: "Em andamento",
    "em andamento": "Em andamento",
    aguardando: "Aguardando peça",
    "aguardando peca": "Aguardando peça",
    concluida: "Concluída",
    "concluída": "Concluída",
    cancelada: "Cancelada"
  };

  const normalized = normalizeStatus(status);
  return labels[normalized] || status || "Aguardando";
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function formatDate(value) {
  if (!value) return "—";

  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime())
    ? "—"
    : date.toLocaleDateString("pt-BR");
}

function renderOrders(orders) {
  const openOrders = orders.filter(order => getStatusClass(order.status) === "aberta").length;
  const inProgressOrders = orders.filter(order => getStatusClass(order.status) === "andamento").length;
  const completedOrders = orders.filter(order => getStatusClass(order.status) === "concluida").length;

  if (openCount) openCount.textContent = String(openOrders);
  if (inProgressCount) inProgressCount.textContent = String(inProgressOrders);
  if (completedCount) completedCount.textContent = String(completedOrders);

  const recentOrders = orders.slice(0, 5);
  if (!tableBody) return;

  if (recentOrders.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="4">Nenhuma ordem de serviço encontrada.</td></tr>';
    return;
  }

  tableBody.innerHTML = recentOrders.map(order => {
    const deviceName = order.device_model || order.device?.name || order.device_name || `Dispositivo #${order.device_id ?? "—"}`;
    const id = escapeHtml(order.id);

    return `
      <tr>
                <td><a href="ordem-detalhe.html?id=${encodeURIComponent(order.id)}&device=${encodeURIComponent(deviceName)}">#${id}</a></td>
        <td>${escapeHtml(deviceName)}</td>
        <td><span class="pill ${getStatusClass(order.status)}">${escapeHtml(getStatusLabel(order.status))}</span></td>
        <td>${escapeHtml(formatDate(order.creation_time || order.created_at))}</td>
      </tr>
    `;
  }).join("");
}

async function loadOrders() {
  if (!tableBody) return;

  try {
    const response = await serviceOrder.listAll();
    if (response?.type === "error" || response?.code >= 400) {
      throw new Error(response.message || "Não foi possível carregar as ordens.");
    }
    renderOrders(unwrapOrders(response));
  } catch (error) {
    console.error("Erro ao carregar ordens do dashboard:", error);
    tableBody.innerHTML = '<tr><td colspan="4">Não foi possível carregar as ordens.</td></tr>';
  }
}

loadOrders();
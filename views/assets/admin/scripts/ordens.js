import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import Company from "../../_common/classes/Company.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const serviceOrder = new ServiceOrder();
const companyService = new Company();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-filter-form]");
const tableBody = document.querySelector("[data-os-table] tbody");
const summary = document.querySelector("[data-os-summary]");
const companyFilter = document.querySelector("[data-order-company-filter]");
let orders = [];

if (token) {
  serviceOrder.setAuthToken(token);
  companyService.setAuthToken(token);
}

function unwrapOrders(response) {
  if (response?.type === "error" || response?.code >= 400) {
    throw new Error(response.message || "Não foi possível carregar as ordens.");
  }
  if (Array.isArray(response)) return response;
  return Array.isArray(response?.data) ? response.data : [];
}

function normalizeStatus(status) {
  return String(status || "").toLowerCase().replaceAll("_", " ");
}

function getStatusClass(status) {
  const normalized = normalizeStatus(status);
  if (["aberta", "aberto"].includes(normalized)) return "aberta";
  if (["em andamento", "andamento"].includes(normalized)) return "andamento";
  if (["aguardando peça", "aguardando peca", "aguardando"].includes(normalized)) return "aguardando";
  if (["concluída", "concluida"].includes(normalized)) return "concluida";
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
    "aguardando peça": "Aguardando peça",
    "aguardando peca": "Aguardando peça",
    concluida: "Concluída",
    "concluída": "Concluída",
    cancelada: "Cancelada"
  };
  const normalized = normalizeStatus(status);
  return labels[normalized] || status || "Aguardando";
}

function getDateValue(value) {
  if (!value) return null;
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? null : date;
}

function formatDate(value) {
  const date = getDateValue(value);
  return date ? date.toLocaleDateString("pt-BR") : "—";
}

function formatPrice(value) {
  const price = Number(value);
  if (!Number.isFinite(price)) return "—";
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL"
  }).format(price);
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
  if (!tableBody) return;

  if (list.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="9">Nenhuma ordem de serviço encontrada.</td></tr>';
    if (summary) summary.textContent = "Mostrando 0 ordens";
    return;
  }

  tableBody.innerHTML = list.map(order => `
    <tr>
      <td><a href="ordem-detalhe.html?id=${encodeURIComponent(order.id)}">#${escapeHtml(order.id)}</a></td>
      <td>${escapeHtml(order.company_name || "—")}</td>
      <td>${escapeHtml(order.user_name || "—")}</td>
      <td>${escapeHtml(order.device_model || "—")}</td>
      <td>${escapeHtml(order.defect || "—")}</td>
      <td><span class="pill ${getStatusClass(order.status)}">${escapeHtml(getStatusLabel(order.status))}</span></td>
      <td>${escapeHtml(formatDate(order.creation_time || order.created_at))}</td>
      <td>${escapeHtml(formatPrice(order.price))}</td>
      <td><span class="row-actions"><a href="ordem-detalhe.html?id=${encodeURIComponent(order.id)}">Abrir</a><button type="button" class="danger" data-order-delete="${escapeHtml(order.id)}">Excluir</button></span></td>
    </tr>
  `).join("");

  if (summary) {
    summary.textContent = `Mostrando ${list.length} ${list.length === 1 ? "ordem" : "ordens"}`;
  }
}

function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  const status = String(filterForm?.elements.status?.value || "").toLowerCase();
  const companyId = String(companyFilter?.value || "");
  const from = filterForm?.elements.from?.value || "";
  const to = filterForm?.elements.to?.value || "";

  const filteredOrders = orders.filter(order => {
    const searchValues = [order.id, order.user_name, order.device_model, order.defect]
      .map(value => String(value || "").toLocaleLowerCase("pt-BR"));
    const matchesQuery = !query || searchValues.some(value => value.includes(query));
    const matchesStatus = !status || getStatusClass(order.status) === status;
    const matchesCompany = !companyId || String(order.company_id) === companyId;
    const date = getDateValue(order.creation_time || order.created_at);
    const dateValue = date ? [date.getFullYear(), String(date.getMonth() + 1).padStart(2, "0"), String(date.getDate()).padStart(2, "0")].join("-") : "";
    const matchesFrom = !from || (dateValue && dateValue >= from);
    const matchesTo = !to || (dateValue && dateValue <= to);

    return matchesQuery && matchesStatus && matchesCompany && matchesFrom && matchesTo;
  });

  renderOrders(filteredOrders);
}

async function loadOrders() {
  if (!tableBody) return;

  try {
    orders = unwrapOrders(await serviceOrder.listAll());
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar ordens:", error);
    tableBody.innerHTML = '<tr><td colspan="9">Não foi possível carregar as ordens.</td></tr>';
    if (summary) summary.textContent = "Falha ao carregar ordens";
    toastPrincipal.error({ message: error.message || "Erro ao carregar as ordens." });
  }
}

filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
filterForm?.addEventListener("change", applyFilters);
companyFilter?.addEventListener("change", applyFilters);
tableBody?.addEventListener("click", async event => {
  const button = event.target.closest("[data-order-delete]");
  if (!button || !window.confirm(`Deseja excluir a OS #${button.dataset.orderDelete}?`)) return;
  try {
    const response = await serviceOrder.remove(Number(button.dataset.orderDelete));
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
      throw new Error(response.message || "Não foi possível excluir a ordem.");
    }
    toastPrincipal.success({ message: response.message || "Ordem excluída com sucesso." });
    await loadOrders();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir ordem." });
  }
});

async function loadCompanies() {
  if (!companyFilter) return;
  try {
    const response = await companyService.listAll();
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
      throw new Error(response.message || "Não foi possível carregar as empresas.");
    }
    const companies = Array.isArray(response?.data) ? response.data : [];
    companies.forEach(company => {
      const option = document.createElement("option");
      option.value = company.id;
      option.textContent = company.name;
      companyFilter.appendChild(option);
    });
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao carregar empresas para o filtro." });
  }
}

loadOrders();
loadCompanies();
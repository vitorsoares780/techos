import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import Company from "../../_common/classes/Company.js";
import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const serviceOrder = new ServiceOrder();
const companyService = new Company();
const userService = new User();
const token = localStorage.getItem("token");
const orderTable = document.querySelector("[data-dashboard-orders]");
const companyTable = document.querySelector("[data-dashboard-companies]");

if (token) {
  serviceOrder.setAuthToken(token);
  companyService.setAuthToken(token);
  userService.setAuthToken(token);
}

function unwrap(response, description) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || `Não foi possível carregar ${description}.`);
  }
  if (Array.isArray(response)) return response;
  return Array.isArray(response?.data) ? response.data : [];
}

function normalizeStatus(status) {
  return String(status || "").toLowerCase().replaceAll("_", " ");
}

function statusClass(status) {
  const value = normalizeStatus(status);
  if (["aberta", "aberto"].includes(value)) return "aberta";
  if (["em andamento", "andamento"].includes(value)) return "andamento";
  if (["aguardando", "aguardando peça", "aguardando peca"].includes(value)) return "aguardando";
  if (["concluída", "concluida"].includes(value)) return "concluida";
  if (value === "cancelada") return "cancelada";
  return "aguardando";
}

function statusLabel(status) {
  const labels = { aberta: "Aberta", aberto: "Aberta", andamento: "Em andamento", "em andamento": "Em andamento", aguardando: "Aguardando peça", "aguardando peça": "Aguardando peça", "aguardando peca": "Aguardando peça", concluida: "Concluída", "concluída": "Concluída", cancelada: "Cancelada" };
  return labels[normalizeStatus(status)] || status || "Aguardando";
}

function formatDate(value) {
  if (!value) return "—";
  const date = new Date(String(value).replace(" ", "T"));
  return Number.isNaN(date.getTime()) ? "—" : date.toLocaleDateString("pt-BR");
}

function escapeHtml(value) {
  return String(value ?? "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
}

function renderOrders(orders) {
  const open = orders.filter(order => statusClass(order.status) === "aberta").length;
  document.querySelector("[data-kpi-open]").textContent = String(open);
  document.querySelector("[data-kpi-orders]").textContent = String(orders.length);

  if (!orderTable) return;
  const recent = orders.slice(0, 5);
  if (!recent.length) {
    orderTable.innerHTML = '<tr><td colspan="6">Nenhuma ordem ativa encontrada.</td></tr>';
    return;
  }
  orderTable.innerHTML = recent.map(order => `
    <tr>
      <td><a href="ordem-detalhe.html?id=${encodeURIComponent(order.id)}">#${escapeHtml(order.id)}</a></td>
      <td>${escapeHtml(order.company_name || "—")}</td>
      <td>${escapeHtml(order.user_name || "—")}</td>
      <td>${escapeHtml(order.device_model || "—")}</td>
      <td><span class="pill ${statusClass(order.status)}">${escapeHtml(statusLabel(order.status))}</span></td>
      <td>${escapeHtml(formatDate(order.creation_time || order.created_at))}</td>
    </tr>
  `).join("");
}

function renderCompanies(companies) {
  document.querySelector("[data-kpi-companies]").textContent = String(companies.length);
  if (!companyTable) return;
  const recent = companies.slice(0, 5);
  if (!recent.length) {
    companyTable.innerHTML = '<tr><td colspan="3">Nenhuma empresa ativa encontrada.</td></tr>';
    return;
  }
  companyTable.innerHTML = recent.map(company => `
    <tr><td><a href="empresa-detalhe.html?id=${encodeURIComponent(company.id)}">${escapeHtml(company.name || "—")}</a></td><td>${escapeHtml(company.owner_name || "—")}</td><td>${escapeHtml(company.plan_name || "—")}</td></tr>
  `).join("");
}

function renderClients(users) {
  const clients = users.filter(user => Number(user.typeId ?? user.type_id) === 3 && Number(user.active) === 1);
  document.querySelector("[data-kpi-clients]").textContent = String(clients.length);
}

async function loadDashboard() {
  try {
    const [orderResponse, companyResponse, userResponse] = await Promise.all([
      serviceOrder.listAll(),
      companyService.listAll(),
      userService.listAll()
    ]);
    const orders = unwrap(orderResponse, "as ordens");
    const companies = unwrap(companyResponse, "as empresas");
    const users = unwrap(userResponse, "os usuários");
    renderOrders(orders);
    renderCompanies(companies);
    renderClients(users);
    const dateElement = document.querySelector("[data-current-date]");
    if (dateElement) dateElement.textContent = new Date().toLocaleDateString("pt-BR", { day: "2-digit", month: "long", year: "numeric" });
  } catch (error) {
    console.error("Erro ao carregar dashboard geral:", error);
    if (orderTable) orderTable.innerHTML = '<tr><td colspan="6">Não foi possível carregar as ordens.</td></tr>';
    if (companyTable) companyTable.innerHTML = '<tr><td colspan="3">Não foi possível carregar as empresas.</td></tr>';
    toastPrincipal.error({ message: error.message || "Não foi possível carregar o painel geral." });
  }
}

loadDashboard();

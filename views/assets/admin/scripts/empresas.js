import Company from "../../_common/classes/Company.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const companyService = new Company();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-company-filter]");
const tableBody = document.querySelector("[data-company-table]");
const summary = document.querySelector("[data-company-summary]");
let companies = [];

if (token) companyService.setAuthToken(token);

function unwrap(response) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || "Não foi possível carregar as empresas.");
  }
  if (Array.isArray(response)) return response;
  return Array.isArray(response?.data) ? response.data : [];
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function render(list) {
  if (!tableBody) return;
  if (!list.length) {
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem">Nenhuma empresa encontrada.</td></tr>';
  } else {
    tableBody.innerHTML = list.map(company => `
      <tr>
        <td>${escapeHtml(company.name || "—")}</td>
        <td>${escapeHtml(company.cnpj || "—")}</td>
        <td>${escapeHtml(company.email || "—")}</td>
        <td>${escapeHtml(company.owner_name || "—")}</td>
        <td>${escapeHtml(company.plan_name || "—")}</td>
        <td><span class="row-actions"><a href="empresa-detalhe.html?id=${encodeURIComponent(company.id)}">Detalhes</a><button type="button" class="danger" data-company-delete="${escapeHtml(company.id)}">Excluir</button></span></td>
      </tr>
    `).join("");
  }
  if (summary) summary.textContent = `Mostrando ${list.length} ${list.length === 1 ? "empresa" : "empresas"}`;
}

function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  const filtered = companies.filter(company => [company.name, company.cnpj, company.email, company.owner_name, company.plan_name]
    .some(value => String(value || "").toLocaleLowerCase("pt-BR").includes(query)));
  render(filtered);
}

async function loadCompanies() {
  if (!tableBody) return;
  try {
    companies = unwrap(await companyService.listAll());
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar empresas:", error);
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem">Não foi possível carregar as empresas.</td></tr>';
    if (summary) summary.textContent = "Falha ao carregar empresas";
    toastPrincipal.error({ message: error.message || "Erro ao carregar empresas." });
  }
}

filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
tableBody?.addEventListener("click", async event => {
  const button = event.target.closest("[data-company-delete]");
  if (!button || !window.confirm("Deseja excluir esta empresa?")) return;
  try {
    const response = await companyService.remove(Number(button.dataset.companyDelete));
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
      throw new Error(response.message || "Não foi possível excluir a empresa.");
    }
    toastPrincipal.success({ message: response.message || "Empresa excluída com sucesso." });
    await loadCompanies();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir empresa." });
  }
});
loadCompanies();

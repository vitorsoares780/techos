import Plan from "../../_common/classes/Plan.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const planService = new Plan();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-plan-filter]");
const tableBody = document.querySelector("[data-plan-table]");
const summary = document.querySelector("[data-plan-summary]");
let plans = [];

if (token) planService.setAuthToken(token);

function unwrap(response) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || "Não foi possível carregar os planos.");
  }
  if (Array.isArray(response)) return response;
  return Array.isArray(response?.data) ? response.data : [];
}
function escapeHtml(value) {
  return String(value ?? "").replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;").replaceAll('"', "&quot;").replaceAll("'", "&#039;");
}
function isActive(plan) { return Number(plan.active) === 1; }
function formatPrice(value) {
  const price = Number(value);
  return Number.isFinite(price) ? new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(price) : "—";
}
function render(list) {
  if (!tableBody) return;
  if (!list.length) {
    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem">Nenhum plano encontrado.</td></tr>';
  } else {
    tableBody.innerHTML = list.map(plan => `
      <tr>
        <td>${escapeHtml(plan.name || "—")}</td><td>${escapeHtml(formatPrice(plan.price))}</td>
        <td><span class="pill ${isActive(plan) ? "ativo" : "inativo"}">${isActive(plan) ? "Ativo" : "Inativo"}</span></td>
        <td><span class="row-actions"><a href="plano-detalhe.html?id=${encodeURIComponent(plan.id)}">Detalhes</a><button type="button" class="danger" data-plan-delete="${escapeHtml(plan.id)}">Excluir</button></span></td>
      </tr>
    `).join("");
  }
  if (summary) summary.textContent = `Mostrando ${list.length} ${list.length === 1 ? "plano" : "planos"}`;
}
function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  const status = String(filterForm?.elements.status?.value || "");
  render(plans.filter(plan => (!query || String(plan.name || "").toLocaleLowerCase("pt-BR").includes(query)) && (!status || (status === "ativo") === isActive(plan))));
}
async function loadPlans() {
  if (!tableBody) return;
  try {
    plans = unwrap(await planService.listAll());
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar planos:", error);
    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem">Não foi possível carregar os planos.</td></tr>';
    if (summary) summary.textContent = "Falha ao carregar planos";
    toastPrincipal.error({ message: error.message || "Erro ao carregar planos." });
  }
}
async function deletePlan(id) {
  if (!window.confirm("Deseja excluir este plano? Empresas vinculadas podem impedir a exclusão.")) return;
  try {
    planService.id = id;
    const response = await planService.delete();
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") throw new Error(response.message || "Não foi possível excluir o plano.");
    toastPrincipal.success({ message: response.message || "Plano excluído com sucesso." });
    await loadPlans();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir plano." });
  }
}
filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
filterForm?.addEventListener("change", applyFilters);
tableBody?.addEventListener("click", event => {
  const button = event.target.closest("[data-plan-delete]");
  if (button) deletePlan(Number(button.dataset.planDelete));
});
loadPlans();

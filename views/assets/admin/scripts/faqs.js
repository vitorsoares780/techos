import Faq from "../../_common/classes/Faq.js";
import FaqCategory from "../../_common/classes/FaqCategory.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const faqService = new Faq();
const categoryService = new FaqCategory();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-faq-filter]");
const categoryFilter = document.querySelector("[data-faq-filter-categories]");
const tableBody = document.querySelector("[data-faq-table]");
const summary = document.querySelector("[data-faq-summary]");
let faqs = [];

if (token) {
  faqService.setAuthToken(token);
  categoryService.setAuthToken(token);
}

function unwrap(response, fallback) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || fallback);
  }
  return response?.data;
}

function asList(data) {
  if (Array.isArray(data)) return data;
  return data ? [data] : [];
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
    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem">Nenhuma FAQ encontrada.</td></tr>';
  } else {
    tableBody.innerHTML = list.map(faq => `
      <tr>
        <td>${escapeHtml(faq.category_name || "—")}</td>
        <td>${escapeHtml(faq.question || "—")}</td>
        <td>${escapeHtml(faq.answer || "—")}</td>
        <td><span class="row-actions"><a href="faq-detalhe.html?id=${encodeURIComponent(faq.id)}">Detalhes</a><button type="button" class="danger" data-faq-delete="${escapeHtml(faq.id)}">Excluir</button></span></td>
      </tr>
    `).join("");
  }
  if (summary) summary.textContent = `Mostrando ${list.length} ${list.length === 1 ? "FAQ" : "FAQs"}`;
}

function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  const categoryId = String(categoryFilter?.value || "");
  render(faqs.filter(faq => {
    const matchesQuery = !query || [faq.question, faq.answer, faq.category_name]
      .some(value => String(value || "").toLocaleLowerCase("pt-BR").includes(query));
    const matchesCategory = !categoryId || String(faq.faqs_category_id) === categoryId;
    return matchesQuery && matchesCategory;
  }));
}

async function loadFaqs() {
  if (!tableBody) return;
  try {
    const response = await faqService.listAll();
    faqs = asList(unwrap(response, "Não foi possível carregar as FAQs."));
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar FAQs:", error);
    tableBody.innerHTML = '<tr><td colspan="4" style="text-align:center;padding:2rem">Não foi possível carregar as FAQs.</td></tr>';
    if (summary) summary.textContent = "Falha ao carregar FAQs";
    toastPrincipal.error({ message: error.message || "Não foi possível carregar as FAQs." });
  }
}

async function loadCategories() {
  try {
    const categories = asList(unwrap(await categoryService.listAll(), "Não foi possível carregar as categorias."));
    categories.filter(category => category.active === undefined || Number(category.active) === 1).forEach(category => {
      const option = document.createElement("option");
      option.value = category.id;
      option.textContent = category.name;
      categoryFilter.appendChild(option);
    });
  } catch (error) {
    console.error("Erro ao carregar categorias:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível carregar categorias de FAQ." });
  }
}

filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
filterForm?.addEventListener("change", applyFilters);
tableBody?.addEventListener("click", async event => {
  const button = event.target.closest("[data-faq-delete]");
  if (!button || !window.confirm("Deseja excluir esta FAQ?")) return;
  try {
    faqService.id = Number(button.dataset.faqDelete);
    const response = await faqService.delete();
    unwrap(response, "Não foi possível excluir a FAQ.");
    toastPrincipal.success({ message: response.message || "FAQ excluída com sucesso." });
    await loadFaqs();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir FAQ." });
  }
});

await Promise.all([loadCategories(), loadFaqs()]);

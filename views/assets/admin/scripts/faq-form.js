import Faq from "../../_common/classes/Faq.js";
import FaqCategory from "../../_common/classes/FaqCategory.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const faqService = new Faq();
const categoryService = new FaqCategory();
const form = document.querySelector("[data-faq-form]");
const categorySelect = document.querySelector("[data-faq-categories]");
const submitButton = form?.querySelector("button[type='submit']");
const faqId = new URLSearchParams(window.location.search).get("id");
const isEditing = Boolean(faqId);
const token = localStorage.getItem("token");
let originalFaq = null;

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

async function loadCategories() {
  const categories = asList(unwrap(await categoryService.listAll(), "Não foi possível carregar categorias de FAQ."));
  categorySelect.innerHTML = '<option value="">Selecione uma categoria</option>';
  categories.filter(category => category.active === undefined || Number(category.active) === 1).forEach(category => {
    const option = document.createElement("option");
    option.value = category.id;
    option.textContent = category.name;
    categorySelect.appendChild(option);
  });
  if (categorySelect.options.length === 1) {
    throw new Error("Cadastre uma categoria de FAQ antes de criar uma pergunta.");
  }
}

async function loadFaq() {
  if (!isEditing) return;
  if (!/^\d+$/.test(faqId)) throw new Error("Identificador de FAQ inválido.");
  const data = unwrap(await faqService.listById(faqId), "Não foi possível carregar a FAQ.");
  originalFaq = Array.isArray(data) ? data[0] : data;
  if (!originalFaq) throw new Error("FAQ não encontrada.");
  categorySelect.value = String(originalFaq.faqs_category_id ?? "");
  form.elements.question.value = originalFaq.question || "";
  form.elements.answer.value = originalFaq.answer || "";
}

form?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!form.reportValidity()) return;

  const data = {
    faqs_category_id: Number(categorySelect.value),
    question: String(form.elements.question.value || "").trim(),
    answer: String(form.elements.answer.value || "").trim()
  };
  if (!data.faqs_category_id) {
    toastPrincipal.warning({ message: "Selecione uma categoria para a FAQ." });
    return;
  }

  if (isEditing && originalFaq
    && Number(originalFaq.faqs_category_id) === data.faqs_category_id
    && originalFaq.question === data.question
    && originalFaq.answer === data.answer) {
    toastPrincipal.success({ message: "Nenhuma alteração necessária." });
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = isEditing ? "Salvando..." : "Cadastrando...";

  try {
    faqService.category_id = data.faqs_category_id;
    faqService.question = data.question;
    faqService.answer = data.answer;
    if (isEditing) faqService.id = Number(faqId);

    const response = isEditing ? await faqService.update() : await faqService.insert();
    unwrap(response, "Não foi possível salvar a FAQ.");
    toastPrincipal.success({ message: response.message || (isEditing ? "FAQ atualizada com sucesso." : "FAQ cadastrada com sucesso.") });
    window.setTimeout(() => { window.location.href = "faqs.html"; }, 900);
  } catch (error) {
    console.error("Erro ao salvar FAQ:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível salvar a FAQ." });
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = isEditing ? "Salvar alterações" : "Cadastrar FAQ";
  }
});

async function initialize() {
  try {
    await loadCategories();
    await loadFaq();
  } catch (error) {
    console.error("Erro ao inicializar formulário de FAQ:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível carregar o formulário." });
    if (submitButton) submitButton.disabled = true;
  }
}

initialize();

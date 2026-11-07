import Plan from "../../_common/classes/Plan.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const planService = new Plan();
const form = document.querySelector("[data-plan-form]");
const submitButton = form?.querySelector("button[type='submit']");
const planId = new URLSearchParams(window.location.search).get("id");
const isEditing = Boolean(planId);
const token = localStorage.getItem("token");
let originalPlan = null;
if (token) planService.setAuthToken(token);

function unwrap(response, fallback) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") throw new Error(response.message || fallback);
  return response?.data;
}

async function loadPlan() {
  if (!isEditing) return;
  if (!/^\d+$/.test(planId)) throw new Error("Identificador de plano inválido.");
  const data = unwrap(await planService.listById(planId), "Não foi possível carregar o plano.");
  originalPlan = Array.isArray(data) ? data[0] : data;
  if (!originalPlan) throw new Error("Plano não encontrado.");
  form.elements.name.value = originalPlan.name || "";
  form.elements.price.value = originalPlan.price ?? "";
}

form?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!form.reportValidity()) return;
  const name = String(form.elements.name.value || "").trim();
  const price = Number(form.elements.price.value);
  if (!name || !Number.isFinite(price) || price < 0) {
    toastPrincipal.warning({ message: "Informe um nome e preço válidos." });
    return;
  }
  if (isEditing && originalPlan && originalPlan.name === name && Number(originalPlan.price) === price) {
    toastPrincipal.success({ message: "Nenhuma alteração necessária." });
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = isEditing ? "Salvando..." : "Cadastrando...";
  try {
    planService.name = name;
    planService.price = price;
    if (isEditing) planService.id = Number(planId);
    const response = isEditing ? await planService.update() : await planService.insert();
    unwrap(response, "Não foi possível salvar o plano.");
    toastPrincipal.success({ message: response.message || (isEditing ? "Plano atualizado com sucesso." : "Plano cadastrado com sucesso.") });
    window.setTimeout(() => { window.location.href = "planos.html"; }, 900);
  } catch (error) {
    console.error("Erro ao salvar plano:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível salvar o plano." });
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = isEditing ? "Salvar alterações" : "Cadastrar plano";
  }
});

loadPlan().catch(error => {
  console.error("Erro ao carregar plano:", error);
  toastPrincipal.error({ message: error.message || "Não foi possível carregar o plano." });
  if (submitButton) submitButton.disabled = true;
});

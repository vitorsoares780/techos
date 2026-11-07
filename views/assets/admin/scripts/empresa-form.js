import Company from "../../_common/classes/Company.js";
import User from "../../_common/classes/User.js";
import HttpClientBase from "../../_common/classes/HttpClientBase.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const companyService = new Company();
const userService = new User();
const httpClient = new HttpClientBase();
const form = document.querySelector("[data-company-form]");
const ownerSelect = document.querySelector("[data-company-owners]");
const planSelect = document.querySelector("[data-company-plans]");
const submitButton = form?.querySelector("button[type='submit']");
const companyId = new URLSearchParams(window.location.search).get("id");
const isEditing = Boolean(companyId);
const token = localStorage.getItem("token");
let originalCompany = null;

if (token) {
  companyService.setAuthToken(token);
  userService.setAuthToken(token);
  httpClient.setAuthToken(token);
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

async function loadSelects() {
  const [usersResponse, plansResponse] = await Promise.all([
    userService.listAll(),
    httpClient.get("/plans/list")
  ]);
  const users = asList(unwrap(usersResponse, "Não foi possível carregar responsáveis."));
  const plans = asList(unwrap(plansResponse, "Não foi possível carregar planos."));

  ownerSelect.innerHTML = '<option value="">Selecione o responsável</option>';
  users.filter(user => user.active === undefined || Number(user.active) === 1).forEach(user => {
    const option = document.createElement("option");
    option.value = user.id;
    option.textContent = `${user.name}${user.email ? ` (${user.email})` : ""}`;
    ownerSelect.appendChild(option);
  });

  planSelect.innerHTML = '<option value="">Selecione o plano</option>';
  plans.filter(plan => plan.active === undefined || Number(plan.active) === 1).forEach(plan => {
    const option = document.createElement("option");
    option.value = plan.id;
    option.textContent = `${plan.name}${plan.price !== undefined ? ` — ${new Intl.NumberFormat("pt-BR", { style: "currency", currency: "BRL" }).format(Number(plan.price))}` : ""}`;
    planSelect.appendChild(option);
  });
}

function setFormValues(company) {
  form.elements.name.value = company.name || "";
  form.elements.cnpj.value = company.cnpj || "";
  form.elements.email.value = company.email || "";
  ownerSelect.value = String(company.owner_id ?? "");
  planSelect.value = String(company.plan_id ?? "");
}

async function loadCompany() {
  if (!isEditing) return;
  if (!/^\d+$/.test(companyId)) throw new Error("Identificador de empresa inválido.");

  const data = unwrap(await companyService.listById(companyId), "Não foi possível carregar a empresa.");
  originalCompany = Array.isArray(data) ? data[0] : data;
  if (!originalCompany) throw new Error("Empresa não encontrada.");
  setFormValues(originalCompany);
}

function getPayload() {
  return {
    name: String(form.elements.name.value || "").trim(),
    cnpj: String(form.elements.cnpj.value || "").trim(),
    email: String(form.elements.email.value || "").trim(),
    owner_id: Number(ownerSelect.value),
    plan_id: Number(planSelect.value)
  };
}

form?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!form.reportValidity()) return;

  const payload = getPayload();
  if (!payload.owner_id || !payload.plan_id) {
    toastPrincipal.warning({ message: "Selecione um responsável e um plano." });
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = isEditing ? "Salvando..." : "Cadastrando...";

  try {
    let response;
    if (isEditing) {
      const unchanged = originalCompany && ["name", "cnpj", "email"].every(key => String(originalCompany[key] || "") === payload[key])
        && Number(originalCompany.owner_id) === payload.owner_id
        && Number(originalCompany.plan_id) === payload.plan_id;
      if (unchanged) {
        toastPrincipal.success({ message: "Nenhuma alteração necessária." });
        return;
      }
      response = await companyService.update(companyId, payload);
    } else {
      response = await companyService.insert(payload);
    }

    unwrap(response, "Não foi possível salvar a empresa.");
    toastPrincipal.success({ message: response.message || (isEditing ? "Empresa atualizada com sucesso." : "Empresa cadastrada com sucesso.") });
    window.setTimeout(() => { window.location.href = "empresas.html"; }, 900);
  } catch (error) {
    console.error("Erro ao salvar empresa:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível salvar a empresa." });
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = isEditing ? "Salvar alterações" : "Cadastrar empresa";
  }
});

async function initialize() {
  try {
    await loadSelects();
    await loadCompany();
  } catch (error) {
    console.error("Erro ao inicializar formulário de empresa:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível carregar o formulário da empresa." });
    if (submitButton) submitButton.disabled = true;
  }
}

initialize();

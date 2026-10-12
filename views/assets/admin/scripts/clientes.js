import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const userService = new User();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-filter-form]");
const tableBody = document.querySelector("[data-client-table]");
const summary = document.querySelector("[data-client-summary]");
const clientModal = document.querySelector("[data-client-modal]");
const clientForm = document.querySelector("[data-client-form]");
const openClientForm = document.querySelector("[data-open-client-form]");
const closeClientButtons = document.querySelectorAll("[data-close-client-form]");
const saveClientButton = clientForm?.querySelector("button[type='submit']");
let clients = [];

if (token) userService.setAuthToken(token);

function unwrapUsers(response) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || "Não foi possível carregar os clientes.");
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

function isActive(user) {
  return Number(user.active) === 1;
}

function getCpf(user) {
  return user.cpf || user.CPF || user.document || "Não informado";
}

function renderClients(list) {
  if (!tableBody) return;

  if (list.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem">Nenhum cliente encontrado.</td></tr>';
  } else {
    tableBody.innerHTML = list.map(user => `
      <tr>
        <td>${escapeHtml(user.name || "—")}</td>
        <td>${escapeHtml(getCpf(user))}</td>
        <td>${escapeHtml(user.email || "—")}</td>
        <td><span class="pill ${isActive(user) ? "ativo" : "inativo"}">${isActive(user) ? "Ativo" : "Inativo"}</span></td>
        <td>${isActive(user) ? `<span class="row-actions"><button type="button" class="danger" data-client-delete="${escapeHtml(user.id)}">Excluir</button></span>` : "—"}</td>
      </tr>
    `).join("");
  }

  if (summary) {
    summary.textContent = `Mostrando ${list.length} ${list.length === 1 ? "cliente" : "clientes"}`;
  }
}

function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  const status = String(filterForm?.elements.status?.value || "");
  const filtered = clients.filter(user => {
    const matchesQuery = !query || [user.name, user.email, user.cpf]
      .some(value => String(value || "").toLocaleLowerCase("pt-BR").includes(query));
    const matchesStatus = !status || (status === "ativo") === isActive(user);
    return matchesQuery && matchesStatus;
  });

  renderClients(filtered);
}

async function loadClients() {
  if (!tableBody) return;

  try {
    const users = unwrapUsers(await userService.listAll());
    clients = users.filter(user => Number(user.typeId ?? user.type_id) === 3);
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar clientes:", error);
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem">Não foi possível carregar os clientes.</td></tr>';
    if (summary) summary.textContent = "Falha ao carregar clientes";
    toastPrincipal.error({ message: error.message || "Não foi possível carregar os clientes." });
  }
}

filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
filterForm?.addEventListener("change", applyFilters);
tableBody?.addEventListener("click", async event => {
  const button = event.target.closest("[data-client-delete]");
  if (!button || !window.confirm("Deseja desativar este cliente?")) return;
  try {
    const result = await userService.remove(Number(button.dataset.clientDelete));
    if (result?.type === "error" || result?.code >= 400 || result?.status === "error") {
      throw new Error(result.message || "Não foi possível excluir o cliente.");
    }
    toastPrincipal.success({ message: result.message || "Cliente desativado com sucesso." });
    await loadClients();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir cliente." });
  }
});

openClientForm?.addEventListener("click", () => clientModal?.classList.add("is-open"));
closeClientButtons.forEach(button => button.addEventListener("click", () => clientModal?.classList.remove("is-open")));
clientModal?.addEventListener("click", event => {
  if (event.target === clientModal) clientModal.classList.remove("is-open");
});

clientForm?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!clientForm.reportValidity()) return;
  const payload = Object.fromEntries(new FormData(clientForm).entries());
  payload.name = String(payload.name || "").trim();
  payload.email = String(payload.email || "").trim();
  payload.cpf = String(payload.cpf || "").trim();
  if (payload.password.length < 6) {
    toastPrincipal.warning({ message: "A senha inicial deve ter pelo menos 6 caracteres." });
    return;
  }

  saveClientButton.disabled = true;
  saveClientButton.textContent = "Cadastrando...";
  try {
    const response = await userService.post("/users/register", payload);
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
      throw new Error(response.message || "Não foi possível cadastrar o cliente.");
    }
    toastPrincipal.success({ message: response.message || "Cliente cadastrado com sucesso." });
    clientForm.reset();
    clientModal.classList.remove("is-open");
    await loadClients();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao cadastrar cliente." });
  } finally {
    saveClientButton.disabled = false;
    saveClientButton.textContent = "Cadastrar cliente";
  }
});

loadClients();
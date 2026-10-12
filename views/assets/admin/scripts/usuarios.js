import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const userService = new User();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-filter-form]");
const tableBody = document.querySelector(".data tbody");
let currentUserId = null;
let users = [];

try {
  currentUserId = Number(JSON.parse(localStorage.getItem("user") || "null")?.id) || null;
} catch (error) {
  console.warn("Não foi possível ler o usuário da sessão:", error);
}

if (token) userService.setAuthToken(token);

function unwrapUsers(response) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || "Não foi possível carregar os usuários.");
  }
  if (Array.isArray(response)) return response;
  return Array.isArray(response?.data) ? response.data : [];
}

function getTypeId(user) {
  return Number(user.typeId ?? user.type_id ?? user.typeid);
}

function getProfile(user) {
  const profiles = {
    1: { label: "Administrador", className: "admin" },
    2: { label: "Técnico", className: "tecnico" },
    3: { label: "Cliente", className: "cliente" }
  };
  return profiles[getTypeId(user)] || { label: "Usuário", className: "" };
}

function isActive(user) {
  return Number(user.active) === 1;
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#039;");
}

function renderKpis() {
  document.querySelector("[data-users-total]").textContent = users.length;
  document.querySelector("[data-users-admin]").textContent = users.filter(user => getTypeId(user) === 1).length;
  document.querySelector("[data-users-staff]").textContent = users.filter(user => getTypeId(user) === 2).length;
  document.querySelector("[data-users-clients]").textContent = users.filter(user => getTypeId(user) === 3).length;
}

function renderUsers(list) {
  if (!tableBody) return;

  if (list.length === 0) {
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem">Nenhum usuário encontrado.</td></tr>';
    return;
  }

  tableBody.innerHTML = list.map(user => {
    const profile = getProfile(user);
    const status = isActive(user);
    const firstLetter = Array.from(String(user.name || "?").trim())[0]?.toLocaleUpperCase("pt-BR") || "?";

    return `
      <tr>
        <td><section style="display:flex;align-items:center;gap:0.6rem"><span class="avatar" style="width:2rem;height:2rem;font-size:0.8rem;flex-shrink:0">${escapeHtml(firstLetter)}</span>${escapeHtml(user.name || "—")}</section></td>
        <td>${escapeHtml(user.email || "—")}</td>
        <td><span class="pill ${escapeHtml(profile.className)}">${escapeHtml(profile.label)}</span></td>
        <td><span class="pill ${status ? "ativo" : "inativo"}">${status ? "Ativo" : "Inativo"}</span></td>
        <td>${status && Number(user.id) !== currentUserId ? `<span class="row-actions"><button type="button" class="danger" data-user-delete="${escapeHtml(user.id)}">Excluir</button></span>` : "—"}</td>
      </tr>
    `;
  }).join("");
}

function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  const profile = String(filterForm?.elements.perfil?.value || "");
  const status = String(filterForm?.elements.status?.value || "");

  const filtered = users.filter(user => {
    const matchesQuery = !query || [user.name, user.email]
      .some(value => String(value || "").toLocaleLowerCase("pt-BR").includes(query));
    const matchesProfile = !profile || (profile === "admin" ? getTypeId(user) === 1 : profile === "tecnico" ? getTypeId(user) === 2 : getTypeId(user) === 3);
    const matchesStatus = !status || (status === "ativo") === isActive(user);
    return matchesQuery && matchesProfile && matchesStatus;
  });

  renderUsers(filtered);
}

async function loadUsers() {
  if (!tableBody) return;

  try {
    users = unwrapUsers(await userService.listAll());
    renderKpis();
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar usuários:", error);
    tableBody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:2rem">Não foi possível carregar os usuários.</td></tr>';
    toastPrincipal.error({ message: error.message || "Não foi possível carregar os usuários." });
  }
}

filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
filterForm?.addEventListener("change", applyFilters);
tableBody?.addEventListener("click", async event => {
  const button = event.target.closest("[data-user-delete]");
  if (!button || !window.confirm("Deseja desativar este usuário?")) return;
  try {
    const result = await userService.remove(Number(button.dataset.userDelete));
    if (result?.type === "error" || result?.code >= 400 || result?.status === "error") {
      throw new Error(result.message || "Não foi possível excluir o usuário.");
    }
    toastPrincipal.success({ message: result.message || "Usuário desativado com sucesso." });
    await loadUsers();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir usuário." });
  }
});

loadUsers();
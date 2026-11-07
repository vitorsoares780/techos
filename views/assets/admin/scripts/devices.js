import Device from "../../_common/classes/Device.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const deviceService = new Device();
const token = localStorage.getItem("token");
const filterForm = document.querySelector("[data-device-filter]");
const tableBody = document.querySelector("[data-device-table]");
const summary = document.querySelector("[data-device-summary]");
let devices = [];

if (token) deviceService.setAuthToken(token);

function unwrap(response) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || "Não foi possível carregar os equipamentos.");
  }
  if (Array.isArray(response)) return response;
  return Array.isArray(response?.data) ? response.data : [];
}

function escapeHtml(value) {
  return String(value ?? "")
    .replaceAll("&", "&amp;").replaceAll("<", "&lt;").replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;").replaceAll("'", "&#039;");
}

function render(list) {
  if (!tableBody) return;
  if (!list.length) {
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem">Nenhum equipamento encontrado.</td></tr>';
  } else {
    tableBody.innerHTML = list.map(device => `
      <tr>
        <td>${escapeHtml(device.name || "—")}</td>
        <td>${escapeHtml([device.brand, device.model].filter(Boolean).join(" / ") || "—")}</td>
        <td>${escapeHtml(device.serial_number || "—")}</td>
        <td>${escapeHtml(device.user_name || "—")}</td>
        <td>${escapeHtml(device.category_name || "—")}</td>
        <td><span class="row-actions"><a href="equipamento-detalhe.html?id=${encodeURIComponent(device.id)}">Detalhes</a><button type="button" class="danger" data-device-delete="${escapeHtml(device.id)}">Excluir</button></span></td>
      </tr>
    `).join("");
  }
  if (summary) summary.textContent = `Mostrando ${list.length} ${list.length === 1 ? "equipamento" : "equipamentos"}`;
}

function applyFilters() {
  const query = String(filterForm?.elements.q?.value || "").trim().toLocaleLowerCase("pt-BR");
  render(devices.filter(device => [device.name, device.brand, device.model, device.serial_number, device.user_name, device.category_name]
    .some(value => String(value || "").toLocaleLowerCase("pt-BR").includes(query))));
}

async function loadDevices() {
  if (!tableBody) return;
  try {
    devices = unwrap(await deviceService.listAll());
    applyFilters();
  } catch (error) {
    console.error("Erro ao carregar equipamentos:", error);
    tableBody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:2rem">Não foi possível carregar os equipamentos.</td></tr>';
    if (summary) summary.textContent = "Falha ao carregar equipamentos";
    toastPrincipal.error({ message: error.message || "Erro ao carregar equipamentos." });
  }
}

async function deleteDevice(id) {
  if (!window.confirm("Deseja excluir este equipamento?")) return;
  try {
    const response = await deviceService.remove(id);
    if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
      throw new Error(response.message || "Não foi possível excluir o equipamento.");
    }
    toastPrincipal.success({ message: response.message || "Equipamento excluído com sucesso." });
    await loadDevices();
  } catch (error) {
    toastPrincipal.error({ message: error.message || "Erro ao excluir equipamento." });
  }
}

filterForm?.addEventListener("submit", event => event.preventDefault());
filterForm?.addEventListener("input", applyFilters);
tableBody?.addEventListener("click", event => {
  const button = event.target.closest("[data-device-delete]");
  if (button) deleteDevice(Number(button.dataset.deviceDelete));
});
loadDevices();

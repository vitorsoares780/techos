import Device from "../../_common/classes/Device.js";
import DeviceCategory from "../../_common/classes/DeviceCategory.js";
import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const deviceService = new Device();
const categoryService = new DeviceCategory();
const userService = new User();
const form = document.querySelector("[data-device-form]");
const categorySelect = document.querySelector("[data-device-categories]");
const userSelect = document.querySelector("[data-device-users]");
const submitButton = form?.querySelector("button[type='submit']");
const deviceId = new URLSearchParams(window.location.search).get("id");
const isEditing = Boolean(deviceId);
const token = localStorage.getItem("token");
let originalDevice = null;

if (token) {
  deviceService.setAuthToken(token);
  categoryService.setAuthToken(token);
  userService.setAuthToken(token);
}

function unwrap(response, fallback) {
  if (response?.type === "error" || response?.code >= 400 || response?.status === "error") {
    throw new Error(response.message || fallback);
  }
  return response?.data;
}
function asList(data) { return Array.isArray(data) ? data : data ? [data] : []; }

async function loadOptions() {
  const [categoryResponse, userResponse] = await Promise.all([
    categoryService.listAll(),
    userService.listAll()
  ]);
  const categories = asList(unwrap(categoryResponse, "Não foi possível carregar categorias."));
  const users = asList(unwrap(userResponse, "Não foi possível carregar clientes."));

  categorySelect.innerHTML = '<option value="">Selecione a categoria</option>';
  categories.filter(category => category.active === undefined || Number(category.active) === 1).forEach(category => {
    const option = document.createElement("option");
    option.value = category.id;
    option.textContent = category.name;
    categorySelect.appendChild(option);
  });
  userSelect.innerHTML = '<option value="">Selecione o cliente</option>';
  users.filter(user => Number(user.typeId ?? user.type_id) === 3 && (user.active === undefined || Number(user.active) === 1)).forEach(user => {
    const option = document.createElement("option");
    option.value = user.id;
    option.textContent = user.name;
    userSelect.appendChild(option);
  });

  if (categorySelect.options.length === 1) throw new Error("Não há categorias ativas cadastradas.");
  if (userSelect.options.length === 1) throw new Error("Não há clientes ativos cadastrados.");
}

async function loadDevice() {
  if (!isEditing) return;
  if (!/^\d+$/.test(deviceId)) throw new Error("Identificador de equipamento inválido.");
  const data = unwrap(await deviceService.listById(deviceId), "Não foi possível carregar o equipamento.");
  originalDevice = Array.isArray(data) ? data[0] : data;
  if (!originalDevice) throw new Error("Equipamento não encontrado.");
  form.elements.name.value = originalDevice.name || "";
  form.elements.brand.value = originalDevice.brand || "";
  form.elements.model.value = originalDevice.model || "";
  form.elements.serial_number.value = originalDevice.serial_number || "";
  categorySelect.value = String(originalDevice.category_id ?? "");
  userSelect.value = String(originalDevice.user_id ?? "");
}

function getPayload() {
  const serial = Number(form.elements.serial_number.value);
  return {
    name: String(form.elements.name.value || "").trim(),
    brand: String(form.elements.brand.value || "").trim(),
    model: String(form.elements.model.value || "").trim(),
    serial_number: serial,
    category_id: Number(categorySelect.value),
    user_id: Number(userSelect.value)
  };
}

form?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!form.reportValidity()) return;
  const payload = getPayload();
  if (!payload.category_id || !payload.user_id || !Number.isSafeInteger(payload.serial_number) || payload.serial_number <= 0) {
    toastPrincipal.warning({ message: "Preencha cliente, categoria e número de série válido." });
    return;
  }

  if (isEditing && originalDevice && Object.keys(payload).every(key => String(originalDevice[key] ?? "") === String(payload[key] ?? ""))) {
    toastPrincipal.success({ message: "Nenhuma alteração necessária." });
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = isEditing ? "Salvando..." : "Cadastrando...";
  try {
    const response = isEditing
      ? await deviceService.update(deviceId, { ...payload, deviceId: Number(deviceId) })
      : await deviceService.insert(payload);
    unwrap(response, "Não foi possível salvar o equipamento.");
    toastPrincipal.success({ message: response.message || (isEditing ? "Equipamento atualizado com sucesso." : "Equipamento cadastrado com sucesso.") });
    window.setTimeout(() => { window.location.href = "equipamentos.html"; }, 900);
  } catch (error) {
    console.error("Erro ao salvar equipamento:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível salvar o equipamento." });
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = isEditing ? "Salvar alterações" : "Cadastrar equipamento";
  }
});

async function initialize() {
  try {
    await loadOptions();
    await loadDevice();
  } catch (error) {
    console.error("Erro ao inicializar equipamento:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível carregar o formulário." });
    if (submitButton) submitButton.disabled = true;
  }
}
initialize();

import Company from "../../_common/classes/Company.js";
import Device from "../../_common/classes/Device.js";
import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const companyService = new Company();
const deviceService = new Device();
const serviceOrderService = new ServiceOrder();
const userService = new User();
const form = document.querySelector("[data-admin-order-form]");
const clientSelect = document.querySelector("[data-order-client]");
const companySelect = document.querySelector("[data-order-company]");
const deviceSelect = document.querySelector("[data-order-device]");
const photoInput = document.querySelector("[data-order-photo]");
const submitButton = form?.querySelector("button[type='submit']");
const token = localStorage.getItem("token");
let clients = [];
let devices = [];

if (token) {
  [companyService, deviceService, serviceOrderService, userService].forEach(service => service.setAuthToken(token));
}

function unwrap(response, fallback) {
  if (response?.type === "error" || response?.status === "error" || response?.code >= 400) {
    throw new Error(response.message || fallback);
  }
  if (!Array.isArray(response?.data)) {
    throw new Error("A API retornou uma lista inválida.");
  }
  return response.data;
}

function appendOptions(select, items, label, getLabel) {
  select.replaceChildren(new Option(label, ""));
  items.forEach(item => select.add(new Option(getLabel(item), String(item.id))));
}

async function loadOptions() {
  const [userResponse, deviceResponse, companyResponse] = await Promise.all([
    userService.listAll(),
    deviceService.listAll(),
    companyService.listAll()
  ]);
  clients = unwrap(userResponse, "Não foi possível carregar os clientes.")
    .filter(user => Number(user.typeId ?? user.type_id) === 3 && (user.active === undefined || Number(user.active) === 1));
  devices = unwrap(deviceResponse, "Não foi possível carregar os aparelhos.")
    .filter(device => device.active === undefined || Number(device.active) === 1);
  const companies = unwrap(companyResponse, "Não foi possível carregar as empresas.")
    .filter(company => company.active === undefined || Number(company.active) === 1);

  appendOptions(clientSelect, clients, "Selecione o cliente", user => user.name);
  appendOptions(companySelect, companies, "Selecione a empresa", company => company.name);
  updateDeviceOptions();

  if (!clients.length || !companies.length) {
    throw new Error("Cadastre ao menos um cliente e uma empresa ativa antes de criar a OS.");
  }
}

function updateDeviceOptions() {
  const clientId = clientSelect.value;
  const clientDevices = devices.filter(device => String(device.user_id) === clientId);
  appendOptions(
    deviceSelect,
    clientDevices,
    clientId ? "Selecione o aparelho" : "Selecione primeiro um cliente",
    device => [device.name, device.brand, device.model].filter(Boolean).join(" ") || `Aparelho #${device.id}`
  );
  deviceSelect.disabled = !clientId || !clientDevices.length;
  if (clientId && !clientDevices.length) {
    deviceSelect.replaceChildren(new Option("Nenhum aparelho ativo deste cliente", ""));
  }
}

clientSelect?.addEventListener("change", updateDeviceOptions);

form?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!form.reportValidity()) return;

  const photo = photoInput.files?.[0] || null;
  if (photo && (!["image/jpeg", "image/png", "image/webp"].includes(photo.type) || photo.size > 5 * 1024 * 1024)) {
    toastPrincipal.warning({ message: "Selecione uma imagem JPEG, PNG ou WebP de até 5 MB." });
    photoInput.focus();
    return;
  }

  const payload = {
    user_id: Number(clientSelect.value),
    device_id: Number(deviceSelect.value),
    company_id: Number(companySelect.value),
    defect: String(form.elements.defect.value).trim(),
    diagnosis: String(form.elements.diagnosis.value).trim(),
    price: Number(form.elements.price.value)
  };
  if (!clients.some(client => Number(client.id) === payload.user_id) ||
      !devices.some(device => Number(device.id) === payload.device_id && Number(device.user_id) === payload.user_id) ||
      !Number.isFinite(payload.price) || payload.price <= 0) {
    toastPrincipal.warning({ message: "Confira cliente, aparelho e valor da ordem." });
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = "Cadastrando...";
  try {
    const response = await serviceOrderService.insert(payload);
    if (response?.type === "error" || response?.status === "error" || response?.code >= 400) {
      throw new Error(response.message || "Não foi possível cadastrar a ordem.");
    }

    const order = Array.isArray(response?.data) ? response.data[0] : response?.data;
    if (!order?.id) throw new Error("A API não retornou o identificador da ordem cadastrada.");

    let photoUploadError = null;
    if (photo) {
      try {
        const uploadResponse = await serviceOrderService.uploadImage(order.id, photo);
        if (uploadResponse?.type === "error" || uploadResponse?.status === "error" || uploadResponse?.code >= 400) {
          throw new Error(uploadResponse.message || "Não foi possível salvar a foto.");
        }
      } catch (error) {
        photoUploadError = error;
      }
    }

    if (photoUploadError) {
      toastPrincipal.warning({
        message: `A OS foi criada, mas a foto não foi salva: ${photoUploadError.message}`
      });
    } else {
      toastPrincipal.success({ message: response.message || "Ordem de serviço criada com sucesso." });
    }

    window.setTimeout(() => {
      window.location.href = `ordem-detalhe.html?id=${encodeURIComponent(order.id)}`;
    }, photoUploadError ? 1800 : 1000);
  } catch (error) {
    console.error("Erro ao cadastrar ordem:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível cadastrar a ordem." });
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = "Cadastrar ordem";
  }
});

loadOptions().catch(error => {
  console.error("Erro ao carregar opções de criação da OS:", error);
  toastPrincipal.error({ message: error.message || "Não foi possível carregar os dados para a OS." });
  if (submitButton) submitButton.disabled = true;
});

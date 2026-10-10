import Device from "../../_common/classes/Device.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const device = new Device();
const formDevice = document.querySelector("[data-device-form]");
const formOs = document.querySelector("[data-os-form]");
const categorySelect = formDevice?.elements.category_id;
const deviceSelect = formOs?.elements.device_id;
const feedback = document.querySelector("[data-device-feedback]");
const submitButton = formDevice?.querySelector("button[type='submit']");
const token = localStorage.getItem("token");

if (token) {
    device.setAuthToken(token);
}

function unwrapData(response) {
    if (response?.type === "error" || response?.code >= 400) {
        throw new Error(response.message || "Não foi possível concluir a solicitação.");
    }

    return response?.data;
}

function getUserId() {
    const userData = JSON.parse(localStorage.getItem("user") || "null");
    return userData?.id || localStorage.getItem("userId");
}

function appendDeviceOption(deviceData) {
    if (!deviceSelect || !deviceData?.id) return;

    const existingOption = Array.from(deviceSelect.options)
        .find(option => option.value === String(deviceData.id));
    if (existingOption) return existingOption;

    const option = document.createElement("option");
    option.value = deviceData.id;
    option.textContent = [deviceData.name, deviceData.brand, deviceData.model]
        .filter(Boolean)
        .join(" ") || `Aparelho #${deviceData.id}`;
    deviceSelect.appendChild(option);
    return option;
}

async function loadDevices() {
    if (!deviceSelect) return;

    deviceSelect.innerHTML = '<option value="">Carregando aparelhos...</option>';

    try {
        const response = await device.listAll();
        const devices = unwrapData(response);

        if (!Array.isArray(devices)) {
            throw new Error("A resposta da API não contém uma lista de aparelhos.");
        }

        const pendingDevices = Array.from(deviceSelect.options)
            .filter(option => option.value)
            .map(option => ({ id: option.value, name: option.textContent }));

        deviceSelect.innerHTML = '<option value="">Selecione...</option>';
        devices
            .filter(item => item.active === undefined || Number(item.active) === 1)
            .forEach(appendDeviceOption);
        pendingDevices.forEach(appendDeviceOption);

        if (deviceSelect.options.length === 1) {
            deviceSelect.innerHTML = '<option value="">Nenhum aparelho ativo encontrado</option>';
        }
    } catch (error) {
        console.error("Erro ao carregar aparelhos:", error);
        deviceSelect.innerHTML = '<option value="">Aparelhos indisponíveis</option>';
        toastPrincipal.error({
            message: error.message || "Não foi possível carregar os aparelhos ativos."
        });
    }
}

async function loadCategories() {
    if (!categorySelect) return;

    try {
        const response = await device.get("/devices-categories/list");
        const categories = unwrapData(response);

        if (!Array.isArray(categories) || categories.length === 0) {
            throw new Error("Nenhuma categoria de aparelho está cadastrada.");
        }

        categorySelect.innerHTML = '<option value="">Selecione...</option>';
        categories.forEach(category => {
            const option = document.createElement("option");
            option.value = category.id;
            option.textContent = category.name;
            categorySelect.appendChild(option);
        });
    } catch (error) {
        console.error("Erro ao carregar categorias de aparelhos:", error);
        categorySelect.innerHTML = '<option value="">Categorias indisponíveis</option>';
        toastPrincipal.error({
            message: error.message || "Não foi possível carregar as categorias."
        });
    }
}

async function handleDeviceSubmit(event) {
    event.preventDefault();

    const userId = getUserId();
    if (!userId) {
        toastPrincipal.error({ message: "Usuário logado não encontrado." });
        return;
    }

    const data = new FormData(formDevice);
    const deviceName = [data.get("tipo"), data.get("marca"), data.get("modelo")]
        .map(value => String(value || "").trim())
        .filter(Boolean)
        .join(" ");
    const payload = {
        user_id: Number(userId),
        category_id: Number(data.get("category_id")),
        serial_number: String(data.get("serial") || "").trim(),
        name: deviceName
    };

    if (!payload.category_id || !payload.serial_number || !deviceName) {
        toastPrincipal.warning({ message: "Preencha os dados do aparelho e selecione uma categoria." });
        return;
    }

    submitButton.disabled = true;
    submitButton.textContent = "Cadastrando...";

    try {
        const response = await device.post("/devices", payload);
        const result = unwrapData(response);
        const createdDevice = Array.isArray(result) ? result[0] : result;
        const deviceId = createdDevice?.id;

        if (!deviceId) {
            throw new Error("A API não retornou o ID do aparelho cadastrado.");
        }

        appendDeviceOption(createdDevice);
        deviceSelect.value = String(deviceId);
        formDevice.reset();
        if (feedback) feedback.textContent = "";
        toastPrincipal.success({ message: response.message || "Aparelho cadastrado com sucesso." });
    } catch (error) {
        console.error("Erro ao cadastrar aparelho:", error);
        toastPrincipal.error({
            message: error.message || "Erro ao cadastrar aparelho."
        });
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = "Cadastrar aparelho";
    }
}

formDevice?.addEventListener("submit", handleDeviceSubmit);
loadCategories();
loadDevices();
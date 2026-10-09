import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

const serviceOrder = new ServiceOrder();

const token = localStorage.getItem("token");
if (token) {
    serviceOrder.setAuthToken(token);
}

const formDevice = document.querySelector("[data-device-form]");
const formOs = document.querySelector("[data-os-form]");
const submitButtonDevice = formDevice?.querySelector("button[type='submit']");
const submitButtonOs = formOs?.querySelector("button[type='submit']");

async function handleSubmit(event) {
    event.preventDefault();

    loginFormController.init(formOs);

    const validation = loginFormController.validateRequired(["defeito"]);

    if (!validation.valid) {
        toastPrincipal.warning({
            message: "Informe o defeito do equipamento."
        });
        formOs.elements[validation.field]?.focus();
        return;
    }

    const userData = JSON.parse(localStorage.getItem("user") || "null");
    const userId = userData?.id || localStorage.getItem("userId");

    if (!userId) {
        toastPrincipal.error({
            message: "Usuário logado não encontrado."
        });
        return;
    }

    const data = loginFormController.getData();

    const payload = {
        user_id: Number(userId),
        device_id: 1,
        defect: String(data.defeito).trim(),
        diagnosis: String(data.diagnostico).trim(),
        status: "",
        price: Number(data.preco)
    };

    submitButtonOs.disabled = true;
    submitButtonOs.textContent = "Enviando...";

    try {
        const response = await serviceOrder.insert(payload);
        console.log(response)

        toastPrincipal.success(
            response?.message
                ? response
                : { message: "Ordem de serviço criada com sucesso!" }
        );

        formOs.reset();

        setTimeout(() => {
            window.location.href = "ordens.html";
        }, 1200);
    } catch (error) {
        console.error("Erro ao criar ordem:", error);
        toastPrincipal.error({
            message: error.message || "Erro ao criar a ordem de serviço."
        });
    } finally {
        submitButtonOs.disabled = false;
        submitButtonOs.textContent = "Solicitar ordem de serviço";
    }
}

if (formOs) {
    formOs.addEventListener("submit", handleSubmit);
}

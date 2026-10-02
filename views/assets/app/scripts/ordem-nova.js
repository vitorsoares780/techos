import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

const serviceOrder = new ServiceOrder();

const token = localStorage.getItem("token");
if (token) {
    serviceOrder.setAuthToken(token);
}

const form = document.querySelector("[data-os-form]");
const submitButton = form?.querySelector("button[type='submit']");

async function handleSubmit(event) {
    event.preventDefault();

    loginFormController.init(form);

    const validation = loginFormController.validateRequired(["defeito"]);

    if (!validation.valid) {
        toastPrincipal.warning({
            message: "Informe o defeito do equipamento."
        });
        form.elements[validation.field]?.focus();
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
        defect: String(data.defeito).trim(),
        status: "aguardando"
    };

    submitButton.disabled = true;
    submitButton.textContent = "Enviando...";

    try {
        const response = await serviceOrder.insert(payload);

        toastPrincipal.success(
            response?.message
                ? response
                : { message: "Ordem de serviço criada com sucesso!" }
        );

        form.reset();

        setTimeout(() => {
            window.location.href = "ordens.html";
        }, 1200);
    } catch (error) {
        console.error("Erro ao criar ordem:", error);
        toastPrincipal.error({
            message: error.message || "Erro ao criar a ordem de serviço."
        });
    } finally {
        submitButton.disabled = false;
        submitButton.textContent = "Solicitar ordem de serviço";
    }
}

if (form) {
    form.addEventListener("submit", handleSubmit);
}

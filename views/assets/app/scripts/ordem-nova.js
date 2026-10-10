import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

const serviceOrder = new ServiceOrder();

const token = localStorage.getItem("token");
if (token) {
    serviceOrder.setAuthToken(token);
}

const formOs = document.querySelector("[data-os-form]");
const submitButtonOs = formOs?.querySelector("button[type='submit']");
const deviceSelect = formOs?.elements.device_id;

function assertSuccess(response) {
    if (response?.type === "error" || response?.code >= 400) {
        throw new Error(response.message || "Não foi possível criar a ordem de serviço.");
    }
}

async function handleSubmit(event) {
    event.preventDefault();

    loginFormController.init(formOs);

    const validation = loginFormController.validateRequired([
        "device_id",
        "defeito",
        "diagnostico",
        "preco"
    ]);

    if (!validation.valid) {
        toastPrincipal.warning({
            message: "Selecione o aparelho e preencha os dados do serviço."
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
        device_id: Number(data.device_id),
        company_id: 1, //ALTERAR FUTURAMENTE
        defect: String(data.defeito).trim(),
        diagnosis: String(data.diagnostico).trim(),
        status: "aberta",
        price: Number(data.preco),
        photo: formOs.elements.fotos.files[0]?.name || "sem-foto"
    };

    if (!Number.isFinite(payload.price) || payload.price <= 0) {
        toastPrincipal.warning({ message: "Informe um preço maior que zero." });
        formOs.elements.preco.focus();
        return;
    }

    submitButtonOs.disabled = true;
    submitButtonOs.textContent = "Enviando...";

    try {
        console.log(payload)
        const response = await serviceOrder.insert(payload);
        assertSuccess(response);

        toastPrincipal.success({
            message: response?.message || "Ordem de serviço criada com sucesso!"
        });

        formOs.reset();
        if (deviceSelect) deviceSelect.value = "";

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

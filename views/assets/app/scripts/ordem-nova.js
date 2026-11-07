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
    if (response?.type === "error" || response?.status === "error" || response?.code >= 400) {
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
    const [photo] = formOs.elements.photo.files || [];
    if (photo && (!["image/jpeg", "image/png", "image/webp"].includes(photo.type) || photo.size > 5 * 1024 * 1024)) {
        toastPrincipal.warning({ message: "Selecione uma imagem JPEG, PNG ou WebP de até 5 MB." });
        formOs.elements.photo.focus();
        return;
    }

    const payload = {
        user_id: Number(userId),
        device_id: Number(data.device_id),
        company_id: 1, //ALTERAR FUTURAMENTE
        defect: String(data.defeito).trim(),
        diagnosis: String(data.diagnostico).trim(),
        status: "aberta",
        price: Number(data.preco)
    };

    if (!Number.isFinite(payload.price) || payload.price <= 0) {
        toastPrincipal.warning({ message: "Informe um preço maior que zero." });
        formOs.elements.preco.focus();
        return;
    }

    submitButtonOs.disabled = true;
    submitButtonOs.textContent = "Enviando...";

    try {
        const response = await serviceOrder.insert(payload);
        assertSuccess(response);

        const createdOrder = Array.isArray(response?.data) ? response.data[0] : response?.data;
        const orderId = createdOrder?.id;
        if (!orderId) {
            throw new Error("A ordem foi enviada, mas a API não retornou seu identificador.");
        }

        let photoUploadError = null;
        if (photo) {
            try {
                const imageResponse = await serviceOrder.uploadImage(orderId, photo);
                assertSuccess(imageResponse);
            } catch (error) {
                photoUploadError = error;
            }
        }

        if (photoUploadError) {
            toastPrincipal.warning({
                message: `A OS foi criada, mas a foto não foi salva: ${photoUploadError.message}`
            });
        } else {
            toastPrincipal.success({
                message: response?.message || "Ordem de serviço criada com sucesso!"
            });
        }

        formOs.reset();
        if (deviceSelect) deviceSelect.value = "";

        window.setTimeout(() => {
            window.location.href = `ordem-detalhe.html?id=${encodeURIComponent(orderId)}`;
        }, photoUploadError ? 1800 : 1200);
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

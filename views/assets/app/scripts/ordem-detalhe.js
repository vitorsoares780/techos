import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const serviceOrder = new ServiceOrder();
const token = localStorage.getItem("token");
const params = new URLSearchParams(window.location.search);
const orderId = params.get("id");
const imageInput = document.querySelector("[data-order-image-input]");
const imageUploadButton = document.querySelector("[data-order-image-upload]");
const imageStatus = document.querySelector("[data-order-image-status]");
const imageList = document.querySelector("[data-order-image-list]");

if (token) {
    serviceOrder.setAuthToken(token);
}

function getStatusClass(status) {
    const normalized = String(status || "").toLowerCase().replaceAll("_", " ");
    const classes = {
        aberta: "aberta",
        aberto: "aberta",
        andamento: "andamento",
        "em andamento": "andamento",
        aguardando: "aguardando",
        "aguardando peca": "aguardando",
        "aguardando peça": "aguardando",
        concluida: "concluida",
        "concluída": "concluida",
        cancelada: "cancelada"
    };

    return classes[normalized] || "aguardando";
}

function getStatusLabel(status) {
    const labels = {
        aberta: "Aberta",
        aberto: "Aberta",
        andamento: "Em andamento",
        "em andamento": "Em andamento",
        aguardando: "Aguardando peça",
        "aguardando peca": "Aguardando peça",
        "aguardando peça": "Aguardando peça",
        concluida: "Concluída",
        "concluída": "Concluída",
        cancelada: "Cancelada"
    };
    const normalized = String(status || "").toLowerCase().replaceAll("_", " ");

    return labels[normalized] || status || "Aguardando";
}

function formatDate(value) {
    if (!value) return "—";

    const date = new Date(value);
    return Number.isNaN(date.getTime()) ? "—" : date.toLocaleString("pt-BR");
}

function formatPrice(value) {
    const price = Number(value);
    if (!Number.isFinite(price)) return "—";

    return new Intl.NumberFormat("pt-BR", {
        style: "currency",
        currency: "BRL"
    }).format(price);
}

function renderOrder(order) {
    const statusLabel = getStatusLabel(order.status);
    const deviceName = params.get("device") || order.device_name || order.device_model || `Dispositivo #${order.device_id ?? "—"}`;
    const date = formatDate(order.creation_time || order.created_at);
    const statusElement = document.querySelector("[data-detail-status]");

    document.querySelector("[data-detail-title]").textContent = `OS #${order.id} · ${deviceName}`;
    document.querySelector("[data-detail-meta]").textContent = `Aberta em ${date} · ${statusLabel}`;
    document.querySelector("[data-detail-defect]").textContent = order.defect || "—";
    document.querySelector("[data-detail-diagnosis]").textContent = order.diagnosis || "—";
    document.querySelector("[data-detail-price]").textContent = formatPrice(order.price);
    document.querySelector("[data-detail-device]").textContent = deviceName;
    statusElement.className = `pill ${getStatusClass(order.status)}`;
    statusElement.textContent = statusLabel;
}

function assertSuccess(response, fallbackMessage) {
    if (response?.type === "error" || response?.status === "error" || response?.code >= 400) {
        throw new Error(response.message || fallbackMessage);
    }
}

function renderImages(images) {
    if (!imageList) return;
    imageList.replaceChildren();

    if (!images.length) {
        const emptyState = document.createElement("li");
        emptyState.textContent = "Esta ordem ainda não possui imagens.";
        imageList.append(emptyState);
        return;
    }

    images.forEach(image => {
        try {
            const imageUrl = new URL(image.image_url, window.location.href);
            if (!["http:", "https:"].includes(imageUrl.protocol) || imageUrl.origin !== window.location.origin) {
                throw new Error("Endereço de imagem fora da origem da aplicação.");
            }

            const item = document.createElement("li");
            const preview = document.createElement("img");
            preview.src = imageUrl.href;
            preview.alt = image.alt_text || "Imagem da ordem de serviço";
            preview.loading = "lazy";
            preview.width = 120;
            preview.height = 90;
            item.append(preview);
            imageList.append(item);
        } catch (error) {
            console.error("Endereço de imagem inválido recebido da API:", error);
        }
    });
}

async function loadImages() {
    if (!orderId || !/^\d+$/.test(orderId) || !token) {
        if (imageStatus) imageStatus.textContent = "Acesse uma ordem válida com uma sessão autenticada.";
        return;
    }

    if (imageStatus) imageStatus.textContent = "Carregando imagens...";
    try {
        const response = await serviceOrder.listImages(orderId);
        assertSuccess(response, "Não foi possível carregar as imagens.");
        if (!Array.isArray(response.data)) {
            throw new Error("A resposta da API não contém a lista de imagens.");
        }
        renderImages(response.data);
        if (imageStatus) imageStatus.textContent = "Imagens da ordem atualizadas.";
    } catch (error) {
        console.error("Erro ao carregar imagens da ordem:", error);
        if (imageStatus) imageStatus.textContent = "Não foi possível carregar as imagens.";
        toastPrincipal.error({ message: error.message || "Erro ao carregar as imagens." });
    }
}

imageUploadButton?.addEventListener("click", async () => {
    if (!orderId || !/^\d+$/.test(orderId)) {
        toastPrincipal.error({ message: "ID da ordem de serviço inválido." });
        return;
    }

    const files = Array.from(imageInput?.files || []);
    if (!files.length) {
        toastPrincipal.warning({ message: "Selecione ao menos uma foto." });
        return;
    }

    if (files.some(file => !["image/jpeg", "image/png", "image/webp"].includes(file.type) || file.size > 5 * 1024 * 1024)) {
        toastPrincipal.warning({ message: "Use somente imagens JPEG, PNG ou WebP de até 5 MB." });
        return;
    }

    imageUploadButton.disabled = true;
    let uploaded = 0;
    try {
        for (const file of files) {
            if (imageStatus) imageStatus.textContent = `Enviando ${uploaded + 1} de ${files.length}...`;
            const response = await serviceOrder.uploadImage(orderId, file);
            assertSuccess(response, "Não foi possível adicionar uma das fotos.");
            uploaded += 1;
        }
        imageInput.value = "";
        toastPrincipal.success({ message: `${uploaded} foto(s) adicionada(s) à ordem.` });
    } catch (error) {
        if (uploaded) imageInput.value = "";
        toastPrincipal.error({
            message: uploaded
                ? `${error.message} ${uploaded} foto(s) foram adicionadas; a galeria foi atualizada.`
                : error.message || "Erro ao adicionar fotos."
        });
    } finally {
        imageUploadButton.disabled = false;
        await loadImages();
    }
});

async function loadOrder() {
    if (!orderId || !/^\d+$/.test(orderId)) {
        toastPrincipal.error({ message: "ID da ordem de serviço inválido." });
        document.querySelector("[data-detail-title]").textContent = "Ordem não encontrada";
        return;
    }

    try {
        const response = await serviceOrder.listById(orderId);
        assertSuccess(response, "Não foi possível carregar a ordem de serviço.");

        const order = Array.isArray(response?.data) ? response.data[0] : response?.data;
        if (!order) {
            throw new Error("Ordem de serviço não encontrada.");
        }

        renderOrder(order);
    } catch (error) {
        console.error("Erro ao carregar ordem de serviço:", error);
        document.querySelector("[data-detail-title]").textContent = "Não foi possível carregar a ordem";
        document.querySelector("[data-detail-meta]").textContent = "Verifique o acesso e tente novamente.";
        toastPrincipal.error({
            message: error.message || "Erro ao carregar a ordem de serviço."
        });
    }
}

loadOrder();
loadImages();
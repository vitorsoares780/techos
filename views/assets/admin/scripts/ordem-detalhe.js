import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const serviceOrder = new ServiceOrder();
const token = localStorage.getItem("token");
const orderId = new URLSearchParams(window.location.search).get("id");
const updateForm = document.querySelector("[data-order-update-form]");
const imageInput = document.querySelector("[data-order-image-input]");
const imageUploadButton = document.querySelector("[data-order-image-upload]");
const imageStatus = document.querySelector("[data-order-image-status]");
const imageList = document.querySelector("[data-order-image-list]");
const updateButton = updateForm?.querySelector("button[type='submit']");

if (token) serviceOrder.setAuthToken(token);

function assertSuccess(response, fallbackMessage) {
  if (response?.type === "error" || response?.status === "error" || response?.code >= 400) {
    throw new Error(response.message || fallbackMessage);
  }
}

function unwrapOrder(response) {
  assertSuccess(response, "Não foi possível carregar a ordem de serviço.");
  const order = Array.isArray(response?.data) ? response.data[0] : response?.data;
  if (!order) throw new Error("Ordem de serviço não encontrada.");
  return order;
}

function renderOrder(order) {
  document.querySelector("[data-order-title]").textContent =
    `OS #${order.id} · ${order.device_model || `Aparelho #${order.device_id}`}`;
  const date = order.creation_time ? new Date(String(order.creation_time).replace(" ", "T")) : null;
  const openedAt = date && !Number.isNaN(date.getTime()) ? date.toLocaleString("pt-BR") : "—";
  document.querySelector("[data-order-meta]").textContent = `Aberta em ${openedAt}`;
  document.querySelector("[data-order-client]").textContent = order.user_name || "—";
  document.querySelector("[data-order-company]").textContent = order.company_name || "—";
  document.querySelector("[data-order-device]").textContent = order.device_model || `Aparelho #${order.device_id}`;
  updateForm.elements.status.value = order.status || "aberta";
  updateForm.elements.price.value = order.price ?? "";
  updateForm.elements.defect.value = order.defect || "";
  updateForm.elements.diagnosis.value = order.diagnosis || "";
}

function renderImages(images) {
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

      const removeButton = document.createElement("button");
      removeButton.type = "button";
      removeButton.className = "btn btn-ghost";
      removeButton.dataset.imageDelete = String(image.id);
      removeButton.textContent = "Remover";
      item.append(preview, removeButton);
      imageList.append(item);
    } catch (error) {
      console.error("Endereço de imagem inválido recebido da API:", error);
    }
  });
}

async function loadOrder() {
  if (!orderId || !/^\d+$/.test(orderId)) throw new Error("ID da ordem de serviço inválido.");
  renderOrder(unwrapOrder(await serviceOrder.listById(orderId)));
}

async function loadImages() {
  if (!orderId || !/^\d+$/.test(orderId) || !token) {
    imageStatus.textContent = "Acesse uma ordem válida com uma sessão autenticada.";
    return;
  }
  imageStatus.textContent = "Carregando imagens...";
  try {
    const response = await serviceOrder.listImages(orderId);
    assertSuccess(response, "Não foi possível carregar as imagens.");
    if (!Array.isArray(response.data)) throw new Error("A resposta da API não contém a lista de imagens.");
    renderImages(response.data);
    imageStatus.textContent = "Imagens da ordem atualizadas.";
  } catch (error) {
    console.error("Erro ao carregar imagens da ordem:", error);
    imageStatus.textContent = "Não foi possível carregar as imagens.";
    toastPrincipal.error({ message: error.message || "Erro ao carregar as imagens." });
  }
}

updateForm?.addEventListener("submit", async event => {
  event.preventDefault();
  if (!orderId || !/^\d+$/.test(orderId) || !updateForm.reportValidity()) return;

  const payload = {
    status: updateForm.elements.status.value,
    price: Number(updateForm.elements.price.value),
    defect: String(updateForm.elements.defect.value).trim(),
    diagnosis: String(updateForm.elements.diagnosis.value).trim()
  };
  updateButton.disabled = true;
  updateButton.textContent = "Salvando...";
  try {
    const response = await serviceOrder.update(orderId, payload);
    assertSuccess(response, "Não foi possível atualizar a ordem.");
    renderOrder(unwrapOrder(response));
    toastPrincipal.success({ message: response.message || "Ordem atualizada com sucesso." });
  } catch (error) {
    console.error("Erro ao atualizar ordem:", error);
    toastPrincipal.error({ message: error.message || "Não foi possível atualizar a ordem." });
  } finally {
    updateButton.disabled = false;
    updateButton.textContent = "Salvar alterações";
  }
});

imageUploadButton?.addEventListener("click", async () => {
  if (!orderId || !/^\d+$/.test(orderId)) {
    toastPrincipal.error({ message: "ID da ordem de serviço inválido." });
    return;
  }
  const files = Array.from(imageInput.files || []);
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
      imageStatus.textContent = `Enviando ${uploaded + 1} de ${files.length}...`;
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

imageList?.addEventListener("click", async event => {
  const button = event.target.closest("[data-image-delete]");
  if (!button || !window.confirm("Remover esta imagem da ordem de serviço?")) return;
  button.disabled = true;
  try {
    const response = await serviceOrder.removeImage(orderId, button.dataset.imageDelete);
    assertSuccess(response, "Não foi possível remover a imagem.");
    toastPrincipal.success({ message: response.message || "Imagem removida." });
    await loadImages();
  } catch (error) {
    button.disabled = false;
    toastPrincipal.error({ message: error.message || "Erro ao remover a imagem." });
  }
});

Promise.all([loadOrder(), loadImages()]).catch(error => {
  console.error("Erro ao carregar detalhes da OS:", error);
  toastPrincipal.error({ message: error.message || "Não foi possível carregar a ordem de serviço." });
  document.querySelector("[data-order-title]").textContent = "Não foi possível carregar a ordem";
});

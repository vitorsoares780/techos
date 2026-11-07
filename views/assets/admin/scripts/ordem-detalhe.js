import ServiceOrder from "../../_common/classes/ServiceOrder.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

(function () {
  "use strict";

  const btnPrint = document.querySelector('[data-imprimir]');
  const btnSend = document.querySelector('[data-enviar-cliente]');
  const btnConclude = document.querySelector('[data-concluir]');
  const updateStatus = document.querySelector('[data-atualizar-status]');
  const statusSelect = document.querySelector('[data-status-select]');
  const conclusao = document.querySelector('[data-conclusao]');
  const imageInput = document.querySelector("[data-order-image-input]");
  const imageUploadButton = document.querySelector("[data-order-image-upload]");
  const imageStatus = document.querySelector("[data-order-image-status]");
  const imageList = document.querySelector("[data-order-image-list]");
  const orderId = new URLSearchParams(window.location.search).get("id");
  const token = localStorage.getItem("token");
  const imageService = new ServiceOrder();

  if (token) imageService.setAuthToken(token);

  function toggleConclusao() {
    if (!conclusao) return;
    if (statusSelect && statusSelect.value === "concluida") {
      conclusao.value = new Date().toISOString().slice(0, 10);
      conclusao.disabled = true;
    } else if (conclusao) {
      conclusao.disabled = false;
    }
  }

  function showToast(message) {
    console.info(message);
    alert(message);
  }

  if (btnPrint) {
    btnPrint.addEventListener('click', function () {
      showToast('Imprimir OS selecionada');
    });
  }

  if (btnSend) {
    btnSend.addEventListener('click', function () {
      showToast('Enviar OS ao cliente');
    });
  }

  if (btnConclude) {
    btnConclude.addEventListener('click', function () {
      showToast('OS concluída');
      if (statusSelect) { statusSelect.value = "concluida"; toggleConclusao(); }
    });
  }

  if (updateStatus && statusSelect) {
    updateStatus.addEventListener('click', function () {
      const status = statusSelect.value;
      showToast('Status atualizado para: ' + status);
      toggleConclusao();
    });
  }

  // Watch for status select changes directly
  if (statusSelect) {
    statusSelect.addEventListener('change', toggleConclusao);
  }

  function isSuccessful(response) {
    return response?.type !== "error" && response?.status !== "error" && response?.code < 400;
  }

  function renderImages(images) {
    if (!imageList) return;
    imageList.replaceChildren();

    if (images.length === 0) {
      const emptyState = document.createElement("li");
      emptyState.textContent = "Esta ordem ainda não possui imagens.";
      imageList.append(emptyState);
      return;
    }

    images.forEach((image) => {
      let imageUrl;
      try {
        imageUrl = new URL(image.image_url, window.location.href);
        if (!["http:", "https:"].includes(imageUrl.protocol) || imageUrl.origin !== window.location.origin) {
          throw new Error("Endereço de imagem fora da origem da aplicação.");
        }
      } catch (error) {
        console.error("Endereço de imagem inválido recebido da API:", error);
        return;
      }

      const item = document.createElement("li");
      const preview = document.createElement("img");
      preview.src = imageUrl.href;
      preview.alt = "Imagem da ordem de serviço";
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
    });
  }

  async function loadImages() {
    if (!orderId || !/^\d+$/.test(orderId) || !token) {
      if (imageStatus) imageStatus.textContent = "Acesse uma ordem válida com uma sessão autenticada.";
      return;
    }

    if (imageStatus) imageStatus.textContent = "Carregando galeria...";
    try {
      const response = await imageService.listImages(orderId);
      if (!isSuccessful(response)) {
        throw new Error(response?.message || "Não foi possível carregar as imagens.");
      }
      renderImages(Array.isArray(response.data) ? response.data : []);
      if (imageStatus) imageStatus.textContent = "Galeria atualizada.";
    } catch (error) {
      console.error("Erro ao carregar galeria da ordem:", error);
      if (imageStatus) imageStatus.textContent = "Não foi possível carregar a galeria.";
      toastPrincipal.error({ message: error.message || "Erro ao carregar as imagens." });
    }
  }

  if (imageUploadButton && imageInput) {
    imageUploadButton.addEventListener("click", async () => {
      if (!orderId || !/^\d+$/.test(orderId)) {
        toastPrincipal.error({ message: "ID da ordem de serviço inválido." });
        return;
      }

      const files = Array.from(imageInput.files || []);
      if (files.length === 0) {
        toastPrincipal.warning({ message: "Selecione ao menos uma imagem." });
        return;
      }

      const invalid = files.find((file) =>
        !["image/jpeg", "image/png", "image/webp"].includes(file.type) ||
        file.size > 5 * 1024 * 1024
      );
      if (invalid) {
        toastPrincipal.warning({ message: "Use somente imagens JPEG, PNG ou WebP de até 5 MB." });
        return;
      }

      imageUploadButton.disabled = true;
      let uploaded = 0;
      try {
        for (const file of files) {
          if (imageStatus) imageStatus.textContent = `Enviando ${uploaded + 1} de ${files.length}...`;
          const response = await imageService.uploadImage(orderId, file);
          if (!isSuccessful(response)) {
            throw new Error(response?.message || "Não foi possível enviar uma das imagens.");
          }
          uploaded += 1;
        }

        imageInput.value = "";
        toastPrincipal.success({ message: `${uploaded} imagem(ns) adicionada(s) à ordem.` });
      } catch (error) {
        toastPrincipal.error({
          message: uploaded
            ? `${error.message} ${uploaded} imagem(ns) enviada(s); a galeria foi atualizada.`
            : error.message || "Erro ao enviar as imagens."
        });
      } finally {
        imageUploadButton.disabled = false;
        await loadImages();
      }
    });
  }

  if (imageList) {
    imageList.addEventListener("click", async (event) => {
      const button = event.target.closest("[data-image-delete]");
      if (!button || !window.confirm("Remover esta imagem da ordem de serviço?")) return;

      button.disabled = true;
      try {
        const response = await imageService.removeImage(orderId, button.dataset.imageDelete);
        if (!isSuccessful(response)) {
          throw new Error(response?.message || "Não foi possível remover a imagem.");
        }
        toastPrincipal.success({ message: "Imagem removida." });
        await loadImages();
      } catch (error) {
        button.disabled = false;
        toastPrincipal.error({ message: error.message || "Erro ao remover a imagem." });
      }
    });
  }

  // Run on page load in case status is already "concluida"
  toggleConclusao();
  loadImages();
})();

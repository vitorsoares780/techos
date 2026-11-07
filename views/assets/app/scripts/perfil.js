import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

/* TechOS — Perfil do cliente: dados pessoais e senha */
(function () {
  "use strict";

  const perfilForm = document.querySelector("[data-perfil-form]");
  const senhaForm = document.querySelector("[data-senha-form]");
  const photoInput = document.querySelector("[data-profile-photo]");
  const photoPreview = document.querySelector("[data-profile-photo-preview]");
  const photoInitial = document.querySelector("[data-profile-photo-initial]");
  const photoUploadButton = document.querySelector("[data-profile-photo-upload]");
  const photoStatus = document.querySelector("[data-profile-photo-status]");

  let userData = {};
  try {
    const storedUser = JSON.parse(localStorage.getItem("user") || "{}");
    if (storedUser && typeof storedUser === "object" && !Array.isArray(storedUser)) {
      userData = storedUser;
    }
  } catch (error) {
    console.warn("Erro ao carregar os dados da sessão:", error);
  }

  const token = localStorage.getItem("token");
  let profilePhoto = typeof userData.photo === "string" ? userData.photo : "";
  let selectedPhoto = null;
  let previewUrl = null;

  const userService = new User();

  if (token) {
    userService.setAuthToken(token);
  }

  function fillProfile() {
    if (!perfilForm) {
      return;
    }

    const nameField = perfilForm.elements.namedItem("nome");
    const emailField = perfilForm.elements.namedItem("email");

    if (nameField) {
      nameField.value = userData.name || "";
    }

    if (emailField) {
      emailField.value = userData.email || "";
    }

    renderProfilePhoto(profilePhoto);
  }

  function renderProfilePhoto(photo) {
    const name = String(userData.name || "Usuário").trim();
    const initial = Array.from(name)[0]?.toLocaleUpperCase("pt-BR") || "U";

    if (photo && photoPreview) {
      photoPreview.src = photo;
      photoPreview.hidden = false;
      if (photoInitial) photoInitial.hidden = true;
      return;
    }

    if (photoPreview) {
      photoPreview.removeAttribute("src");
      photoPreview.hidden = true;
    }
    if (photoInitial) {
      photoInitial.textContent = initial;
      photoInitial.hidden = false;
    }
  }

  function assertUpdateSuccess(result, fallbackMessage) {
    if (
      result?.type === "error" ||
      result?.code >= 400 ||
      (result?.status && result.status !== "success")
    ) {
      throw new Error(result.message || fallbackMessage);
    }
  }

  if (perfilForm) {
    loginFormController.init(perfilForm);
    fillProfile();

    perfilForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const validation = loginFormController.validateRequired([
        "nome",
        "email"
      ]);

      if (!validation.valid) {
        toastPrincipal.warning({
          message: "Preencha nome e e-mail para continuar."
        });
        return;
      }

      const data = loginFormController.getData();

      const name = String(data.nome || "").trim();
      const email = String(data.email || "").trim();

      try {
        const result = await userService.update({
            name,
            email
          });
        assertUpdateSuccess(result, "Não foi possível atualizar o perfil.");

        userData = {
          ...userData,
          id: result?.data?.id ?? userData?.id,
          name: result?.data?.name ?? name,
          email: result?.data?.email ?? email,
          photo: profilePhoto
        };

        localStorage.setItem("user", JSON.stringify(userData));
        window.dispatchEvent(new CustomEvent("user:updated", { detail: userData }));

        toastPrincipal.success({
          message: result?.message || "Dados atualizados com sucesso!"
        });
      } catch (error) {
        toastPrincipal.error({
          message: error.message || "Não foi possível atualizar o perfil."
        });
      }
    });
  }

  if (photoInput) {
    photoInput.addEventListener("change", async () => {
      const [file] = photoInput.files || [];
      if (!file) return;

      if (!["image/jpeg", "image/png", "image/webp"].includes(file.type)) {
        photoInput.value = "";
        toastPrincipal.warning({ message: "Use uma imagem JPEG, PNG ou WebP." });
        return;
      }

      if (file.size > 5 * 1024 * 1024) {
        photoInput.value = "";
        toastPrincipal.warning({ message: "A imagem deve ter no máximo 5 MB." });
        return;
      }

      selectedPhoto = file;
      if (previewUrl) {
        URL.revokeObjectURL(previewUrl);
      }
      previewUrl = URL.createObjectURL(file);
      renderProfilePhoto(previewUrl);
      if (photoStatus) photoStatus.textContent = "Prévia pronta. Envie para salvar a nova foto.";
    });
  }

  if (photoUploadButton) {
    photoUploadButton.addEventListener("click", async () => {
      if (!selectedPhoto) {
        toastPrincipal.warning({ message: "Selecione uma imagem antes de enviar." });
        return;
      }

      photoUploadButton.disabled = true;
      if (photoStatus) photoStatus.textContent = "Enviando foto...";

      try {
        const result = await userService.uploadPhoto(selectedPhoto);
        assertUpdateSuccess(result, "Não foi possível atualizar a foto.");
        if (typeof result?.data?.photo !== "string" || !result.data.photo) {
          throw new Error("A API não retornou o endereço da foto atualizada.");
        }

        profilePhoto = result.data.photo;
        selectedPhoto = null;
        photoInput.value = "";
        if (previewUrl) {
          URL.revokeObjectURL(previewUrl);
          previewUrl = null;
        }
        userData = { ...userData, photo: profilePhoto };
        localStorage.setItem("user", JSON.stringify(userData));
        renderProfilePhoto(profilePhoto);
        window.dispatchEvent(new CustomEvent("user:updated", { detail: userData }));
        if (photoStatus) photoStatus.textContent = "Foto atualizada.";
        toastPrincipal.success({ message: result.message || "Foto atualizada com sucesso." });
      } catch (error) {
        if (photoStatus) photoStatus.textContent = "Não foi possível atualizar a foto.";
        toastPrincipal.error({
          message: error.message || "Não foi possível atualizar a foto."
        });
      } finally {
        photoUploadButton.disabled = false;
      }
    });
  }

  if (senhaForm) {
    senhaForm.addEventListener("submit", async function (event) {
      event.preventDefault();

      const nova = document.querySelector("[data-senha-nova]");
      const confirma = document.querySelector("[data-senha-confirma]");

      if (!nova || !confirma) {
        return;
      }

      if (nova.value !== confirma.value) {
        toastPrincipal.warning({
          message: "As senhas não conferem."
        });
        return;
      }

      if (nova.value.length < 8) {
        toastPrincipal.warning({
          message: "A nova senha deve possuir pelo menos 8 caracteres."
        });
        return;
      }

      try {
        const result = await userService.update({
          id: userData?.id,
          password: nova.value
        });
        assertUpdateSuccess(result, "Não foi possível atualizar a senha.");

        toastPrincipal.success({
          message: result?.message || "Senha atualizada com sucesso!"
        });

        senhaForm.reset();
      } catch (error) {
        toastPrincipal.error({
          message: error.message || "Não foi possível atualizar a senha."
        });
      }
    });
  }
})();

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

  function readPhoto(file) {
    return new Promise((resolve, reject) => {
      const reader = new FileReader();
      reader.addEventListener("load", () => resolve(reader.result));
      reader.addEventListener("error", () => reject(new Error("Não foi possível ler a imagem.")));
      reader.readAsDataURL(file);
    });
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
          id: userData?.id,
          name,
          email,
          photo: profilePhoto
        });
        assertUpdateSuccess(result, "Não foi possível atualizar o perfil.");

        userData = {
          ...userData,
          id: result?.data?.id ?? userData?.id,
          name: result?.data?.name ?? name,
          email: result?.data?.email ?? email,
          photo: result?.data?.photo ?? profilePhoto
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

      if (!file.type.startsWith("image/")) {
        photoInput.value = "";
        toastPrincipal.warning({ message: "Selecione um arquivo de imagem." });
        return;
      }

      if (file.size > 1024 * 1024) {
        photoInput.value = "";
        toastPrincipal.warning({ message: "A imagem deve ter no máximo 1 MB." });
        return;
      }

      try {
        profilePhoto = await readPhoto(file);
        renderProfilePhoto(profilePhoto);
      } catch (error) {
        toastPrincipal.error({ message: error.message });
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

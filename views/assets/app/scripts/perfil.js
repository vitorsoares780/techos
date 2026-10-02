import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

/* TechOS — Perfil do cliente: dados pessoais e senha */
(function () {
  "use strict";

  const perfilForm = document.querySelector("[data-perfil-form]");
  const senhaForm = document.querySelector("[data-senha-form]");

  const userData = JSON.parse(localStorage.getItem("user") || "null");
  const token = localStorage.getItem("token");

  const userService = new User();

  if (token) {
    userService.setAuthToken(token);
  }

  function fillProfile() {
    if (!perfilForm || !userData) {
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
          email
        });

        if (result?.status && result.status !== "success") {
          throw new Error(
            result.message || "Não foi possível atualizar o perfil."
          );
        }

        const updatedUser = {
          ...userData,
          id: result?.data?.id ?? userData?.id,
          name: result?.data?.name ?? name,
          email: result?.data?.email ?? email
        };

        localStorage.setItem("user", JSON.stringify(updatedUser));

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

      if (nova.value.length < 6) {
        toastPrincipal.warning({
          message: "A nova senha deve possuir pelo menos 6 caracteres."
        });
        return;
      }

      try {
        const result = await userService.update({
          id: userData?.id,
          password: nova.value
        });

        if (result?.status && result.status !== "success") {
          throw new Error(
            result.message || "Não foi possível atualizar a senha."
          );
        }

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

import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

/* ============================================================
   TechOS — Tela de Cadastro de usuário
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-signup-form]");
  const cpfField = document.querySelector("[data-cpf]");
  const formMessage = document.querySelector("[data-form-message]");
  const submitButton = form?.querySelector("button[type='submit']");

  const userService = new User();

  function setMessage(message, isError = false) {
    if (!formMessage) {
      return;
    }

    formMessage.textContent = message;
    formMessage.classList.toggle("error", isError);
    formMessage.classList.toggle("success", !isError);
  }

  function maskCpf(event) {
    const digits = event.target.value.replace(/\D/g, "").slice(0, 11);
    const masked = digits
      .replace(/^(\d{3})(\d)/, "$1.$2")
      .replace(/^(\d{3})\.(\d{3})(\d)/, "$1.$2.$3")
      .replace(/(\d{3})(\d{2})$/, "$1-$2");

    event.target.value = masked;
  }

  if (cpfField) {
    cpfField.addEventListener("input", maskCpf);
  }

  if (!form) {
    return;
  }

  loginFormController.init(form);

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const validation = loginFormController.validateRequired([
      "name",
      "email",
      "password"
    ]);

    if (!validation.valid) {
      setMessage("Preencha nome, e-mail e senha para continuar.", true);
      toastPrincipal.warning({
        message: "Preencha nome, e-mail e senha para continuar."
      });
      return;
    }

    const data = loginFormController.getData();

    const name = String(data.name || "").trim();
    const email = String(data.email || "").trim();
    const password = String(data.password || "");
    const cpf = String(data.cpf || "").trim();

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setMessage("Informe um e-mail válido.", true);
      toastPrincipal.warning({
        message: "Informe um e-mail válido."
      });
      return;
    }

    if (cpf && cpf.replace(/\D/g, "").length < 11) {
      setMessage("Informe um CPF válido.", true);
      toastPrincipal.warning({
        message: "Informe um CPF válido."
      });
      return;
    }

    submitButton.disabled = true;
    setMessage("Enviando cadastro...");

    try {
      const result = await userService.register({
        name: name,
        email,
        password,
        cpf
      });

      if (!result || result.status !== "success") {
        throw new Error(
          result?.message || "Não foi possível concluir o cadastro."
        );
      }

      setMessage(
        "Cadastro realizado com sucesso! Redirecionando para o login...",
        false
      );

      toastPrincipal.success({
        message: result.message || "Cadastro realizado com sucesso!"
      });

      form.reset();

      window.setTimeout(() => {
        window.location.href = "login.html";
      }, 1200);
    } catch (error) {
      const message =
        error.message || "Não foi possível concluir o cadastro.";

      setMessage(message, true);

      toastPrincipal.error({
        message
      });
    } finally {
      submitButton.disabled = false;
    }
  });
})();

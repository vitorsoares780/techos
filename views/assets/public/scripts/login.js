import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";

/* ============================================================
   TechOS — Tela de Login
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-login-form]");
  const errorMessage = document.querySelector("[data-form-message]");

  function setMessage(message, isError = false) {
    if (!errorMessage) {
      return;
    }

    errorMessage.textContent = message;
    errorMessage.classList.toggle("error", isError);
    errorMessage.classList.toggle("success", !isError);
  }

  function saveSession(payload) {
    const user = {
      id: payload.id,
      name: payload.name,
      photo: payload.photo || "",
      email: payload.email || ""
    };

    localStorage.setItem("token", payload.token);
    localStorage.setItem("user", JSON.stringify(user));
  }

  if (!form) {
    return;
  }

  // Inicializa o FormController com o formulário de Login
  loginFormController.init(form);

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    // Validação utilizando o FormController
    const validation = loginFormController.validateRequired([
      "email",
      "password"
    ]);

    if (!validation.valid) {
      setMessage("Informe e-mail e senha para continuar.", true);

      toastPrincipal.warning({
        message: "Informe e-mail e senha para continuar."
      });

      return;
    }

    // Obtém os dados utilizando o FormController
    const formData = loginFormController.getData();

    const email = String(formData.email || "").trim();
    const password = String(formData.password || "").trim();

    setMessage("Entrando...");

    try {
      const response = await fetch("../../../api/users/login", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({
          email,
          password
        })
      });

      const result = await response.json();

      if (!response.ok || result.status !== "success") {
        toastPrincipal.error({
          message: result.message || "Credenciais inválidas."
        });

        throw new Error(result.message || "Credenciais inválidas.");
      }

      saveSession(result.data);
      setMessage("");

      toastPrincipal.success({
        message: result.message || "Login realizado com sucesso!"
      });

      window.location.href = "../app/dashboard.html";
    } catch (error) {
      toastPrincipal.error({
        message: error.message || "Não foi possível entrar no sistema."
      });

      setMessage(
        error.message || "Não foi possível entrar no sistema.",
        true
      );
    }
  });
})();
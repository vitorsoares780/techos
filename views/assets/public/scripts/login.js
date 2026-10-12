import { toastPrincipal } from "../../_common/classes/Toast.js";
import { loginFormController } from "../../_common/classes/FormController.js";
import User from "../../_common/classes/User.js";

/* ============================================================
   TechOS — Tela de Login
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-login-form]");
  const errorMessage = document.querySelector("[data-form-message]");
  const userService = new User();

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

    const user = await userService.listByEmail(email);
    const typeId = user.data.typeId;

    try {
      const result = await userService.login(email, password);

      if (
        result?.type === "error" ||
        result?.code >= 400 ||
        result?.status !== "success"
      ) {
        throw new Error(result?.message || "Credenciais inválidas.");
      }

      if (!result.data?.token) {
        throw new Error("A resposta de login não contém um token válido.");
      }

      saveSession(result.data);
      setMessage("");

      toastPrincipal.success({
        message: result.message || "Login realizado com sucesso!"
      });

      if(typeId === 1){
        window.location.href = "../admin/painel.html";
      } else if (typeId == 2 || typeId == 3) {
        window.location.href = "../app/dashboard.html";
      }
    } catch (error) {
      const message = error.message || "Não foi possível entrar no sistema.";
      toastPrincipal.error({
        message
      });

      setMessage(message, true);
    }
  });
})();
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

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const formData = new FormData(form);
    const email = String(formData.get("email") || "").trim();
    const password = String(formData.get("password") || "").trim();

    if (!email || !password) {
      setMessage("Informe e-mail e senha para continuar.", true);
      return;
    }

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
        throw new Error(result.message || "Credenciais inválidas.");
      }

      saveSession(result.data);
      setMessage("");
      window.location.href = "../app/dashboard.html";
    } catch (error) {
      setMessage(error.message || "Não foi possível entrar no sistema.", true);
    }
  });
})();

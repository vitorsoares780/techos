/* ============================================================
   TechOS — Tela de Cadastro de empresa
   ============================================================ */
(function () {
  "use strict";

  const form = document.querySelector("[data-signup-form]");
  const cpfField = document.querySelector("[data-cpf]");
  const formMessage = document.querySelector("[data-form-message]");
  const submitButton = form?.querySelector("button[type='submit']");

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

  form.addEventListener("submit", async function (event) {
    event.preventDefault();

    const fullname = (new FormData(form).get("fullname") || "").toString().trim();
    const email = (new FormData(form).get("email") || "").toString().trim();
    const password = (new FormData(form).get("password") || "").toString();
    const cpf = (new FormData(form).get("cpf") || "").toString().trim();

    if (!fullname || !email || !password) {
      setMessage("Preencha nome, e-mail e senha para continuar.", true);
      return;
    }

    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
      setMessage("Informe um e-mail válido.", true);
      return;
    }

    if (cpf && cpf.replace(/\D/g, "").length < 11) {
      setMessage("Informe um CPF válido.", true);
      return;
    }

    submitButton.disabled = true;
    setMessage("Enviando cadastro...");

    try {
      const response = await fetch("../../../api/users/register", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          "Accept": "application/json"
        },
        body: JSON.stringify({
          name: fullname,
          email,
          password,
          cpf
        })
      });

      const responseText = await response.text();
      let result = {};

      try {
        result = JSON.parse(responseText);
      } catch (error) {
        throw new Error("Resposta inválida do servidor.");
      }

      if (!response.ok || result.status !== "success") {
        throw new Error(result.message || "Não foi possível concluir o cadastro.");
      }

      setMessage("Cadastro realizado com sucesso! Redirecionando para o login...", false);
      form.reset();

      window.setTimeout(() => {
        window.location.href = "login.html";
      }, 1200);
    } catch (error) {
      setMessage(error.message, true);
    } finally {
      submitButton.disabled = false;
    }
  });
})();

import HttpClientBase from "../../_common/classes/HttpClientBase.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

const form = document.querySelector("[data-admin-login-form]");
const httpClient = new HttpClientBase();
const submitButton = form?.querySelector("button[type='submit']");

form?.addEventListener("submit", async event => {
  event.preventDefault();

  const email = String(form.elements.email?.value || "").trim();
  const password = String(form.elements.password?.value || "");

  if (!email || !password) {
    toastPrincipal.warning({ message: "Preencha e-mail e senha para continuar." });
    return;
  }

  submitButton.disabled = true;
  submitButton.textContent = "Entrando...";

  try {
    const result = await httpClient.post("/users/login-admin", { email, password });
    if (result?.type === "error" || result?.code >= 400 || result?.status !== "success") {
      throw new Error(result?.message || "Não foi possível autenticar o administrador.");
    }

    const user = result.data;
    if (!user?.token) {
      throw new Error("A resposta do login não contém um token válido.");
    }

    localStorage.setItem("token", user.token);
    localStorage.setItem("user", JSON.stringify({
      id: user.id,
      name: user.name,
      photo: user.photo || "",
      email: user.email || email
    }));

    window.location.href = "painel.html";
  } catch (error) {
    toastPrincipal.error({
      message: error.message || "Não foi possível entrar no painel administrativo."
    });
  } finally {
    submitButton.disabled = false;
    submitButton.textContent = "Entrar";
  }
});

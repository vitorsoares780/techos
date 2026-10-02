import User from "../../_common/classes/User.js";
import { toastPrincipal } from "../../_common/classes/Toast.js";

(function () {
  "use strict";

  const modal = document.querySelector("[data-modal-usuario]");
  const btnNew = document.querySelector("[data-novo-usuario]");
  const closeButtons = document.querySelectorAll("[data-fechar-modal]");
  const saveButton = document.querySelector("[data-salvar-usuario]");
  const tableBody = document.querySelector(".data tbody");
  const filterForm = document.querySelector("[data-filter-form]");

  const userService = new User();

  const token = localStorage.getItem("token");
  if (token) {
    userService.setAuthToken(token);
  }

  function getUsers(result) {
    if (Array.isArray(result)) {
      return result;
    }

    if (Array.isArray(result?.data)) {
      return result.data;
    }

    return [];
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  function getInitials(name) {
    const parts = String(name || "").trim().split(/\s+/).filter(Boolean);

    if (!parts.length) {
      return "?";
    }

    if (parts.length === 1) {
      return parts[0].slice(0, 2).toUpperCase();
    }

    return `${parts[0][0]}${parts[parts.length - 1][0]}`.toUpperCase();
  }

  function getProfileName(user) {
    const profiles = {
      1: "Administrador",
      2: "Técnico",
      3: "Gestor"
    };

    return profiles[user.type_id] || user.type || "Usuário";
  }

  function getProfileClass(user) {
    const profile = getProfileName(user).toLowerCase();

    if (profile.includes("admin")) {
      return "admin";
    }

    if (profile.includes("técnico") || profile.includes("tecnico")) {
      return "tecnico";
    }

    return "gestor";
  }

  function getStatus(user) {
    return Number(user.active) === 1;
  }

  function renderUsers(users) {
    if (!tableBody) {
      return;
    }

    if (!users.length) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align:center;padding:2rem;">
            Nenhum usuário encontrado.
          </td>
        </tr>
      `;
      return;
    }

    tableBody.innerHTML = users.map(function (user) {
      const active = getStatus(user);
      const profileName = getProfileName(user);
      const profileClass = getProfileClass(user);

      return `
        <tr data-user-id="${escapeHtml(user.id)}">
          <td>
            <section style="display:flex;align-items:center;gap:0.6rem">
              <span class="avatar" style="width:2rem;height:2rem;font-size:0.8rem;flex-shrink:0">
                ${escapeHtml(getInitials(user.name))}
              </span>
              ${escapeHtml(user.name)}
            </section>
          </td>
          <td>${escapeHtml(user.email)}</td>
          <td>
            <span class="pill ${escapeHtml(profileClass)}">
              ${escapeHtml(profileName)}
            </span>
          </td>
          <td>
            <span class="pill ${active ? "ativo" : "inativo"}">
              ${active ? "Ativo" : "Inativo"}
            </span>
          </td>
          <td>—</td>
          <td>—</td>
          <td>
            <span class="row-actions">
              <button type="button" data-user-edit="${escapeHtml(user.id)}">
                Editar
              </button>
              <button
                type="button"
                class="${active ? "danger" : ""}"
                data-user-delete="${escapeHtml(user.id)}"
              >
                ${active ? "Excluir" : "Excluir"}
              </button>
            </span>
          </td>
        </tr>
      `;
    }).join("");
  }

  async function loadUsers() {
    if (!tableBody) {
      return;
    }

    tableBody.innerHTML = `
      <tr>
        <td colspan="7" style="text-align:center;padding:2rem;">
          Carregando usuários...
        </td>
      </tr>
    `;

    try {
      const result = await userService.listAll();

      if (result?.status && result.status !== "success") {
        throw new Error(result.message || "Não foi possível carregar os usuários.");
      }

      const users = getUsers(result);
      renderUsers(users);
    } catch (error) {
      tableBody.innerHTML = `
        <tr>
          <td colspan="7" style="text-align:center;padding:2rem;">
            Não foi possível carregar os usuários.
          </td>
        </tr>
      `;

      toastPrincipal.error({
        message: error.message || "Não foi possível carregar os usuários."
      });
    }
  }

  async function deleteUser(id) {
    const confirmed = window.confirm(
      "Deseja realmente excluir este usuário?"
    );

    if (!confirmed) {
      return;
    }

    try {
      const result = await userService.remove(id);

      if (result?.status && result.status !== "success") {
        throw new Error(result.message || "Não foi possível excluir o usuário.");
      }

      toastPrincipal.success({
        message: result?.message || "Usuário excluído com sucesso!"
      });

      await loadUsers();
    } catch (error) {
      toastPrincipal.error({
        message: error.message || "Não foi possível excluir o usuário."
      });
    }
  }

  if (btnNew && modal) {
    btnNew.addEventListener("click", function () {
      modal.classList.add("is-open");
    });
  }

  closeButtons.forEach(function (button) {
    button.addEventListener("click", function () {
      if (modal) {
        modal.classList.remove("is-open");
      }
    });
  });

  if (saveButton) {
    saveButton.addEventListener("click", function () {
      toastPrincipal.warning({
        message: "O cadastro administrativo ainda não foi integrado."
      });
    });
  }

  if (tableBody) {
    tableBody.addEventListener("click", function (event) {
      const deleteButton = event.target.closest("[data-user-delete]");

      if (deleteButton) {
        deleteUser(Number(deleteButton.dataset.userDelete));
      }
    });
  }

  if (filterForm) {
    filterForm.addEventListener("submit", function (event) {
      event.preventDefault();
    });
  }

  loadUsers();
})();

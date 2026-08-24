/* ============================================================
   TechOS — Logout
   ============================================================ */
(function () {
  "use strict";

  const logoutLinks = document.querySelectorAll('[data-logout]');

  function clearSession() {
    localStorage.removeItem("token");
    localStorage.removeItem("user");
    sessionStorage.clear();
    window.location.href = "../public/login.html";
  }

  logoutLinks.forEach(function (link) {
    link.addEventListener("click", function (event) {
      event.preventDefault();
      clearSession();
    });
  });

  const logoutButtons = document.querySelectorAll('[data-logout-button]');
  logoutButtons.forEach(function (button) {
    button.addEventListener("click", function (event) {
      event.preventDefault();
      clearSession();
    });
  });

  document.addEventListener("keydown", function (event) {
    if (event.key === "Escape" && localStorage.getItem("token")) {
      clearSession();
    }
  });
})();

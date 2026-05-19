/* ============================================================
   TechOS — Navegação responsiva (mobile menu toggle)
   ============================================================ */
(function () {
  "use strict";

  const toggle = document.querySelector("[data-menu-toggle]");
  const mobileNav = document.querySelector("[data-mobile-nav]");

  if (!toggle || !mobileNav) {
    return;
  }

  toggle.addEventListener("click", function () {
    const isOpen = mobileNav.classList.toggle("is-open");
    toggle.setAttribute("aria-expanded", String(isOpen));
  });

  const links = mobileNav.querySelectorAll("a");
  links.forEach(function (link) {
    link.addEventListener("click", function () {
      mobileNav.classList.remove("is-open");
      toggle.setAttribute("aria-expanded", "false");
    });
  });
})();

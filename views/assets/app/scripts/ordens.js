/* TechOS — Listagem de OS: filtro client-side */
(function () {
  "use strict";

  const form = document.querySelector("[data-filter-form]");
  const table = document.querySelector("[data-os-table] tbody");

  if (form && table) {
    const rows = Array.from(table.querySelectorAll("tr"));

    form.addEventListener("input", function () {

      const search = (form.elements.q.value || "").trim().toLowerCase();
      const status = form.elements.status.value;

      rows.forEach(function (row) {
        const text = row.textContent.toLowerCase();

        const matchSearch = !search || text.includes(search);
        const matchStatus = !status || row.querySelector(".pill." + status);

        row.hidden = !(matchSearch && matchStatus);
      });

    });
  }
})();
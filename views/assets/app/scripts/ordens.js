/* TechOS — Listagem de OS: filtro client-side + exportação */
(function () {
  "use strict";

  const form = document.querySelector("[data-filter-form]");
  const table = document.querySelector("[data-os-table] tbody");

  if (form && table) {
    const rows = Array.from(table.querySelectorAll("tr"));

    //evento de filtro para barra de pesquisa e selects
    form.addEventListener("input", function () {

      const search = (form.elements.q.value || "").trim().toLowerCase();
      const status = form.elements.status.value;
      const tecnico = form.elements.tecnico.value;

      rows.forEach(function (row) {
        const text = row.textContent.toLowerCase();

        const matchSearch = !search || text.includes(search);
        const matchStatus = !status || row.querySelector(".pill." + status);
        const matchTec = !tecnico || text.includes(tecnico.toLowerCase());

        row.hidden = !(matchSearch && matchStatus && matchTec);
      });

    });
  }

  const exportBtn = document.querySelector("[data-export-csv]");

  if (exportBtn) {
    exportBtn.addEventListener("click", function (event) {
      event.preventDefault();
      // TODO: substituir por HttpClientBase.js → GET /api/ordens/export.csv
      alert("Exportação iniciada — verifique seus downloads.");
    });
  }
})();

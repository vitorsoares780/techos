/* TechOS — Dashboard: hidratação dos KPIs (TODO: API) */
(function () {
  "use strict";
  // TODO: substituir por HttpClientBase.js → GET /api/dashboard/resumo
  document.querySelectorAll(".kpi .value").forEach(function (el) {
    el.dataset.loaded = "true";
  });
})();

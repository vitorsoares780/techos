(function () {
  "use strict";

  const filterForm = document.querySelector('[data-filter-form]');
  if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
      event.preventDefault();
      // TODO: consultar API GET /api/admin/ordens com filtros
      const formData = new FormData(filterForm);
      console.info('Filtrar ordens:', Object.fromEntries(formData.entries()));
    });
  }
})();

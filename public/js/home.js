    (function() {
      // 1. Manejo del colapso/expansión del Sidebar interactivo
      const sidebar = document.getElementById('sidebar');
      const mainWrapper = document.getElementById('mainWrapper');
      const btnToggle = document.getElementById('btnToggleSidebar');
      const btnMiniExpand = document.getElementById('btnMiniExpand');
      const collapseArrow = document.getElementById('collapseArrow');

      function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        mainWrapper.classList.toggle('expanded');
        
        const isCollapsed = sidebar.classList.contains('collapsed');
        try {
          localStorage.setItem('sidebar_collapsed_home', isCollapsed ? 'true' : 'false');
        } catch(e) {}
      }

      if (btnToggle) btnToggle.addEventListener('click', toggleSidebar);
      if (btnMiniExpand) btnMiniExpand.addEventListener('click', toggleSidebar);

      // Restaurar estado previo si existe
      try {
        if (localStorage.getItem('sidebar_collapsed_home') === 'true') {
          sidebar.classList.add('collapsed');
          mainWrapper.classList.add('expanded');
        }
      } catch(e) {}

      // 2. Filtro de búsqueda en tabla en tiempo real (Client-side)
      const searchInput = document.getElementById('globalSearch');
      const tableRows = Array.from(document.querySelectorAll('#invoicesTable tbody tr[data-invoice-row]'));
      const propertySelect = document.getElementById('propertySelect');
      const noInvoiceResults = document.getElementById('invoiceFilterNoResults');
      function filterInvoiceRows() {
        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const propertyId = propertySelect ? propertySelect.value : 'all';
        let visibleCount = 0;

        tableRows.forEach(row => {
          const matchesQuery = row.textContent.toLowerCase().includes(query);
          const matchesProperty = propertyId === 'all' || row.dataset.propertyId === propertyId;
          row.hidden = !matchesQuery || !matchesProperty;
          if (!row.hidden) visibleCount++;
        });

        if (noInvoiceResults) noInvoiceResults.hidden = visibleCount > 0 || tableRows.length === 0;
      }

      if (searchInput) searchInput.addEventListener('input', filterInvoiceRows);
      if (propertySelect) propertySelect.addEventListener('change', filterInvoiceRows);

      // 4. Interacción de botones de acción
      const btnExport = document.getElementById('btnExportSummary');
      if (btnExport) {
        btnExport.addEventListener('click', function() {
          alert('Generando exportación de informe sumario consolidado (PDF/CSV)...');
        });
      }

      const propertySummaryTable = document.getElementById('adminPropertySummaryTable');
      if (propertySummaryTable) {
        const serviceTypeFilter = document.getElementById('adminSummaryServiceFilter');
        const propertyStateFilter = document.getElementById('adminSummaryPropertyStateFilter');
        const accountStateFilter = document.getElementById('adminSummaryAccountStateFilter');
        const resetFilters = document.getElementById('adminSummaryResetFilters');
        const resultCount = document.getElementById('adminSummaryResultCount');
        const noResultsRow = document.getElementById('adminSummaryNoResults');
        const rows = Array.from(propertySummaryTable.querySelectorAll('tbody tr[data-summary-row]'));

        function applySummaryFilters() {
          let visibleCount = 0;
          rows.forEach(row => {
            const matchesServiceType = !serviceTypeFilter.value
              || Array.from(row.querySelectorAll('[data-service-category]'))
                .some(service => service.dataset.serviceCategory === serviceTypeFilter.value);
            const matchesPropertyState = !propertyStateFilter.value
              || row.dataset.propertyState === propertyStateFilter.value;
            const matchesAccountState = !accountStateFilter.value
              || row.dataset.accountState === accountStateFilter.value;
            const isVisible = matchesServiceType && matchesPropertyState && matchesAccountState;

            row.hidden = !isVisible;
            if (isVisible) visibleCount++;
          });

          noResultsRow.hidden = visibleCount > 0 || rows.length === 0;
          resultCount.textContent = `Mostrando ${visibleCount} de ${rows.length} inmuebles`;
        }

        [serviceTypeFilter, propertyStateFilter, accountStateFilter].forEach(filter => {
          filter.addEventListener('change', applySummaryFilters);
        });
        resetFilters.addEventListener('click', function() {
          serviceTypeFilter.value = '';
          propertyStateFilter.value = '';
          accountStateFilter.value = '';
          applySummaryFilters();
        });
        applySummaryFilters();
      }

      // Accesos rápidos
      /*
      document.getElementById('scAddProperty')?.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Abriendo formulario de Alta de Inmueble (US-05)');
      });
      document.getElementById('scAddService')?.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Abriendo modal de Vinculación de Servicio (US-06)');
      });
      document.getElementById('scReports')?.addEventListener('click', (e) => {
        e.preventDefault();
        alert('Redirigiendo al módulo de Informes Tabulares (US-09)');
      });
      */
    })();

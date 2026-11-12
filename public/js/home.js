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
      const tableRows = document.querySelectorAll('#invoicesTable tbody tr');

      if (searchInput) {
        searchInput.addEventListener('input', function(e) {
          const query = e.target.value.toLowerCase().trim();
          tableRows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
          });
        });
      }

      // 3. Filtro interactivo por selector de Inmueble Contextual
      const propertySelect = document.getElementById('propertySelect');
      if (propertySelect) {
        propertySelect.addEventListener('change', function(e) {
          const val = e.target.value;
          tableRows.forEach(row => {
            if (val === 'all') {
              row.style.display = '';
            } else if (val === '1') {
              row.style.display = row.textContent.includes('Torre Bella Vista') ? '' : 'none';
            } else if (val === '2') {
              row.style.display = row.textContent.includes('Complejo Las Acacias') ? '' : 'none';
            } else if (val === '3') {
              row.style.display = row.textContent.includes('Residencial Del Valle') ? '' : 'none';
            }
          });
        });
      }

      // 4. Interacción de botones de acción
      const btnNewInvoice = document.getElementById('btnNewInvoice');
      if (btnNewInvoice) {
        btnNewInvoice.addEventListener('click', function() {
          alert('Acceso al formulario de registro de factura (US-07)');
        });
      }

      const btnExport = document.getElementById('btnExportSummary');
      if (btnExport) {
        btnExport.addEventListener('click', function() {
          alert('Generando exportación de informe sumario consolidado (PDF/CSV)...');
        });
      }

      // Accesos rápidos
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
    })();

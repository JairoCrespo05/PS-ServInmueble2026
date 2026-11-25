(function () {
  const dashboard = document.getElementById('adminDashboardChart');
  if (!dashboard) return;

  const yearSelect = document.getElementById('adminChartYear');
  const metricSelect = document.getElementById('adminChartMetric');
  const monthsList = document.getElementById('adminChartMonths');
  const status = document.getElementById('adminChartStatus');
  const retryButton = document.getElementById('adminChartRetry');
  const monthLabels = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
  const metricLabels = {
    properties: 'inmuebles',
    owners: 'propietarios aprobados',
    associations: 'servicios asociados',
    service_types: 'tipos de servicio del catálogo',
  };
  let latestRequest = 0;

  function renderChart(values) {
    const maximum = Math.max(0, ...values);
    Array.from(monthsList.children).forEach(function (item, index) {
      const value = values[index];
      const bar = item.querySelector('.admin-chart-bar');
      item.querySelector('.admin-chart-value').textContent = String(value);
      bar.style.height = maximum > 0 ? Math.max(4, Math.round((value / maximum) * 120)) + 'px' : '0px';
      item.setAttribute('aria-label', monthLabels[index] + ': ' + value + ' altas');
    });
  }

  async function loadChart() {
    const requestId = ++latestRequest;
    const year = yearSelect.value;
    const metric = metricSelect.value;
    const url = new URL(dashboard.dataset.chartUrl, window.location.origin);
    url.searchParams.set('year', year);
    url.searchParams.set('metric', metric);
    dashboard.setAttribute('aria-busy', 'true');
    monthsList.hidden = true;
    retryButton.hidden = true;
    status.textContent = 'Cargando gráfico…';

    try {
      const response = await fetch(url.toString(), {
        headers: { Accept: 'application/json' },
        credentials: 'same-origin',
      });
      if (!response.ok) throw new Error('No se pudo cargar el gráfico.');

      const result = await response.json();
      if (
        !Array.isArray(result.values)
        || result.values.length !== 12
        || !result.values.every(function (value) {
          return Number.isInteger(value) && value >= 0;
        })
      ) {
        throw new Error('La respuesta del gráfico no es válida.');
      }
      if (requestId !== latestRequest) return;

      renderChart(result.values);
      monthsList.hidden = false;
      const total = result.values.reduce(function (sum, value) { return sum + value; }, 0);
      status.textContent = total === 0
        ? 'No hay altas de ' + metricLabels[metric] + ' para ' + year + '.'
        : 'Altas mensuales de ' + metricLabels[metric] + ' durante ' + year + '.';
    } catch {
      if (requestId !== latestRequest) return;
      status.textContent = 'No se pudo cargar el gráfico. Revisá tu conexión e intentá nuevamente.';
      retryButton.hidden = false;
    } finally {
      if (requestId === latestRequest) dashboard.setAttribute('aria-busy', 'false');
    }
  }

  yearSelect.addEventListener('change', loadChart);
  metricSelect.addEventListener('change', loadChart);
  retryButton.addEventListener('click', loadChart);
})();

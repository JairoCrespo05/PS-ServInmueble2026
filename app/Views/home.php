<?php
$recentInvoiceProperties = [];
foreach ($recentInvoices ?? [] as $invoice) {
    $recentInvoiceProperties[(int) $invoice['inmueble_id']] = $invoice['alias_nombre'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $isAdministrator ? 'Administración - ServiInmueble' : 'ServiInmueble 2026 — Home / Resumen General' ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin-home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
</head>
<body>

  <?= view('partials/sidebar', ['activeNav' => 'home', 'isAdministrator' => $isAdministrator]) ?>

  <!-- ================= CONTENEDOR PRINCIPAL ================= -->
  <div class="main-wrapper" id="mainWrapper">
    
    <!-- TOPBAR -->
    <header class="topbar">
      <?= view('partials/brand') ?>
      <?php if (! $isAdministrator): ?>
        <div class="topbar-left">
          <div class="search-box">
            <svg class="icon" style="color: var(--color-text-muted);" viewBox="0 0 24 24">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" id="globalSearch" placeholder="Buscar inmueble, servicio, número de factura..." autocomplete="off">
          </div>
        </div>
      <?php endif ?>

      <div class="topbar-right">
        <?php if (! $isAdministrator): ?>
          <!-- Selector de Inmueble Contextual -->
          <div class="property-picker">
            <svg class="icon" style="color: var(--color-primary-medium);" viewBox="0 0 24 24">
              <path d="M3 21h18"></path>
              <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
            </svg>
            <select id="propertySelect" aria-label="Seleccionar inmueble activo">
              <option value="all">Todos los Inmuebles (Cartera Global)</option>
              <?php foreach ($recentInvoiceProperties as $propertyId => $propertyName): ?>
                <option value="<?= esc((string) $propertyId, 'attr') ?>"><?= esc($propertyName) ?></option>
              <?php endforeach ?>
            </select>
          </div>

          <!-- Notificaciones -->
          <button class="icon-btn" title="Notificaciones" aria-label="Notificaciones">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            <span class="notification-dot"></span>
          </button>
        <?php endif ?>

        <!-- Perfil Usuario -->
        <div class="user-profile">
          <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1))) ?></div>
          <div class="user-info">
          <div class="user-name"><?= esc(session()->get('user_name') ?? '') ?></div>
            <div class="user-role"><?= esc(session()->get('user_role') ?? '') ?></div>
          </div>
        </div>
      </div>
    </header>

    <!-- ÁREA DE CONTENIDO -->
    <main class="content-area">

      <?php if ($isAdministrator): ?>
        <section class="page-header">
          <div>
            <div class="welcome-badge">Administración</div>
            <h1 class="page-title">Administración del sistema</h1>
            <p class="page-subtitle">Desde acá podés gestionar las cuentas y el catálogo de tipos de servicios.</p>
          </div>
        </section>

        <section class="admin-shortcuts" aria-label="Funciones administrativas">
          <article class="card admin-shortcut">
            <div>
              <h2 class="card-title">Gestión de usuarios</h2>
              <p class="page-subtitle">Consultá las cuentas registradas y revisá solicitudes pendientes.</p>
            </div>
            <a class="btn btn-primary" href="<?= site_url('usuarios') ?>">Abrir usuarios</a>
          </article>
          <article class="card admin-shortcut">
            <div>
              <h2 class="card-title">Tipos de servicios</h2>
              <p class="page-subtitle">Mantené el catálogo de servicios disponible para los propietarios.</p>
            </div>
            <a class="btn btn-secondary" href="<?= site_url('tipos-servicio') ?>">Abrir catálogo</a>
          </article>
        </section>

        <?php
        $summaryServiceTypes = [];
        $summaryPropertyStates = [];
        $summaryAccountStates = [];
        foreach ($propertySummary as $summaryProperty) {
            $propertyState = $summaryProperty['estado_actividad'] === 'Inactivo' ? 'Dado de baja' : trim((string) $summaryProperty['estado']);
            $accountState = trim((string) $summaryProperty['usuario_estado']);
            $summaryPropertyStates[mb_strtolower($propertyState)] = $propertyState;
            $summaryAccountStates[mb_strtolower($accountState)] = $accountState;

            foreach ($summaryProperty['servicios'] as $summaryService) {
                $serviceType = trim((string) ($summaryService['categoria'] ?? ''));
                if ($serviceType !== '') {
                    $summaryServiceTypes[mb_strtolower($serviceType)] = $serviceType;
                }
            }
        }
        asort($summaryServiceTypes, SORT_NATURAL | SORT_FLAG_CASE);
        asort($summaryPropertyStates, SORT_NATURAL | SORT_FLAG_CASE);
        asort($summaryAccountStates, SORT_NATURAL | SORT_FLAG_CASE);
        ?>

        <section class="card" aria-labelledby="systemPropertySummaryTitle">
          <div class="card-header">
            <div class="card-title-group">
              <div>
                <h2 class="card-title" id="systemPropertySummaryTitle">Inmuebles, usuarios y servicios</h2>
                <p class="page-subtitle">Relaciones registradas en el sistema, incluidas las bajas lógicas.</p>
              </div>
              <span class="badge-subtle"><?= count($propertySummary ?? []) ?> inmuebles</span>
            </div>
          </div>
          <div class="admin-summary-filter-panel">
            <div class="admin-summary-filter-heading">
              <div>
                <h3>Filtros</h3>
                <p>Combiná criterios para acotar el listado.</p>
              </div>
              <span class="admin-summary-result-count" id="adminSummaryResultCount" aria-live="polite"></span>
            </div>
            <div class="admin-summary-filters" role="group" aria-label="Filtros del listado de inmuebles">
              <label>
                <span>Tipo de servicio</span>
                <select id="adminSummaryServiceFilter">
                  <option value="">Todos</option>
                  <?php foreach ($summaryServiceTypes as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
                  <?php endforeach ?>
                </select>
              </label>
              <label>
                <span>Estado de inmueble</span>
                <select id="adminSummaryPropertyStateFilter">
                  <option value="">Todos</option>
                  <?php foreach ($summaryPropertyStates as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
                  <?php endforeach ?>
                </select>
              </label>
              <label>
                <span>Estado de cuenta</span>
                <select id="adminSummaryAccountStateFilter">
                  <option value="">Todos</option>
                  <?php foreach ($summaryAccountStates as $value => $label): ?>
                    <option value="<?= esc($value, 'attr') ?>"><?= esc($label) ?></option>
                  <?php endforeach ?>
                </select>
              </label>
              <button type="button" class="admin-summary-reset" id="adminSummaryResetFilters">Limpiar filtros</button>
            </div>
          </div>
          <div class="table-container">
            <table class="data-table" id="adminPropertySummaryTable">
              <thead>
                <tr>
                  <th scope="col">Inmueble</th>
                  <th scope="col">Estado del inmueble</th>
                  <th scope="col">Usuario asociado</th>
                  <th scope="col">Estado de cuenta</th>
                  <th scope="col">Servicios relacionados</th>
                </tr>
              </thead>
              <tbody>
                <?php if (($propertySummary ?? []) === []): ?>
                  <tr><td colspan="5">Todavía no hay inmuebles registrados.</td></tr>
                <?php else: ?>
                  <?php foreach ($propertySummary as $property): ?>
                    <tr
                      data-summary-row
                      data-property-state="<?= esc(mb_strtolower($property['estado_actividad'] === 'Inactivo' ? 'Dado de baja' : trim((string) $property['estado'])), 'attr') ?>"
                      data-account-state="<?= esc(mb_strtolower(trim((string) $property['usuario_estado'])), 'attr') ?>"
                    >
                      <td>
                        <strong><?= esc($property['alias_nombre']) ?></strong>
                        <br><small><?= esc($property['tipo']) ?></small>
                      </td>
                      <td><?= esc($property['estado'] ?? 'Desconocido') ?><?= $property['estado_actividad'] === 'Inactivo' ? ' (Dado de baja)' : '' ?></td>
                      <td>
                        <?= esc($property['usuario_nombre']) ?>
                        <?php if ($property['usuario_rol'] !== null): ?>
                          <br><small><?= esc($property['usuario_rol']) ?></small>
                        <?php endif ?>
                      </td>
                      <td><?= esc($property['usuario_estado']) ?></td>
                      <td>
                        <?php if ($property['servicios'] === []): ?>
                          Sin servicios asociados.
                        <?php else: ?>
                          <ul>
                            <?php foreach ($property['servicios'] as $service): ?>
                              <li data-service-category="<?= esc(mb_strtolower(trim((string) ($service['categoria'] ?? ''))), 'attr') ?>">
                                <?= esc($service['nombre']) ?>
                                <?php if ($service['categoria'] !== null): ?>· <?= esc($service['categoria']) ?><?php endif ?>
                                — <?= mb_strtolower(trim((string) $service['estado'])) === 'inactivo' ? 'De baja' : 'Vigente' ?>
                                · Estado operativo: <?= esc(trim((string) ($service['estado_vinculacion'] ?? '')) !== '' ? $service['estado_vinculacion'] : 'Desconocido') ?>
                              </li>
                            <?php endforeach ?>
                          </ul>
                        <?php endif ?>
                      </td>
                    </tr>
                  <?php endforeach ?>
                <?php endif ?>
                <tr id="adminSummaryNoResults" hidden>
                  <td colspan="5">No se encontraron inmuebles con esos filtros.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      <?php else: ?>

      <!-- Header de la Pantalla -->
      <section class="page-header">
        <div>
          <div class="welcome-badge">
            <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:var(--color-status-info);"></span>
            Sincronización Operativa Activa
          </div>
          <h1 class="page-title">Resumen y Control Operativo</h1>
          <p class="page-subtitle">Supervisión integral de consumos, vencimientos de servicios y estado de cuentas en tiempo real.</p>
        </div>

        <div class="header-actions">
          <!--
          <button class="btn btn-secondary" id="btnExportSummary">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Exportar Informe
          </button>
           -->
          <a class="btn btn-primary" id="btnNewInvoice" href="<?= site_url('facturas/nueva') ?>">
            <svg class="icon" viewBox="0 0 24 24">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            Cargar Factura
          </a>
        </div>
      </section>

      <!-- Alerta de Vencimiento Próximo -->
      <?php if ($upcomingInvoice !== null): ?>
        <?php
        $daysUntilDue = (int) $upcomingInvoice['dias_restantes'];
        $dueMessage = $daysUntilDue === 0
            ? 'vence hoy'
            : 'vence en ' . $daysUntilDue . ' ' . ($daysUntilDue === 1 ? 'día' : 'días');
        $upcomingInvoiceUrl = site_url('facturas/' . $upcomingInvoice['factura_id'] . '#payment-title');
        ?>
        <div class="banner-warning">
          <div class="banner-content">
            <svg class="icon" style="color: #D97706; width:22px; height:22px;" viewBox="0 0 24 24">
              <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
              <line x1="12" y1="9" x2="12" y2="13"></line>
              <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
            <div>
              <div class="banner-title">Aviso de Próximo Vencimiento</div>
              <div class="banner-text">
                La factura de <strong><?= esc($upcomingInvoice['alias_nombre'] . ' · ' . $upcomingInvoice['proveedor'] . ' — ' . $upcomingInvoice['servicio_nombre']) ?></strong>
                <?= esc($dueMessage) ?> (<?= esc(date('d/m/Y', strtotime($upcomingInvoice['proximo_vencimiento']))) ?>).
                Importe: <strong>$ <?= esc(number_format((float) $upcomingInvoice['monto_total'], 2, ',', '.')) ?> ARS</strong>.
              </div>
            </div>
          </div>
          <a class="btn-warning-action" id="upcomingInvoiceAction" href="<?= esc($upcomingInvoiceUrl, 'attr') ?>">Pagar Ahora</a>
        </div>
      <?php endif ?>

      <!-- Tarjetas de Métricas Principales (KPIs) -->
      <section class="kpi-grid">
        <!-- KPI 1 -->
        <div class="kpi-card">
          <div>
            <div class="kpi-label">Inmuebles en Gestión</div>
            <div class="kpi-value" id="kpiProperties">12 <small>propiedades</small></div>
            <div class="kpi-detail success">
              <svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline></svg>
              100% con servicios censados
            </div>
          </div>
          <div class="kpi-icon-wrap">
            <svg class="icon" viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path></svg>
          </div>
        </div>

        <!-- KPI 2 -->
        <div class="kpi-card">
          <div>
            <div class="kpi-label">Servicios Activos</div>
            <div class="kpi-value" id="kpiServices">38 <small>contratos</small></div>
            <div class="kpi-detail" style="color:var(--color-text-muted);">
              Luz, Gas, Agua, Expensas
            </div>
          </div>
          <div class="kpi-icon-wrap">
            <svg class="icon" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
          </div>
        </div>

        <!-- KPI 3 -->
        <div class="kpi-card">
          <div>
            <div class="kpi-label">A Pagar Este Mes</div>
            <div class="kpi-value" id="kpiToPay">$ 84.350 <small>ARS</small></div>
            <div class="kpi-detail warning">
              <svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
              2 facturas pendientes
            </div>
          </div>
          <div class="kpi-icon-wrap" style="color:var(--color-status-warning); background:var(--color-status-warning-bg);">
            <svg class="icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
          </div>
        </div>

        <!-- KPI 4 -->
        <div class="kpi-card">
          <div>
            <div class="kpi-label">Tasa de Cumplimiento</div>
            <div class="kpi-value" id="kpiCompliance">96.4%</div>
            <div class="kpi-detail success">
              <svg class="icon" style="width:14px;height:14px;" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
              Sin mora punitoria devengada
            </div>
          </div>
          <div class="kpi-icon-wrap" style="color:var(--color-status-success); background:var(--color-status-success-bg);">
            <svg class="icon" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
          </div>
        </div>
      </section>

      <!-- Layout Central en 2 Columnas -->
      <section class="dashboard-layout">
        
        <!-- Columna Izquierda: Tabla de Facturas Recientes y Vencimientos -->
        <div class="card">
          <div class="card-header">
            <div class="card-title-group">
              <h2 class="card-title">Facturación y Vencimientos Recientes</h2>
              <span class="badge-subtle"><?= count($recentInvoices) ?> <?= count($recentInvoices) === 1 ? 'factura' : 'facturas' ?></span>
            </div>
            <a href="<?= site_url('facturas') ?>" class="card-action-link" id="linkViewAllInvoices">Ver todas las facturas →</a>
          </div>

          <div class="table-container">
            <table class="data-table" id="invoicesTable">
              <thead>
                <tr>
                  <th>Inmueble / Suministro</th>
                  <th>Período</th>
                  <th>Vencimiento</th>
                  <th>Monto</th>
                  <th>Estado</th>
                  <th style="text-align: right;">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <?php if ($recentInvoices === []): ?>
                  <tr>
                    <td colspan="6">Todavía no hay facturas cargadas. <a href="<?= site_url('facturas/nueva') ?>">Cargar factura</a>.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentInvoices as $invoice): ?>
                    <?php
                    $status = trim((string) $invoice['estado_factura']);
                    $statusClass = match (mb_strtolower($status)) {
                        'pagada' => 'paid',
                        'pendiente' => 'pending',
                        default => '',
                    };
                    $invoiceUrl = site_url('facturas/' . $invoice['factura_id']);
                    $consumption = $invoice['consumo_periodo'] === null
                        ? ''
                        : number_format((float) $invoice['consumo_periodo'], 3, ',', '.') . ' '
                            . (string) ($invoice['unidad_medida_std'] ?? '');
                    ?>
                    <tr data-invoice-row data-property-id="<?= esc((string) $invoice['inmueble_id'], 'attr') ?>">
                      <td>
                        <div class="property-cell">
                          <span class="property-name"><?= esc($invoice['alias_nombre']) ?></span>
                          <span class="service-tag">
                            <svg class="icon" style="width:12px;height:12px;color:var(--color-status-info);" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                            <?= esc($invoice['proveedor'] . ' · ' . $invoice['servicio_nombre'] . ($consumption !== '' ? ' · ' . $consumption : '')) ?>
                          </span>
                        </div>
                      </td>
                      <td><?= esc(date('m/Y', strtotime($invoice['periodo_facturacion']))) ?></td>
                      <td><strong><?= esc(date('d/m/Y', strtotime($invoice['fecha_primer_vencimiento']))) ?></strong></td>
                      <td><strong>$ <?= esc(number_format((float) $invoice['monto_total'], 2, ',', '.')) ?></strong> ARS</td>
                      <td>
                        <span class="status-badge <?= esc($statusClass, 'attr') ?>">
                          <span class="dot"></span> <?= esc($status) ?>
                        </span>
                      </td>
                      <td style="text-align: right;">
                        <div class="action-icons" style="justify-content: flex-end;">
                          <a class="table-icon-btn" href="<?= esc($invoiceUrl, 'attr') ?>" title="Ver detalle de factura" aria-label="Ver detalle de factura">
                            <svg class="icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                          </a>
                          <?php if (mb_strtolower($status) === 'pendiente'): ?>
                            <a class="table-icon-btn" href="<?= esc($invoiceUrl . '#payment-title', 'attr') ?>" title="Registrar pago" aria-label="Registrar pago">
                              <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2-2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            </a>
                          <?php endif ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach ?>
                <?php endif ?>
                <tr id="invoiceFilterNoResults" hidden>
                  <td colspan="6">No se encontraron facturas con esos filtros.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Columna Derecha: Gráfico de Consumo Semestral y Accesos Rápidos -->
        <div class="side-stack">

          <!-- Gráfico de Consumos (Barras CSS Nativas) -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">Evolución de Consumo Eléctrico</h2>
              <span class="badge-subtle">Últimos 6 meses</span>
            </div>
            
            <div class="chart-container">
              <div class="chart-bars-wrap">
                <div class="bar-group">
                  <div class="bar-pill" style="height: 55%;" title="Dic: 280 kWh"></div>
                  <span class="bar-label">Dic</span>
                </div>
                <div class="bar-group">
                  <div class="bar-pill" style="height: 70%;" title="Ene: 360 kWh"></div>
                  <span class="bar-label">Ene</span>
                </div>
                <div class="bar-group">
                  <div class="bar-pill" style="height: 85%;" title="Feb: 410 kWh"></div>
                  <span class="bar-label">Feb</span>
                </div>
                <div class="bar-group">
                  <div class="bar-pill" style="height: 60%;" title="Mar: 310 kWh"></div>
                  <span class="bar-label">Mar</span>
                </div>
                <div class="bar-group">
                  <div class="bar-pill" style="height: 50%;" title="Abr: 290 kWh"></div>
                  <span class="bar-label">Abr</span>
                </div>
                <div class="bar-group">
                  <div class="bar-pill active" style="height: 65%;" title="May: 325 kWh (Actual)"></div>
                  <span class="bar-label" style="font-weight:700;color:var(--color-primary-medium);">May</span>
                </div>
              </div>

              <div class="chart-summary">
                <div class="summary-item">
                  <span class="summary-key">Consumo Promedio</span>
                  <span class="summary-val">329 kWh / mes</span>
                </div>
                <div class="summary-item" style="text-align: right;">
                  <span class="summary-key">Variación vs Abr</span>
                  <span class="summary-val" style="color:var(--color-status-success);">+12% Estacional</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Accesos Rápidos Operativos -->
          <div class="card">
            <div class="card-header">
              <h2 class="card-title">Acciones Frecuentes</h2>
            </div>
            <div class="shortcuts-grid">
              <a href="#" class="shortcut-card" id="scAddProperty">
                <div class="shortcut-icon">
                  <svg class="icon" viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="10" y1="11" x2="14" y2="11"></line></svg>
                </div>
                <div class="shortcut-title">+ Nuevo Inmueble</div>
                <div class="shortcut-desc">Alta en padrón catastral</div>
              </a>

              <a href="#" class="shortcut-card" id="scAddService">
                <div class="shortcut-icon">
                  <svg class="icon" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                </div>
                <div class="shortcut-title">Asociar Suministro</div>
                <div class="shortcut-desc">Vincular contrato (US-06)</div>
              </a>

              <a href="#" class="shortcut-card" id="scReports">
                <div class="shortcut-icon">
                  <svg class="icon" viewBox="0 0 24 24"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                </div>
                <div class="shortcut-title">Informes de Pago</div>
                <div class="shortcut-desc">Listado tabular US-09</div>
              </a>

              <a href="#" class="shortcut-card" id="scSupport">
                <div class="shortcut-icon">
                  <svg class="icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <div class="shortcut-title">Centro de Ayuda</div>
                <div class="shortcut-desc">Guía y soporte técnico</div>
              </a>
            </div>

            <div class="info-box">
              <svg class="icon" style="color:var(--color-primary-medium); width:20px;height:20px;" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="16" x2="12" y2="12"></line>
                <line x1="12" y1="8" x2="12.01" y2="8"></line>
              </svg>
              <span>El sistema audita automáticamente saltos atípicos de lectura física para prevenir facturaciones erróneas de los prestadores.</span>
            </div>
          </div>

        </div>

      </section>

      <?php endif ?>

    </main>

    <!-- FOOTER -->
    <footer class="app-footer">
      <div>© 2026 ServiInmueble Corp. Todos los derechos reservados. Arquitectura y Gestión Operativa.</div>
      <div class="footer-links">
        <a href="#">Términos del Servicio</a>
        <a href="#">Políticas de Privacidad</a>
        <a href="#">Centro de Ayuda</a>
      </div>
    </footer>

  </div>

  <!-- ================= JAVASCRIPT VANILLA PURO ================= -->
  <script src="<?= base_url('js/home.js') ?>"></script>
</body>
</html>

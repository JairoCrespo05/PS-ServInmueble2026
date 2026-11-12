<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ServiInmueble 2026 — Home / Resumen General</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
</head>
<body>

  <!-- ================= SIDEBAR PERSISTENTE ================= -->
  <aside class="sidebar" id="sidebar">
    <div>
      <!-- Header Brand & Collapse Button -->
      <div class="sidebar-header">
        <a href="#" class="brand-group">
          <div class="brand-logo-badge">SI</div>
          <span class="brand-title">ServiInmueble</span>
        </a>
        <button class="btn-toggle-sidebar" id="btnToggleSidebar" title="Colapsar o expandir barra lateral">
          <svg class="icon" viewBox="0 0 24 24" id="collapseArrow">
            <polyline points="15 18 9 12 15 6"></polyline>
          </svg>
        </button>
        <button class="expand-mini-btn" id="btnMiniExpand" title="Expandir menú">
          <svg class="icon" viewBox="0 0 24 24">
            <polyline points="9 18 15 12 9 6"></polyline>
          </svg>
        </button>
      </div>

      <!-- Navigation Content -->
      <div class="sidebar-content">
        <div class="nav-section-title">Gestión Patrimonial</div>
        <ul class="nav-list">
          <li>
            <a href="#" class="nav-link active" title="Inicio / Resumen">
              <svg class="icon" viewBox="0 0 24 24">
                <rect x="3" y="3" width="7" height="7"></rect>
                <rect x="14" y="3" width="7" height="7"></rect>
                <rect x="14" y="14" width="7" height="7"></rect>
                <rect x="3" y="14" width="7" height="7"></rect>
              </svg>
              <span class="nav-text">Inicio / Resumen</span>
            </a>
          </li>
          <li>
            <a href="#" class="nav-link" title="Inmuebles (ABMC)">
              <svg class="icon" viewBox="0 0 24 24">
                <path d="M3 21h18"></path>
                <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
                <path d="M9 9h1"></path>
                <path d="M14 9h1"></path>
                <path d="M9 13h1"></path>
                <path d="M14 13h1"></path>
                <path d="M9 17h1"></path>
                <path d="M14 17h1"></path>
              </svg>
              <span class="nav-text">Inmuebles (ABMC)</span>
            </a>
          </li>
          <li>
            <a href="#" class="nav-link" title="Servicios Asociados">
              <svg class="icon" viewBox="0 0 24 24">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
              </svg>
              <span class="nav-text">Servicios Asociados</span>
            </a>
          </li>
          <li>
            <a href="#" class="nav-link" title="Facturas y Pagos">
              <svg class="icon" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <span class="nav-text">Facturas y Pagos</span>
            </a>
          </li>
          <li>
            <a href="#" class="nav-link" title="Informes (US-09)">
              <svg class="icon" viewBox="0 0 24 24">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
              </svg>
              <span class="nav-text">Informes (US-09)</span>
            </a>
          </li>
          <?php if ($isAdministrator): ?>
            <li>
              <a href="#gestion-usuarios" class="nav-link" title="Gestión de usuarios">
                <svg class="icon" viewBox="0 0 24 24">
                  <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                  <circle cx="9" cy="7" r="4"></circle>
                  <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                  <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                <span class="nav-text">Gestión de usuarios</span>
              </a>
            </li>
          <?php endif ?>
        </ul>

      </div>
    </div>

    <!-- Sidebar Footer -->
    <div class="sidebar-footer">
      <ul class="nav-list">
        <li>
          <form method="post" action="<?= base_url('logout') ?>">
            <?= csrf_field() ?>
            <button type="submit" class="nav-link logout-button" title="Cerrar Sesión">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
              <polyline points="16 17 21 12 16 7"></polyline>
              <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span class="nav-text">Cerrar Sesión</span>
            </button>
          </form>
        </li>
      </ul>
    </div>
  </aside>

  <!-- ================= CONTENEDOR PRINCIPAL ================= -->
  <div class="main-wrapper" id="mainWrapper">
    
    <!-- TOPBAR -->
    <header class="topbar">
      <div class="topbar-left">
        <div class="search-box">
          <svg class="icon" style="color: var(--color-text-muted);" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="globalSearch" placeholder="Buscar inmueble, servicio, número de factura..." autocomplete="off">
        </div>
      </div>

      <div class="topbar-right">
        <!-- Selector de Inmueble Contextual -->
        <div class="property-picker">
          <svg class="icon" style="color: var(--color-primary-medium);" viewBox="0 0 24 24">
            <path d="M3 21h18"></path>
            <path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path>
          </svg>
          <select id="propertySelect" aria-label="Seleccionar inmueble activo">
            <option value="all">Todos los Inmuebles (Cartera Global)</option>
            <option value="1" selected>Torre Bella Vista (Unidad 402)</option>
            <option value="2">Complejo Las Acacias (Local 12)</option>
            <option value="3">Residencial Del Valle (Casa 7)</option>
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

        <!-- Perfil Usuario -->
        <div class="user-profile">
          <div class="user-avatar"><?= esc(strtoupper(substr($user['nombre'] ?? 'U', 0, 1))) ?></div>
          <div class="user-info">
          <div class="user-name"><?= esc($user['nombre'] ?? '') ?></div>
            <div class="user-role"><?= esc($user['role'] ?? '') ?></div>
          </div>
        </div>
      </div>
    </header>

    <!-- ÁREA DE CONTENIDO -->
    <main class="content-area">

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
          <button class="btn btn-secondary" id="btnExportSummary">
            <svg class="icon" viewBox="0 0 24 24">
              <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
              <polyline points="7 10 12 15 17 10"></polyline>
              <line x1="12" y1="15" x2="12" y2="3"></line>
            </svg>
            Exportar Informe
          </button>
          <button class="btn btn-primary" id="btnNewInvoice">
            <svg class="icon" viewBox="0 0 24 24">
              <line x1="12" y1="5" x2="12" y2="19"></line>
              <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            + Cargar Factura (US-07)
          </button>
        </div>
      </section>

      <!-- Alerta de Vencimiento Próximo -->
      <div class="banner-warning">
        <div class="banner-content">
          <svg class="icon" style="color: #D97706; width:22px; height:22px;" viewBox="0 0 24 24">
            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
            <line x1="12" y1="9" x2="12" y2="13"></line>
            <line x1="12" y1="17" x2="12.01" y2="17"></line>
          </svg>
          <div>
            <div class="banner-title">Aviso de Próximo Vencimiento (US-12)</div>
            <div class="banner-text">La factura de <strong>Edenor — Electricidad (Cta: 0839-291847)</strong> vence en 4 días hábiles ($ 38.450 ARS).</div>
          </div>
        </div>
        <button class="btn-warning-action" onclick="alert('Redirigiendo a registro de pago / comprobante...')">Pagar Ahora</button>
      </div>

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
              <span class="badge-subtle">Mayo 2026</span>
            </div>
            <a href="#" class="card-action-link" id="linkViewAllInvoices">Ver todas las facturas →</a>
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
                <tr>
                  <td>
                    <div class="property-cell">
                      <span class="property-name">Torre Bella Vista (402)</span>
                      <span class="service-tag">
                        <svg class="icon" style="width:12px;height:12px;color:var(--color-status-warning);" viewBox="0 0 24 24"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon></svg>
                        Edenor • 325 kWh
                      </span>
                    </div>
                  </td>
                  <td>May 2026</td>
                  <td><strong>18/05/2026</strong></td>
                  <td><strong>$ 38.450</strong> ARS</td>
                  <td>
                    <span class="status-badge pending">
                      <span class="dot"></span> Pendiente
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <div class="action-icons" style="justify-content: flex-end;">
                      <button class="table-icon-btn" title="Ver detalle de factura">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                      </button>
                      <button class="table-icon-btn" title="Cargar comprobante de pago">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td>
                    <div class="property-cell">
                      <span class="property-name">Torre Bella Vista (402)</span>
                      <span class="service-tag">
                        <svg class="icon" style="width:12px;height:12px;color:var(--color-status-info);" viewBox="0 0 24 24"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
                        AySA Agua • Bimestral
                      </span>
                    </div>
                  </td>
                  <td>May 2026</td>
                  <td><strong>22/05/2026</strong></td>
                  <td><strong>$ 12.900</strong> ARS</td>
                  <td>
                    <span class="status-badge pending">
                      <span class="dot"></span> Pendiente
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <div class="action-icons" style="justify-content: flex-end;">
                      <button class="table-icon-btn" title="Ver detalle de factura">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                      </button>
                      <button class="table-icon-btn" title="Cargar comprobante de pago">
                        <svg class="icon" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td>
                    <div class="property-cell">
                      <span class="property-name">Complejo Las Acacias (Local 12)</span>
                      <span class="service-tag">
                        <svg class="icon" style="width:12px;height:12px;color:#0284C7;" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path></svg>
                        Expensas Ordinarias
                      </span>
                    </div>
                  </td>
                  <td>Abr 2026</td>
                  <td>10/05/2026</td>
                  <td>$ 52.800 ARS</td>
                  <td>
                    <span class="status-badge paid">
                      <span class="dot"></span> Pagado
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <div class="action-icons" style="justify-content: flex-end;">
                      <button class="table-icon-btn" title="Descargar comprobante adjunto">
                        <svg class="icon" viewBox="0 0 24 24"><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                      </button>
                    </div>
                  </td>
                </tr>

                <tr>
                  <td>
                    <div class="property-cell">
                      <span class="property-name">Residencial Del Valle (Casa 7)</span>
                      <span class="service-tag">
                        <svg class="icon" style="width:12px;height:12px;color:#EA580C;" viewBox="0 0 24 24"><path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"></path></svg>
                        Metrogas • 48 m³
                      </span>
                    </div>
                  </td>
                  <td>Abr 2026</td>
                  <td>06/05/2026</td>
                  <td>$ 14.200 ARS</td>
                  <td>
                    <span class="status-badge paid">
                      <span class="dot"></span> Pagado
                    </span>
                  </td>
                  <td style="text-align: right;">
                    <div class="action-icons" style="justify-content: flex-end;">
                      <button class="table-icon-btn" title="Descargar comprobante adjunto">
                        <svg class="icon" viewBox="0 0 24 24"><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                      </button>
                    </div>
                  </td>
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

      <?php if ($isAdministrator): ?>
        <section class="user-management" id="gestion-usuarios" aria-labelledby="userManagementTitle">
          <div class="page-header">
            <div>
              <div class="welcome-badge">Administración</div>
              <h2 class="page-title" id="userManagementTitle">Gestión de usuarios</h2>
              <p class="page-subtitle">Revisá las solicitudes pendientes y consultá el listado general de cuentas.</p>
            </div>
          </div>

          <?php if (! empty($adminMessage)): ?>
            <div class="access-alert <?= esc($adminMessage['type'], 'attr') ?>" role="status">
              <?= esc($adminMessage['text']) ?>
            </div>
          <?php endif ?>

          <section class="card" aria-labelledby="pendingUsersTitle">
            <div class="card-header">
              <div class="card-title-group">
                <h3 class="card-title" id="pendingUsersTitle">Solicitudes pendientes</h3>
                <span class="badge-subtle"><?= count($pendingUsers) ?></span>
              </div>
            </div>
            <div class="table-container">
              <table class="data-table" id="pending-users-table">
                <thead>
                  <tr>
                    <th scope="col">Nombre y apellido</th>
                    <th scope="col">Correo electrónico</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Fecha de registro</th>
                    <th scope="col">Acción</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($pendingUsers === []): ?>
                    <tr>
                      <td colspan="5">No hay solicitudes pendientes.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($pendingUsers as $pendingUser): ?>
                      <tr>
                        <td><?= esc(trim($pendingUser['nombre'] . ' ' . $pendingUser['apellido'])) ?></td>
                        <td><?= esc($pendingUser['email']) ?></td>
                        <td><?= esc($pendingUser['telefono'] ?: 'No informado') ?></td>
                        <td><?= esc($pendingUser['created_at'] ?? 'Sin fecha') ?></td>
                        <td>
                          <form method="post" action="<?= base_url('admin/solicitudes/' . (int) $pendingUser['id'] . '/aprobar') ?>">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary">Aprobar como Propietario</button>
                          </form>
                        </td>
                      </tr>
                    <?php endforeach ?>
                  <?php endif ?>
                </tbody>
              </table>
            </div>
          </section>

          <section class="card" aria-labelledby="allUsersTitle">
            <div class="card-header">
              <div class="card-title-group">
                <h3 class="card-title" id="allUsersTitle">Listado general de usuarios</h3>
                <span class="badge-subtle"><?= count($allUsers) ?></span>
              </div>
            </div>
            <p class="page-subtitle">Incluye todas las cuentas, también las que siguen pendientes de aprobación.</p>
            <div class="table-container">
              <table class="data-table" id="all-users-table">
                <thead>
                  <tr>
                    <th scope="col">Nombre y apellido</th>
                    <th scope="col">Correo electrónico</th>
                    <th scope="col">Teléfono</th>
                    <th scope="col">Rol</th>
                    <th scope="col">Fecha de registro</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if ($allUsers === []): ?>
                    <tr>
                      <td colspan="5">Todavía no hay usuarios registrados.</td>
                    </tr>
                  <?php else: ?>
                    <?php foreach ($allUsers as $listedUser): ?>
                      <tr>
                        <td><?= esc(trim($listedUser['nombre'] . ' ' . $listedUser['apellido'])) ?></td>
                        <td><?= esc($listedUser['email']) ?></td>
                        <td><?= esc($listedUser['telefono'] ?: 'No informado') ?></td>
                        <td><?= esc($listedUser['role'] ?? 'Sin rol asignado') ?></td>
                        <td><?= esc($listedUser['created_at'] ?? 'Sin fecha') ?></td>
                      </tr>
                    <?php endforeach ?>
                  <?php endif ?>
                </tbody>
              </table>
            </div>
          </section>
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

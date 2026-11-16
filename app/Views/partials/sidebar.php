<?php

$activeNav = $activeNav ?? 'home';
$isAdministrator = $isAdministrator ?? false;

?>
<aside class="sidebar" id="sidebar">
  <div>
    <div class="sidebar-header">
      <a href="<?= site_url('home') ?>" class="brand-group">
        <span class="brand-logo-badge">SI</span>
        <span class="brand-title">ServiInmueble</span>
      </a>
      <button class="btn-toggle-sidebar" id="btnToggleSidebar" type="button" title="Colapsar barra lateral" aria-label="Colapsar barra lateral">
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
          <polyline points="15 18 9 12 15 6"></polyline>
        </svg>
      </button>
      <button class="expand-mini-btn" id="btnMiniExpand" type="button" title="Expandir menú" aria-label="Expandir menú">
        <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
          <polyline points="9 18 15 12 9 6"></polyline>
        </svg>
      </button>
    </div>

    <div class="sidebar-content">
      <div class="nav-section-title"><?= $isAdministrator ? 'Administración' : 'Gestión Patrimonial' ?></div>
      <ul class="nav-list">
        <li>
          <a href="<?= site_url('home') ?>" class="nav-link<?= $activeNav === 'home' ? ' active' : '' ?>" title="Inicio / Resumen"<?= $activeNav === 'home' ? ' aria-current="page"' : '' ?>>
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
              <rect x="3" y="3" width="7" height="7"></rect>
              <rect x="14" y="3" width="7" height="7"></rect>
              <rect x="14" y="14" width="7" height="7"></rect>
              <rect x="3" y="14" width="7" height="7"></rect>
            </svg>
            <span class="nav-text">Inicio / Resumen</span>
          </a>
        </li>
        <?php if ($isAdministrator): ?>
          <li>
            <a href="<?= site_url('tipos-servicio') ?>" class="nav-link<?= $activeNav === 'tipos-servicio' ? ' active' : '' ?>" title="Tipos de servicios"<?= $activeNav === 'tipos-servicio' ? ' aria-current="page"' : '' ?>>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
              </svg>
              <span class="nav-text">Tipos de servicios</span>
            </a>
          </li>
          <li>
            <a href="<?= site_url('usuarios') ?>" class="nav-link<?= $activeNav === 'usuarios' ? ' active' : '' ?>" title="Gestión de usuarios"<?= $activeNav === 'usuarios' ? ' aria-current="page"' : '' ?>>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
              </svg>
              <span class="nav-text">Gestión de usuarios</span>
            </a>
          </li>
        <?php else: ?>
          <li>
            <a href="<?= site_url('inmuebles') ?>" class="nav-link<?= $activeNav === 'inmuebles' ? ' active' : '' ?>" title="Inmuebles (ABMC)"<?= $activeNav === 'inmuebles' ? ' aria-current="page"' : '' ?>>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
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
            <a href="<?= site_url('servicios/asociaciones') ?>" class="nav-link<?= $activeNav === 'servicios' ? ' active' : '' ?>" title="Servicios Asociados"<?= $activeNav === 'servicios' ? ' aria-current="page"' : '' ?>>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
              </svg>
              <span class="nav-text">Servicios Asociados</span>
            </a>
          </li>
          <li>
            <a href="<?= site_url('facturas') ?>" class="nav-link<?= $activeNav === 'facturas' ? ' active' : '' ?>" title="Registrar factura"<?= $activeNav === 'facturas' ? ' aria-current="page"' : '' ?>>
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
                <polyline points="10 9 9 9 8 9"></polyline>
              </svg>
              <span class="nav-text">Registrar factura</span>
            </a>
          </li>
          <li>
            <a href="#" class="nav-link" title="Informes (US-09)">
              <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
                <line x1="18" y1="20" x2="18" y2="10"></line>
                <line x1="12" y1="20" x2="12" y2="4"></line>
                <line x1="6" y1="20" x2="6" y2="14"></line>
              </svg>
              <span class="nav-text">Informes (US-09)</span>
            </a>
          </li>
        <?php endif ?>
      </ul>
    </div>
  </div>

  <div class="sidebar-footer">
    <ul class="nav-list">
      <li>
        <form method="post" action="<?= site_url('logout') ?>">
          <?= csrf_field() ?>
          <button type="submit" class="nav-link logout-button" title="Cerrar Sesión">
            <svg class="icon" viewBox="0 0 24 24" aria-hidden="true">
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

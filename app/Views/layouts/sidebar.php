<?php

$activeSection = $activeSection ?? '';

?>
<aside class="sidebar">
  <div>
    <div class="brand"><span class="brand-mark icon">apartment</span>ServiInmueble</div>
    <div class="role">Propietario / Gestor</div>
    <nav class="nav" aria-label="Navegación principal">
      <a class="<?= $activeSection === 'inmuebles' ? 'active' : '' ?>" href="<?= site_url('inmuebles') ?>"<?= $activeSection === 'inmuebles' ? ' aria-current="page"' : '' ?>>
        <span class="icon">apartment</span><span class="nav-label">Inmuebles</span>
      </a>
      <a class="<?= $activeSection === 'servicios-asociados' ? 'active' : '' ?>" href="<?= site_url('servicios') ?>"<?= $activeSection === 'servicios-asociados' ? ' aria-current="page"' : '' ?>>
        <span class="icon">power</span><span class="nav-label">Servicios asociados</span>
      </a>
      <a class="disabled" href="#" aria-disabled="true">
        <span class="icon">receipt_long</span><span class="nav-label">Facturas y pagos</span>
      </a>
      <a class="disabled" href="#" aria-disabled="true">
        <span class="icon">bar_chart</span><span class="nav-label">Informes</span>
      </a>
      <div class="nav-section">Administración</div>
      <a class="<?= $activeSection === 'catalogo-servicios' ? 'active' : '' ?>" href="<?= site_url('tipos-servicio') ?>"<?= $activeSection === 'catalogo-servicios' ? ' aria-current="page"' : '' ?>>
        <span class="icon">home_repair_service</span><span class="nav-label">Catálogo de servicios</span>
      </a>
    </nav>
  </div>
  <div class="sidebar-bottom">Administración de inmuebles</div>
</aside>

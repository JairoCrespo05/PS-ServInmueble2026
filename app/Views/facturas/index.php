<?php

$formatAmount = static fn ($value): string => '$ ' . number_format((float) $value, 2, ',', '.');
$formatDate = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '—';
$formatPeriod = static fn ($value): string => $value ? date('m/Y', strtotime($value)) : '—';

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Facturas | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/servicios.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/facturas.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'facturas', 'isAdministrator' => false]) ?>
  <div class="workspace">
    <header class="topbar">
      <div class="top-brand"><span class="icon">domain</span>ServiInmueble</div>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Facturas</span>
      <div class="user-profile">
        <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1))) ?></div>
        <div class="user-info">
          <div class="user-name"><?= esc(session()->get('user_name') ?? '') ?></div>
          <div class="user-role"><?= esc(session()->get('user_role') ?? '') ?></div>
        </div>
      </div>
    </header>

    <main class="page">
      <section class="page-head">
        <div>
          <p class="eyebrow">Gestión patrimonial / Servicios</p>
          <h1>Facturas</h1>
          <p class="subtitle">Consultá las facturas y el detalle de cada servicio de tus inmuebles.</p>
        </div>
        <div class="actions">
          <a class="button" href="<?= site_url('facturas/informe') ?>"><span class="icon">assessment</span>Ver informe</a>
          <a class="button primary" href="<?= site_url('facturas/nueva') ?>"><span class="icon">add</span>Cargar factura</a>
        </div>
      </section>

      <?php if ($success): ?>
        <div class="notice" role="status"><?= esc($success) ?></div>
      <?php endif ?>
      <?php if ($error): ?>
        <div class="notice error" role="alert"><?= esc($error) ?></div>
      <?php endif ?>

      <?php if ($facturas === []): ?>
        <section class="service-card invoice-empty" aria-labelledby="invoice-empty-title">
          <span class="icon" aria-hidden="true">receipt_long</span>
          <h2 id="invoice-empty-title">Todavía no registraste facturas</h2>
          <p>Cuando cargues una factura de un servicio asociado a tus inmuebles, aparecerá en este listado.</p>
          <a class="button primary" href="<?= site_url('facturas/nueva') ?>">Cargar primera factura</a>
        </section>
      <?php else: ?>
        <section class="service-card invoice-list-card" aria-labelledby="invoice-list-title">
          <div class="invoice-list-head">
            <div>
              <h2 id="invoice-list-title">Facturas registradas</h2>
              <p>Ordenadas desde el período más reciente.</p>
            </div>
            <a class="button secondary" href="<?= site_url('facturas/informe') ?>"><span class="icon" aria-hidden="true">assessment</span>Pendientes y pagadas</a>
          </div>
          <div class="table-scroll" role="region" aria-label="Listado de facturas registradas" tabindex="0">
            <table class="invoice-table">
              <thead>
                <tr>
                  <th scope="col">Período</th>
                  <th scope="col">Inmueble y servicio</th>
                  <th scope="col">Importe</th>
                  <th scope="col">Vencimiento</th>
                  <th scope="col">Estado</th>
                  <th scope="col"><span class="visually-hidden">Acciones</span></th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($facturas as $factura): ?>
                  <tr>
                    <td><?= esc($formatPeriod($factura['periodo_facturacion'])) ?></td>
                    <td>
                      <strong><?= esc($factura['alias_nombre']) ?></strong>
                      <span class="invoice-secondary"><?= esc($factura['servicio_nombre'] . ' · ' . $factura['proveedor']) ?></span>
                    </td>
                    <td><?= esc($formatAmount($factura['monto_total'])) ?></td>
                    <td><?= esc($formatDate($factura['fecha_primer_vencimiento'])) ?></td>
                    <td><span class="invoice-status <?= $factura['estado_factura'] === 'Pagada' ? 'paid' : 'pending' ?>"><?= esc($factura['estado_factura']) ?></span></td>
                    <td><a class="invoice-detail-link" href="<?= site_url('facturas/' . $factura['factura_id']) ?>"><span class="icon" aria-hidden="true">visibility</span>Ver detalle<span class="visually-hidden"> de <?= esc($factura['servicio_nombre'] . ' ' . $formatPeriod($factura['periodo_facturacion'])) ?></span></a></td>
                  </tr>
                <?php endforeach ?>
              </tbody>
            </table>
          </div>
          <?php if ($pagerLinks !== ''): ?>
            <nav class="invoice-pagination" aria-label="Páginas de facturas"><?= $pagerLinks ?></nav>
          <?php endif ?>
        </section>
      <?php endif ?>
    </main>
  </div>
  <script src="<?= base_url('js/sidebar.js') ?>"></script>
</body>
</html>

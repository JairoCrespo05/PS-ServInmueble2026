<?php

$formatAmount = static fn ($value): string => '$ ' . number_format((float) $value, 2, ',', '.');
$formatDate = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '—';
$formatPeriod = static fn ($value): string => $value ? date('m/Y', strtotime($value)) : '—';

$renderRows = static function (array $invoices) use ($formatAmount, $formatDate, $formatPeriod): void {
    foreach ($invoices as $invoice): ?>
      <tr>
        <td><?= esc($formatPeriod($invoice['periodo_facturacion'])) ?></td>
        <td>
          <strong><?= esc($invoice['alias_nombre']) ?></strong>
          <span class="invoice-secondary"><?= esc($invoice['servicio_nombre'] . ' · ' . $invoice['proveedor']) ?></span>
        </td>
        <td><?= esc($formatAmount($invoice['monto_total'])) ?></td>
        <td><?= esc($formatDate($invoice['fecha_primer_vencimiento'])) ?></td>
        <td><a class="invoice-detail-link" href="<?= site_url('facturas/' . $invoice['factura_id']) ?>"><span class="icon" aria-hidden="true">visibility</span>Ver detalle</a></td>
      </tr>
    <?php endforeach;
};

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Informe de facturación | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/servicios.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/facturas.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'informe-facturas', 'isAdministrator' => false]) ?>
  <div class="workspace">
    <header class="topbar">
      <div class="top-brand"><span class="icon">domain</span>ServiInmueble</div>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Informe de facturación</span>
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
          <p class="eyebrow">Facturas / US-09</p>
          <h1>Informe de facturación</h1>
          <p class="subtitle">Importes de las facturas pendientes y pagadas de tus inmuebles.</p>
        </div>
        <div class="actions">
          <a class="button secondary" href="<?= site_url('facturas') ?>"><span class="icon" aria-hidden="true">arrow_back</span>Volver a facturas</a>
        </div>
      </section>

      <section class="invoice-report-totals" aria-label="Totales de facturación">
        <article class="service-card invoice-total-card pending">
          <div class="invoice-total-heading">
            <span class="invoice-total-icon icon" aria-hidden="true">schedule</span>
            <span class="invoice-total-label">Total pendiente</span>
          </div>
          <strong><?= esc($formatAmount($totalPendiente)) ?></strong>
          <span class="invoice-total-count"><?= esc(count($pendientes)) ?> facturas</span>
        </article>
        <article class="service-card invoice-total-card paid">
          <div class="invoice-total-heading">
            <span class="invoice-total-icon icon" aria-hidden="true">task_alt</span>
            <span class="invoice-total-label">Total pagado</span>
          </div>
          <strong><?= esc($formatAmount($totalPagado)) ?></strong>
          <span class="invoice-total-count"><?= esc(count($pagadas)) ?> facturas</span>
        </article>
      </section>

      <section class="service-card invoice-report-section" aria-labelledby="pending-invoices-title">
        <div class="invoice-list-head">
          <div class="invoice-report-heading">
            <span class="invoice-section-icon pending icon" aria-hidden="true">schedule</span>
            <div>
              <h2 id="pending-invoices-title">Facturas pendientes</h2>
              <p>Total: <strong><?= esc($formatAmount($totalPendiente)) ?></strong></p>
            </div>
          </div>
        </div>
        <?php if ($pendientes === []): ?>
          <p class="invoice-report-empty">No tenés facturas pendientes.</p>
        <?php else: ?>
          <div class="table-scroll" role="region" aria-label="Listado de facturas pendientes" tabindex="0">
            <table class="invoice-table">
              <thead><tr><th scope="col">Período</th><th scope="col">Inmueble y servicio</th><th scope="col">Importe</th><th scope="col">Vencimiento</th><th scope="col">Detalle</th></tr></thead>
              <tbody><?php $renderRows($pendientes) ?></tbody>
            </table>
          </div>
        <?php endif ?>
      </section>

      <section class="service-card invoice-report-section" aria-labelledby="paid-invoices-title">
        <div class="invoice-list-head">
          <div class="invoice-report-heading">
            <span class="invoice-section-icon paid icon" aria-hidden="true">task_alt</span>
            <div>
              <h2 id="paid-invoices-title">Facturas pagadas</h2>
              <p>Total: <strong><?= esc($formatAmount($totalPagado)) ?></strong></p>
            </div>
          </div>
        </div>
        <?php if ($pagadas === []): ?>
          <p class="invoice-report-empty">Todavía no tenés facturas pagadas.</p>
        <?php else: ?>
          <div class="table-scroll" role="region" aria-label="Listado de facturas pagadas" tabindex="0">
            <table class="invoice-table">
              <thead><tr><th scope="col">Período</th><th scope="col">Inmueble y servicio</th><th scope="col">Importe</th><th scope="col">Fecha de pago</th><th scope="col">Detalle</th></tr></thead>
              <tbody><?php $renderRows($pagadas) ?></tbody>
            </table>
          </div>
        <?php endif ?>
      </section>
    </main>
  </div>
  <script src="<?= base_url('js/sidebar.js') ?>"></script>
</body>
</html>

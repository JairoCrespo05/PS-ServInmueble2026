<?php

$formatAmount = static fn ($value): string => '$ ' . number_format((float) $value, 2, ',', '.');
$formatDate = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '—';

?>
<section class="invoice-dashboard" aria-labelledby="invoice-dashboard-title">
  <div class="invoice-dashboard-head">
    <div>
      <p class="eyebrow">Seguimiento</p>
      <h2 id="invoice-dashboard-title">Estado y vencimientos</h2>
      <p class="subtitle">Métricas y avisos para la búsqueda y el período seleccionado. El filtro de estado afecta los listados, no estas métricas.</p>
    </div>
  </div>
  <div class="invoice-dashboard-metrics">
    <article class="service-card invoice-metric-card pending">
      <div class="invoice-metric-heading">
        <span class="invoice-metric-icon icon" aria-hidden="true">schedule</span>
        <span class="invoice-metric-label">Pendientes</span>
      </div>
      <strong class="invoice-metric-value"><?= esc($dashboard['cantidadPendiente']) ?></strong>
      <span class="invoice-metric-help">Aún dentro del vencimiento</span>
    </article>
    <article class="service-card invoice-metric-card overdue">
      <div class="invoice-metric-heading">
        <span class="invoice-metric-icon icon" aria-hidden="true">warning</span>
        <span class="invoice-metric-label">Vencidas</span>
      </div>
      <strong class="invoice-metric-value"><?= esc($dashboard['cantidadVencida']) ?></strong>
      <span class="invoice-metric-help">Requieren atención</span>
    </article>
    <article class="service-card invoice-metric-card paid">
      <div class="invoice-metric-heading">
        <span class="invoice-metric-icon icon" aria-hidden="true">task_alt</span>
        <span class="invoice-metric-label">Pagadas</span>
      </div>
      <strong class="invoice-metric-value"><?= esc($dashboard['cantidadPagada']) ?></strong>
      <span class="invoice-metric-help">Pagos registrados</span>
    </article>
    <article class="service-card invoice-metric-card overdue-rate" aria-labelledby="invoice-overdue-chart-title">
      <div class="invoice-metric-heading">
        <span class="invoice-metric-icon icon" aria-hidden="true">pie_chart</span>
        <span class="invoice-metric-label" id="invoice-overdue-chart-title">Porcentaje de mora</span>
      </div>
      <?php $overdueRate = $dashboard['porcentajeMora'] ?? 0; ?>
      <div class="invoice-mora-chart-layout">
        <div
          class="invoice-mora-chart"
          role="img"
          aria-label="<?= $dashboard['porcentajeMora'] === null ? 'Sin facturas para calcular el porcentaje de mora' : esc(number_format($overdueRate, 1, ',', '.') . '% de las facturas están vencidas') ?>"
          style="--mora-porcentaje: <?= esc((string) $overdueRate, 'attr') ?>%"
        >
          <span><?= $dashboard['porcentajeMora'] === null ? '—' : esc(number_format($overdueRate, 1, ',', '.') . '%') ?></span>
        </div>
        <div class="invoice-mora-legend" aria-hidden="true">
          <span><i class="overdue"></i>Vencidas <strong><?= esc($dashboard['cantidadVencida']) ?></strong></span>
          <span><i class="not-overdue"></i>Sin mora <strong><?= esc($dashboard['cantidadPendiente'] + $dashboard['cantidadPagada']) ?></strong></span>
        </div>
      </div>
      <span class="invoice-metric-help">Sobre el total de facturas filtradas</span>
    </article>
  </div>
  <p class="invoice-dashboard-note">
    El estado vencida se calcula desde el segundo vencimiento cuando existe; si no, desde el primero.
    Los avisos de próximos vencimientos consideran los próximos 7 días.
  </p>
  <div class="invoice-alert-grid">
    <section class="service-card invoice-alert-card overdue" aria-labelledby="overdue-invoices-title">
      <div class="invoice-list-head">
        <div>
          <h3 id="overdue-invoices-title">Facturas vencidas</h3>
          <p>Ordenadas desde la que acumula más días de mora.</p>
        </div>
      </div>
      <?php if ($dashboard['vencidas'] === []): ?>
        <p class="invoice-report-empty" role="status">No hay facturas vencidas para estos filtros.</p>
      <?php else: ?>
        <ul class="invoice-alert-list">
          <?php foreach ($dashboard['vencidas'] as $invoice): ?>
            <li>
              <div class="invoice-alert-main">
                <strong><?= esc($invoice['alias_nombre']) ?> · <?= esc($invoice['servicio_nombre']) ?></strong>
                <span><?= esc($invoice['proveedor']) ?> · venció el <?= esc($formatDate($invoice['fecha_vencimiento_efectivo'])) ?></span>
              </div>
              <div class="invoice-alert-meta">
                <span class="invoice-status overdue"><?= esc($invoice['dias_mora']) ?> <?= $invoice['dias_mora'] === 1 ? 'día' : 'días' ?> de mora</span>
                <strong><?= esc($formatAmount($invoice['monto_total'])) ?></strong>
                <a class="invoice-detail-link" href="<?= site_url('facturas/' . $invoice['factura_id']) ?>" aria-label="Ver factura vencida de <?= esc($invoice['alias_nombre'] . ', ' . $invoice['servicio_nombre'], 'attr') ?>">
                  <span class="icon" aria-hidden="true">visibility</span>
                </a>
              </div>
            </li>
          <?php endforeach ?>
        </ul>
        <?php if ($dashboard['cantidadVencida'] > count($dashboard['vencidas'])): ?>
          <a class="invoice-alert-more" href="<?= site_url('facturas?estado=Vencida') ?>">Ver todas las facturas vencidas</a>
        <?php endif ?>
      <?php endif ?>
    </section>
    <section class="service-card invoice-alert-card upcoming" aria-labelledby="upcoming-invoices-title">
      <div class="invoice-list-head">
        <div>
          <h3 id="upcoming-invoices-title">Próximos vencimientos</h3>
          <p>Facturas pendientes que vencen dentro de los próximos 7 días.</p>
        </div>
      </div>
      <?php if ($dashboard['proximas'] === []): ?>
        <p class="invoice-report-empty" role="status">No hay vencimientos próximos para estos filtros.</p>
      <?php else: ?>
        <ul class="invoice-alert-list">
          <?php foreach ($dashboard['proximas'] as $invoice): ?>
            <li>
              <div class="invoice-alert-main">
                <strong><?= esc($invoice['alias_nombre']) ?> · <?= esc($invoice['servicio_nombre']) ?></strong>
                <span><?= esc($invoice['proveedor']) ?> · vence el <?= esc($formatDate($invoice['fecha_vencimiento_efectivo'])) ?></span>
              </div>
              <div class="invoice-alert-meta">
                <span class="invoice-status pending"><?= $invoice['dias_restantes'] === 0 ? 'Vence hoy' : 'En ' . esc($invoice['dias_restantes']) . ($invoice['dias_restantes'] === 1 ? ' día' : ' días') ?></span>
                <strong><?= esc($formatAmount($invoice['monto_total'])) ?></strong>
                <a class="invoice-detail-link" href="<?= site_url('facturas/' . $invoice['factura_id']) ?>" aria-label="Ver factura próxima a vencer de <?= esc($invoice['alias_nombre'] . ', ' . $invoice['servicio_nombre'], 'attr') ?>">
                  <span class="icon" aria-hidden="true">visibility</span>
                </a>
              </div>
            </li>
          <?php endforeach ?>
        </ul>
        <?php if ($dashboard['cantidadProxima'] > count($dashboard['proximas'])): ?>
          <a class="invoice-alert-more" href="<?= site_url('facturas?estado=Pendiente') ?>">Ver facturas pendientes</a>
        <?php endif ?>
      <?php endif ?>
    </section>
  </div>
</section>

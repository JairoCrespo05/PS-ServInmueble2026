<?php

$formatAmount = static fn ($value): string => '$ ' . number_format((float) $value, 2, ',', '.');
$formatDecimal = static fn ($value): string => $value === null ? '—' : number_format((float) $value, 3, ',', '.');
$formatDate = static fn ($value): string => $value ? date('d/m/Y', strtotime($value)) : '—';
$formatPeriod = static fn ($value): string => $value ? date('m/Y', strtotime($value)) : '—';
$paid = $factura['estado_factura'] === 'Pagada';
$paymentValue = static fn (string $field, string $fallback = ''): string => (string) ($formData[$field] ?? $fallback);

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Detalle de factura | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/servicios.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/facturas.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'facturas', 'isAdministrator' => false]) ?>
  <div class="workspace">
    <header class="topbar">
      <?= view('partials/brand') ?>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Detalle de factura</span>
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
          <p class="eyebrow"><a href="<?= site_url('facturas') ?>">Facturas</a> / Detalle</p>
          <h1><?= esc($factura['servicio_nombre']) ?> · <?= esc($formatPeriod($factura['periodo_facturacion'])) ?></h1>
          <p class="subtitle"><?= esc($factura['alias_nombre'] . ' — ' . $factura['proveedor']) ?></p>
        </div>
        <div class="actions">
          <a class="button secondary" href="<?= site_url('facturas') ?>">Volver al listado</a>
        </div>
      </section>

      <?php if ($success): ?><div class="notice" role="status"><?= esc($success) ?></div><?php endif ?>
      <?php if ($error): ?><div class="notice error" role="alert"><?= esc($error) ?></div><?php endif ?>

      <div class="invoice-detail-layout">
        <section class="service-card invoice-detail-card" aria-labelledby="invoice-detail-title">
          <div class="invoice-list-head">
            <h2 id="invoice-detail-title">Datos de la factura</h2>
            <span class="invoice-status <?= $paid ? 'paid' : 'pending' ?>"><?= esc($factura['estado_factura']) ?></span>
          </div>
          <dl class="invoice-detail-grid">
            <div><dt>Inmueble</dt><dd><?= esc($factura['alias_nombre']) ?></dd></div>
            <div><dt>Tipo de inmueble</dt><dd><?= esc($factura['tipo_inmueble']) ?></dd></div>
            <div><dt>Dirección</dt><dd><?= esc(trim($factura['calle'] . ' ' . $factura['numero'] . ($factura['piso_dpto'] ? ', ' . $factura['piso_dpto'] : '') . ', ' . $factura['ciudad'])) ?></dd></div>
            <div><dt>Servicio</dt><dd><?= esc($factura['servicio_nombre']) ?></dd></div>
            <div><dt>Proveedor</dt><dd><?= esc($factura['proveedor']) ?></dd></div>
            <div><dt>Período</dt><dd><?= esc($formatPeriod($factura['periodo_facturacion'])) ?></dd></div>
            <div><dt>Fecha de emisión</dt><dd><?= esc($formatDate($factura['fecha_emision'])) ?></dd></div>
            <div><dt>Primer vencimiento</dt><dd><?= esc($formatDate($factura['fecha_primer_vencimiento'])) ?></dd></div>
            <div><dt>Segundo vencimiento</dt><dd><?= esc($formatDate($factura['fecha_segundo_vencimiento'])) ?></dd></div>
            <div><dt>Monto total</dt><dd><?= esc($formatAmount($factura['monto_total'])) ?></dd></div>
            <div><dt>Consumo</dt><dd><?= esc($formatDecimal($factura['consumo_periodo']) . ' ' . $factura['unidad_medida_std']) ?></dd></div>
            <div><dt>Lectura anterior</dt><dd><?= esc($formatDecimal($factura['lectura_anterior'])) ?></dd></div>
            <div><dt>Lectura actual</dt><dd><?= esc($formatDecimal($factura['lectura_actual'])) ?></dd></div>
            <?php if ($paid): ?>
              <div><dt>Fecha de pago</dt><dd><?= esc($formatDate($factura['fecha_pago_realizado'])) ?></dd></div>
              <div><dt>Monto abonado</dt><dd><?= esc($formatAmount($factura['monto_abonado'])) ?></dd></div>
              <div><dt>Medio de pago</dt><dd><?= esc($factura['medio_pago'] ?? '—') ?></dd></div>
            <?php endif ?>
          </dl>
        </section>

        <?php if (! $paid): ?>
          <section class="service-card invoice-payment-card" aria-labelledby="payment-title">
            <div class="invoice-list-head">
              <div>
                <h2 id="payment-title">Registrar pago total</h2>
                <p>El importe abonado debe coincidir con el total de la factura.</p>
              </div>
            </div>
            <?php if ($errors !== []): ?>
              <div class="notice error invoice-error-summary" role="alert">
                <p>Revisá estos datos:</p>
                <ul><?php foreach ($errors as $message): ?><li><?= esc($message) ?></li><?php endforeach ?></ul>
              </div>
            <?php endif ?>
            <form method="post" action="<?= site_url('facturas/' . $factura['factura_id'] . '/pago') ?>" class="invoice-payment-form">
              <?= csrf_field() ?>
              <div class="field">
                <label for="fecha_pago_realizado">Fecha de pago <span aria-hidden="true">*</span></label>
                <input id="fecha_pago_realizado" name="fecha_pago_realizado" type="date" max="<?= esc(date('Y-m-d'), 'attr') ?>" value="<?= esc($paymentValue('fecha_pago_realizado'), 'attr') ?>" required>
              </div>
              <div class="field">
                <label for="monto_abonado">Monto abonado <span aria-hidden="true">*</span></label>
                <input id="monto_abonado" name="monto_abonado" type="number" min="0.01" max="9999999999.99" step="0.01" value="<?= esc($paymentValue('monto_abonado', (string) $factura['monto_total']), 'attr') ?>" required>
                <small>Importe total: <?= esc($formatAmount($factura['monto_total'])) ?>. No se admiten pagos parciales.</small>
              </div>
              <div class="field">
                <label for="medio_pago">Medio de pago <span aria-hidden="true">*</span></label>
                <select id="medio_pago" name="medio_pago" required>
                  <option value="">Seleccioná un medio</option>
                  <?php foreach ($mediosPago as $medio): ?>
                    <option value="<?= esc($medio, 'attr') ?>"<?= $paymentValue('medio_pago') === $medio ? ' selected' : '' ?>><?= esc($medio) ?></option>
                  <?php endforeach ?>
                </select>
              </div>
              <button class="button primary" type="submit">Registrar pago</button>
            </form>
          </section>
        <?php endif ?>
      </div>
    </main>
  </div>
  <script src="<?= base_url('js/sidebar.js') ?>"></script>
</body>
</html>

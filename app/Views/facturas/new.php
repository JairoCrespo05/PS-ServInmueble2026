<?php

$value = static fn (string $field): string => (string) ($formData[$field] ?? '');

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Nueva factura | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/servicios.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/facturas.css') ?>?v=2">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/modal-forms.css') ?>?v=4">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'facturas', 'isAdministrator' => false]) ?>
  <div class="workspace">
    <header class="topbar">
      <?= view('partials/brand') ?>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Nueva factura</span>
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
          <p class="eyebrow"><a href="<?= site_url('facturas') ?>">Facturas</a> / Alta</p>
          <h1>Cargar factura</h1>
          <p class="subtitle">Registrá los datos que figuran en la factura del servicio.</p>
        </div>
        <div class="actions">
          <a class="button secondary" href="<?= site_url('facturas') ?>">Volver a facturas</a>
        </div>
      </section>

      <?php if ($error): ?>
        <div class="notice error" role="alert"><?= esc($error) ?></div>
      <?php endif ?>

      <?php if ($errors !== []): ?>
        <div class="notice error invoice-error-summary" role="alert">
          <p>Revisá estos datos antes de guardar:</p>
          <ul>
            <?php foreach ($errors as $field => $message): ?>
              <li><?php if ($asociaciones !== []): ?><a href="#<?= esc($field, 'attr') ?>"><?= esc($message) ?></a><?php else: ?><?= esc($message) ?><?php endif ?></li>
            <?php endforeach ?>
          </ul>
        </div>
      <?php endif ?>

      <?php if ($asociaciones === []): ?>
        <section class="service-card invoice-empty">
          <span class="icon" aria-hidden="true">receipt_long</span>
          <h2>No tenés vínculos habilitados para facturar</h2>
          <p>Para registrar una factura necesitás un vínculo activo y un inmueble que no esté inactivo.</p>
          <a class="button primary" href="<?= site_url('servicios/asociaciones') ?>">Ir a servicios asociados</a>
        </section>
      <?php else: ?>
        <section class="service-card invoice-card" aria-labelledby="invoice-form-title">
          <div class="invoice-card-head">
            <h2 id="invoice-form-title">Datos de la factura</h2>
            <p><span aria-hidden="true">*</span> Campo obligatorio</p>
          </div>
          <form method="post" action="<?= site_url('facturas') ?>">
            <?= csrf_field() ?>
            <div class="invoice-form-grid">
              <section class="form-section" aria-labelledby="invoice-service-section">
              <h3 class="invoice-section-title form-section-title" id="invoice-service-section">Servicio y período</h3>
              <div class="field full">
                <label for="inmueble_servicio_id">Servicio asociado <span aria-hidden="true">*</span></label>
                <select id="inmueble_servicio_id" name="inmueble_servicio_id" required aria-describedby="inmueble-servicio-help<?= isset($errors['inmueble_servicio_id']) ? ' inmueble_servicio_id-error' : '' ?>"<?= isset($errors['inmueble_servicio_id']) ? ' aria-invalid="true"' : '' ?>>
                  <option value="">Seleccioná el servicio</option>
                  <?php foreach ($asociaciones as $asociacion): ?>
                    <?php
                    $address = trim($asociacion['calle'] . ' ' . $asociacion['numero']);
                    $label = $asociacion['alias_nombre'] . ' — ' . $asociacion['servicio_nombre']
                        . ' (' . $asociacion['proveedor'] . ') · ' . $address . ', '
                        . $asociacion['ciudad'] . ' · ' . $asociacion['unidad_medida_std'];
                    ?>
                    <option value="<?= esc($asociacion['id'], 'attr') ?>"<?= $value('inmueble_servicio_id') === (string) $asociacion['id'] ? ' selected' : '' ?>><?= esc($label) ?></option>
                  <?php endforeach ?>
                </select>
                <small id="inmueble-servicio-help">Solo se muestran vínculos activos de tus inmuebles habilitados.</small>
                <?php if (isset($errors['inmueble_servicio_id'])): ?><span class="field-error" id="inmueble_servicio_id-error"><?= esc($errors['inmueble_servicio_id']) ?></span><?php endif ?>
              </div>

              <div class="field">
                <label for="periodo_facturacion">Período de facturación <span aria-hidden="true">*</span></label>
                <input id="periodo_facturacion" name="periodo_facturacion" type="month" value="<?= esc($value('periodo_facturacion'), 'attr') ?>" required aria-describedby="<?= isset($errors['periodo_facturacion']) ? 'periodo_facturacion-error' : 'periodo-help' ?>"<?= isset($errors['periodo_facturacion']) ? ' aria-invalid="true"' : '' ?>>
                <small id="periodo-help">Elegí el mes y año facturados.</small>
                <?php if (isset($errors['periodo_facturacion'])): ?><span class="field-error" id="periodo_facturacion-error"><?= esc($errors['periodo_facturacion']) ?></span><?php endif ?>
              </div>

              </section>
              <section class="form-section" aria-labelledby="invoice-dates-section">
              <h3 class="invoice-section-title form-section-title" id="invoice-dates-section">Fechas de la factura</h3>
              <div class="field">
                <label for="fecha_emision">Fecha de emisión <span aria-hidden="true">*</span></label>
                <input id="fecha_emision" name="fecha_emision" type="date" value="<?= esc($value('fecha_emision'), 'attr') ?>" required aria-describedby="<?= isset($errors['fecha_emision']) ? 'fecha_emision-error' : 'fecha-emision-help' ?>"<?= isset($errors['fecha_emision']) ? ' aria-invalid="true"' : '' ?>>
                <small id="fecha-emision-help">Fecha en que el proveedor emitió la factura.</small>
                <?php if (isset($errors['fecha_emision'])): ?><span class="field-error" id="fecha_emision-error"><?= esc($errors['fecha_emision']) ?></span><?php endif ?>
              </div>
              <div class="field">
                <label for="fecha_primer_vencimiento">Primer vencimiento <span aria-hidden="true">*</span></label>
                <input id="fecha_primer_vencimiento" name="fecha_primer_vencimiento" type="date" value="<?= esc($value('fecha_primer_vencimiento'), 'attr') ?>" required aria-describedby="<?= isset($errors['fecha_primer_vencimiento']) ? 'fecha_primer_vencimiento-error' : 'primer-vencimiento-help' ?>"<?= isset($errors['fecha_primer_vencimiento']) ? ' aria-invalid="true"' : '' ?>>
                <small id="primer-vencimiento-help">Fecha límite del primer pago.</small>
                <?php if (isset($errors['fecha_primer_vencimiento'])): ?><span class="field-error" id="fecha_primer_vencimiento-error"><?= esc($errors['fecha_primer_vencimiento']) ?></span><?php endif ?>
              </div>
              <div class="field">
                <label for="fecha_segundo_vencimiento">Segundo vencimiento</label>
                <input id="fecha_segundo_vencimiento" name="fecha_segundo_vencimiento" type="date" value="<?= esc($value('fecha_segundo_vencimiento'), 'attr') ?>" aria-describedby="<?= isset($errors['fecha_segundo_vencimiento']) ? 'fecha_segundo_vencimiento-error' : 'segundo-vencimiento-help' ?>"<?= isset($errors['fecha_segundo_vencimiento']) ? ' aria-invalid="true"' : '' ?>>
                <small id="segundo-vencimiento-help">Opcional; no puede ser anterior al primero.</small>
                <?php if (isset($errors['fecha_segundo_vencimiento'])): ?><span class="field-error" id="fecha_segundo_vencimiento-error"><?= esc($errors['fecha_segundo_vencimiento']) ?></span><?php endif ?>
              </div>

              </section>
              <section class="form-section" aria-labelledby="invoice-amount-section">
              <h3 class="invoice-section-title form-section-title" id="invoice-amount-section">Importe y consumo</h3>
              <div class="field">
                <label for="monto_total">Monto total <span aria-hidden="true">*</span></label>
                <input id="monto_total" name="monto_total" type="number" min="0.01" max="9999999999.99" step="0.01" inputmode="decimal" value="<?= esc($value('monto_total'), 'attr') ?>" required aria-describedby="<?= isset($errors['monto_total']) ? 'monto_total-error' : 'monto-help' ?>"<?= isset($errors['monto_total']) ? ' aria-invalid="true"' : '' ?>>
                <small id="monto-help">Importe total, sin separador de miles.</small>
                <?php if (isset($errors['monto_total'])): ?><span class="field-error" id="monto_total-error"><?= esc($errors['monto_total']) ?></span><?php endif ?>
              </div>
              <div class="field">
                <label for="consumo_periodo">Consumo del período <span aria-hidden="true">*</span></label>
                <input id="consumo_periodo" name="consumo_periodo" type="number" min="0" max="999999999.999" step="0.001" inputmode="decimal" value="<?= esc($value('consumo_periodo'), 'attr') ?>" required aria-describedby="<?= isset($errors['consumo_periodo']) ? 'consumo_periodo-error' : 'consumo-help' ?>"<?= isset($errors['consumo_periodo']) ? ' aria-invalid="true"' : '' ?>>
                <small id="consumo-help">Ingresá el valor en la unidad estándar del servicio.</small>
                <?php if (isset($errors['consumo_periodo'])): ?><span class="field-error" id="consumo_periodo-error"><?= esc($errors['consumo_periodo']) ?></span><?php endif ?>
              </div>

              </section>
              <section class="form-section" aria-labelledby="invoice-readings-section">
              <h3 class="invoice-section-title form-section-title" id="invoice-readings-section">Lecturas del medidor <span>Opcional</span></h3>
              <div class="field">
                <label for="lectura_anterior">Lectura anterior</label>
                <input id="lectura_anterior" name="lectura_anterior" type="number" min="0" max="999999999.999" step="0.001" inputmode="decimal" value="<?= esc($value('lectura_anterior'), 'attr') ?>" aria-describedby="<?= isset($errors['lectura_anterior']) ? 'lectura_anterior-error lecturas-help' : 'lecturas-help' ?>"<?= isset($errors['lectura_anterior']) ? ' aria-invalid="true"' : '' ?>>
                <?php if (isset($errors['lectura_anterior'])): ?><span class="field-error" id="lectura_anterior-error"><?= esc($errors['lectura_anterior']) ?></span><?php endif ?>
              </div>
              <div class="field">
                <label for="lectura_actual">Lectura actual</label>
                <input id="lectura_actual" name="lectura_actual" type="number" min="0" max="999999999.999" step="0.001" inputmode="decimal" value="<?= esc($value('lectura_actual'), 'attr') ?>" aria-describedby="<?= isset($errors['lectura_actual']) ? 'lectura_actual-error lecturas-help' : 'lecturas-help' ?>"<?= isset($errors['lectura_actual']) ? ' aria-invalid="true"' : '' ?>>
                <small id="lecturas-help">Si la factura no incluye lecturas, dejá ambas vacías.</small>
                <?php if (isset($errors['lectura_actual'])): ?><span class="field-error" id="lectura_actual-error"><?= esc($errors['lectura_actual']) ?></span><?php endif ?>
              </div>
              </section>
            </div>
            <div class="invoice-form-footer">
              <p>La factura se registrará como <strong>Pendiente</strong>.</p>
              <button class="button primary" type="submit"><span class="icon" aria-hidden="true">save</span>Guardar factura</button>
            </div>
          </form>
        </section>
      <?php endif ?>
    </main>
  </div>
  <script src="<?= base_url('js/sidebar.js') ?>"></script>
</body>
</html>

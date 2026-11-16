<?php
$formData = $formData ?? [];
$errors = $errors ?? [];
$success = $success ?? null;
$error = $error ?? null;
$value = static fn (string $field): string => (string) ($formData[$field] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6fafe">
  <title>Registrar factura | ServiInmueble</title>
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
      <div class="top-brand"><span class="icon" aria-hidden="true">domain</span>ServiInmueble</div>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Facturas</span>
    </header>

    <main class="page">
      <section class="page-head">
        <div>
          <p class="eyebrow">Patrimonio inmobiliario / Facturas</p>
          <h1>Registrar factura</h1>
          <p class="subtitle">Cargá los datos que aparecen en la factura de un servicio de tu inmueble.</p>
        </div>
        <a class="button secondary" href="<?= site_url('servicios/asociaciones') ?>">
          <span class="icon" aria-hidden="true">arrow_back</span>Volver a servicios
        </a>
      </section>

      <?php if ($success): ?>
        <div class="notice" role="status"><?= esc($success) ?></div>
      <?php endif ?>
      <?php if ($error): ?>
        <div class="notice error" role="alert"><?= esc($error) ?></div>
      <?php endif ?>

      <?php if ($asociaciones === []): ?>
        <section class="service-card invoice-empty" aria-labelledby="invoice-empty-title">
          <span class="icon" aria-hidden="true">receipt_long</span>
          <h2 id="invoice-empty-title">Todavía no tenés servicios asociados</h2>
          <p>Para registrar una factura, primero asociá un servicio a uno de tus inmuebles.</p>
          <a class="button" href="<?= site_url('servicios/asociaciones') ?>">Ir a servicios asociados</a>
        </section>
      <?php else: ?>
        <section class="service-card invoice-card" aria-labelledby="invoice-form-title">
          <div class="invoice-card-head">
            <h2 id="invoice-form-title">Datos de la factura</h2>
            <p><span aria-hidden="true">*</span> Campo obligatorio</p>
          </div>

          <?php if ($errors !== []): ?>
            <div class="notice error invoice-error-summary" role="alert">
              <p>Revisá estos datos antes de guardar:</p>
              <ul>
                <?php foreach ($errors as $field => $message): ?>
                  <li><a href="#<?= esc($field, 'attr') ?>"><?= esc($message) ?></a></li>
                <?php endforeach ?>
              </ul>
            </div>
          <?php endif ?>

          <form method="post" action="<?= site_url('facturas') ?>">
            <?= csrf_field() ?>
            <div class="invoice-form-grid">
              <h3 class="invoice-section-title">Servicio y período</h3>
              <div class="field full">
                <label for="inmueble_servicio_id">Servicio asociado <span aria-hidden="true">*</span></label>
                <select id="inmueble_servicio_id" name="inmueble_servicio_id" required <?= isset($errors['inmueble_servicio_id']) ? 'aria-invalid="true"' : '' ?> aria-describedby="inmueble-servicio-help<?= isset($errors['inmueble_servicio_id']) ? ' inmueble_servicio_id-error' : '' ?>">
                  <option value="">Seleccioná el servicio</option>
                  <?php foreach ($asociaciones as $asociacion): ?>
                    <?php
                      $direccion = trim($asociacion['calle'] . ' ' . $asociacion['numero']);
                      $etiqueta = $asociacion['alias_nombre'] . ' — ' . $asociacion['servicio_nombre'] . ' (' . $asociacion['proveedor'] . ')';
                    ?>
                    <option value="<?= esc($asociacion['id'], 'attr') ?>" <?= $value('inmueble_servicio_id') === (string) $asociacion['id'] ? 'selected' : '' ?>>
                      <?= esc($etiqueta . ' · ' . $direccion . ', ' . $asociacion['ciudad'] . ' · ' . $asociacion['unidad_medida_std']) ?>
                    </option>
                  <?php endforeach ?>
                </select>
                <small id="inmueble-servicio-help">Solo se muestran servicios vinculados a tus inmuebles.</small>
                <?php if (isset($errors['inmueble_servicio_id'])): ?>
                  <span class="field-error" id="inmueble_servicio_id-error"><?= esc($errors['inmueble_servicio_id']) ?></span>
                <?php endif ?>
              </div>

              <div class="field">
                <label for="periodo_facturacion">Período de facturación <span aria-hidden="true">*</span></label>
                <input id="periodo_facturacion" name="periodo_facturacion" type="month" value="<?= esc($value('periodo_facturacion'), 'attr') ?>" required <?= isset($errors['periodo_facturacion']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['periodo_facturacion']) ? 'periodo_facturacion-error' : 'periodo-help' ?>">
                <small id="periodo-help">Elegí el mes y el año que figuran en la factura.</small>
                <?php if (isset($errors['periodo_facturacion'])): ?>
                  <span class="field-error" id="periodo_facturacion-error"><?= esc($errors['periodo_facturacion']) ?></span>
                <?php endif ?>
              </div>

              <h3 class="invoice-section-title">Fechas de la factura</h3>
              <div class="field">
                <label for="fecha_emision">Fecha de emisión <span aria-hidden="true">*</span></label>
                <input id="fecha_emision" name="fecha_emision" type="date" value="<?= esc($value('fecha_emision'), 'attr') ?>" required <?= isset($errors['fecha_emision']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['fecha_emision']) ? 'fecha_emision-error' : 'fecha-emision-help' ?>">
                <small id="fecha-emision-help">Fecha en que el proveedor emitió la factura.</small>
                <?php if (isset($errors['fecha_emision'])): ?>
                  <span class="field-error" id="fecha_emision-error"><?= esc($errors['fecha_emision']) ?></span>
                <?php endif ?>
              </div>

              <div class="field">
                <label for="fecha_primer_vencimiento">Primer vencimiento <span aria-hidden="true">*</span></label>
                <input id="fecha_primer_vencimiento" name="fecha_primer_vencimiento" type="date" value="<?= esc($value('fecha_primer_vencimiento'), 'attr') ?>" required <?= isset($errors['fecha_primer_vencimiento']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['fecha_primer_vencimiento']) ? 'fecha_primer_vencimiento-error' : 'primer-vencimiento-help' ?>">
                <small id="primer-vencimiento-help">Fecha límite del primer pago.</small>
                <?php if (isset($errors['fecha_primer_vencimiento'])): ?>
                  <span class="field-error" id="fecha_primer_vencimiento-error"><?= esc($errors['fecha_primer_vencimiento']) ?></span>
                <?php endif ?>
              </div>

              <div class="field">
                <label for="fecha_segundo_vencimiento">Segundo vencimiento</label>
                <input id="fecha_segundo_vencimiento" name="fecha_segundo_vencimiento" type="date" value="<?= esc($value('fecha_segundo_vencimiento'), 'attr') ?>" <?= isset($errors['fecha_segundo_vencimiento']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['fecha_segundo_vencimiento']) ? 'fecha_segundo_vencimiento-error' : 'segundo-vencimiento-help' ?>">
                <small id="segundo-vencimiento-help">Opcional. No puede ser anterior al primero.</small>
                <?php if (isset($errors['fecha_segundo_vencimiento'])): ?>
                  <span class="field-error" id="fecha_segundo_vencimiento-error"><?= esc($errors['fecha_segundo_vencimiento']) ?></span>
                <?php endif ?>
              </div>

              <h3 class="invoice-section-title">Importe y consumo</h3>
              <div class="field">
                <label for="monto_total">Monto total <span aria-hidden="true">*</span></label>
                <input id="monto_total" name="monto_total" type="number" min="0.01" max="9999999999.99" step="0.01" inputmode="decimal" value="<?= esc($value('monto_total'), 'attr') ?>" required <?= isset($errors['monto_total']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['monto_total']) ? 'monto_total-error' : 'monto-help' ?>">
                <small id="monto-help">Importe total a pagar, sin separador de miles.</small>
                <?php if (isset($errors['monto_total'])): ?>
                  <span class="field-error" id="monto_total-error"><?= esc($errors['monto_total']) ?></span>
                <?php endif ?>
              </div>

              <div class="field">
                <label for="consumo_periodo">Consumo del período <span aria-hidden="true">*</span></label>
                <input id="consumo_periodo" name="consumo_periodo" type="number" min="0" max="999999999.999" step="0.001" inputmode="decimal" value="<?= esc($value('consumo_periodo'), 'attr') ?>" required <?= isset($errors['consumo_periodo']) ? 'aria-invalid="true"' : '' ?> aria-describedby="consumo-help<?= isset($errors['consumo_periodo']) ? ' consumo_periodo-error' : '' ?>">
                <small id="consumo-help">Indicá el consumo en <span id="consumo-unidad">la unidad del servicio</span>.</small>
                <?php if (isset($errors['consumo_periodo'])): ?>
                  <span class="field-error" id="consumo_periodo-error"><?= esc($errors['consumo_periodo']) ?></span>
                <?php endif ?>
              </div>

              <h3 class="invoice-section-title">Lecturas del medidor <span>Opcional</span></h3>
              <div class="field">
                <label for="lectura_anterior">Lectura anterior</label>
                <input id="lectura_anterior" name="lectura_anterior" type="number" min="0" max="999999999.999" step="0.001" inputmode="decimal" value="<?= esc($value('lectura_anterior'), 'attr') ?>" <?= isset($errors['lectura_actual']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['lectura_actual']) ? 'lectura_actual-error' : 'lecturas-help' ?>">
                <small id="lecturas-help">Si la factura no incluye lecturas, podés dejar ambas vacías.</small>
              </div>

              <div class="field">
                <label for="lectura_actual">Lectura actual</label>
                <input id="lectura_actual" name="lectura_actual" type="number" min="0" max="999999999.999" step="0.001" inputmode="decimal" value="<?= esc($value('lectura_actual'), 'attr') ?>" <?= isset($errors['lectura_actual']) ? 'aria-invalid="true"' : '' ?> aria-describedby="<?= isset($errors['lectura_actual']) ? 'lectura_actual-error lecturas-help' : 'lecturas-help' ?>">
                <?php if (isset($errors['lectura_actual'])): ?>
                  <span class="field-error" id="lectura_actual-error"><?= esc($errors['lectura_actual']) ?></span>
                <?php endif ?>
              </div>
            </div>

            <div class="invoice-form-footer">
              <p><span class="invoice-status-dot" aria-hidden="true"></span>Se registrará como <strong>Pendiente</strong>.</p>
              <button class="button" type="submit"><span class="icon" aria-hidden="true">save</span>Guardar factura</button>
            </div>
          </form>
        </section>
      <?php endif ?>
    </main>
  </div>
  <script src="<?= base_url('js/sidebar.js') ?>"></script>
  <script src="<?= base_url('js/facturas.js') ?>"></script>
</body>
</html>

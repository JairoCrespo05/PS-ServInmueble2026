<?php

$categorias = array_values(array_unique(array_column($servicios, 'categoria')));
sort($categorias);

$esActivo = static fn ($estado): bool => in_array(
    mb_strtolower(trim((string) $estado)),
    ['activo', 'ocupado'],
    true,
);
$iconoServicio = static function (array $servicio): string {
    $identificador = mb_strtolower(trim($servicio['categoria'] . ' ' . $servicio['nombre']));

    return match (true) {
        str_contains($identificador, 'electric') || str_contains($identificador, 'energ') => 'bolt',
        str_contains($identificador, 'agua') => 'water_drop',
        str_contains($identificador, 'gas') => 'local_fire_department',
        str_contains($identificador, 'internet') || str_contains($identificador, 'wifi') => 'wifi',
        str_contains($identificador, 'expensa') || str_contains($identificador, 'consorcio') => 'receipt_long',
        default => 'miscellaneous_services',
    };
};
$abrirModal = $formData !== [] || $errors !== [];
$editAssociationId = $editAssociationId ?? null;
$editandoAsociacion = $editAssociationId !== null;

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6fafe">
  <title>Servicios asociados | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <style>
    :root {
      font-family: Inter, "Segoe UI", sans-serif;
      color: #171c1f;
      background: #f0f4f8;
      font-synthesis: none;
      --navy: #102a43;
      --blue: #2d628e;
      --muted: #627d98;
      --line: #e2e8f0;
      --soft: #f0f4f8;
      --green: #146c43;
    }
    * { box-sizing: border-box; }
    [hidden] { display: none !important; }
    body { min-width: 320px; min-height: 100vh; margin: 0; }
    .icon { font-family: "Material Symbols Outlined"; font-size: 20px; font-weight: normal; font-style: normal; line-height: 1; white-space: nowrap; direction: ltr; -webkit-font-smoothing: antialiased; }
    .workspace { min-height: 100vh; margin-left: 256px; }
    .topbar { position: sticky; z-index: 3; top: 0; display: flex; height: 64px; align-items: center; justify-content: space-between; gap: 16px; padding: 0 26px; border-bottom: 1px solid var(--line); background: rgb(255 255 255 / 96%); }
    .top-brand { display: flex; align-items: center; gap: 10px; color: var(--navy); font: 700 15px Manrope, sans-serif; }
    .top-context { color: var(--muted); font-size: 11px; }
    .page { width: min(1280px, 100%); margin: 0 auto; padding: 24px 26px 40px; }
    .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .eyebrow { margin: 0 0 5px; color: var(--blue); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
    h1, h2, p { margin-top: 0; }
    h1 { margin-bottom: 5px; color: var(--navy); font: 700 clamp(24px, 2.4vw, 34px)/1.2 Manrope, sans-serif; letter-spacing: -.025em; }
    .subtitle { margin-bottom: 0; color: var(--muted); font-size: 13px; }
    .button { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; border: 0; border-radius: 8px; padding: 0 14px; background: var(--blue); color: white; box-shadow: 0 1px 3px rgb(16 42 67 / 8%); font-size: 11px; font-weight: 600; text-decoration: none; }
    .button:hover { background: var(--navy); }
    button { cursor: pointer; font-family: inherit; }
    .button.secondary { border: 1px solid var(--line); background: white; color: var(--navy); }
    .button.secondary:hover { background: var(--soft); }
    .summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .stat { display: flex; min-height: 118px; align-items: center; justify-content: space-between; gap: 12px; border-radius: 14px; padding: 20px; background: white; box-shadow: 0 1px 4px rgb(16 42 67 / 5%); }
    .stat-label { display: block; color: var(--muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .stat-value { display: block; margin-top: 8px; color: var(--navy); font: 700 30px/1 Manrope, sans-serif; }
    .stat-note { display: block; margin-top: 8px; color: var(--muted); font-size: 10px; }
    .stat-icon { display: grid; width: 44px; height: 44px; flex: 0 0 44px; place-items: center; border-radius: 12px; background: #e7f0f7; color: var(--blue); }
    .filters { display: grid; grid-template-columns: minmax(220px, 1fr) minmax(180px, .55fr); gap: 10px; margin-bottom: 16px; border-radius: 13px; padding: 14px; background: white; box-shadow: 0 1px 4px rgb(16 42 67 / 5%); }
    .field { display: grid; gap: 5px; min-width: 0; }
    .field label { color: var(--muted); font-size: 10px; font-weight: 600; }
    .field input, .field select { width: 100%; min-height: 39px; border: 1px solid transparent; border-radius: 8px; outline: 0; padding: 0 11px; background: var(--soft); color: #263d4d; font: inherit; font-size: 11px; }
    .field input:focus, .field select:focus { border-color: #7aa4c4; background: white; box-shadow: 0 0 0 3px rgb(45 98 142 / 10%); }
    .service-list { display: grid; gap: 12px; }
    .service-card { overflow: hidden; border-radius: 14px; background: white; box-shadow: 0 1px 5px rgb(16 42 67 / 5%); }
    .service-card[hidden] { display: none; }
    .service-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 18px 20px; }
    .service-title { display: flex; align-items: center; gap: 12px; min-width: 0; }
    .service-icon { display: grid; width: 42px; height: 42px; flex: 0 0 42px; place-items: center; border-radius: 11px; background: #e7f0f7; color: var(--blue); }
    .service-name { margin: 0; color: var(--navy); font: 700 15px Manrope, sans-serif; }
    .service-category { display: inline-block; margin-top: 5px; border-radius: 5px; padding: 4px 7px; background: #e9eef3; color: #526b7e; font-size: 9px; }
    .service-meta { display: flex; flex-wrap: wrap; gap: 6px 16px; margin-top: 9px; color: var(--muted); font-size: 10px; }
    .association-count { flex: 0 0 auto; border-radius: 7px; padding: 7px 9px; background: #e7f6ed; color: var(--green); font-size: 10px; font-weight: 700; white-space: nowrap; }
    .association-count.none { background: #edf1f5; color: #566875; }
    .association-toggle { min-height: 34px; border: 1px solid var(--line); border-radius: 7px; padding: 0 9px; background: white; color: var(--navy); font-size: 10px; font-weight: 600; white-space: nowrap; }
    .association-toggle:hover { background: var(--soft); }
    .association-section { border-top: 1px solid var(--line); padding: 14px 20px 18px; }
    .association-heading { margin: 0 0 10px; color: var(--muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .association-list { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 8px; }
    .property-card { display: flex; align-items: flex-start; gap: 10px; min-width: 0; border: 1px solid var(--line); border-radius: 9px; padding: 11px; }
    .property-icon { display: grid; width: 32px; height: 32px; flex: 0 0 32px; place-items: center; border-radius: 8px; background: var(--soft); color: var(--blue); }
    .property-info { min-width: 0; }
    .association-actions { display: flex; flex: 0 0 auto; flex-direction: column; gap: 5px; }
    .association-actions form { margin: 0; }
    .association-action { min-height: 28px; border: 1px solid var(--line); border-radius: 6px; padding: 0 7px; background: white; color: var(--navy); font-size: 9px; font-weight: 600; white-space: nowrap; }
    .association-action:hover { background: var(--soft); }
    .association-action.danger { color: #b42318; }
    .property-name { display: block; color: var(--navy); font-size: 11px; font-weight: 700; }
    .property-address { display: block; margin-top: 4px; color: var(--muted); font-size: 10px; line-height: 1.45; }
    .property-tags { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 7px; }
    .tag { border-radius: 5px; padding: 4px 6px; background: #edf1f5; color: #566875; font-size: 9px; }
    .tag.active { background: #e7f6ed; color: var(--green); }
    .no-associations, .empty { color: var(--muted); font-size: 11px; }
    .no-associations { margin: 0; border-radius: 8px; padding: 12px; background: #f7f9fb; }
    .notice { margin-bottom: 14px; border-radius: 8px; padding: 11px 14px; background: #e7f6ed; color: var(--green); font-size: 11px; }
    .notice.error { background: #fef3f2; color: #b42318; }
    .service-actions { display: flex; align-items: flex-start; gap: 8px; }
    .service-actions .button { min-height: 34px; padding: 0 10px; font-size: 10px; }
    .modal-backdrop { position: fixed; z-index: 10; inset: 0; display: none; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; background: rgb(7 29 49 / 55%); }
    .modal-backdrop.open { display: flex; }
    .modal { width: min(620px, 100%); max-height: calc(100vh - 40px); overflow-y: auto; border-radius: 14px; background: white; box-shadow: 0 20px 60px rgb(0 0 0 / 22%); }
    .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); padding: 18px 20px; }
    .modal-head h2 { margin: 0; color: var(--navy); font: 700 18px Manrope, sans-serif; }
    .modal-head p { margin: 5px 0 0; color: var(--muted); font-size: 10px; }
    .close-modal { display: grid; width: 32px; height: 32px; place-items: center; border: 0; border-radius: 7px; background: var(--soft); color: var(--navy); }
    .form-body { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 18px 20px; }
    .form-section-title { grid-column: 1 / -1; margin: 2px 0 -3px; color: var(--blue); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .form-field { display: grid; gap: 5px; min-width: 0; }
    .form-field.full { grid-column: 1 / -1; }
    .form-field label { color: var(--muted); font-size: 10px; font-weight: 600; }
    .form-field input, .form-field select { min-height: 38px; border: 1px solid var(--line); border-radius: 7px; padding: 0 10px; background: white; color: #263d4d; font: inherit; font-size: 11px; }
    .field-error { color: #b42318; font-size: 10px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--line); padding: 13px 20px; }
    .empty { display: none; padding: 36px 16px; border-radius: 14px; background: white; text-align: center; }
    .empty.visible { display: block; }
    @media (max-width: 1100px) { .workspace { margin-left: 220px; } .page { padding: 20px; } }
    @media (max-width: 760px) {
      .workspace { margin-left: 0; }
      .topbar { position: static; height: 50px; padding: 0 14px; }
      .top-context { display: none; }
      .page { padding: 18px 12px 30px; }
      .page-head { flex-direction: column; }
      .summary { grid-template-columns: 1fr; gap: 8px; }
      .stat { min-height: 96px; padding: 14px; }
      .filters { grid-template-columns: 1fr; }
      .service-head { padding: 15px; }
      .association-section { padding: 12px 15px 15px; }
    }
    @media (max-width: 600px) { .service-head { flex-direction: column; } .service-actions { width: 100%; justify-content: space-between; } .association-count { align-self: flex-start; } .association-list { grid-template-columns: 1fr; } .form-body { grid-template-columns: 1fr; } .form-section-title, .form-field.full { grid-column: auto; } }
  </style>
  <link rel="stylesheet" href="<?= base_url('assets/css/sidebar.css') ?>">
</head>
<body>
  <?= view('layouts/sidebar', ['activeSection' => 'servicios-asociados']) ?>

  <div class="workspace">
    <header class="topbar">
      <div class="top-brand"><span class="icon">domain</span>ServiInmueble</div>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Servicios asociados</span>
    </header>

    <main class="page">
      <section class="page-head">
        <div>
          <p class="eyebrow">Patrimonio inmobiliario / Servicios</p>
          <h1>Servicios asociados</h1>
          <p class="subtitle">Consulte los servicios disponibles y los inmuebles vinculados a cada uno.</p>
        </div>
        <div class="service-actions">
          <a class="button secondary" href="<?= site_url('inmuebles') ?>"><span class="icon">apartment</span>Ver inmuebles</a>
          <?php if ($servicios !== [] && $inmuebles !== []): ?>
            <button class="button" id="open-associate" type="button"><span class="icon">add_link</span>Asociar servicio</button>
          <?php endif ?>
        </div>
      </section>

      <?php if ($success): ?><div class="notice" role="status"><?= esc($success) ?></div><?php endif ?>
      <?php if ($error): ?><div class="notice error" role="alert"><?= esc($error) ?></div><?php endif ?>

      <section class="summary" aria-label="Resumen de servicios">
        <article class="stat">
          <div><span class="stat-label">Servicios registrados</span><strong class="stat-value"><?= esc($resumen['total']) ?></strong><span class="stat-note">Tipos de servicio disponibles</span></div>
          <span class="stat-icon icon">power</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Asociaciones</span><strong class="stat-value"><?= esc($resumen['asociaciones']) ?></strong><span class="stat-note">Vínculos entre servicios e inmuebles</span></div>
          <span class="stat-icon icon">link</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Sin inmuebles asociados</span><strong class="stat-value"><?= esc($resumen['sin_inmuebles']) ?></strong><span class="stat-note">Servicios aún no vinculados</span></div>
          <span class="stat-icon icon">link_off</span>
        </article>
      </section>

      <section class="filters" aria-label="Buscar y filtrar servicios">
        <div class="field">
          <label for="search-service">Buscar servicio o inmueble</label>
          <input id="search-service" type="search" placeholder="Nombre, proveedor, inmueble o localidad..." autocomplete="off">
        </div>
        <div class="field">
          <label for="filter-category">Categoría</label>
          <select id="filter-category">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= esc(mb_strtolower($categoria), 'attr') ?>"><?= esc($categoria) ?></option>
            <?php endforeach ?>
          </select>
        </div>
      </section>

      <section class="service-list" id="service-list" aria-label="Servicios registrados">
        <?php foreach ($servicios as $servicio): ?>
          <?php
            $terminos = [
                $servicio['nombre'],
                $servicio['categoria'],
                $servicio['proveedor'],
                $servicio['unidad_medida_std'],
            ];
            foreach ($servicio['inmuebles'] as $inmueble) {
                $terminos[] = $inmueble['alias_nombre'];
                $terminos[] = $inmueble['tipo'];
                $terminos[] = $inmueble['calle'] . ' ' . $inmueble['numero'];
                $terminos[] = $inmueble['ciudad'];
            }
          ?>
          <article
            class="service-card"
            data-service-card
            data-category="<?= esc(mb_strtolower($servicio['categoria']), 'attr') ?>"
            data-search="<?= esc(mb_strtolower(implode(' ', $terminos)), 'attr') ?>"
          >
            <header class="service-head">
              <div class="service-title">
                <span class="service-icon icon" aria-hidden="true"><?= esc($iconoServicio($servicio)) ?></span>
                <div>
                  <h2 class="service-name"><?= esc($servicio['nombre']) ?></h2>
                  <span class="service-category"><?= esc($servicio['categoria']) ?></span>
                  <div class="service-meta">
                    <span><strong>Proveedor:</strong> <?= esc($servicio['proveedor']) ?></span>
                    <span><strong>Unidad:</strong> <?= esc($servicio['unidad_medida_std']) ?></span>
                  </div>
                </div>
              </div>
              <div class="service-actions">
                <?php if ($servicio['inmuebles'] !== []): ?>
                  <button
                    class="association-toggle"
                    type="button"
                    data-toggle-associations
                    aria-expanded="false"
                    aria-controls="service-associations-<?= esc($servicio['id'], 'attr') ?>"
                    aria-label="Ver inmuebles asociados a <?= esc($servicio['nombre'], 'attr') ?>"
                  ><span class="icon" aria-hidden="true">expand_more</span>Ver inmuebles</button>
                <?php endif ?>
                <button
                  class="button secondary"
                  type="button"
                  data-associate-service
                  data-service-id="<?= esc($servicio['id'], 'attr') ?>"
                  data-associated-ids="<?= esc(json_encode($servicio['inmueble_ids']), 'attr') ?>"
                  <?= count($servicio['inmueble_ids']) >= count($inmuebles) ? 'disabled' : '' ?>
                ><span class="icon">add_link</span>Asociar</button>
                <span class="association-count<?= $servicio['inmuebles'] === [] ? ' none' : '' ?>">
                  <?= esc(count($servicio['inmuebles'])) ?> <?= count($servicio['inmuebles']) === 1 ? 'inmueble' : 'inmuebles' ?>
                </span>
              </div>
            </header>

            <div class="association-section" id="service-associations-<?= esc($servicio['id'], 'attr') ?>"<?= $servicio['inmuebles'] !== [] ? ' hidden' : '' ?>>
              <h3 class="association-heading">Inmuebles asociados</h3>
              <?php if ($servicio['inmuebles'] === []): ?>
                <p class="no-associations">Este servicio todavía no está asociado a ningún inmueble.</p>
              <?php else: ?>
                <div class="association-list">
                  <?php foreach ($servicio['inmuebles'] as $inmueble): ?>
                    <?php
                      $direccion = trim(
                          $inmueble['calle'] . ' ' . $inmueble['numero']
                          . ($inmueble['piso_dpto'] ? ', ' . $inmueble['piso_dpto'] : '')
                          . ', ' . $inmueble['ciudad'],
                      );
                      $iconoTipo = match (mb_strtolower($inmueble['tipo'])) {
                          'casa' => 'home',
                          'departamento' => 'apartment',
                          default => 'storefront',
                      };
                    ?>
                    <article class="property-card">
                      <span class="property-icon icon"><?= esc($iconoTipo) ?></span>
                      <div class="property-info">
                        <span class="property-name"><?= esc($inmueble['alias_nombre']) ?></span>
                        <span class="property-address"><?= esc($direccion) ?></span>
                        <div class="property-tags">
                          <span class="tag">ID: INM-<?= esc(str_pad((string) $inmueble['id'], 4, '0', STR_PAD_LEFT)) ?></span>
                          <span class="tag<?= $esActivo($inmueble['estado_servicio']) ? ' active' : '' ?>">Servicio: <?= esc($inmueble['estado_servicio']) ?></span>
                          <span class="tag">Inmueble: <?= esc($inmueble['estado_inmueble']) ?></span>
                          <?php if ($inmueble['nro_medidor']): ?><span class="tag">Medidor: <?= esc($inmueble['nro_medidor']) ?></span><?php endif ?>
                        </div>
                      </div>
                      <div class="association-actions">
                        <button
                          class="association-action"
                          type="button"
                          data-edit-association
                          data-id="<?= esc($inmueble['asociacion_id'], 'attr') ?>"
                          data-service-id="<?= esc($servicio['id'], 'attr') ?>"
                          data-property-id="<?= esc($inmueble['id'], 'attr') ?>"
                          data-holder="<?= esc($inmueble['titular_inmueble'], 'attr') ?>"
                          data-status="<?= esc($inmueble['estado_servicio'], 'attr') ?>"
                          data-meter="<?= esc($inmueble['nro_medidor'] ?? '', 'attr') ?>"
                          data-account="<?= esc($inmueble['nro_cuenta_contrato'] ?? '', 'attr') ?>"
                          data-payment="<?= esc($inmueble['cod_pago_electronico'] ?? '', 'attr') ?>"
                          data-billing="<?= esc($inmueble['domicilio_facturacion'] ?? '', 'attr') ?>"
                        >Modificar</button>
                        <?php if (mb_strtolower(trim($inmueble['estado_servicio'])) !== 'inactivo'): ?>
                          <form method="post" action="<?= site_url('servicios/asociaciones/' . $inmueble['asociacion_id'] . '/baja') ?>" data-deactivate-association>
                            <?= csrf_field() ?>
                            <button class="association-action danger" type="submit">Dar de baja</button>
                          </form>
                        <?php endif ?>
                      </div>
                    </article>
                  <?php endforeach ?>
                </div>
              <?php endif ?>
            </div>
          </article>
        <?php endforeach ?>
      </section>
      <div class="empty" id="empty-state">No se encontraron servicios con esos filtros.</div>
    </main>
  </div>

  <div class="modal-backdrop<?= $abrirModal ? ' open' : '' ?>" id="associate-modal" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="associate-title">
      <div class="modal-head">
        <div><h2 id="associate-title"><?= $editandoAsociacion ? 'Modificar asociación' : 'Asociar servicio a inmueble' ?></h2><p id="associate-description"><?= $editandoAsociacion ? 'Actualizá el estado y los datos registrados para este servicio.' : 'Registrá el vínculo y los datos de identificación del servicio.' ?></p></div>
        <button class="close-modal" id="close-associate" type="button" aria-label="Cerrar"><span class="icon">close</span></button>
      </div>
      <form method="post" action="<?= $editandoAsociacion ? site_url('servicios/asociaciones/' . $editAssociationId) : site_url('servicios/asociar') ?>" data-associate-action="<?= esc(site_url('servicios/asociar'), 'attr') ?>" id="associate-form">
        <?= csrf_field() ?>
        <div class="form-body">
          <?php if ($errors !== []): ?>
            <div class="form-field full" role="alert">
              <?php foreach ($errors as $message): ?><span class="field-error"><?= esc($message) ?></span><?php endforeach ?>
            </div>
          <?php endif ?>
          <div class="form-field full" id="service-field"<?= $editandoAsociacion ? ' hidden' : '' ?>>
            <label for="associate-service">Servicio<?= $editandoAsociacion ? ' (no modificable)' : '' ?></label>
            <select id="associate-service" required<?= $serviceLocked || $editandoAsociacion ? ' disabled' : '' ?>>
              <option value="">Seleccioná un servicio</option>
              <?php foreach ($servicios as $servicio): ?>
                <option value="<?= esc($servicio['id'], 'attr') ?>" data-associated-ids="<?= esc(json_encode($servicio['inmueble_ids']), 'attr') ?>"<?= (string) ($formData['servicio_id'] ?? '') === (string) $servicio['id'] ? ' selected' : '' ?>><?= esc($servicio['nombre'] . ' — ' . $servicio['proveedor']) ?></option>
              <?php endforeach ?>
            </select>
            <input id="associate-service-id" type="hidden" name="servicio_id" value="<?= esc($formData['servicio_id'] ?? '', 'attr') ?>">
            <input id="service-locked" type="hidden" name="service_locked" value="<?= $serviceLocked || $editandoAsociacion ? '1' : '0' ?>">
            <span class="field-error" id="service-lock-note"<?= $serviceLocked || $editandoAsociacion ? '' : ' hidden' ?>>Este servicio está definido por la opción elegida y no se puede cambiar desde aquí.</span>
            <?php if (isset($errors['servicio_id'])): ?><span class="field-error"><?= esc($errors['servicio_id']) ?></span><?php endif ?>
          </div>
          <div class="form-field full" id="property-field"<?= $editandoAsociacion ? ' hidden' : '' ?>>
            <label for="associate-property">Inmueble<?= $editandoAsociacion ? ' (no modificable)' : '' ?></label>
            <select id="associate-property" name="inmueble_id" required>
              <option value="">Seleccioná un inmueble</option>
              <?php foreach ($inmuebles as $inmueble): ?>
                <?php
                  $direccion = trim(
                      $inmueble['calle'] . ' ' . $inmueble['numero']
                      . ($inmueble['piso_dpto'] ? ', ' . $inmueble['piso_dpto'] : '')
                      . ', ' . $inmueble['ciudad'],
                  );
                ?>
                <option value="<?= esc($inmueble['id'], 'attr') ?>"<?= (string) ($formData['inmueble_id'] ?? '') === (string) $inmueble['id'] ? ' selected' : '' ?>><?= esc($inmueble['alias_nombre'] . ' — ' . $direccion . ' (' . $inmueble['estado'] . ')') ?></option>
              <?php endforeach ?>
            </select>
            <?php if (isset($errors['inmueble_id'])): ?><span class="field-error"><?= esc($errors['inmueble_id']) ?></span><?php endif ?>
          </div>
          <p class="form-section-title">Datos de la asociación</p>
          <div class="form-field">
            <label for="associate-holder">Titular del servicio</label>
            <input id="associate-holder" name="titular_inmueble" maxlength="150" required value="<?= esc($formData['titular_inmueble'] ?? '', 'attr') ?>">
            <?php if (isset($errors['titular_inmueble'])): ?><span class="field-error"><?= esc($errors['titular_inmueble']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-status">Estado del servicio</label>
            <select id="associate-status" name="estado" required>
              <?php foreach (['Activo', 'Inactivo', 'Suspendido'] as $estado): ?>
                <option value="<?= esc($estado, 'attr') ?>"<?= ($formData['estado'] ?? 'Activo') === $estado ? ' selected' : '' ?>><?= esc($estado) ?></option>
              <?php endforeach ?>
            </select>
            <?php if (isset($errors['estado'])): ?><span class="field-error"><?= esc($errors['estado']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-meter">Número de medidor <span>(opcional)</span></label>
            <input id="associate-meter" name="nro_medidor" maxlength="50" value="<?= esc($formData['nro_medidor'] ?? '', 'attr') ?>">
            <?php if (isset($errors['nro_medidor'])): ?><span class="field-error"><?= esc($errors['nro_medidor']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-account">Número de cuenta / contrato <span>(opcional)</span></label>
            <input id="associate-account" name="nro_cuenta_contrato" maxlength="100" value="<?= esc($formData['nro_cuenta_contrato'] ?? '', 'attr') ?>">
            <?php if (isset($errors['nro_cuenta_contrato'])): ?><span class="field-error"><?= esc($errors['nro_cuenta_contrato']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-payment">Código de pago electrónico <span>(opcional)</span></label>
            <input id="associate-payment" name="cod_pago_electronico" maxlength="100" value="<?= esc($formData['cod_pago_electronico'] ?? '', 'attr') ?>">
            <?php if (isset($errors['cod_pago_electronico'])): ?><span class="field-error"><?= esc($errors['cod_pago_electronico']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-billing">Domicilio de facturación <span>(opcional)</span></label>
            <input id="associate-billing" name="domicilio_facturacion" maxlength="255" value="<?= esc($formData['domicilio_facturacion'] ?? '', 'attr') ?>">
            <?php if (isset($errors['domicilio_facturacion'])): ?><span class="field-error"><?= esc($errors['domicilio_facturacion']) ?></span><?php endif ?>
          </div>
        </div>
        <div class="modal-actions">
          <button class="button secondary" id="cancel-associate" type="button">Cancelar</button>
          <button class="button" id="save-association" type="submit"><?= $editandoAsociacion ? 'Guardar cambios' : 'Guardar asociación' ?></button>
        </div>
      </form>
    </section>
  </div>

  <script>
    const cards = [...document.querySelectorAll("[data-service-card]")];
    const searchInput = document.querySelector("#search-service");
    const categoryFilter = document.querySelector("#filter-category");
    const emptyState = document.querySelector("#empty-state");

    function filterServices() {
      const query = searchInput.value.trim().toLocaleLowerCase("es");
      const category = categoryFilter.value;
      let visibleCount = 0;

      cards.forEach((card) => {
        const visible = card.dataset.search.includes(query)
          && (!category || card.dataset.category === category);
        card.hidden = !visible;
        if (visible) visibleCount += 1;
      });

      emptyState.classList.toggle("visible", visibleCount === 0);
    }

    searchInput.addEventListener("input", filterServices);
    categoryFilter.addEventListener("change", filterServices);

    document.querySelectorAll("[data-toggle-associations]").forEach((button) => {
      button.addEventListener("click", () => {
        const panel = document.getElementById(button.getAttribute("aria-controls"));
        const expanded = button.getAttribute("aria-expanded") === "true";

        panel.hidden = expanded;
        button.setAttribute("aria-expanded", String(!expanded));
        button.setAttribute(
          "aria-label",
          `${expanded ? "Ver" : "Ocultar"} inmuebles asociados a ${button.closest("[data-service-card]").querySelector(".service-name").textContent.trim()}`,
        );
        button.innerHTML = `<span class="icon" aria-hidden="true">${expanded ? "expand_more" : "expand_less"}</span>${expanded ? "Ver" : "Ocultar"} inmuebles`;
      });
    });

    const associateModal = document.querySelector("#associate-modal");
    const associateForm = document.querySelector("#associate-form");
    const serviceSelect = document.querySelector("#associate-service");
    const serviceIdInput = document.querySelector("#associate-service-id");
    const serviceLockedInput = document.querySelector("#service-locked");
    const serviceLockNote = document.querySelector("#service-lock-note");
    const propertySelect = document.querySelector("#associate-property");
    const initialPropertyOptions = [...propertySelect.options].slice(1);
    const serviceField = document.querySelector("#service-field");
    const propertyField = document.querySelector("#property-field");
    const associateTitle = document.querySelector("#associate-title");
    const associateDescription = document.querySelector("#associate-description");
    const saveAssociation = document.querySelector("#save-association");
    const closeAssociateModal = () => associateModal.classList.remove("open");

    function updateAvailableProperties() {
      const selectedService = serviceSelect.selectedOptions[0];
      const associatedIds = selectedService
        ? JSON.parse(selectedService.dataset.associatedIds || "[]").map(String)
        : [];
      const selectedProperty = propertySelect.value;

      initialPropertyOptions.forEach((option) => {
        option.disabled = associatedIds.includes(option.value);
      });

      if (associatedIds.includes(selectedProperty)) {
        propertySelect.value = "";
      }
    }

    function setService(serviceId, locked) {
      serviceSelect.value = serviceId;
      serviceSelect.disabled = locked;
      serviceIdInput.value = serviceId;
      serviceLockedInput.value = locked ? "1" : "0";
      serviceLockNote.hidden = !locked;
      updateAvailableProperties();
    }

    function setAssociationMode(editing) {
      serviceField.hidden = editing;
      propertyField.hidden = editing;
      associateTitle.textContent = editing ? "Modificar asociación" : "Asociar servicio a inmueble";
      associateDescription.textContent = editing
        ? "Actualizá el estado y los datos registrados para este servicio."
        : "Registrá el vínculo y los datos de identificación del servicio.";
      saveAssociation.textContent = editing ? "Guardar cambios" : "Guardar asociación";
    }

    document.querySelector("#open-associate")?.addEventListener("click", () => {
      associateForm.reset();
      associateForm.action = associateForm.dataset.associateAction;
      setAssociationMode(false);
      setService("", false);
      updateAvailableProperties();
      associateModal.classList.add("open");
    });
    document.querySelectorAll("[data-associate-service]").forEach((button) => {
      button.addEventListener("click", () => {
        associateForm.reset();
        associateForm.action = associateForm.dataset.associateAction;
        setAssociationMode(false);
        setService(button.dataset.serviceId, true);
        associateModal.classList.add("open");
      });
    });
    document.querySelectorAll("[data-edit-association]").forEach((button) => {
      button.addEventListener("click", () => {
        associateForm.reset();
        associateForm.action = `${associateForm.dataset.associateAction.replace(/\/asociar$/, "")}/asociaciones/${button.dataset.id}`;
        setAssociationMode(true);
        setService(button.dataset.serviceId, true);
        propertySelect.value = button.dataset.propertyId;
        associateForm.elements.namedItem("titular_inmueble").value = button.dataset.holder;
        associateForm.elements.namedItem("estado").value = button.dataset.status;
        associateForm.elements.namedItem("nro_medidor").value = button.dataset.meter;
        associateForm.elements.namedItem("nro_cuenta_contrato").value = button.dataset.account;
        associateForm.elements.namedItem("cod_pago_electronico").value = button.dataset.payment;
        associateForm.elements.namedItem("domicilio_facturacion").value = button.dataset.billing;
        associateModal.classList.add("open");
      });
    });
    document.querySelectorAll("[data-deactivate-association]").forEach((form) => {
      form.addEventListener("submit", (event) => {
        if (!window.confirm("¿Querés dar de baja este servicio? La asociación y su historial de facturas se conservarán.")) {
          event.preventDefault();
        }
      });
    });
    serviceSelect.addEventListener("change", () => {
      serviceIdInput.value = serviceSelect.value;
      updateAvailableProperties();
    });
    document.querySelector("#close-associate").addEventListener("click", closeAssociateModal);
    document.querySelector("#cancel-associate").addEventListener("click", closeAssociateModal);
    associateModal.addEventListener("click", (event) => {
      if (event.target === associateModal) closeAssociateModal();
    });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") closeAssociateModal();
    });
    if (serviceLockedInput.value === "1") {
      setService(serviceIdInput.value, true);
    } else {
      serviceIdInput.value = serviceSelect.value;
      updateAvailableProperties();
    }
    setAssociationMode(<?= $editandoAsociacion ? 'true' : 'false' ?>);
  </script>
</body>
</html>

<?php

$errors      = session()->getFlashdata('errors') ?? [];
$formData    = session()->getFlashdata('formData') ?? [];
$success     = session()->getFlashdata('success');
$error       = session()->getFlashdata('error');
$editId      = session()->getFlashdata('editId');
$tipos       = array_values(array_unique(array_column($inmuebles, 'tipo')));
$estados     = array_values(array_unique(array_column($inmuebles, 'estado')));
$abrirModal  = $errors !== [];

$esEstadoActivo = static fn ($estado): bool => in_array(mb_strtolower(trim((string) $estado)), ['activo', 'ocupado'], true);
$estadoOptions  = array_values(array_unique([
    'Activo',
    'Inactivo',
    'Ocupado',
    'Disponible',
    'En Mantenimiento',
    ...$estados,
    $formData['estado'] ?? '',
]));
$estadoOptions = array_values(array_filter($estadoOptions, static fn ($estado): bool => $estado !== ''));

sort($tipos);
sort($estados);

$formatNumber = static fn ($value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6fafe">
  <title>Gestión de inmuebles | ServiInmueble</title>
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
      --surface: #fff;
      --soft: #f0f4f8;
      --green: #198754;
      --amber: #d97706;
    }
    * { box-sizing: border-box; }
    body { min-width: 320px; min-height: 100vh; margin: 0; }
    button, input, select { font: inherit; }
    button { cursor: pointer; }
    .icon { font-family: "Material Symbols Outlined"; font-size: 20px; font-weight: normal; font-style: normal; line-height: 1; white-space: nowrap; direction: ltr; -webkit-font-smoothing: antialiased; }
    .workspace { min-height: 100vh; margin-left: 256px; }
    .topbar { position: sticky; z-index: 3; top: 0; display: flex; height: 64px; align-items: center; justify-content: space-between; gap: 16px; padding: 0 26px; border-bottom: 1px solid var(--line); background: rgb(255 255 255 / 96%); }
    .top-brand { display: flex; align-items: center; gap: 10px; color: var(--navy); font: 700 15px Manrope, sans-serif; }
    .top-context { color: var(--muted); font-size: 11px; }
    .page { width: min(1500px, 100%); margin: 0 auto; padding: 24px 26px 40px; }
    .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .eyebrow { margin: 0 0 5px; color: var(--blue); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
    h1, h2, h3, p { margin-top: 0; }
    h1 { margin-bottom: 5px; color: var(--navy); font: 700 clamp(24px, 2.4vw, 34px)/1.2 Manrope, sans-serif; letter-spacing: -.025em; }
    .subtitle { margin-bottom: 0; color: var(--muted); font-size: 13px; }
    .actions { display: flex; flex-wrap: wrap; justify-content: flex-end; gap: 8px; }
    .button { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; border: 0; border-radius: 8px; padding: 0 14px; background: white; color: var(--navy); box-shadow: 0 1px 3px rgb(16 42 67 / 8%); font-size: 11px; font-weight: 600; }
    .button:hover { background: #e9f1f7; }
    .button.primary { background: var(--blue); color: white; }
    .button.primary:hover { background: var(--navy); }
    .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .stat { display: flex; min-height: 132px; align-items: center; justify-content: space-between; gap: 12px; border-radius: 14px; padding: 20px; background: var(--surface); box-shadow: 0 1px 4px rgb(16 42 67 / 5%); }
    .stat-label { display: block; color: var(--muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .stat-value { display: block; margin-top: 8px; color: var(--navy); font: 700 30px/1 Manrope, sans-serif; }
    .stat-note { display: block; margin-top: 8px; color: var(--muted); font-size: 10px; }
    .stat-icon { display: grid; width: 48px; height: 48px; flex: 0 0 48px; place-items: center; border-radius: 12px; background: var(--soft); color: var(--blue); }
    .filters { display: grid; grid-template-columns: minmax(200px, 1.4fr) minmax(150px, .8fr) minmax(150px, .8fr) auto; align-items: end; gap: 10px; margin-bottom: 16px; border-radius: 13px; padding: 14px; background: white; box-shadow: 0 1px 4px rgb(16 42 67 / 5%); }
    .field { display: grid; gap: 5px; min-width: 0; }
    .field label { color: var(--muted); font-size: 10px; font-weight: 600; }
    .field input, .field select, .form-control { width: 100%; min-height: 39px; border: 1px solid transparent; border-radius: 8px; outline: 0; padding: 0 11px; background: var(--soft); color: #263d4d; font-size: 11px; }
    .field input:focus, .field select:focus, .form-control:focus { border-color: #7aa4c4; background: white; box-shadow: 0 0 0 3px rgb(45 98 142 / 10%); }
    .search-wrap { position: relative; }
    .search-wrap .icon { position: absolute; top: 10px; left: 10px; color: #7890a5; font-size: 18px; }
    .search-wrap input { padding-left: 36px; }
    .filter-reset { min-height: 39px; border: 0; border-radius: 8px; padding: 0 12px; background: #e9eef3; color: var(--navy); font-size: 11px; font-weight: 600; }
    .table-card { overflow: hidden; border-radius: 14px; background: white; box-shadow: 0 1px 5px rgb(16 42 67 / 5%); }
    .table-scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 14px 16px; background: #f0f4f8; color: var(--muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .035em; }
    td { padding: 15px 16px; border-top: 1px solid #edf1f5; color: #344b5c; font-size: 11px; vertical-align: middle; }
    tbody tr:hover { background: #fbfdff; }
    .property { display: flex; min-width: 190px; align-items: center; gap: 10px; }
    .property-icon { display: grid; width: 40px; height: 40px; flex: 0 0 40px; place-items: center; border-radius: 10px; background: #e7f0f7; color: var(--blue); }
    .property-name { display: block; color: var(--navy); font-size: 12px; font-weight: 600; }
    .property-id { display: block; margin-top: 4px; color: var(--muted); font-size: 9px; }
    .address { max-width: 200px; line-height: 1.5; }
    .type-tag { display: inline-flex; border-radius: 5px; padding: 4px 7px; background: #e9eef3; color: var(--navy); font-size: 10px; }
    .area { color: var(--navy); font-size: 12px; font-weight: 600; white-space: nowrap; }
    .services { min-width: 112px; }
    .services-count { display: inline-flex; align-items: center; gap: 4px; margin-bottom: 5px; border-radius: 5px; padding: 4px 6px; background: var(--soft); color: var(--navy); font-size: 9px; font-weight: 600; }
    .service-tags { display: flex; max-width: 150px; flex-wrap: wrap; gap: 3px; }
    .service-tag { border-radius: 4px; padding: 3px 5px; background: #e9eef3; color: #69849d; font-size: 9px; }
    .status { display: inline-flex; align-items: center; gap: 6px; border-radius: 6px; padding: 6px 9px; background: #e7f6ed; color: #146c43; font-size: 10px; font-weight: 600; white-space: nowrap; }
    .status::before { width: 6px; height: 6px; border-radius: 50%; background: currentColor; content: ""; }
    .status.inactive { background: #edf1f5; color: #566875; }
    .row-actions { display: flex; align-items: center; justify-content: flex-end; gap: 4px; white-space: nowrap; }
    .row-actions form { display: inline-flex; }
    .row-action { display: inline-grid; width: 34px; min-height: 34px; place-items: center; border: 0; border-radius: 7px; padding: 0; background: transparent; color: var(--muted); }
    .row-action .icon { font-size: 19px; }
    .row-action:hover { background: #e8f2fd; color: var(--blue); }
    .row-action.danger { color: #b42318; }
    .row-action.danger:hover { background: #fef3f2; color: #b42318; }
    .row-action:focus-visible { outline: 2px solid var(--blue); outline-offset: 2px; }
    .visually-hidden { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
    .notice.error { background: #fef3f2; color: #b42318; }
    .empty { display: none; padding: 36px 16px; color: var(--muted); text-align: center; font-size: 12px; }
    .table-footer { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-top: 1px solid var(--line); padding: 12px 16px; color: var(--muted); font-size: 10px; }
    .page-size { display: inline-flex; align-items: center; gap: 7px; }
    .page-size select { min-height: 28px; border: 1px solid var(--line); border-radius: 5px; padding: 0 6px; background: white; color: var(--navy); font-size: 10px; }
    .pagination { display: flex; gap: 4px; }
    .pagination button { min-width: 30px; height: 30px; border: 0; border-radius: 6px; background: white; color: var(--navy); font-size: 10px; }
    .pagination button[aria-current="page"] { background: var(--blue); color: white; }
    .pagination button:disabled { color: #aab8c4; cursor: default; }
    .notice { margin-bottom: 14px; border-radius: 8px; padding: 11px 14px; background: #e7f6ed; color: #146c43; font-size: 11px; }
    .modal-backdrop { position: fixed; z-index: 10; inset: 0; display: none; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; background: rgb(7 29 49 / 55%); }
    .modal-backdrop.open { display: flex; }
    .modal { width: min(620px, 100%); max-height: calc(100vh - 40px); overflow-y: auto; border-radius: 14px; background: white; box-shadow: 0 20px 60px rgb(0 0 0 / 22%); }
    .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); padding: 18px 20px; }
    .modal-head h2 { margin: 0; color: var(--navy); font: 700 18px Manrope, sans-serif; }
    .modal-head p { margin: 5px 0 0; color: var(--muted); font-size: 10px; }
    .close-modal { display: grid; width: 32px; height: 32px; place-items: center; border: 0; border-radius: 7px; background: var(--soft); color: var(--navy); }
    .form-body { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 18px 20px; }
    .form-section-title { grid-column: 1 / -1; margin: 2px 0 -3px; color: var(--blue); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .form-field { display: grid; gap: 5px; }
    .form-field.full { grid-column: 1 / -1; }
    .form-field label { color: var(--muted); font-size: 10px; font-weight: 600; }
    .form-field input, .form-field select { min-height: 38px; border: 1px solid var(--line); border-radius: 7px; padding: 0 10px; background: white; color: #263d4d; font-size: 11px; }
    .field-error { color: #b42318; font-size: 10px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--line); padding: 13px 20px; }
    .details-modal { width: min(760px, 100%); }
    .details-body { display: grid; gap: 20px; padding: 20px; }
    .details-section h3 { margin: 0 0 10px; color: var(--blue); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; }
    .details-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
    .detail-item { min-width: 0; border-radius: 8px; padding: 11px 12px; background: var(--soft); }
    .detail-item-label { display: block; color: var(--muted); font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .detail-item-value { display: block; margin-top: 5px; overflow-wrap: anywhere; color: var(--navy); font-size: 12px; }
    .associated-services { display: grid; gap: 10px; }
    .associated-service { border: 1px solid var(--line); border-radius: 10px; padding: 13px; }
    .associated-service h4 { grid-column: 1 / -1; margin: 0 0 10px; color: var(--navy); font: 700 13px Manrope, sans-serif; }
    .no-associated-services { margin: 0; border-radius: 8px; padding: 13px; background: var(--soft); color: var(--muted); font-size: 11px; }
    @media (max-width: 1100px) { .workspace { margin-left: 220px; } .page { padding: 20px; } .summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 760px) {
      .workspace { margin-left: 0; }
      .topbar { position: static; height: 50px; padding: 0 14px; }
      .top-context { display: none; }
      .page { padding: 18px 12px 30px; }
      .page-head { flex-direction: column; }
      .actions { justify-content: flex-start; }
      .filters { grid-template-columns: 1fr 1fr; }
      .filters .field:first-child { grid-column: 1 / -1; }
      .filter-reset { width: 100%; }
      .table-footer { align-items: flex-start; flex-direction: column; }
    }
    @media (max-width: 480px) { .summary { gap: 8px; } .stat { min-height: 110px; padding: 12px; } .stat-icon { width: 36px; height: 36px; flex-basis: 36px; } .stat-value { font-size: 24px; } .filters { grid-template-columns: 1fr; } .filters .field:first-child { grid-column: auto; } .form-body, .details-grid { grid-template-columns: 1fr; } .form-section-title, .form-field.full { grid-column: auto; } }
  </style>
  <link rel="stylesheet" href="<?= base_url('assets/css/sidebar.css') ?>">
</head>
<body>
  <?= view('layouts/sidebar', ['activeSection' => 'inmuebles']) ?>

  <div class="workspace">
    <header class="topbar">
      <div class="top-brand"><span class="icon">domain</span>ServiInmueble</div>
      <span class="top-context">Gestión patrimonial&nbsp; / &nbsp;Registro de inmuebles</span>
    </header>

    <main class="page">
      <section class="page-head">
        <div>
          <p class="eyebrow">Patrimonio inmobiliario / Registro actual</p>
          <h1>Gestión de Inmuebles</h1>
          <p class="subtitle">Administre sus propiedades, unidades y servicios asociados.</p>
        </div>
        <div class="actions">
          <button class="button" id="export-button" type="button"><span class="icon">download</span>Exportar</button>
          <button class="button primary" id="open-create" type="button"><span class="icon">add</span>Nuevo inmueble</button>
        </div>
      </section>

      <?php if ($success): ?>
        <div class="notice" role="status"><?= esc($success) ?></div>
      <?php endif ?>
      <?php if ($error): ?>
        <div class="notice error" role="alert"><?= esc($error) ?></div>
      <?php endif ?>

      <section class="summary" aria-label="Resumen de inmuebles">
        <article class="stat">
          <div><span class="stat-label">Total de inmuebles</span><strong class="stat-value"><?= esc($resumen['total']) ?></strong><span class="stat-note">Registros en la base de datos</span></div>
          <span class="stat-icon icon">apartment</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Inmuebles activos</span><strong class="stat-value"><?= esc($resumen['activos']) ?></strong><span class="stat-note">Según el estado registrado</span></div>
          <span class="stat-icon icon">verified</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Superficie total</span><strong class="stat-value"><?= esc($formatNumber($resumen['superficie_total'], 2)) ?></strong><span class="stat-note">Metros cuadrados registrados</span></div>
          <span class="stat-icon icon">square_foot</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Servicios activos</span><strong class="stat-value"><?= esc($resumen['servicios_activos']) ?></strong><span class="stat-note">Vinculados a los inmuebles</span></div>
          <span class="stat-icon icon">power</span>
        </article>
      </section>

      <section class="filters" aria-label="Buscar y filtrar inmuebles">
        <div class="field">
          <label for="search-property">Buscar inmueble</label>
          <div class="search-wrap"><span class="icon">search</span><input id="search-property" type="search" placeholder="Buscar por alias, calle o localidad..." autocomplete="off"></div>
        </div>
        <div class="field">
          <label for="filter-type">Tipo</label>
          <select id="filter-type"><option value="">Todos los tipos</option><?php foreach ($tipos as $tipo): ?><option value="<?= esc(mb_strtolower($tipo), 'attr') ?>"><?= esc($tipo) ?></option><?php endforeach ?></select>
        </div>
        <div class="field">
          <label for="filter-status">Estado</label>
          <select id="filter-status"><option value="">Todos los estados</option><?php foreach ($estados as $estado): ?><option value="<?= esc(mb_strtolower($estado), 'attr') ?>"><?= esc($estado) ?></option><?php endforeach ?></select>
        </div>
        <button class="filter-reset" id="reset-filters" type="button">Limpiar filtros</button>
      </section>

      <section class="table-card" aria-label="Listado de inmuebles">
        <div class="table-scroll">
          <table>
            <thead>
              <tr><th>Propiedad / Alias</th><th>Dirección y localidad</th><th>Tipo</th><th>Superficie</th><th>Servicios asociados</th><th>Estado</th><th>Acciones</th></tr>
            </thead>
            <tbody id="property-rows">
              <?php foreach ($inmuebles as $inmueble): ?>
                <?php
                  $serviciosActivos = array_filter(explode('|', (string) ($inmueble['nombres_servicios_activos'] ?? '')));
                  $iconoTipo = match (mb_strtolower($inmueble['tipo'])) {
                      'casa' => 'home',
                      'departamento' => 'apartment',
                      default => 'storefront',
                  };
                  $claseEstado = $esEstadoActivo($inmueble['estado']) ? '' : ' inactive';
                  $direccion = trim($inmueble['calle'] . ' ' . $inmueble['numero']
                      . ($inmueble['piso_dpto'] ? ', ' . $inmueble['piso_dpto'] : '')
                      . ', ' . $inmueble['ciudad']);
                  $searchText = mb_strtolower($inmueble['alias_nombre'] . ' ' . $direccion . ' ' . $inmueble['tipo']);
                  $detalleInmueble = [
                      'id'                    => $inmueble['id'],
                      'alias_nombre'          => $inmueble['alias_nombre'],
                      'tipo'                  => $inmueble['tipo'],
                      'superficie'            => $inmueble['superficie'],
                      'estado'                => $inmueble['estado'],
                      'direccion_id'          => $inmueble['direccion_id'],
                      'calle'                 => $inmueble['calle'],
                      'numero'                => $inmueble['numero'],
                      'piso_dpto'             => $inmueble['piso_dpto'],
                      'ciudad'                => $inmueble['ciudad'],
                      'inmueble_created_at'   => $inmueble['inmueble_created_at'],
                      'inmueble_updated_at'   => $inmueble['inmueble_updated_at'],
                      'direccion_created_at'  => $inmueble['direccion_created_at'],
                      'direccion_updated_at'  => $inmueble['direccion_updated_at'],
                      'servicios'             => $serviciosPorInmueble[$inmueble['id']] ?? [],
                  ];
                ?>
                <tr data-property-row data-search="<?= esc($searchText, 'attr') ?>" data-type="<?= esc(mb_strtolower($inmueble['tipo']), 'attr') ?>" data-status="<?= esc(mb_strtolower($inmueble['estado']), 'attr') ?>">
                  <td><div class="property"><span class="property-icon icon"><?= esc($iconoTipo) ?></span><span><strong class="property-name"><?= esc($inmueble['alias_nombre']) ?></strong><span class="property-id">ID: INM-<?= esc(str_pad((string) $inmueble['id'], 4, '0', STR_PAD_LEFT)) ?></span></span></div></td>
                  <td class="address"><?= esc($direccion) ?></td>
                  <td><span class="type-tag"><?= esc($inmueble['tipo']) ?></span></td>
                  <td><span class="area"><?= esc($formatNumber($inmueble['superficie'], 2)) ?> m²</span></td>
                  <td class="services">
                    <span class="services-count"><span class="icon" style="font-size:14px">power</span><?= esc($inmueble['servicios_activos']) ?> activos</span>
                    <span class="service-tags">
                      <?php foreach (array_slice($serviciosActivos, 0, 2) as $servicio): ?><span class="service-tag"><?= esc($servicio) ?></span><?php endforeach ?>
                      <?php if (count($serviciosActivos) > 2): ?><span class="service-tag">+<?= esc(count($serviciosActivos) - 2) ?></span><?php elseif ($serviciosActivos === []): ?><span class="service-tag">Sin servicios activos</span><?php endif ?>
                    </span>
                  </td>
                  <td><span class="status<?= esc($claseEstado, 'attr') ?>"><?= esc($inmueble['estado']) ?></span></td>
                  <td>
                    <div class="row-actions">
                      <button
                        class="row-action"
                        type="button"
                        data-view-property
                        data-property-details="<?= esc(json_encode($detalleInmueble, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), 'attr') ?>"
                        aria-label="Ver detalles de <?= esc($inmueble['alias_nombre'], 'attr') ?>"
                        title="Ver detalles"
                      ><span class="icon" aria-hidden="true">visibility</span><span class="visually-hidden">Ver detalles</span></button>
                      <button
                        class="row-action"
                        type="button"
                        data-edit-property
                        aria-label="Modificar <?= esc($inmueble['alias_nombre'], 'attr') ?>"
                        title="Modificar"
                        data-id="<?= esc($inmueble['id'], 'attr') ?>"
                        data-alias="<?= esc($inmueble['alias_nombre'], 'attr') ?>"
                        data-tipo="<?= esc($inmueble['tipo'], 'attr') ?>"
                        data-superficie="<?= esc($inmueble['superficie'], 'attr') ?>"
                        data-estado="<?= esc($inmueble['estado'], 'attr') ?>"
                        data-calle="<?= esc($inmueble['calle'], 'attr') ?>"
                        data-numero="<?= esc($inmueble['numero'], 'attr') ?>"
                        data-piso="<?= esc($inmueble['piso_dpto'] ?? '', 'attr') ?>"
                        data-ciudad="<?= esc($inmueble['ciudad'], 'attr') ?>"
                      ><span class="icon" aria-hidden="true">edit</span><span class="visually-hidden">Modificar inmueble</span></button>
                      <?php if (mb_strtolower(trim($inmueble['estado'])) !== 'inactivo'): ?>
                        <form method="post" action="<?= site_url('inmuebles/' . $inmueble['id'] . '/baja') ?>" data-deactivate-form>
                          <?= csrf_field() ?>
                          <button class="row-action danger" type="submit" aria-label="Dar de baja <?= esc($inmueble['alias_nombre'], 'attr') ?>" title="Dar de baja"><span class="icon" aria-hidden="true">person_off</span><span class="visually-hidden">Dar de baja</span></button>
                        </form>
                      <?php endif ?>
                    </div>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
          <div class="empty" id="empty-state">No se encontraron inmuebles con esos filtros.</div>
        </div>
        <footer class="table-footer">
          <div><span id="result-summary"></span><span class="page-size">&nbsp; Filas por página:
            <select id="page-size" aria-label="Filas por página"><option value="5">5</option><option value="10" selected>10</option><option value="20">20</option></select>
          </span></div>
          <nav class="pagination" id="pagination" aria-label="Paginación"></nav>
        </footer>
      </section>
    </main>
  </div>

  <div class="modal-backdrop<?= $abrirModal ? ' open' : '' ?>" id="create-modal" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-head">
        <div><h2 id="modal-title"><?= $editId ? 'Modificar inmueble' : 'Registrar nuevo inmueble' ?></h2><p>Completá los datos de la propiedad y su dirección.</p></div>
        <button class="close-modal" id="close-create" type="button" aria-label="Cerrar"><span class="icon">close</span></button>
      </div>
      <form method="post" action="<?= $editId ? site_url('inmuebles/' . $editId) : site_url('inmuebles') ?>" data-create-action="<?= esc(site_url('inmuebles'), 'attr') ?>">
        <?= csrf_field() ?>
        <div class="form-body">
          <?php if (isset($errors['database'])): ?><div class="field-error form-field full" role="alert"><?= esc($errors['database']) ?></div><?php endif ?>
          <p class="form-section-title">Datos de la propiedad</p>
          <div class="form-field full">
            <label for="alias_nombre">Alias o nombre</label>
            <input id="alias_nombre" name="alias_nombre" maxlength="150" required value="<?= esc($formData['alias_nombre'] ?? '', 'attr') ?>">
            <?php if (isset($errors['alias_nombre'])): ?><span class="field-error"><?= esc($errors['alias_nombre']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="tipo">Tipo de inmueble</label>
            <input id="tipo" name="tipo" maxlength="100" required placeholder="Casa, departamento..." value="<?= esc($formData['tipo'] ?? '', 'attr') ?>">
            <?php if (isset($errors['tipo'])): ?><span class="field-error"><?= esc($errors['tipo']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="superficie">Superficie (m²)</label>
            <input id="superficie" name="superficie" type="number" min="0.01" max="99999999.99" step="0.01" required value="<?= esc($formData['superficie'] ?? '', 'attr') ?>">
            <?php if (isset($errors['superficie'])): ?><span class="field-error"><?= esc($errors['superficie']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="estado">Estado</label>
            <select id="estado" name="estado" required>
              <?php foreach ($estadoOptions as $estado): ?><option value="<?= esc($estado, 'attr') ?>"<?= ($formData['estado'] ?? 'Activo') === $estado ? ' selected' : '' ?>><?= esc($estado) ?></option><?php endforeach ?>
            </select>
            <?php if (isset($errors['estado'])): ?><span class="field-error"><?= esc($errors['estado']) ?></span><?php endif ?>
          </div>
          <p class="form-section-title">Dirección</p>
          <div class="form-field">
            <label for="calle">Calle</label>
            <input id="calle" name="calle" maxlength="150" required value="<?= esc($formData['calle'] ?? '', 'attr') ?>">
            <?php if (isset($errors['calle'])): ?><span class="field-error"><?= esc($errors['calle']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="numero">Número</label>
            <input id="numero" name="numero" maxlength="20" required value="<?= esc($formData['numero'] ?? '', 'attr') ?>">
            <?php if (isset($errors['numero'])): ?><span class="field-error"><?= esc($errors['numero']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="piso_dpto">Piso / departamento <span style="font-weight:400">(opcional)</span></label>
            <input id="piso_dpto" name="piso_dpto" maxlength="50" value="<?= esc($formData['piso_dpto'] ?? '', 'attr') ?>">
            <?php if (isset($errors['piso_dpto'])): ?><span class="field-error"><?= esc($errors['piso_dpto']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="ciudad">Ciudad / localidad</label>
            <input id="ciudad" name="ciudad" maxlength="100" required value="<?= esc($formData['ciudad'] ?? '', 'attr') ?>">
            <?php if (isset($errors['ciudad'])): ?><span class="field-error"><?= esc($errors['ciudad']) ?></span><?php endif ?>
          </div>
        </div>
        <div class="modal-actions"><button class="button" id="cancel-create" type="button">Cancelar</button><button class="button primary" id="save-property" type="submit"><?= $editId ? 'Guardar cambios' : 'Guardar inmueble' ?></button></div>
      </form>
    </section>
  </div>

  <div class="modal-backdrop" id="property-details-modal" role="presentation" aria-hidden="true">
    <section class="modal details-modal" role="dialog" aria-modal="true" aria-labelledby="property-details-title">
      <header class="modal-head">
        <div><h2 id="property-details-title">Detalle del inmueble</h2><p>Información completa de la propiedad y sus servicios asociados.</p></div>
        <button class="close-modal" id="close-property-details" type="button" aria-label="Cerrar detalles"><span class="icon">close</span></button>
      </header>
      <div class="details-body">
        <section class="details-section" aria-labelledby="property-data-heading">
          <h3 id="property-data-heading">Datos del inmueble y dirección</h3>
          <div class="details-grid" id="property-details-grid"></div>
        </section>
        <section class="details-section" aria-labelledby="property-services-heading">
          <h3 id="property-services-heading">Servicios asociados</h3>
          <div class="associated-services" id="property-details-services"></div>
        </section>
      </div>
      <footer class="modal-actions"><button class="button" id="close-property-details-footer" type="button">Cerrar</button></footer>
    </section>
  </div>

  <script>
    const rows = [...document.querySelectorAll("[data-property-row]")];
    const searchInput = document.querySelector("#search-property");
    const typeFilter = document.querySelector("#filter-type");
    const statusFilter = document.querySelector("#filter-status");
    const pageSize = document.querySelector("#page-size");
    const pagination = document.querySelector("#pagination");
    const resultSummary = document.querySelector("#result-summary");
    const emptyState = document.querySelector("#empty-state");
    let currentPage = 1;

    function renderTable() {
      const query = searchInput.value.trim().toLocaleLowerCase("es");
      const type = typeFilter.value;
      const status = statusFilter.value;
      const filtered = rows.filter((row) =>
        row.dataset.search.includes(query)
        && (!type || row.dataset.type === type)
        && (!status || row.dataset.status === status)
      );
      const size = Number(pageSize.value);
      const pageCount = Math.max(1, Math.ceil(filtered.length / size));
      currentPage = Math.min(currentPage, pageCount);
      const start = (currentPage - 1) * size;
      const visible = new Set(filtered.slice(start, start + size));

      rows.forEach((row) => { row.hidden = !visible.has(row); });
      emptyState.style.display = filtered.length ? "none" : "block";
      resultSummary.textContent = filtered.length
        ? `Mostrando ${start + 1} - ${Math.min(start + size, filtered.length)} de ${filtered.length} propiedades`
        : "Mostrando 0 propiedades";

      pagination.replaceChildren();
      const addPageButton = (label, page, disabled = false, current = false) => {
        const button = document.createElement("button");
        button.type = "button";
        button.textContent = label;
        button.disabled = disabled;
        if (current) button.setAttribute("aria-current", "page");
        button.addEventListener("click", () => { currentPage = page; renderTable(); });
        pagination.append(button);
      };
      addPageButton("‹", Math.max(1, currentPage - 1), currentPage === 1);
      for (let page = 1; page <= pageCount; page += 1) {
        addPageButton(String(page), page, false, page === currentPage);
      }
      addPageButton("›", Math.min(pageCount, currentPage + 1), currentPage === pageCount);
    }

    [searchInput, typeFilter, statusFilter].forEach((control) => control.addEventListener("input", () => {
      currentPage = 1;
      renderTable();
    }));
    pageSize.addEventListener("change", () => { currentPage = 1; renderTable(); });
    document.querySelector("#reset-filters").addEventListener("click", () => {
      searchInput.value = "";
      typeFilter.value = "";
      statusFilter.value = "";
      currentPage = 1;
      renderTable();
    });

    document.querySelector("#export-button").addEventListener("click", () => {
      const headers = ["Propiedad / Alias", "Dirección y localidad", "Tipo", "Superficie", "Servicios activos", "Estado"];
      const data = rows.map((row) => [...row.cells].slice(0, 6).map((cell) => cell.innerText.trim()));
      const csv = [headers, ...data].map((record) =>
        record.map((value) => `"${value.replaceAll('"', '""')}"`).join(";")
      ).join("\r\n");
      const link = document.createElement("a");
      link.href = URL.createObjectURL(new Blob(["\uFEFF", csv], { type: "text/csv;charset=utf-8" }));
      link.download = "inmuebles.csv";
      link.click();
      URL.revokeObjectURL(link.href);
    });

    const modal = document.querySelector("#create-modal");
    const form = modal.querySelector("form");
    const closeModal = () => modal.classList.remove("open");
    const detailsModal = document.querySelector("#property-details-modal");
    const detailsGrid = document.querySelector("#property-details-grid");
    const detailsServices = document.querySelector("#property-details-services");
    const detailsTitle = document.querySelector("#property-details-title");
    const closeDetails = () => {
      detailsModal.classList.remove("open");
      detailsModal.setAttribute("aria-hidden", "true");
    };
    const appendDetail = (container, label, value) => {
      const item = document.createElement("div");
      item.className = "detail-item";
      const labelElement = document.createElement("span");
      labelElement.className = "detail-item-label";
      labelElement.textContent = label;
      const valueElement = document.createElement("span");
      valueElement.className = "detail-item-value";
      valueElement.textContent = value === null || value === "" ? "No disponible" : String(value);
      item.append(labelElement, valueElement);
      container.append(item);
    };

    document.querySelectorAll("[data-view-property]").forEach((button) => {
      button.addEventListener("click", () => {
        const property = JSON.parse(button.dataset.propertyDetails);
        detailsTitle.textContent = property.alias_nombre;
        detailsGrid.replaceChildren();
        detailsServices.replaceChildren();

        [
          ["ID del inmueble", property.id],
          ["Alias o nombre", property.alias_nombre],
          ["Tipo", property.tipo],
          ["Superficie", `${property.superficie} m²`],
          ["Estado", property.estado],
          ["ID de dirección", property.direccion_id],
          ["Calle", property.calle],
          ["Número", property.numero],
          ["Piso / departamento", property.piso_dpto],
          ["Ciudad / localidad", property.ciudad],
          ["Inmueble creado", property.inmueble_created_at],
          ["Inmueble actualizado", property.inmueble_updated_at],
          ["Dirección creada", property.direccion_created_at],
          ["Dirección actualizada", property.direccion_updated_at],
        ].forEach(([label, value]) => appendDetail(detailsGrid, label, value));

        if (property.servicios.length === 0) {
          const emptyServices = document.createElement("p");
          emptyServices.className = "no-associated-services";
          emptyServices.textContent = "Este inmueble no tiene servicios asociados.";
          detailsServices.append(emptyServices);
        } else {
          property.servicios.forEach((service) => {
            const card = document.createElement("article");
            card.className = "associated-service";
            const heading = document.createElement("h4");
            heading.textContent = service.servicio_nombre;
            card.append(heading);
            [
              ["ID de asociación", service.asociacion_id],
              ["ID de servicio", service.servicio_id],
              ["Categoría", service.servicio_categoria],
              ["Unidad de medida", service.unidad_medida_std],
              ["Proveedor", service.proveedor],
              ["Titular", service.titular_inmueble],
              ["Estado de la asociación", service.estado_asociacion],
              ["Número de medidor", service.nro_medidor],
              ["Cuenta / contrato", service.nro_cuenta_contrato],
              ["Código de pago electrónico", service.cod_pago_electronico],
              ["Domicilio de facturación", service.domicilio_facturacion],
              ["Asociación creada", service.asociacion_created_at],
              ["Asociación actualizada", service.asociacion_updated_at],
            ].forEach(([label, value]) => appendDetail(card, label, value));
            card.classList.add("details-grid");
            detailsServices.append(card);
          });
        }

        detailsModal.classList.add("open");
        detailsModal.removeAttribute("aria-hidden");
        document.querySelector("#close-property-details").focus();
      });
    });
    document.querySelector("#open-create").addEventListener("click", () => {
      form.reset();
      form.action = form.dataset.createAction;
      document.querySelector("#modal-title").textContent = "Registrar nuevo inmueble";
      document.querySelector("#save-property").textContent = "Guardar inmueble";
      modal.classList.add("open");
    });
    document.querySelectorAll("[data-edit-property]").forEach((button) => {
      button.addEventListener("click", () => {
        form.action = `${form.dataset.createAction}/${button.dataset.id}`;
        document.querySelector("#modal-title").textContent = "Modificar inmueble";
        document.querySelector("#save-property").textContent = "Guardar cambios";
        for (const [field, value] of Object.entries({
          alias_nombre: button.dataset.alias,
          tipo: button.dataset.tipo,
          superficie: button.dataset.superficie,
          estado: button.dataset.estado,
          calle: button.dataset.calle,
          numero: button.dataset.numero,
          piso_dpto: button.dataset.piso,
          ciudad: button.dataset.ciudad,
        })) {
          form.elements.namedItem(field).value = value;
        }
        modal.classList.add("open");
      });
    });
    document.querySelectorAll("[data-deactivate-form]").forEach((deactivateForm) => {
      deactivateForm.addEventListener("submit", (event) => {
        if (!window.confirm("¿Querés dar de baja este inmueble? Se conservará el registro y quedará como Inactivo.")) {
          event.preventDefault();
        }
      });
    });
    document.querySelector("#close-create").addEventListener("click", closeModal);
    document.querySelector("#cancel-create").addEventListener("click", closeModal);
    modal.addEventListener("click", (event) => { if (event.target === modal) closeModal(); });
    document.querySelector("#close-property-details").addEventListener("click", closeDetails);
    document.querySelector("#close-property-details-footer").addEventListener("click", closeDetails);
    detailsModal.addEventListener("click", (event) => { if (event.target === detailsModal) closeDetails(); });
    document.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        closeModal();
        closeDetails();
      }
    });

    renderTable();
  </script>
</body>
</html>

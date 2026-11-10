<?php

$formData = $formData ?? [];
$errors = $errors ?? [];
$editId = isset($editId) ? (int) $editId : null;
$abrirModal = $formData !== [] || $errors !== [];
$serviciosPorCategoria = [];

foreach ($servicios as $servicio) {
    $categoria = $servicio['categoria'];
    $serviciosPorCategoria[$categoria][] = $servicio;
}

ksort($serviciosPorCategoria, SORT_NATURAL | SORT_FLAG_CASE);
$categorias = array_keys($serviciosPorCategoria);
$iconoCategoria = static function (string $categoria): string {
    $categoria = mb_strtolower($categoria);

    return match (true) {
        str_contains($categoria, 'electric') || str_contains($categoria, 'energ') => 'bolt',
        str_contains($categoria, 'agua') => 'water_drop',
        str_contains($categoria, 'gas') => 'local_fire_department',
        str_contains($categoria, 'internet') || str_contains($categoria, 'telecom') => 'router',
        str_contains($categoria, 'expensa') || str_contains($categoria, 'consorcio') => 'apartment',
        str_contains($categoria, 'impuesto') || str_contains($categoria, 'tasa') => 'account_balance',
        default => 'miscellaneous_services',
    };
};
$formatError = static fn (string $field): string => isset($errors[$field])
    ? '<span class="field-error">' . esc($errors[$field]) . '</span>'
    : '';

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6fafe">
  <title>Catálogo de servicios | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <style>
    :root { font-family: Inter, "Segoe UI", sans-serif; color: #171c1f; background: #f0f4f8; font-synthesis: none; --navy: #102a43; --blue: #2d628e; --muted: #627d98; --line: #e2e8f0; --soft: #f0f4f8; }
    * { box-sizing: border-box; }
    [hidden] { display: none !important; }
    body { min-width: 320px; min-height: 100vh; margin: 0; }
    button, input { font: inherit; }
    button { cursor: pointer; }
    .icon { font-family: "Material Symbols Outlined"; font-size: 20px; font-weight: normal; font-style: normal; line-height: 1; white-space: nowrap; -webkit-font-smoothing: antialiased; }
    .workspace { min-height: 100vh; margin-left: 256px; }
    .topbar { position: sticky; z-index: 3; top: 0; display: flex; height: 64px; align-items: center; justify-content: space-between; gap: 16px; padding: 0 26px; border-bottom: 1px solid var(--line); background: rgb(255 255 255 / 96%); }
    .top-brand { display: flex; align-items: center; gap: 10px; color: var(--navy); font: 700 15px Manrope, sans-serif; }
    .top-context { color: var(--muted); font-size: 11px; }
    .page { width: min(1320px, 100%); margin: 0 auto; padding: 24px 26px 40px; }
    .page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 22px; }
    .eyebrow { margin: 0 0 5px; color: var(--blue); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; }
    h1, h2, p { margin-top: 0; }
    h1 { margin-bottom: 5px; color: var(--navy); font: 700 clamp(24px, 2.4vw, 34px)/1.2 Manrope, sans-serif; letter-spacing: -.025em; }
    .subtitle { margin-bottom: 0; color: var(--muted); font-size: 13px; }
    .button { display: inline-flex; min-height: 40px; align-items: center; justify-content: center; gap: 7px; border: 0; border-radius: 8px; padding: 0 14px; background: var(--blue); color: white; box-shadow: 0 1px 3px rgb(16 42 67 / 8%); font-size: 11px; font-weight: 600; }
    .button:hover { background: var(--navy); }
    .button.secondary { border: 1px solid var(--line); background: white; color: var(--navy); }
    .button.secondary:hover { background: var(--soft); }
    .summary { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; margin-bottom: 18px; }
    .stat { min-height: 108px; border-radius: 14px; padding: 18px 20px; background: white; box-shadow: 0 1px 4px rgb(16 42 67 / 5%); }
    .stat-label { display: block; color: var(--muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
    .stat-value { display: block; margin-top: 9px; color: var(--navy); font: 700 28px/1 Manrope, sans-serif; }
    .filters { display: flex; align-items: end; gap: 10px; margin-bottom: 16px; border-radius: 13px; padding: 14px; background: white; box-shadow: 0 1px 4px rgb(16 42 67 / 5%); }
    .field { display: grid; flex: 1; gap: 5px; min-width: 0; }
    .field label { color: var(--muted); font-size: 10px; font-weight: 600; }
    .field input { width: 100%; min-height: 39px; border: 1px solid var(--line); border-radius: 8px; outline: 0; padding: 0 11px; background: var(--soft); color: #263d4d; font-size: 11px; }
    .field input:focus { border-color: #7aa4c4; background: white; box-shadow: 0 0 0 3px rgb(45 98 142 / 10%); }
    .table-card { overflow: hidden; border-radius: 14px; background: white; box-shadow: 0 1px 5px rgb(16 42 67 / 5%); }
    .table-scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; text-align: left; }
    th { padding: 14px 16px; background: var(--soft); color: var(--muted); font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .035em; }
    td { padding: 14px 16px; border-top: 1px solid #edf1f5; color: #344b5c; font-size: 11px; vertical-align: middle; }
    tbody tr:hover { background: #fbfdff; }
    .service-name { color: var(--navy); font-size: 12px; font-weight: 700; }
    .category { display: inline-flex; border-radius: 5px; padding: 5px 7px; background: #e9eef3; color: #526b7e; font-size: 10px; }
    .row-actions { display: flex; flex-wrap: wrap; gap: 6px; }
    .row-action { min-height: 30px; border: 1px solid var(--line); border-radius: 6px; padding: 0 9px; background: white; color: var(--navy); font-size: 10px; font-weight: 600; white-space: nowrap; }
    .row-action:hover { background: var(--soft); }
    .row-action.danger { color: #b42318; }
    .notice { margin-bottom: 14px; border-radius: 8px; padding: 11px 14px; background: #e7f6ed; color: #146c43; font-size: 11px; }
    .notice.error { background: #fef3f2; color: #b42318; }
    .empty { padding: 36px 16px; color: var(--muted); text-align: center; font-size: 12px; }
    .modal-backdrop { position: fixed; z-index: 10; inset: 0; display: none; align-items: center; justify-content: center; overflow-y: auto; padding: 20px; background: rgb(7 29 49 / 55%); }
    .modal-backdrop.open { display: flex; }
    .modal { width: min(560px, 100%); max-height: calc(100vh - 40px); overflow-y: auto; border-radius: 14px; background: white; box-shadow: 0 20px 60px rgb(0 0 0 / 22%); }
    .modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; border-bottom: 1px solid var(--line); padding: 18px 20px; }
    .modal-head h2 { margin: 0; color: var(--navy); font: 700 18px Manrope, sans-serif; }
    .modal-head p { margin: 5px 0 0; color: var(--muted); font-size: 10px; }
    .close-modal { display: grid; width: 32px; height: 32px; place-items: center; border: 0; border-radius: 7px; background: var(--soft); color: var(--navy); }
    .form-body { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 18px 20px; }
    .form-field { display: grid; gap: 5px; min-width: 0; }
    .form-field.full { grid-column: 1 / -1; }
    .form-field label { color: var(--muted); font-size: 10px; font-weight: 600; }
    .form-field input { min-height: 38px; border: 1px solid var(--line); border-radius: 7px; padding: 0 10px; color: #263d4d; font-size: 11px; }
    .field-error { color: #b42318; font-size: 10px; }
    .modal-actions { display: flex; justify-content: flex-end; gap: 8px; border-top: 1px solid var(--line); padding: 13px 20px; }
    @media (max-width: 1100px) { .workspace { margin-left: 220px; } .page { padding: 20px; } }
    @media (max-width: 760px) {
      .workspace { margin-left: 0; }
      .topbar { position: static; height: 50px; padding: 0 14px; }
      .top-context { display: none; }
      .page { padding: 18px 12px 30px; }
      .page-head { flex-direction: column; }
      .summary { grid-template-columns: 1fr; gap: 8px; }
      .stat { min-height: 82px; padding: 14px; }
      .filters { align-items: stretch; flex-direction: column; }
    }
    @media (max-width: 520px) { .form-body { grid-template-columns: 1fr; } .form-field.full { grid-column: auto; } }
    :root { background: #f0f4f8; --soft: #f0f4f8; --line: #e2e8f0; }
    .workspace { margin-left: 256px; }
    .page { width: min(1540px, 100%); padding: 28px 28px 40px; }
    .page-head { align-items: center; margin-bottom: 26px; }
    .eyebrow { font-size: 11px; }
    h1 { font-size: clamp(26px, 2.5vw, 36px); }
    .subtitle { max-width: 900px; font-size: 15px; line-height: 1.55; }
    .button { min-height: 44px; padding: 0 18px; font-size: 13px; }
    .summary { grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin-bottom: 28px; }
    .category-card { display: flex; min-height: 206px; flex-direction: column; justify-content: space-between; border: 1px solid transparent; border-radius: 16px; padding: 20px; background: white; color: inherit; text-align: left; box-shadow: 0 1px 2px rgb(16 42 67 / 4%); transition: border-color .15s, box-shadow .15s, transform .15s; }
    .category-card:hover, .category-card.active { border-color: #9dceff; box-shadow: 0 4px 14px rgb(16 42 67 / 8%); transform: translateY(-1px); }
    .category-card.active { outline: 2px solid rgb(45 98 142 / 14%); }
    .category-card-top, .category-card-bottom { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
    .category-icon { display: grid; width: 52px; height: 52px; place-items: center; border-radius: 12px; background: #e8eef3; color: #2d628e; }
    .category-icon .icon { font-size: 27px; }
    .category-count { border-radius: 999px; padding: 5px 10px; background: #f0f4f8; color: #627d98; font-size: 11px; font-weight: 600; white-space: nowrap; }
    .category-title { display: block; margin: 20px 0 5px; color: #102a43; font: 600 18px/1.35 Manrope, sans-serif; }
    .category-description { display: block; color: #627d98; font-size: 13px; line-height: 1.5; }
    .category-provider-count { color: #2d628e; font-size: 13px; font-weight: 600; }
    .category-provider-count .icon { margin-right: 5px; vertical-align: -4px; }
    .category-card-indicator { width: 9px; height: 9px; border-radius: 50%; background: #2d628e; }
    .filters { align-items: center; gap: 10px; margin: 0; border-radius: 0; padding: 18px 20px; box-shadow: none; }
    .field { flex: 0 1 360px; }
    .field label { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
    .field input, .field select { min-height: 44px; border: 0; border-radius: 10px; padding: 0 14px; background: #f0f4f8; font-size: 13px; }
    .filters select { min-height: 44px; max-width: 230px; border: 0; border-radius: 10px; padding: 0 14px; background: #f0f4f8; color: #344b5c; font-size: 13px; }
    .results-count { margin-left: auto; color: #627d98; font-size: 13px; white-space: nowrap; }
    .results-count strong { color: #102a43; }
    .table-card { border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: none; }
    .table-scroll { overflow-x: auto; }
    table { min-width: 820px; }
    th { height: 52px; padding: 12px 18px; background: #f0f4f8; font-size: 12px; }
    td { padding: 16px 18px; font-size: 13px; }
    tbody tr:hover { background: #f8fafc; }
    .service-cell { display: flex; min-width: 240px; align-items: center; gap: 11px; }
    .service-icon { display: grid; width: 44px; height: 44px; flex: 0 0 44px; place-items: center; border-radius: 10px; background: #e8f2fd; color: #2d628e; }
    .service-icon .icon { font-size: 23px; }
    .service-name { display: block; font-size: 14px; line-height: 1.45; }
    .service-meta { display: block; margin-top: 4px; color: #627d98; font-size: 12px; }
    .category { border-radius: 999px; padding: 5px 10px; background: #eaeef2; color: #43474d; font-size: 11px; }
    .unit-value { color: #102a43; font-size: 14px; font-weight: 600; white-space: nowrap; }
    .provider-chip { display: inline-block; margin: 2px 3px 2px 0; border-radius: 5px; padding: 4px 8px; background: #f0f4f8; color: #344b5c; font-size: 11px; white-space: nowrap; }
    .row-actions { align-items: center; justify-content: flex-end; gap: 8px; }
    .row-actions form { display: inline-flex; }
    .row-action { display: inline-grid; width: 34px; min-height: 34px; place-items: center; border: 0; padding: 0; color: #627d98; }
    .row-action:hover { background: #e8f2fd; color: #2d628e; }
    .row-action.danger:hover { background: #fef3f2; color: #b42318; }
    .pagination { display: flex; align-items: center; justify-content: space-between; gap: 12px; border-top: 1px solid #edf1f5; padding: 12px 18px; color: #627d98; font-size: 12px; }
    .pagination-pages { display: flex; align-items: center; gap: 5px; }
    .pagination button { min-width: 34px; height: 34px; border: 0; border-radius: 8px; padding: 0 9px; background: #f0f4f8; color: #627d98; font-size: 12px; }
    .pagination button[aria-current="page"] { background: #2d628e; color: white; font-weight: 600; }
    .pagination button:disabled { opacity: .5; cursor: default; }
    .empty { padding: 38px 16px; }
    .visually-hidden { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0, 0, 0, 0); white-space: nowrap; }
    @media (max-width: 1100px) { .workspace { margin-left: 220px; } .page { padding: 22px 20px 34px; } .category-card { min-height: 190px; padding: 16px; } }
    @media (max-width: 760px) {
      .workspace { margin-left: 0; }
      .page { padding: 18px 12px 28px; }
      .summary { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 18px; }
      .category-card { min-height: 170px; }
      .filters { align-items: stretch; flex-direction: column; }
      .field { flex-basis: auto; }
      .filters select { max-width: none; width: 100%; }
      .results-count { margin-left: 0; }
      .page-head { align-items: flex-start; }
    }
    @media (max-width: 520px) { .summary { grid-template-columns: 1fr 1fr; gap: 8px; } .category-card { min-height: 164px; padding: 12px; } .category-icon { width: 42px; height: 42px; } .category-title { margin-top: 14px; font-size: 15px; } .category-description { font-size: 11px; } .pagination { align-items: flex-start; flex-direction: column; } }
  </style>
  <link rel="stylesheet" href="<?= base_url('assets/css/sidebar.css') ?>">
</head>
<body>
  <?= view('layouts/sidebar', ['activeSection' => 'catalogo-servicios']) ?>

  <div class="workspace">
    <header class="topbar">
      <div class="top-brand"><span class="icon">domain</span>ServiInmueble</div>
      <span class="top-context">Administración&nbsp; / &nbsp;Catálogo de servicios</span>
    </header>

    <main class="page">
      <section class="page-head">
        <div>
          <p class="eyebrow">Administración&nbsp; › &nbsp;Parámetros globales</p>
          <h1>Catálogo de Tipos de Servicios</h1>
          <p class="subtitle">Configurá los tipos de servicios, unidades de medida y proveedores predeterminados para la asociación en inmuebles.</p>
        </div>
        <button class="button" id="open-create" type="button"><span class="icon">add_circle</span>Nuevo Tipo de Servicio</button>
      </section>

      <?php if ($success): ?><div class="notice" role="status"><?= esc($success) ?></div><?php endif ?>
      <?php if ($error): ?><div class="notice error" role="alert"><?= esc($error) ?></div><?php endif ?>

      <section class="summary" aria-label="Servicios por categoría">
        <?php foreach ($serviciosPorCategoria as $categoria => $serviciosCategoria): ?>
          <?php
            $proveedoresCategoria = array_unique(array_column($serviciosCategoria, 'proveedor'));
            $nombresServicios = implode(', ', array_column($serviciosCategoria, 'nombre'));
            $cantidadServicios = count($serviciosCategoria);
          ?>
          <button
            class="category-card"
            type="button"
            data-category-card
            data-category="<?= esc(mb_strtolower($categoria), 'attr') ?>"
            aria-pressed="false"
          >
            <span class="category-card-top">
              <span class="category-icon"><span class="icon"><?= esc($iconoCategoria($categoria)) ?></span></span>
              <span class="category-count"><?= esc($cantidadServicios) ?> <?= $cantidadServicios === 1 ? 'tipo' : 'tipos' ?></span>
            </span>
            <span>
              <span class="category-title"><?= esc($categoria) ?></span>
              <span class="category-description"><?= esc($nombresServicios) ?></span>
            </span>
            <span class="category-card-bottom">
              <span class="category-provider-count"><span class="icon">domain</span><?= esc(count($proveedoresCategoria)) ?> <?= count($proveedoresCategoria) === 1 ? 'proveedor' : 'proveedores' ?></span>
              <span class="category-card-indicator" aria-hidden="true"></span>
            </span>
          </button>
        <?php endforeach ?>
      </section>

      <section class="table-card" aria-label="Catálogo de servicios">
        <div class="filters" aria-label="Filtros del catálogo">
          <div class="field">
            <label for="service-search">Filtrar por nombre o categoría</label>
            <input id="service-search" type="search" placeholder="Filtrar por nombre, categoría..." autocomplete="off">
          </div>
          <select id="category-filter" aria-label="Filtrar por categoría">
            <option value="">Todas las categorías</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= esc(mb_strtolower($categoria), 'attr') ?>"><?= esc($categoria) ?></option>
            <?php endforeach ?>
          </select>
          <span class="results-count" id="results-count" aria-live="polite">Mostrando <strong><?= esc(count($servicios)) ?></strong> de <?= esc(count($servicios)) ?> tipos</span>
        </div>
        <div class="table-scroll">
          <table>
            <thead><tr><th>Tipo / Servicio</th><th>Categoría</th><th>Unidad de medida</th><th>Proveedor predeterminado</th><th>Acciones</th></tr></thead>
            <tbody id="service-rows">
              <?php foreach ($servicios as $servicio): ?>
                <?php
                  $textoBusqueda = mb_strtolower(implode(' ', [
                      $servicio['nombre'],
                      $servicio['categoria'],
                      $servicio['unidad_medida_std'],
                      $servicio['proveedor'],
                  ]));
                ?>
                <tr data-service-row data-search="<?= esc($textoBusqueda, 'attr') ?>" data-category="<?= esc(mb_strtolower($servicio['categoria']), 'attr') ?>">
                  <td>
                    <div class="service-cell">
                      <span class="service-icon"><span class="icon"><?= esc($iconoCategoria($servicio['categoria'])) ?></span></span>
                      <span><span class="service-name"><?= esc($servicio['nombre']) ?></span><span class="service-meta">Tipo de servicio</span></span>
                    </div>
                  </td>
                  <td><span class="category"><?= esc($servicio['categoria']) ?></span></td>
                  <td><span class="unit-value"><?= esc($servicio['unidad_medida_std']) ?></span></td>
                  <td><span class="provider-chip"><?= esc($servicio['proveedor']) ?></span></td>
                  <td>
                    <div class="row-actions">
                      <button
                        class="row-action"
                        type="button"
                        data-edit-service
                        data-id="<?= esc($servicio['id'], 'attr') ?>"
                        data-name="<?= esc($servicio['nombre'], 'attr') ?>"
                        data-unit="<?= esc($servicio['unidad_medida_std'], 'attr') ?>"
                        data-provider="<?= esc($servicio['proveedor'], 'attr') ?>"
                        data-category="<?= esc($servicio['categoria'], 'attr') ?>"
                      >
                        <span class="icon" aria-hidden="true">edit</span>
                        <span class="visually-hidden">Modificar <?= esc($servicio['nombre']) ?></span>
                      </button>
                      <form method="post" action="<?= site_url('tipos-servicio/' . $servicio['id'] . '/baja') ?>" data-delete-form>
                        <?= csrf_field() ?>
                        <button class="row-action danger" type="submit" aria-label="Eliminar <?= esc($servicio['nombre'], 'attr') ?>" title="Eliminar"><span class="icon" aria-hidden="true">block</span></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach ?>
            </tbody>
          </table>
        </div>
        <div class="empty" id="empty-state"<?= $servicios === [] ? '' : ' hidden' ?>>
          <?= $servicios === [] ? 'Todavía no hay servicios registrados.' : 'No se encontraron servicios con ese criterio.' ?>
        </div>
        <nav class="pagination" id="pagination" aria-label="Paginación del catálogo"<?= $servicios === [] ? ' hidden' : '' ?>>
          <span id="pagination-summary">Página 1 de 1</span>
          <div class="pagination-pages">
            <button type="button" id="previous-page" aria-label="Página anterior" disabled>Anterior</button>
            <div id="pagination-numbers" class="pagination-pages"></div>
            <button type="button" id="next-page" aria-label="Página siguiente" disabled>Siguiente</button>
          </div>
        </nav>
      </section>
    </main>
  </div>

  <div class="modal-backdrop<?= $abrirModal ? ' open' : '' ?>" id="service-modal"<?= $abrirModal ? '' : ' aria-hidden="true"' ?>>
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <header class="modal-head">
        <div><h2 id="modal-title"><?= $editId ? 'Modificar servicio' : 'Nuevo servicio' ?></h2><p>Completá la información del tipo de servicio.</p></div>
        <button class="close-modal" type="button" data-close-modal aria-label="Cerrar"><span class="icon">close</span></button>
      </header>
      <form method="post" action="<?= $editId ? site_url('tipos-servicio/' . $editId) : site_url('tipos-servicio') ?>" id="service-form" data-create-action="<?= esc(site_url('tipos-servicio'), 'attr') ?>">
        <?= csrf_field() ?>
        <div class="form-body">
          <div class="form-field full">
            <label for="service-name">Nombre</label>
            <input id="service-name" name="nombre" maxlength="100" required value="<?= esc($formData['nombre'] ?? '') ?>">
            <?= $formatError('nombre') ?>
          </div>
          <div class="form-field">
            <label for="service-category">Categoría</label>
            <input id="service-category" name="categoria" maxlength="100" required value="<?= esc($formData['categoria'] ?? '') ?>">
            <?= $formatError('categoria') ?>
          </div>
          <div class="form-field">
            <label for="service-unit">Unidad de medida estándar</label>
            <input id="service-unit" name="unidad_medida_std" maxlength="30" required value="<?= esc($formData['unidad_medida_std'] ?? '') ?>">
            <?= $formatError('unidad_medida_std') ?>
          </div>
          <div class="form-field full">
            <label for="service-provider">Proveedor</label>
            <input id="service-provider" name="proveedor" maxlength="150" required value="<?= esc($formData['proveedor'] ?? '') ?>">
            <?= $formatError('proveedor') ?>
          </div>
        </div>
        <footer class="modal-actions">
          <button class="button secondary" type="button" data-close-modal>Cancelar</button>
          <button class="button" type="submit"><?= $editId ? 'Guardar cambios' : 'Registrar servicio' ?></button>
        </footer>
      </form>
    </section>
  </div>

  <script>
    (() => {
      const modal = document.getElementById("service-modal");
      const form = document.getElementById("service-form");
      const title = document.getElementById("modal-title");
      const nameInput = document.getElementById("service-name");
      const categoryInput = document.getElementById("service-category");
      const unitInput = document.getElementById("service-unit");
      const providerInput = document.getElementById("service-provider");
      const createAction = form.dataset.createAction;

      function openModal(service = null) {
        form.reset();
        form.action = service ? `${createAction}/${service.id}` : createAction;
        title.textContent = service ? "Modificar servicio" : "Nuevo servicio";
        form.querySelector('[type="submit"]').textContent = service ? "Guardar cambios" : "Registrar servicio";
        nameInput.value = service?.name ?? "";
        categoryInput.value = service?.category ?? "";
        unitInput.value = service?.unit ?? "";
        providerInput.value = service?.provider ?? "";
        modal.classList.add("open");
        modal.removeAttribute("aria-hidden");
        nameInput.focus();
      }

      function closeModal() {
        modal.classList.remove("open");
        modal.setAttribute("aria-hidden", "true");
      }

      document.getElementById("open-create").addEventListener("click", () => openModal());
      document.querySelectorAll("[data-edit-service]").forEach((button) => {
        button.addEventListener("click", () => openModal({
          id: button.dataset.id,
          name: button.dataset.name,
          category: button.dataset.category,
          unit: button.dataset.unit,
          provider: button.dataset.provider,
        }));
      });
      document.querySelectorAll("[data-close-modal]").forEach((button) => button.addEventListener("click", closeModal));
      modal.addEventListener("click", (event) => {
        if (event.target === modal) closeModal();
      });
      document.addEventListener("keydown", (event) => {
        if (event.key === "Escape" && modal.classList.contains("open")) closeModal();
      });
      document.querySelectorAll("[data-delete-form]").forEach((deleteForm) => {
        deleteForm.addEventListener("submit", (event) => {
          if (!window.confirm("¿Eliminar este servicio? No se puede eliminar si ya tiene asociaciones registradas.")) {
            event.preventDefault();
          }
        });
      });

      const search = document.getElementById("service-search");
      const categoryFilter = document.getElementById("category-filter");
      const rows = [...document.querySelectorAll("[data-service-row]")];
      const categoryCards = [...document.querySelectorAll("[data-category-card]")];
      const empty = document.getElementById("empty-state");
      const count = document.getElementById("results-count");
      const pagination = document.getElementById("pagination");
      const paginationSummary = document.getElementById("pagination-summary");
      const paginationNumbers = document.getElementById("pagination-numbers");
      const previousPage = document.getElementById("previous-page");
      const nextPage = document.getElementById("next-page");
      const pageSize = 8;
      let currentPage = 1;

      function updateCatalog() {
        const query = search.value.trim().toLocaleLowerCase();
        const category = categoryFilter.value;
        const matchingRows = rows.filter((row) =>
          row.dataset.search.includes(query)
          && (category === "" || row.dataset.category === category),
        );
        const pageCount = Math.max(1, Math.ceil(matchingRows.length / pageSize));
        currentPage = Math.min(currentPage, pageCount);
        const firstIndex = (currentPage - 1) * pageSize;
        const lastIndex = Math.min(firstIndex + pageSize, matchingRows.length);

        rows.forEach((row) => {
          row.hidden = true;
        });
        matchingRows.slice(firstIndex, lastIndex).forEach((row) => {
          row.hidden = false;
        });

        empty.hidden = matchingRows.length !== 0;
        pagination.hidden = matchingRows.length === 0;
        count.innerHTML = `Mostrando <strong>${matchingRows.length === 0 ? 0 : firstIndex + 1}-${lastIndex}</strong> de ${matchingRows.length} tipos`;
        paginationSummary.textContent = `Página ${currentPage} de ${pageCount}`;
        previousPage.disabled = currentPage === 1;
        nextPage.disabled = currentPage === pageCount;
        paginationNumbers.replaceChildren();

        for (let pageNumber = 1; pageNumber <= pageCount; pageNumber++) {
          const button = document.createElement("button");
          button.type = "button";
          button.textContent = String(pageNumber);
          button.setAttribute("aria-label", `Página ${pageNumber}`);
          if (pageNumber === currentPage) {
            button.setAttribute("aria-current", "page");
          }
          button.addEventListener("click", () => {
            currentPage = pageNumber;
            updateCatalog();
          });
          paginationNumbers.append(button);
        }

        categoryCards.forEach((card) => {
          const selected = card.dataset.category === category;
          card.classList.toggle("active", selected);
          card.setAttribute("aria-pressed", String(selected));
        });
      }

      search.addEventListener("input", () => {
        currentPage = 1;
        updateCatalog();
      });
      categoryFilter.addEventListener("change", () => {
        currentPage = 1;
        updateCatalog();
      });
      categoryCards.forEach((card) => {
        card.addEventListener("click", () => {
          categoryFilter.value = categoryFilter.value === card.dataset.category ? "" : card.dataset.category;
          currentPage = 1;
          updateCatalog();
        });
      });
      previousPage.addEventListener("click", () => {
        currentPage--;
        updateCatalog();
      });
      nextPage.addEventListener("click", () => {
        currentPage++;
        updateCatalog();
      });
      updateCatalog();
    })();
  </script>
</body>
</html>

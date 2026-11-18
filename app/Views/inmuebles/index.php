<?php

$errors      = session()->getFlashdata('errors') ?? [];
$formData    = session()->getFlashdata('formData') ?? [];
$success     = session()->getFlashdata('success');
$error       = session()->getFlashdata('error');
$editId      = session()->getFlashdata('editId');
$tipos       = array_values(array_unique(array_column($inmuebles, 'tipo')));
$estados     = array_values(array_unique(array_column($inmuebles, 'estado')));
$ciudades    = array_values(array_unique(array_column($inmuebles, 'ciudad')));
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
sort($ciudades);

$formatNumber = static fn ($value, int $decimals = 0): string => number_format((float) $value, $decimals, ',', '.');
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f6fafe">
  <title>Gestion de inmuebles | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/inmuebles.css') ?>?v=5">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/radio-options.css') ?>?v=1">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'inmuebles', 'isAdministrator' => false]) ?>

  <div class="workspace">
    <header class="topbar">
      <?= view('partials/brand') ?>
      <span class="top-context">Gestion patrimonial&nbsp; / &nbsp;Registro de inmuebles</span>

      <!-- Perfil Usuario -->
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
          <p class="eyebrow">Patrimonio inmobiliario / Registro actual</p>
          <h1>Gestion de Inmuebles</h1>
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
          <div><span class="stat-label">Inmuebles activos</span><strong class="stat-value"><?= esc($resumen['activos']) ?></strong><span class="stat-note">Segun el estado registrado</span></div>
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
          <label for="filter-city">Ciudad / localidad</label>
          <select id="filter-city"><option value="">Todas las localidades</option><?php foreach ($ciudades as $ciudad): ?><option value="<?= esc(mb_strtolower($ciudad), 'attr') ?>"><?= esc($ciudad) ?></option><?php endforeach ?></select>
        </div>
        <div class="field">
          <label for="filter-type">Tipo</label>
          <select id="filter-type"><option value="">Todos los tipos</option><?php foreach ($tipos as $tipo): ?><option value="<?= esc(mb_strtolower($tipo), 'attr') ?>"><?= esc($tipo) ?></option><?php endforeach ?></select>
        </div>
        <div class="field">
          <label for="filter-status">Estado</label>
          <select id="filter-status"><option value="">Todos los estados</option><?php foreach ($estados as $estado): ?><option value="<?= esc(mb_strtolower($estado), 'attr') ?>"><?= esc($estado) ?></option><?php endforeach ?></select>
        </div>
        <div class="field">
          <label for="filter-debt">Facturas pendientes</label>
          <select id="filter-debt">
            <option value="">Todas</option>
            <option value="con">Con facturas pendientes</option>
            <option value="sin">Sin facturas pendientes</option>
          </select>
        </div>
        <button class="filter-reset" id="reset-filters" type="button">Limpiar filtros</button>
      </section>

      <section class="property-list" aria-label="Listado de inmuebles">
        <div class="property-grid">
              <?php foreach ($inmuebles as $inmueble): ?>
                <?php
                  $serviciosActivos = array_filter(explode('|', (string) ($inmueble['nombres_servicios_activos'] ?? '')));
                  $iconoTipo = match (mb_strtolower($inmueble['tipo'])) {
                      'casa' => 'home',
                      'departamento' => 'apartment',
                      default => 'storefront',
                  };
                  $claseEstado = $esEstadoActivo($inmueble['estado']) ? '' : ' inactive';
                  $pendientes = $facturasPendientesPorInmueble[(int) $inmueble['id']] ?? 0;
                  $direccion = trim($inmueble['calle'] . ' ' . $inmueble['numero']
                      . ($inmueble['piso_dpto'] ? ', ' . $inmueble['piso_dpto'] : '')
                      . ', ' . $inmueble['ciudad']
                      . ($inmueble['provincia'] ? ', ' . $inmueble['provincia'] : ''));
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
                      'provincia'             => $inmueble['provincia'],
                      'ciudad'                => $inmueble['ciudad'],
                      'inmueble_created_at'   => $inmueble['inmueble_created_at'],
                      'inmueble_updated_at'   => $inmueble['inmueble_updated_at'],
                      'direccion_created_at'  => $inmueble['direccion_created_at'],
                      'direccion_updated_at'  => $inmueble['direccion_updated_at'],
                      'servicios'             => $serviciosPorInmueble[$inmueble['id']] ?? [],
                  ];
                ?>
                <article class="property-card" data-property-row data-search="<?= esc($searchText, 'attr') ?>" data-city="<?= esc(mb_strtolower($inmueble['ciudad']), 'attr') ?>" data-debt="<?= $pendientes > 0 ? 'con' : 'sin' ?>" data-type="<?= esc(mb_strtolower($inmueble['tipo']), 'attr') ?>" data-status="<?= esc(mb_strtolower($inmueble['estado']), 'attr') ?>">
                  <div class="property-card-head">
                    <div class="property"><span class="property-icon icon" aria-hidden="true"><?= esc($iconoTipo) ?></span><div data-export-value="<?= esc($inmueble['alias_nombre'] . ' - ID: INM-' . str_pad((string) $inmueble['id'], 4, '0', STR_PAD_LEFT), 'attr') ?>"><h2 class="property-name"><?= esc($inmueble['alias_nombre']) ?></h2><span class="property-id">ID: INM-<?= esc(str_pad((string) $inmueble['id'], 4, '0', STR_PAD_LEFT)) ?></span></div></div>
                    <span class="status<?= esc($claseEstado, 'attr') ?>" data-export-status><?= esc($inmueble['estado']) ?></span>
                  </div>
                  <p class="address" data-export-value="<?= esc($direccion, 'attr') ?>"><?= esc($direccion) ?></p>
                  <div class="property-card-meta">
                    <span class="type-tag" data-export-value="<?= esc($inmueble['tipo'], 'attr') ?>"><?= esc($inmueble['tipo']) ?></span>
                    <span class="area" data-export-value="<?= esc($formatNumber($inmueble['superficie'], 2) . ' m²', 'attr') ?>"><?= esc($formatNumber($inmueble['superficie'], 2)) ?> m²</span>
                  </div>
                  <div class="services" data-export-value="<?= esc($inmueble['servicios_activos'] . ' activos' . ($serviciosActivos === [] ? ' - Sin servicios activos' : ' - ' . implode(', ', array_slice($serviciosActivos, 0, 2)) . (count($serviciosActivos) > 2 ? ', +' . (count($serviciosActivos) - 2) : '')), 'attr') ?>">
                    <span class="services-count"><span class="icon" style="font-size:14px">power</span><?= esc($inmueble['servicios_activos']) ?> activos</span>
                    <span class="service-tags">
                      <?php foreach (array_slice($serviciosActivos, 0, 2) as $servicio): ?><span class="service-tag"><?= esc($servicio) ?></span><?php endforeach ?>
                      <?php if (count($serviciosActivos) > 2): ?><span class="service-tag">+<?= esc(count($serviciosActivos) - 2) ?></span><?php elseif ($serviciosActivos === []): ?><span class="service-tag">Sin servicios activos</span><?php endif ?>
                    </span>
                  </div>
                  <p class="property-debt"><?= $pendientes > 0 ? esc($pendientes) . ($pendientes === 1 ? ' factura pendiente' : ' facturas pendientes') : 'Sin facturas pendientes' ?></p>
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
                        data-provincia="<?= esc($inmueble['provincia'] ?? '', 'attr') ?>"
                        data-ciudad="<?= esc($inmueble['ciudad'], 'attr') ?>"
                      ><span class="icon" aria-hidden="true">edit</span><span class="visually-hidden">Modificar inmueble</span></button>
                      <?php if (mb_strtolower(trim($inmueble['estado'])) !== 'inactivo'): ?>
                        <form method="post" action="<?= site_url('inmuebles/' . $inmueble['id'] . '/baja') ?>" data-deactivate-form>
                          <?= csrf_field() ?>
                          <button class="row-action danger" type="submit" aria-label="Dar de baja <?= esc($inmueble['alias_nombre'], 'attr') ?>" title="Dar de baja"><span class="icon" aria-hidden="true">person_off</span><span class="visually-hidden">Dar de baja</span></button>
                        </form>
                      <?php endif ?>
                    </div>
                </article>
              <?php endforeach ?>
        </div>
        <div class="empty" id="empty-state"><?= $inmuebles === [] ? 'Todavía no registraste inmuebles.' : 'No se encontraron inmuebles con esos filtros.' ?></div>
        <footer class="table-footer">
          <div><span id="result-summary"></span><span class="page-size">&nbsp; Inmuebles por página:
            <select id="page-size" aria-label="Inmuebles por página"><option value="5">5</option><option value="10" selected>10</option><option value="20">20</option></select>
          </span></div>
          <nav class="pagination" id="pagination" aria-label="Paginacion"></nav>
        </footer>
      </section>
    </main>
  </div>

  <div class="modal-backdrop<?= $abrirModal ? ' open' : '' ?>" id="create-modal" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <div class="modal-head">
        <div><h2 id="modal-title"><?= $editId ? 'Modificar inmueble' : 'Registrar nuevo inmueble' ?></h2><p>Completar los datos de la propiedad y su direccion.</p></div>
        <button class="close-modal" id="close-create" type="button" aria-label="Cerrar"><span class="icon">close</span></button>
      </div>
      <form
        method="post"
        action="<?= $editId ? site_url('inmuebles/' . $editId) : site_url('inmuebles') ?>"
        data-create-action="<?= esc(site_url('inmuebles'), 'attr') ?>"
        data-provinces-url="<?= esc(site_url('inmuebles/geo/provincias'), 'attr') ?>"
        data-localities-url="<?= esc(site_url('inmuebles/geo/provincias'), 'attr') ?>"
        data-initial-province="<?= esc($formData['provincia'] ?? '', 'attr') ?>"
        data-initial-city="<?= esc($formData['ciudad'] ?? '', 'attr') ?>"
      >
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
            <label for="superficie">Superficie (m², mínimo 50)</label>
            <input id="superficie" name="superficie" type="number" min="50" max="99999999.99" step="0.01" required value="<?= esc($formData['superficie'] ?? '', 'attr') ?>">
            <?php if (isset($errors['superficie'])): ?><span class="field-error"><?= esc($errors['superficie']) ?></span><?php endif ?>
          </div>
          <fieldset class="form-field radio-field">
            <legend>Estado</legend>
            <div class="radio-options">
              <?php foreach ($estadoOptions as $estado): ?>
                <label class="radio-option"><input type="radio" name="estado" value="<?= esc($estado, 'attr') ?>" required<?= ($formData['estado'] ?? 'Activo') === $estado ? ' checked' : '' ?>> <span><?= esc($estado) ?></span></label>
              <?php endforeach ?>
            </div>
            <?php if (isset($errors['estado'])): ?><span class="field-error"><?= esc($errors['estado']) ?></span><?php endif ?>
          </fieldset>
          <p class="form-section-title">Direccion</p>
          <div class="form-field">
            <label for="calle">Calle</label>
            <input id="calle" name="calle" maxlength="150" required value="<?= esc($formData['calle'] ?? '', 'attr') ?>">
            <?php if (isset($errors['calle'])): ?><span class="field-error"><?= esc($errors['calle']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="numero">Numero</label>
            <input id="numero" name="numero" maxlength="20" required value="<?= esc($formData['numero'] ?? '', 'attr') ?>">
            <?php if (isset($errors['numero'])): ?><span class="field-error"><?= esc($errors['numero']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="piso_dpto">Piso / departamento <span style="font-weight:400">(opcional)</span></label>
            <input id="piso_dpto" name="piso_dpto" maxlength="50" value="<?= esc($formData['piso_dpto'] ?? '', 'attr') ?>">
            <?php if (isset($errors['piso_dpto'])): ?><span class="field-error"><?= esc($errors['piso_dpto']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="provincia">Provincia</label>
            <select id="provincia" name="provincia" required aria-describedby="location-status">
              <option value="">Cargando provincias...</option>
            </select>
            <?php if (isset($errors['provincia'])): ?><span class="field-error"><?= esc($errors['provincia']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="ciudad">Ciudad / localidad</label>
            <select id="ciudad" name="ciudad" required disabled aria-describedby="location-status">
              <option value="">Primero seleccioná una provincia</option>
            </select>
            <?php if (isset($errors['ciudad'])): ?><span class="field-error"><?= esc($errors['ciudad']) ?></span><?php endif ?>
          </div>
          <div class="form-field full">
            <span id="location-status" role="status" aria-live="polite"></span>
            <button class="button" id="retry-location-catalog" type="button" hidden>Reintentar carga del catálogo</button>
          </div>
        </div>
        <div class="modal-actions"><button class="button" id="cancel-create" type="button">Cancelar</button><button class="button primary" id="save-property" type="submit"><?= $editId ? 'Guardar cambios' : 'Guardar inmueble' ?></button></div>
      </form>
    </section>
  </div>

  <div class="modal-backdrop" id="property-details-modal" role="presentation" aria-hidden="true">
    <section class="modal details-modal" role="dialog" aria-modal="true" aria-labelledby="property-details-title">
      <header class="modal-head">
        <div><h2 id="property-details-title">Detalle del inmueble</h2><p>Informacion completa de la propiedad y sus servicios asociados.</p></div>
        <button class="close-modal" id="close-property-details" type="button" aria-label="Cerrar detalles"><span class="icon">close</span></button>
      </header>
      <div class="details-body">
        <section class="details-section" aria-labelledby="property-data-heading">
          <h3 id="property-data-heading">Datos del inmueble y direccion</h3>
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

  <script src="<?= base_url('js/sidebar.js') ?>"></script>
  <script src="<?= base_url('js/inmuebles.js') ?>?v=7"></script>
</body>
</html>

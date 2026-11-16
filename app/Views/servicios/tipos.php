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
  <title>Catalogo de servicios | ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/tipos-servicio.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'tipos-servicio', 'isAdministrator' => true]) ?>

  <div class="workspace">
    <header class="topbar">
      <div class="top-brand"><span class="icon">domain</span>ServiInmueble</div>
      <span class="top-context">Administracion&nbsp; / &nbsp;Catalogo de servicios</span>

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
          <p class="eyebrow">Administracion&nbsp; / &nbsp;Parametros globales</p>
          <h1>Catalogo de Tipos de Servicios</h1>
          <p class="subtitle">Configura los tipos de servicios, unidades de medida y proveedores predeterminados para la asociacion en inmuebles.</p>
        </div>
        <button class="button" id="open-create" type="button"><span class="icon">add_circle</span>Nuevo Tipo de Servicio</button>
      </section>

      <?php if ($success): ?><div class="notice" role="status"><?= esc($success) ?></div><?php endif ?>
      <?php if ($error): ?><div class="notice error" role="alert"><?= esc($error) ?></div><?php endif ?>

      <section class="summary" aria-label="Servicios por categoria">
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

      <section class="table-card" aria-label="Catalogo de servicios">
        <div class="filters" aria-label="Filtros del catalogo">
          <div class="field">
            <label for="service-search">Filtrar por nombre o categoria</label>
            <input id="service-search" type="search" placeholder="Filtrar por nombre, categoria..." autocomplete="off">
          </div>
          <select id="category-filter" aria-label="Filtrar por categoria">
            <option value="">Todas las categorias</option>
            <?php foreach ($categorias as $categoria): ?>
              <option value="<?= esc(mb_strtolower($categoria), 'attr') ?>"><?= esc($categoria) ?></option>
            <?php endforeach ?>
          </select>
          <span class="results-count" id="results-count" aria-live="polite">Mostrando <strong><?= esc(count($servicios)) ?></strong> de <?= esc(count($servicios)) ?> tipos</span>
        </div>
        <div class="table-scroll">
          <table>
            <thead><tr><th>Tipo / Servicio</th><th>Categoria</th><th>Unidad de medida</th><th>Proveedor predeterminado</th><th>Acciones</th></tr></thead>
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
          <?= $servicios === [] ? 'Todavia no hay servicios registrados.' : 'No se encontraron servicios con ese criterio.' ?>
        </div>
        <nav class="pagination" id="pagination" aria-label="Paginacion del catalogo"<?= $servicios === [] ? ' hidden' : '' ?>>
          <span id="pagination-summary">Pagina 1 de 1</span>
          <div class="pagination-pages">
            <button type="button" id="previous-page" aria-label="Pagina anterior" disabled>Anterior</button>
            <div id="pagination-numbers" class="pagination-pages"></div>
            <button type="button" id="next-page" aria-label="Pagina siguiente" disabled>Siguiente</button>
          </div>
        </nav>
      </section>
    </main>
  </div>

  <div class="modal-backdrop<?= $abrirModal ? ' open' : '' ?>" id="service-modal"<?= $abrirModal ? '' : ' aria-hidden="true"' ?>>
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="modal-title">
      <header class="modal-head">
        <div><h2 id="modal-title"><?= $editId ? 'Modificar servicio' : 'Nuevo servicio' ?></h2><p>Completa la informacion del tipo de servicio.</p></div>
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
          <div class="form-field" id="create-category-field"<?= $editId ? ' hidden' : '' ?>>
            <label for="service-category">Categoria</label>
            <select id="service-category" name="categoria" required<?= $editId ? ' disabled' : '' ?>>
              <option value="">Seleccioná una categoría</option>
              <?php foreach ($unidadesPorCategoria as $categoria => $unidad): ?>
                <option value="<?= esc($categoria, 'attr') ?>" data-unit="<?= esc($unidad, 'attr') ?>"<?= ($formData['categoria'] ?? '') === $categoria ? ' selected' : '' ?>><?= esc($categoria) ?></option>
              <?php endforeach ?>
            </select>
            <?= $editId ? '' : $formatError('categoria') ?>
          </div>
          <div class="form-field" id="edit-category-field"<?= $editId ? '' : ' hidden' ?>>
            <label for="edit-service-category">Categoria</label>
            <input id="edit-service-category" name="categoria" maxlength="100" required value="<?= esc($formData['categoria'] ?? '', 'attr') ?>"<?= $editId ? '' : ' disabled' ?>>
            <?= $editId ? $formatError('categoria') : '' ?>
          </div>
          <div class="form-field">
            <label for="service-unit">Unidad de medida estandar</label>
            <input id="service-unit" name="unidad_medida_std" maxlength="30" required value="<?= esc($formData['unidad_medida_std'] ?? '', 'attr') ?>"<?= $editId ? '' : ' readonly' ?> aria-describedby="unit-help">
            <small id="unit-help"<?= $editId ? ' hidden' : '' ?>>Se completa automáticamente al seleccionar una categoría.</small>
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
  <script src="<?= base_url('js/sidebar.js') ?>"></script>
  <script src="<?= base_url('js/tipos-servicio.js') ?>"></script>
</body>
</html>

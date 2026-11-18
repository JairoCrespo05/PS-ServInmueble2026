<?php

$categorias = array_values(array_unique(array_column($servicios, 'categoria')));
sort($categorias);

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
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/servicios.css') ?>?v=2">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/radio-options.css') ?>?v=1">

</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'servicios', 'isAdministrator' => false]) ?>

  <div class="workspace">
    <header class="topbar">
      <?= view('partials/brand') ?>
      <span class="top-context">Gestion patrimonial&nbsp; / &nbsp;Servicios asociados</span>

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
          <p class="eyebrow">Patrimonio inmobiliario / Servicios</p>
          <h1>Servicios asociados</h1>
          <p class="subtitle">Consulte los servicios asociados a sus inmuebles.</p>
        </div>
        <div class="service-actions">
          <a class="button secondary" href="<?= site_url('inmuebles') ?>"><span class="icon">apartment</span>Ver inmuebles</a>
          <?php if ($catalogoServicios !== [] && $inmuebles !== []): ?>
            <button class="button" id="open-associate" type="button"><span class="icon">add_link</span>Asociar servicio</button>
          <?php endif ?>
        </div>
      </section>

      <?php if ($success): ?><div class="notice" role="status"><?= esc($success) ?></div><?php endif ?>
      <?php if ($error): ?><div class="notice error" role="alert"><?= esc($error) ?></div><?php endif ?>

      <section class="summary" aria-label="Resumen de servicios">
        <article class="stat">
          <div><span class="stat-label">Servicios asociados</span><strong class="stat-value"><?= esc($resumen['total']) ?></strong><span class="stat-note">Tipos vinculados a tus inmuebles</span></div>
          <span class="stat-icon icon">power</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Asociaciones</span><strong class="stat-value"><?= esc($resumen['asociaciones']) ?></strong><span class="stat-note">Vinculos entre servicios e inmuebles</span></div>
          <span class="stat-icon icon">link</span>
        </article>
        <article class="stat">
          <div><span class="stat-label">Inmuebles con servicios</span><strong class="stat-value"><?= esc($resumen['inmuebles']) ?></strong><span class="stat-note">Inmuebles propios con al menos un servicio</span></div>
          <span class="stat-icon icon">apartment</span>
        </article>
      </section>

      <?php if ($servicios !== []): ?>
      <section class="filters" aria-label="Buscar y filtrar servicios">
        <div class="field">
          <label for="search-service">Buscar servicio o inmueble</label>
          <input id="search-service" type="search" placeholder="Nombre, proveedor, inmueble o localidad..." autocomplete="off">
        </div>
        <div class="field">
          <label for="filter-category">Categoria</label>
          <select id="filter-category">
            <option value="">Todas las categorias</option>
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
                <p class="no-associations">Este servicio todavia no esta asociado a ningun inmueble.</p>
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
                          <?php
                            $estadoClase = match ($inmueble['estado_servicio']) {
                                'Activo' => ' active',
                                'Pausado' => ' paused',
                                'En Revisión' => ' review',
                                default => '',
                            };
                          ?>
                          <span class="tag<?= esc($estadoClase, 'attr') ?>">Vínculo: <?= esc($inmueble['estado_servicio'] ?? 'Desconocido') ?></span>
                          <span class="tag">Inmueble: <?= esc($inmueble['estado_inmueble'] ?? 'Desconocido') ?><?= $inmueble['estado_actividad'] === 'Inactivo' ? ' (Dado de baja)' : '' ?></span>
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
      <?php else: ?>
        <div class="empty visible" id="empty-state" role="status">
          <?php if ($catalogoServicios !== [] && $inmuebles !== []): ?>
            Todavia no tenes servicios asociados a tus inmuebles. Usa "Asociar servicio" para comenzar.
          <?php elseif ($catalogoServicios === []): ?>
            No hay tipos de servicio disponibles para asociar.
          <?php else: ?>
            Primero agregá un inmueble para poder asociarle servicios.
          <?php endif ?>
        </div>
      <?php endif ?>
    </main>
  </div>

  <div class="modal-backdrop<?= $abrirModal ? ' open' : '' ?>" id="associate-modal" data-editing="<?= $editandoAsociacion ? 'true' : 'false' ?>" role="presentation">
    <section class="modal" role="dialog" aria-modal="true" aria-labelledby="associate-title">
      <div class="modal-head">
        <div><h2 id="associate-title"><?= $editandoAsociacion ? 'Modificar asociacion' : 'Asociar servicio a inmueble' ?></h2><p id="associate-description"><?= $editandoAsociacion ? 'Actualiz├í el estado y los datos registrados para este servicio.' : 'Registr├í el v├¡nculo y los datos de identificaci├│n del servicio.' ?></p></div>
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
              <option value="">Selecciona un servicio</option>
              <?php foreach ($catalogoServicios as $servicio): ?>
                <option value="<?= esc($servicio['id'], 'attr') ?>" data-associated-ids="<?= esc(json_encode($servicio['inmueble_ids']), 'attr') ?>"<?= (string) ($formData['servicio_id'] ?? '') === (string) $servicio['id'] ? ' selected' : '' ?>><?= esc($servicio['nombre']) ?> &mdash; <?= esc($servicio['proveedor']) ?></option>
              <?php endforeach ?>
            </select>
            <input id="associate-service-id" type="hidden" name="servicio_id" value="<?= esc($formData['servicio_id'] ?? '', 'attr') ?>">
            <input id="service-locked" type="hidden" name="service_locked" value="<?= $serviceLocked || $editandoAsociacion ? '1' : '0' ?>">
            <span class="field-error" id="service-lock-note"<?= $serviceLocked || $editandoAsociacion ? '' : ' hidden' ?>>Este servicio esta definido por la opcion elegida y no se puede cambiar desde aqui.</span>
            <?php if (isset($errors['servicio_id'])): ?><span class="field-error"><?= esc($errors['servicio_id']) ?></span><?php endif ?>
          </div>
          <div class="form-field full" id="property-field"<?= $editandoAsociacion ? ' hidden' : '' ?>>
            <label for="associate-property">Inmueble<?= $editandoAsociacion ? ' (no modificable)' : '' ?></label>
            <select id="associate-property" name="inmueble_id" required>
              <option value="">Selecciona un inmueble</option>
              <?php foreach ($inmuebles as $inmueble): ?>
                <?php
                  $direccion = trim(
                      $inmueble['calle'] . ' ' . $inmueble['numero']
                      . ($inmueble['piso_dpto'] ? ', ' . $inmueble['piso_dpto'] : '')
                      . ', ' . $inmueble['ciudad'],
                  );
                ?>
                <option value="<?= esc($inmueble['id'], 'attr') ?>"<?= (string) ($formData['inmueble_id'] ?? '') === (string) $inmueble['id'] ? ' selected' : '' ?>><?= esc($inmueble['alias_nombre']) ?> &mdash; <?= esc($direccion . ' (' . ($inmueble['estado'] ?? 'Desconocido') . ($inmueble['estado_actividad'] === 'Inactivo' ? ', dado de baja' : '') . ')') ?></option>
              <?php endforeach ?>
            </select>
            <?php if (isset($errors['inmueble_id'])): ?><span class="field-error"><?= esc($errors['inmueble_id']) ?></span><?php endif ?>
          </div>
          <p class="form-section-title">Datos de la asociacion</p>
          <div class="form-field">
            <label for="associate-holder">Titular del servicio</label>
            <input id="associate-holder" name="titular_inmueble" maxlength="150" required value="<?= esc($formData['titular_inmueble'] ?? '', 'attr') ?>">
            <?php if (isset($errors['titular_inmueble'])): ?><span class="field-error"><?= esc($errors['titular_inmueble']) ?></span><?php endif ?>
          </div>
          <fieldset class="form-field radio-field">
            <legend>Estado operativo del vínculo</legend>
            <div class="radio-options">
              <?php foreach (\App\Models\InmuebleServicioModel::ESTADOS_OPERATIVOS as $estado): ?>
                <label class="radio-option"><input type="radio" name="estado_vinculacion" value="<?= esc($estado, 'attr') ?>" required aria-describedby="association-status-help"<?= ($formData['estado_vinculacion'] ?? 'Activo') === $estado ? ' checked' : '' ?>> <span><?= esc($estado) ?></span></label>
              <?php endforeach ?>
            </div>
            <small id="association-status-help">Un vínculo no puede activarse si el inmueble está inactivo. Solo los vínculos activos permiten cargar facturas.</small>
            <?php if (isset($errors['estado_vinculacion'])): ?><span class="field-error"><?= esc($errors['estado_vinculacion']) ?></span><?php endif ?>
          </fieldset>
          <div class="form-field">
            <label for="associate-meter">Numero de medidor <span>(opcional)</span></label>
            <input id="associate-meter" name="nro_medidor" maxlength="50" value="<?= esc($formData['nro_medidor'] ?? '', 'attr') ?>">
            <?php if (isset($errors['nro_medidor'])): ?><span class="field-error"><?= esc($errors['nro_medidor']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-account">Numero de cuenta / contrato <span>(opcional)</span></label>
            <input id="associate-account" name="nro_cuenta_contrato" maxlength="100" value="<?= esc($formData['nro_cuenta_contrato'] ?? '', 'attr') ?>">
            <?php if (isset($errors['nro_cuenta_contrato'])): ?><span class="field-error"><?= esc($errors['nro_cuenta_contrato']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-payment">Codigo de pago electronico <span>(opcional)</span></label>
            <input id="associate-payment" name="cod_pago_electronico" maxlength="100" value="<?= esc($formData['cod_pago_electronico'] ?? '', 'attr') ?>">
            <?php if (isset($errors['cod_pago_electronico'])): ?><span class="field-error"><?= esc($errors['cod_pago_electronico']) ?></span><?php endif ?>
          </div>
          <div class="form-field">
            <label for="associate-billing">Domicilio de facturacion <span>(opcional)</span></label>
            <input id="associate-billing" name="domicilio_facturacion" maxlength="255" value="<?= esc($formData['domicilio_facturacion'] ?? '', 'attr') ?>">
            <?php if (isset($errors['domicilio_facturacion'])): ?><span class="field-error"><?= esc($errors['domicilio_facturacion']) ?></span><?php endif ?>
          </div>
        </div>
        <div class="modal-actions">
          <button class="button secondary" id="cancel-associate" type="button">Cancelar</button>
          <button class="button" id="save-association" type="submit"><?= $editandoAsociacion ? 'Guardar cambios' : 'Guardar asociacion' ?></button>
        </div>
      </form>
    </section>
  </div>

  <script src="<?= base_url('js/sidebar.js') ?>"></script>
  <script src="<?= base_url('js/servicios.js') ?>?v=2"></script>
</body>
</html>

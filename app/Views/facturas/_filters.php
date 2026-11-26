<form class="invoice-filters" method="get" action="<?= esc($action, 'attr') ?>">
  <div class="field invoice-filter-search">
    <label for="invoice-search">Buscar por inmueble, servicio o proveedor</label>
    <input id="invoice-search" name="buscar" type="search" maxlength="100" value="<?= esc($buscar, 'attr') ?>" placeholder="Ej.: Casa Centro, electricidad o proveedor">
  </div>
  <div class="field">
    <label for="invoice-status">Estado</label>
    <select id="invoice-status" name="estado">
      <option value="">Todos los estados</option>
      <option value="Pendiente"<?= $estado === 'Pendiente' ? ' selected' : '' ?>>Pendiente</option>
      <option value="Vencida"<?= $estado === 'Vencida' ? ' selected' : '' ?>>Vencida</option>
      <option value="Pagada"<?= $estado === 'Pagada' ? ' selected' : '' ?>>Pagada</option>
    </select>
  </div>
  <div class="field">
    <label for="invoice-due-from">Vencimiento desde</label>
    <input id="invoice-due-from" name="desde" type="date" value="<?= esc($desde, 'attr') ?>">
  </div>
  <div class="field">
    <label for="invoice-due-to">Vencimiento hasta</label>
    <input id="invoice-due-to" name="hasta" type="date" value="<?= esc($hasta, 'attr') ?>">
  </div>
  <div class="invoice-filter-actions">
    <button class="button" type="submit"><span class="icon" aria-hidden="true">search</span>Buscar</button>
    <a class="button secondary" href="<?= esc($action, 'attr') ?>">Limpiar</a>
  </div>
</form>
<?php if ($filterErrors !== []): ?>
  <div class="notice error invoice-filter-error" role="alert">
    <?php foreach ($filterErrors as $message): ?>
      <p><?= esc($message) ?></p>
    <?php endforeach ?>
  </div>
<?php endif ?>

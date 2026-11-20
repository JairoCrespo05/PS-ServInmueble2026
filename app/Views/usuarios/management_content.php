<?php

$allUsers = $allUsers ?? [];
$pendingUsers = $pendingUsers ?? [];
$userStats = [
    'total'          => count($allUsers),
    'owners'         => 0,
    'administrators' => 0,
    'active'         => 0,
];

foreach ($allUsers as $listedUser) {
    if (($listedUser['role'] ?? '') === \App\Models\RolModel::ROLE_OWNER) {
        $userStats['owners']++;
    } elseif (($listedUser['role'] ?? '') === \App\Models\RolModel::ROLE_ADMINISTRATOR) {
        $userStats['administrators']++;
    }

    if ($listedUser['activo']) {
        $userStats['active']++;
    }
}

?>
<section class="user-page-header">
  <div>
    <nav class="user-breadcrumb" aria-label="Ruta de navegación">
      <span>Seguridad &amp; accesos</span>
      <span aria-hidden="true">›</span>
      <span aria-current="page">Usuarios</span>
    </nav>
    <h1 class="page-title">Administración de usuarios</h1>
    <p class="page-subtitle">Gestioná las cuentas de acceso, revisá solicitudes y administrá los estados de usuario.</p>
  </div>
</section>

<?php if (! empty($adminMessage)): ?>
  <div class="access-alert <?= esc($adminMessage['type'], 'attr') ?>" role="status">
    <?= esc($adminMessage['text']) ?>
  </div>
<?php endif ?>

<section class="user-stats-grid" aria-label="Resumen de cuentas">
  <article class="user-stat-card">
    <div class="user-stat-heading">
      <span>Total de usuarios</span>
      <span class="user-stat-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M22 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
      </span>
    </div>
    <strong><?= $userStats['total'] ?></strong>
    <span class="user-stat-note">Cuentas registradas</span>
  </article>
  <article class="user-stat-card">
    <div class="user-stat-heading">
      <span>Propietarios / gestores</span>
      <span class="user-stat-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M3 21h18"></path><path d="M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16"></path><path d="M9 7h1"></path><path d="M14 7h1"></path><path d="M9 11h1"></path><path d="M14 11h1"></path></svg>
      </span>
    </div>
    <strong><?= $userStats['owners'] ?></strong>
    <span class="user-stat-note">Con rol Propietario</span>
  </article>
  <article class="user-stat-card">
    <div class="user-stat-heading">
      <span>Administradores</span>
      <span class="user-stat-icon" aria-hidden="true">
        <svg viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>
      </span>
    </div>
    <strong><?= $userStats['administrators'] ?></strong>
    <span class="user-stat-note">Acceso total al sistema</span>
  </article>
  <article class="user-stat-card">
    <div class="user-stat-heading">
      <span>Cuentas activas</span>
      <span class="user-stat-icon is-success" aria-hidden="true">
        <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><path d="m8 12 3 3 5-6"></path></svg>
      </span>
    </div>
    <strong class="is-success"><?= $userStats['active'] ?></strong>
    <span class="user-stat-note"><?= count($pendingUsers) ?> solicitudes pendientes</span>
  </article>
</section>

<section class="card user-list-card" aria-labelledby="allUsersTitle">
  <div class="card-header">
    <div class="card-title-group">
      <div>
        <h2 class="card-title" id="allUsersTitle">Listado de usuarios</h2>
        <p class="user-card-description">Cuentas registradas, roles y estado de acceso.</p>
      </div>
      <span class="badge-subtle"><?= $userStats['total'] ?></span>
    </div>
  </div>

  <div class="user-filters" role="search" aria-label="Filtrar usuarios">
    <label class="user-search-control">
      <span class="sr-only">Buscar usuario por nombre, correo o teléfono</span>
      <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"></circle><path d="m20 20-4-4"></path></svg>
      <input id="usersSearch" type="search" autocomplete="off" placeholder="Buscar por nombre, apellido o correo electrónico...">
    </label>
    <label class="user-select-control">
      <span class="sr-only">Filtrar por rol</span>
      <select id="usersRoleFilter">
        <option value="all">Rol: Todos</option>
        <option value="administrador">Administrador</option>
        <option value="propietario">Propietario</option>
        <option value="usuario_nuevo">Usuario nuevo</option>
        <option value="sin rol asignado">Sin rol asignado</option>
      </select>
    </label>
    <label class="user-select-control">
      <span class="sr-only">Filtrar por estado</span>
      <select id="usersStatusFilter">
        <option value="all">Estado: Todos</option>
        <option value="aprobada">Aprobada</option>
        <option value="pendiente">Pendiente</option>
        <option value="rechazada">Rechazada</option>
        <option value="inactiva">Baja</option>
      </select>
    </label>
    <button class="user-filter-reset" id="usersResetFilters" type="button" aria-label="Limpiar filtros" title="Limpiar filtros">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 4h18l-7 8v6l-4 2v-8L3 4z"></path><path d="m17 17 4 4"></path><path d="m21 17-4 4"></path></svg>
    </button>
  </div>

  <div class="table-container user-table-container">
    <table class="data-table user-data-table" id="all-users-table">
      <thead>
        <tr>
          <th scope="col">Usuario / nombre</th>
          <th scope="col">Correo electrónico</th>
          <th scope="col">Teléfono</th>
          <th scope="col">Rol</th>
          <th scope="col">Fecha de alta</th>
          <th scope="col">Estado</th>
          <th scope="col">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($allUsers === []): ?>
          <tr>
            <td colspan="7" class="user-empty-state">Todavía no hay usuarios registrados.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($allUsers as $listedUser): ?>
            <?php
            $fullName = trim($listedUser['nombre'] . ' ' . $listedUser['apellido']);
            $role = $listedUser['role'] ?? '';
            $status = ! $listedUser['activo']
                ? 'inactiva'
                : mb_strtolower($listedUser['estado_solicitud']);
            ?>
            <tr
              data-user-row
              data-user-role="<?= esc(mb_strtolower($role ?: 'sin rol asignado'), 'attr') ?>"
              data-user-status="<?= esc($status, 'attr') ?>"
            >
              <td>
                <div class="user-identity">
                  <span class="user-initials" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($listedUser['nombre'], 0, 1) . mb_substr($listedUser['apellido'], 0, 1))) ?></span>
                  <span>
                    <strong><?= esc($fullName) ?></strong>
                    <small>#<?= (int) $listedUser['id'] ?></small>
                  </span>
                </div>
              </td>
              <td class="user-secondary-cell"><?= esc($listedUser['email']) ?></td>
              <td class="user-secondary-cell"><?= esc($listedUser['telefono'] ?: 'No informado') ?></td>
              <td>
                <span class="user-role-badge <?= $role === \App\Models\RolModel::ROLE_ADMINISTRATOR ? 'is-admin' : '' ?>">
                  <?= esc($role ?: 'Sin rol asignado') ?>
                </span>
              </td>
              <td class="user-secondary-cell"><?= esc($listedUser['created_at'] ?? 'Sin fecha') ?></td>
              <td>
                <?php if (! $listedUser['activo']): ?>
                  <span class="user-state-badge is-inactive state-color" data-state-domain="usuario" data-state="baja">Baja</span>
                <?php else: ?>
                  <?php
                  $stateClass = match ($listedUser['estado_solicitud']) {
                      \App\Models\UserModel::REQUEST_APPROVED => 'is-approved',
                      \App\Models\UserModel::REQUEST_PENDING => 'is-pending',
                      \App\Models\UserModel::REQUEST_REJECTED => 'is-rejected',
                      default => '',
                  };
                  ?>
                  <span class="user-state-badge state-color <?= esc($stateClass, 'attr') ?>" data-state-domain="usuario" data-state="<?= esc(mb_strtolower(trim($listedUser['estado_solicitud'])), 'attr') ?>"><?= esc($listedUser['estado_solicitud']) ?></span>
                <?php endif ?>
              </td>
              <td>
                <div class="user-row-actions">
                  <a
                    class="crud-button crud-button--icon crud-button--edit"
                    href="<?= site_url('usuarios/' . (int) $listedUser['id'] . '/editar') ?>"
                    aria-label="<?= esc('Modificar datos de ' . $fullName, 'attr') ?>"
                    title="Modificar"
                  >
                    <?= view('partials/crud_icon', ['entity' => 'person', 'action' => 'edit']) ?>
                  </a>
                  <a
                    class="crud-button crud-button--icon crud-button--detail"
                    href="<?= site_url('usuarios/' . (int) $listedUser['id'] . '/inmuebles') ?>"
                    aria-label="<?= esc('Ver inmuebles de ' . $fullName, 'attr') ?>"
                    title="Ver inmuebles"
                  ><span class="crud-symbol" aria-hidden="true">apartment</span></a>
                  <?php if ($listedUser['activo'] && (int) $listedUser['id'] !== $currentAdminId): ?>
                    <form method="post" action="<?= site_url('usuarios/' . (int) $listedUser['id'] . '/baja') ?>" onsubmit="return confirm('¿Dar de baja esta cuenta? Se conservarán sus inmuebles y su historial.')">
                      <?= csrf_field() ?>
                      <button
                        type="submit"
                        class="crud-button crud-button--icon crud-button--delete"
                        aria-label="<?= esc('Dar de baja a ' . $fullName, 'attr') ?>"
                        title="Dar de baja"
                      >
                        <?= view('partials/crud_icon', ['entity' => 'person', 'action' => 'block']) ?>
                      </button>
                    </form>
                  <?php elseif (! $listedUser['activo']): ?>
                    <span class="user-inactive-note">Cuenta inactiva</span>
                  <?php elseif ((int) $listedUser['id'] === $currentAdminId): ?>
                    <span class="user-inactive-note">Tu cuenta</span>
                  <?php endif ?>
                  <?php if ($listedUser['activo'] && $listedUser['estado_solicitud'] === \App\Models\UserModel::REQUEST_REJECTED): ?>
                    <form method="post" action="<?= site_url('admin/solicitudes/' . (int) $listedUser['id'] . '/reabrir') ?>">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-secondary">Reabrir solicitud</button>
                    </form>
                  <?php endif ?>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
        <?php endif ?>
        <tr class="user-filter-empty" id="usersNoResults" hidden>
          <td colspan="7" class="user-empty-state">No se encontraron usuarios con esos criterios. Ajustá los filtros para ver resultados.</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="user-table-footer">
    <span>Mostrando <strong id="usersVisibleCount"><?= $userStats['total'] ?></strong> de <strong><?= $userStats['total'] ?></strong> usuarios</span>
    <button class="user-filter-reset user-clear-link" id="usersClearFilters" type="button">Limpiar filtros</button>
  </div>
</section>

<section class="card user-pending-card" aria-labelledby="pendingUsersTitle">
  <div class="card-header">
    <div class="card-title-group">
      <div>
        <h2 class="card-title" id="pendingUsersTitle">Solicitudes pendientes</h2>
        <p class="user-card-description">Revisá las cuentas que esperan aprobación.</p>
      </div>
      <span class="badge-subtle"><?= count($pendingUsers) ?></span>
    </div>
  </div>
  <div class="table-container user-table-container">
    <table class="data-table user-data-table" id="pending-users-table">
      <thead>
        <tr>
          <th scope="col">Nombre y apellido</th>
          <th scope="col">Correo electrónico</th>
          <th scope="col">Teléfono</th>
          <th scope="col">Fecha de registro</th>
          <th scope="col">Decisión</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($pendingUsers === []): ?>
          <tr>
            <td colspan="5" class="user-empty-state">No hay solicitudes pendientes.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($pendingUsers as $pendingUser): ?>
            <tr>
              <td><strong><?= esc(trim($pendingUser['nombre'] . ' ' . $pendingUser['apellido'])) ?></strong></td>
              <td class="user-secondary-cell"><?= esc($pendingUser['email']) ?></td>
              <td class="user-secondary-cell"><?= esc($pendingUser['telefono'] ?: 'No informado') ?></td>
              <td class="user-secondary-cell"><?= esc($pendingUser['created_at'] ?? 'Sin fecha') ?></td>
              <td>
                <div class="user-approval-actions">
                  <form class="approval-form" method="post" action="<?= site_url('admin/solicitudes/' . (int) $pendingUser['id'] . '/aprobar') ?>">
                    <?= csrf_field() ?>
                    <label for="approval-role-<?= (int) $pendingUser['id'] ?>">Rol al aprobar</label>
                    <select id="approval-role-<?= (int) $pendingUser['id'] ?>" name="role" required>
                      <option value="Propietario">Propietario</option>
                      <option value="Administrador">Administrador</option>
                    </select>
                    <button
                      type="submit"
                      class="btn btn-primary"
                      aria-label="<?= esc('Aprobar solicitud de ' . trim($pendingUser['nombre'] . ' ' . $pendingUser['apellido']), 'attr') ?>"
                    >Aprobar</button>
                  </form>
                  <form method="post" action="<?= site_url('admin/solicitudes/' . (int) $pendingUser['id'] . '/rechazar') ?>" onsubmit="return confirm('¿Rechazar esta solicitud? La cuenta quedará conservada y sin acceso.')">
                    <?= csrf_field() ?>
                    <button
                      type="submit"
                      class="btn btn-danger"
                      aria-label="<?= esc('Rechazar solicitud de ' . trim($pendingUser['nombre'] . ' ' . $pendingUser['apellido']), 'attr') ?>"
                    >Rechazar</button>
                  </form>
                </div>
              </td>
            </tr>
          <?php endforeach ?>
        <?php endif ?>
      </tbody>
    </table>
  </div>
</section>

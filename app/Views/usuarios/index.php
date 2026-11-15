<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de usuarios - ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'usuarios', 'isAdministrator' => true]) ?>

  <div class="main-wrapper" id="mainWrapper">
    <header class="topbar">
      <div class="topbar-left"></div>
      <div class="topbar-right">
        <!-- Perfil Usuario -->
        <div class="user-profile">
          <div class="user-avatar"><?= esc(strtoupper(substr(session()->get('user_name') ?? 'U', 0, 1))) ?></div>
          <div class="user-info">
          <div class="user-name"><?= esc(session()->get('user_name') ?? '') ?></div>
            <div class="user-role"><?= esc(session()->get('user_role') ?? '') ?></div>
          </div>
        </div>
      </div>
    </header>

    <main class="content-area user-management">
      <section class="page-header">
        <div>
          <div class="welcome-badge">Administración</div>
          <h1 class="page-title">Gestión de usuarios</h1>
          <p class="page-subtitle">Revisá solicitudes, administrá cuentas y consultá los inmuebles vinculados.</p>
        </div>
      </section>

      <?php if (! empty($adminMessage)): ?>
        <div class="access-alert <?= esc($adminMessage['type'], 'attr') ?>" role="status">
          <?= esc($adminMessage['text']) ?>
        </div>
      <?php endif ?>

      <section class="card" aria-labelledby="pendingUsersTitle">
        <div class="card-header">
          <div class="card-title-group">
            <h2 class="card-title" id="pendingUsersTitle">Solicitudes pendientes</h2>
            <span class="badge-subtle"><?= count($pendingUsers) ?></span>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table" id="pending-users-table">
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
                  <td colspan="5">No hay solicitudes pendientes.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($pendingUsers as $pendingUser): ?>
                  <tr>
                    <td><?= esc(trim($pendingUser['nombre'] . ' ' . $pendingUser['apellido'])) ?></td>
                    <td><?= esc($pendingUser['email']) ?></td>
                    <td><?= esc($pendingUser['telefono'] ?: 'No informado') ?></td>
                    <td><?= esc($pendingUser['created_at'] ?? 'Sin fecha') ?></td>
                    <td>
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
                    </td>
                  </tr>
                <?php endforeach ?>
              <?php endif ?>
            </tbody>
          </table>
        </div>
      </section>

      <section class="card" aria-labelledby="allUsersTitle">
        <div class="card-header">
          <div class="card-title-group">
            <h2 class="card-title" id="allUsersTitle">Listado general de usuarios</h2>
            <span class="badge-subtle"><?= count($allUsers) ?></span>
          </div>
        </div>
        <p class="page-subtitle">Incluye todas las cuentas, también las que siguen pendientes de aprobación.</p>
        <div class="table-container">
          <table class="data-table" id="all-users-table">
            <thead>
              <tr>
                <th scope="col">Nombre y apellido</th>
                <th scope="col">Correo electrónico</th>
                <th scope="col">Teléfono</th>
                <th scope="col">Rol</th>
                <th scope="col">Estado</th>
                <th scope="col">Fecha de registro</th>
                <th scope="col">Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($allUsers === []): ?>
                <tr>
                  <td colspan="7">Todavía no hay usuarios registrados.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($allUsers as $listedUser): ?>
                  <tr>
                    <td><?= esc(trim($listedUser['nombre'] . ' ' . $listedUser['apellido'])) ?></td>
                    <td><?= esc($listedUser['email']) ?></td>
                    <td><?= esc($listedUser['telefono'] ?: 'No informado') ?></td>
                    <td><?= esc($listedUser['role'] ?? 'Sin rol asignado') ?></td>
                    <td>
                      <?php if (! $listedUser['activo']): ?>
                        Baja
                      <?php else: ?>
                        <?= esc($listedUser['estado_solicitud']) ?>
                      <?php endif ?>
                    </td>
                    <td><?= esc($listedUser['created_at'] ?? 'Sin fecha') ?></td>
                    <td>
                      <div class="user-row-actions">
                        <a class="btn btn-secondary" href="<?= site_url('usuarios/' . (int) $listedUser['id'] . '/editar') ?>">Modificar</a>
                        <a class="btn btn-secondary" href="<?= site_url('usuarios/' . (int) $listedUser['id'] . '/inmuebles') ?>">Ver inmuebles</a>
                        <?php if ($listedUser['activo'] && (int) $listedUser['id'] !== $currentAdminId): ?>
                          <form method="post" action="<?= site_url('usuarios/' . (int) $listedUser['id'] . '/baja') ?>" onsubmit="return confirm('¿Dar de baja esta cuenta? Se conservarán sus inmuebles y su historial.')">
                            <?= csrf_field() ?>
                            <button
                              type="submit"
                              class="btn btn-danger"
                              aria-label="<?= esc('Dar de baja a ' . trim($listedUser['nombre'] . ' ' . $listedUser['apellido']), 'attr') ?>"
                            >Dar de baja</button>
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
            </tbody>
          </table>
        </div>
      </section>
    </main>

    <footer class="app-footer">
      <div>© 2026 ServiInmueble</div>
    </footer>
  </div>

  <script src="<?= base_url('js/home.js') ?>"></script>
</body>
</html>

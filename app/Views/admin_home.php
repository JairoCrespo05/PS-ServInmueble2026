<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Administración - ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
</head>
<body>
  <div class="main-wrapper admin-wrapper">
    <header class="topbar">
      <div class="topbar-left">
        <a class="brand-group admin-brand" href="<?= base_url('home') ?>">
          <span class="brand-logo-badge">SI</span>
          <span class="brand-title">ServiInmueble</span>
        </a>
      </div>
      <div class="topbar-right">
        <div class="user-profile">
          <div class="user-info">
            <div class="user-name"><?= esc($user['nombre'] ?? '') ?></div>
            <div class="user-role">Administrador</div>
          </div>
        </div>
        <form method="post" action="<?= base_url('logout') ?>">
          <?= csrf_field() ?>
          <button type="submit" class="btn btn-secondary">Cerrar sesión</button>
        </form>
      </div>
    </header>

    <main class="content-area admin-content">
      <section class="page-header">
        <div>
          <div class="welcome-badge">Administración</div>
          <h1 class="page-title">Solicitudes de acceso</h1>
          <p class="page-subtitle">Revisá las cuentas pendientes y aprobá las que podrán operar como Propietario.</p>
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
            <h2 class="card-title" id="pendingUsersTitle">Cuentas pendientes</h2>
            <span class="badge-subtle"><?= count($pendingUsers) ?></span>
          </div>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th scope="col">Nombre</th>
                <th scope="col">Correo electrónico</th>
                <th scope="col">Fecha de registro</th>
                <th scope="col">Rol y acción</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($pendingUsers === []): ?>
                <tr>
                  <td colspan="4">No hay solicitudes pendientes.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($pendingUsers as $pendingUser): ?>
                  <tr>
                    <td><?= esc(trim($pendingUser['nombre'] . ' ' . $pendingUser['apellido'])) ?></td>
                    <td><?= esc($pendingUser['email']) ?></td>
                    <td><?= esc($pendingUser['created_at'] ?? 'Sin fecha') ?></td>
                    <td>
                      <form class="approval-form" method="post" action="<?= base_url('admin/solicitudes/' . (int) $pendingUser['id'] . '/aprobar') ?>">
                        <?= csrf_field() ?>
                        <label for="approval-role-<?= (int) $pendingUser['id'] ?>">Rol al aprobar</label>
                        <select id="approval-role-<?= (int) $pendingUser['id'] ?>" name="role" required>
                          <option value="Propietario">Propietario</option>
                          <option value="Administrador">Administrador</option>
                        </select>
                        <button type="submit" class="btn btn-primary">Aprobar</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach ?>
              <?php endif ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>
</body>
</html>

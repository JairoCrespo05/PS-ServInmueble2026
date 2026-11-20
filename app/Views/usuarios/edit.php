<?php

$canChangeRole = $user['activo']
    && $user['estado_solicitud'] === \App\Models\UserModel::REQUEST_APPROVED
    && (int) $user['id'] !== $currentAdminId
    && in_array($user['role'], [\App\Models\RolModel::ROLE_OWNER, \App\Models\RolModel::ROLE_ADMINISTRATOR], true);

?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Modificar usuario - ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'usuarios', 'isAdministrator' => true]) ?>

  <div class="main-wrapper" id="mainWrapper">
    <header class="topbar">
      <div class="topbar-left"><?= view('partials/brand') ?></div>
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

    <main class="content-area">
      <section class="page-header">
        <div>
          <div class="welcome-badge">Administración</div>
          <h1 class="page-title">Modificar usuario</h1>
          <p class="page-subtitle"><?= esc(trim($user['nombre'] . ' ' . $user['apellido'])) ?> · <?= esc($user['email']) ?></p>
        </div>
        <a class="btn btn-secondary" href="<?= site_url('usuarios') ?>">Volver al listado</a>
      </section>

      <?php if (! empty($adminMessage)): ?>
        <div class="access-alert <?= esc($adminMessage['type'], 'attr') ?>" role="status"><?= esc($adminMessage['text']) ?></div>
      <?php endif ?>

      <section class="card user-edit-card" aria-labelledby="editUserTitle">
        <div class="card-header">
          <h2 class="card-title" id="editUserTitle">Datos de la cuenta</h2>
        </div>
        <form method="post" action="<?= site_url('usuarios/' . (int) $user['id']) ?>">
          <?= csrf_field() ?>
          <div class="user-edit-fields">
            <div class="user-edit-field">
              <label for="nombre">Nombre</label>
              <input id="nombre" name="nombre" maxlength="100" required value="<?= esc($formData['nombre'] ?? $user['nombre'], 'attr') ?>">
              <?php if (isset($errors['nombre'])): ?><span class="field-error"><?= esc($errors['nombre']) ?></span><?php endif ?>
            </div>
            <div class="user-edit-field">
              <label for="apellido">Apellido</label>
              <input id="apellido" name="apellido" maxlength="100" required value="<?= esc($formData['apellido'] ?? $user['apellido'], 'attr') ?>">
              <?php if (isset($errors['apellido'])): ?><span class="field-error"><?= esc($errors['apellido']) ?></span><?php endif ?>
            </div>
            <div class="user-edit-field">
              <label for="email">Correo electrónico</label>
              <input id="email" name="email" type="email" maxlength="150" required value="<?= esc($formData['email'] ?? $user['email'], 'attr') ?>">
              <?php if (isset($errors['email'])): ?><span class="field-error"><?= esc($errors['email']) ?></span><?php endif ?>
            </div>
            <div class="user-edit-field">
              <label for="telefono">Teléfono</label>
              <input id="telefono" name="telefono" maxlength="30" value="<?= esc($formData['telefono'] ?? $user['telefono'] ?? '', 'attr') ?>">
              <?php if (isset($errors['telefono'])): ?><span class="field-error"><?= esc($errors['telefono']) ?></span><?php endif ?>
            </div>

            <?php if ($canChangeRole): ?>
              <div class="user-edit-field">
                <label for="role">Rol</label>
                <select id="role" name="role" required>
                  <?php $selectedRole = $formData['role'] ?? $user['role']; ?>
                  <option value="Propietario" <?= $selectedRole === 'Propietario' ? 'selected' : '' ?>>Propietario</option>
                  <option value="Administrador" <?= $selectedRole === 'Administrador' ? 'selected' : '' ?>>Administrador</option>
                </select>
                <?php if (isset($errors['role'])): ?><span class="field-error"><?= esc($errors['role']) ?></span><?php endif ?>
              </div>
            <?php else: ?>
              <div class="user-edit-field user-edit-note">
                <span>Rol actual</span>
                <strong><?= esc($user['role'] ?? 'Sin rol asignado') ?></strong>
                <?php if ($user['estado_solicitud'] === \App\Models\UserModel::REQUEST_PENDING): ?>
                  <small>El rol se elige al aprobar la solicitud.</small>
                <?php elseif ($user['estado_solicitud'] === \App\Models\UserModel::REQUEST_REJECTED): ?>
                  <small>Reabrí y aprobá la solicitud para asignar un rol.</small>
                <?php elseif ((int) $user['id'] === $currentAdminId): ?>
                  <small>No podés cambiar tu propio rol.</small>
                <?php elseif (! $user['activo']): ?>
                  <small>La cuenta está dada de baja; no se puede cambiar su rol.</small>
                <?php endif ?>
              </div>
            <?php endif ?>
          </div>

          <div class="user-edit-actions">
            <button type="submit" class="btn btn-primary">Guardar cambios</button>
            <a class="btn btn-secondary" href="<?= site_url('usuarios') ?>">Cancelar</a>
          </div>
        </form>
      </section>
    </main>
  </div>

  <script src="<?= base_url('js/home.js') ?>"></script>
</body>
</html>

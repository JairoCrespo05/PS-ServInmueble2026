<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestión de usuarios - ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/usuarios.css') ?>">
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
      <?= view('usuarios/management_content', [
        'adminMessage'   => $adminMessage ?? null,
        'pendingUsers'   => $pendingUsers ?? [],
        'allUsers'       => $allUsers ?? [],
        'currentAdminId' => $currentAdminId ?? 0,
      ]) ?>
    </main>

    <footer class="app-footer">
      <div>© 2026 ServiInmueble</div>
    </footer>
  </div>

  <script src="<?= base_url('js/home.js') ?>"></script>
  <script src="<?= base_url('js/user-management.js') ?>"></script>
</body>
</html>

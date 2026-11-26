<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard no disponible - ServiInmueble</title>
  <link rel="stylesheet" href="<?= base_url('css/sidebar.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>?v=<?= hash_file('sha256', FCPATH . 'css/home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/admin-home.css') ?>?v=<?= hash_file('sha256', FCPATH . 'css/admin-home.css') ?>">
  <link rel="stylesheet" href="<?= base_url('css/brand.css') ?>">
</head>
<body>
  <?= view('partials/sidebar', ['activeNav' => 'home', 'isAdministrator' => true]) ?>
  <div class="main-wrapper" id="mainWrapper">
    <header class="topbar">
      <?= view('partials/brand') ?>
      <div class="topbar-right">
        <div class="user-profile">
          <div class="user-info">
            <div class="user-name"><?= esc($user['nombre'] ?? '') ?></div>
            <div class="user-role">Administrador</div>
          </div>
        </div>
      </div>
    </header>
    <main class="content-area">
      <section class="admin-dashboard-load-error" role="alert" aria-labelledby="adminDashboardErrorTitle">
        <h1 id="adminDashboardErrorTitle">No se pudo cargar el dashboard</h1>
        <p>Las métricas no están disponibles en este momento. No se muestran resultados parciales.</p>
        <a class="btn btn-primary" href="<?= esc(site_url('home'), 'attr') ?>">Reintentar</a>
      </section>
    </main>
  </div>
  <script src="<?= base_url('js/home.js') ?>?v=<?= hash_file('sha256', FCPATH . 'js/home.js') ?>"></script>
</body>
</html>

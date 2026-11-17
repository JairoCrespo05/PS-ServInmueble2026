<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Inmuebles del usuario - ServiInmueble</title>
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

    <main class="content-area">
      <section class="page-header">
        <div>
          <div class="welcome-badge">Inmuebles asociados</div>
          <h1 class="page-title"><?= esc(trim($user['nombre'] . ' ' . $user['apellido'])) ?></h1>
          <p class="page-subtitle">
            <?= esc($user['email']) ?> · <?= count($properties) ?> inmueble(s)
            <?php if (! $user['activo']): ?> · Cuenta dada de baja<?php endif ?>
          </p>
        </div>
        <a class="btn btn-secondary" href="<?= site_url('usuarios') ?>">Volver al listado</a>
      </section>

      <section class="card" aria-labelledby="userPropertiesTitle">
        <div class="card-header">
          <h2 class="card-title" id="userPropertiesTitle">Inmuebles e historial</h2>
        </div>
        <div class="table-container">
          <table class="data-table">
            <thead>
              <tr>
                <th scope="col">Alias</th>
                <th scope="col">Tipo</th>
                <th scope="col">Superficie</th>
                <th scope="col">Estado</th>
                <th scope="col">Domicilio</th>
                <th scope="col">Servicios relacionados</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($properties === []): ?>
                <tr><td colspan="6">Este usuario todavía no tiene inmuebles asociados.</td></tr>
              <?php else: ?>
                <?php foreach ($properties as $property): ?>
                  <tr>
                    <td><?= esc($property['alias_nombre']) ?></td>
                    <td><?= esc($property['tipo']) ?></td>
                    <td><?= esc($property['superficie']) ?> m²</td>
                    <td><?= esc($property['estado']) ?></td>
                    <td>
                      <?= esc(trim($property['calle'] . ' ' . $property['numero'])) ?>
                      <?= esc($property['piso_dpto'] ?? '') ?>
                      · <?= esc($property['ciudad']) ?>
                      <?= ! empty($property['provincia']) ? ', ' . esc($property['provincia']) : '' ?>
                    </td>
                    <td>
                      <?php $propertyServices = $servicesByProperty[(int) $property['id']] ?? []; ?>
                      <?php if ($propertyServices === []): ?>
                        Sin servicios asociados.
                      <?php else: ?>
                        <ul>
                          <?php foreach ($propertyServices as $service): ?>
                            <li>
                              <?= esc($service['servicio_nombre']) ?>
                              — <?= esc($service['estado_asociacion']) ?>
                            </li>
                          <?php endforeach ?>
                        </ul>
                      <?php endif ?>
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

  <script src="<?= base_url('js/home.js') ?>"></script>
</body>
</html>

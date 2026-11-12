<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Solicitud pendiente - ServiInmueble</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/home.css') ?>">
</head>
<body class="access-status-page">
  <main class="access-status-card" aria-labelledby="statusTitle">
    <div class="welcome-badge">Solicitud recibida</div>
    <h1 class="page-title" id="statusTitle">Tu cuenta está pendiente de aprobación</h1>
    <p class="page-subtitle">
      Hola <?= esc($user['nombre'] ?? '') ?>. Un administrador debe aprobar tu solicitud.
      Cuando la apruebe, vas a poder usar las funciones del rol Propietario.
    </p>
    <p class="access-status-note">
      Hasta entonces, las funciones del sistema permanecen bloqueadas. Volvé a ingresar más tarde para consultar el estado.
    </p>
    <form method="post" action="<?= base_url('logout') ?>">
      <?= csrf_field() ?>
      <button type="submit" class="btn btn-primary">Cerrar sesión</button>
    </form>
  </main>
</body>
</html>

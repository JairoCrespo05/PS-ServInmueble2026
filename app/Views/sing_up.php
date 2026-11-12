<?php
$errors = $errors ?? [];
$oldInput = $oldInput ?? [];
$formError = $formError ?? null;
$success = $success ?? null;
$fieldError = static function (string $field) use ($errors): string {
  if (! isset($errors[$field])) {
    return '';
  }

  return '<div class="field-error" id="error-' . esc($field, 'attr') . '" role="alert">' . esc($errors[$field]) . '</div>';
};
$fieldErrorAttributes = static function (string $field) use ($errors): string {
  if (! isset($errors[$field])) {
    return '';
  }

  return ' aria-invalid="true" aria-describedby="error-' . esc($field, 'attr') . '"';
};
$alertMessage = $formError ?? ($errors ? 'Revisá los campos marcados e intentá nuevamente.' : $success);
$alertClass = $success !== null && $formError === null && ! $errors ? 'alert-success' : 'alert-error';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ServiInmueble - Registro de Usuario (US-01)</title>
  <!-- Google Fonts: Manrope & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>">
</head>
<body class="signup-page">

  <!-- TOPBAR PÚBLICA -->
  <header class="topbar">
    <a href="#" class="brand-container">
      <div class="brand-logo-icon">SI</div>
      <div class="brand-text-block">
        <span class="brand-title">ServiInmueble</span>
        <span class="brand-subtitle">Gestión Inmobiliaria</span>
      </div>
    </a>

    <nav class="topbar-nav">
      <a href="<?= base_url('') ?>" class="nav-link" id="navLinkLogin">Iniciar Sesión</a>
      <a href="<?= base_url('sing_up') ?>" class="nav-link active">Registrarse</a>
      <a href="#" class="nav-link">Ayuda</a>
    </nav>

    <div class="topbar-right">
      <a href="#" class="btn-help">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"></circle>
          <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
          <line x1="12" y1="17" x2="12.01" y2="17"></line>
        </svg>
        Soporte
      </a>
      <div class="user-avatar-placeholder" title="Acceso no autenticado">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
          <circle cx="12" cy="7" r="4"></circle>
        </svg>
      </div>
    </div>
  </header>

  <!-- CONTENEDOR PRINCIPAL -->
  <main class="page-main">
    <div class="auth-container">
      
      <!-- HERO INSTITUCIONAL LATERAL -->
      <section class="hero-card">
        <div>
          <div class="pill-badge">
            <span class="pill-dot"></span>
            Plataforma PropTech Homologada
          </div>

          <div class="hero-pretitle">Módulo de Gestión Patrimonial</div>
          <h1 class="hero-title">
            Control total de vencimientos, auditoría de consumos y cálculo de mora en tiempo real.
          </h1>
          <p class="hero-description">
            Infraestructura especializada para optimizar la rentabilidad, digitalizar contratos y liquidar expensas con máxima rigurosidad contable.
          </p>

          <!-- Tarjeta de KPIs embebida -->
          <div class="hero-stats-box">
            <div class="stats-header">
              <span>Métricas Operativas Mensuales</span>
              <span class="stats-highlight">+99.4% Conciliación</span>
            </div>
            
            <svg class="stats-sparkline-svg" viewBox="0 0 260 40" fill="none">
              <path d="M2 30 Q 50 10, 100 24 T 180 14 T 258 8" stroke="rgba(255,255,255,0.7)" stroke-width="2.5" fill="none"/>
              <circle cx="258" cy="8" r="3.5" fill="#10B981"/>
            </svg>

            <div class="stats-grid">
              <div>
                <div class="stat-label">Vencimientos</div>
                <div class="stat-val">0% Desvío</div>
              </div>
              <div>
                <div class="stat-label">Mora Punitoria</div>
                <div class="stat-val">Automatizada</div>
              </div>
              <div>
                <div class="stat-label">Auditoría</div>
                <div class="stat-val">Inalterable</div>
              </div>
            </div>
          </div>

          <ul class="hero-features">
            <li class="hero-feature-item">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
              </svg>
              <span>Auditoría Automatizada de Servicios y detección de anomalías</span>
            </li>
            <li class="hero-feature-item">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
              </svg>
              <span>Indexación y Resarcimiento por Mora según coeficientes BCRA</span>
            </li>
          </ul>
        </div>

        <div class="hero-footer">
          <div>Gestión Centralizada Multi-Consorcio</div>
          <div>AR - 01 - SEC</div>
        </div>
      </section>

      <!-- TARJETA FORMULARIO ALTA DE USUARIO (US-01) -->
      <section class="form-card">
        <div class="form-header">
          <div class="form-meta">
            <span class="form-category">Alta de Usuario</span>
            <span class="badge-code">SEC-4.2 // US-01</span>
          </div>
          <h2 class="form-title">Crear Cuenta</h2>
          <p class="form-subtitle">Comenzá a gestionar tus propiedades y servicios de forma centralizada.</p>
        </div>

        <!-- Alerta de Validación -->
        <div class="form-alert <?= $alertMessage ? 'show ' . esc($alertClass, 'attr') : '' ?>" id="registerAlert" aria-live="polite">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <span id="registerAlertText"><?= esc($alertMessage ?? '') ?></span>
        </div>

        <form id="formRegister" method="post" action="<?= base_url('sing_up') ?>">
          <?= csrf_field() ?>

          <!-- Fila Nombre y Apellido -->
          <div class="form-grid-2">
            <div class="form-group">
              <label for="inputFirstName" class="form-label">Nombre *</label>
              <input type="text" id="inputFirstName" name="first_name" class="form-input no-icon" placeholder="Ej. Martín" value="<?= esc($oldInput['first_name'] ?? '') ?>" required<?= $fieldErrorAttributes('first_name') ?> />
              <?= $fieldError('first_name') ?>
            </div>
            <div class="form-group">
              <label for="inputLastName" class="form-label">Apellido *</label>
              <input type="text" id="inputLastName" name="last_name" class="form-input no-icon" placeholder="Ej. Rossi" value="<?= esc($oldInput['last_name'] ?? '') ?>" required<?= $fieldErrorAttributes('last_name') ?> />
              <?= $fieldError('last_name') ?>
            </div>
          </div>

          <!-- Correo Electrónico -->
          <div class="form-group">
            <label for="inputRegEmail" class="form-label">Correo Electrónico *</label>
            <div class="input-wrapper">
              <div class="input-icon-left">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
              </div>
              <input type="email" id="inputRegEmail" name="email" class="form-input" placeholder="ejemplo@serviinmueble.com.ar" value="<?= esc($oldInput['email'] ?? '') ?>" required autocomplete="email"<?= $fieldErrorAttributes('email') ?> />
            </div>
            <?= $fieldError('email') ?>
          </div>

          <!-- Teléfono Móvil (Opcional según 4.2) -->
          <div class="form-group">
            <label for="inputPhone" class="form-label">Teléfono Móvil <span class="optional">(Opcional)</span></label>
            <div class="input-wrapper">
              <div class="phone-prefix-block">
                <span>🇦🇷 +54</span>
              </div>
              <input type="tel" id="inputPhone" name="phone" class="form-input with-prefix" placeholder="11 4589 1234" value="<?= esc($oldInput['phone'] ?? '') ?>"<?= $fieldErrorAttributes('phone') ?> />
            </div>
            <?= $fieldError('phone') ?>
          </div>

          <!-- Fila Contraseña y Confirmación -->
          <div class="form-grid-2">
            <div class="form-group">
              <label for="inputRegPassword" class="form-label">Contraseña *</label>
              <div class="input-wrapper">
                <input type="password" id="inputRegPassword" name="password" class="form-input no-icon" placeholder="Mín. 8 caracteres" minlength="8" required<?= $fieldErrorAttributes('password') ?> />
                <button type="button" class="btn-toggle-password" id="toggleRegPass" aria-controls="inputRegPassword" aria-label="Mostrar u ocultar contraseña" aria-pressed="false">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
              </div>
              <?= $fieldError('password') ?>
            </div>

            <div class="form-group">
              <label for="inputConfirmPassword" class="form-label">Confirmar Contraseña *</label>
              <div class="input-wrapper">
                <input type="password" id="inputConfirmPassword" name="confirm_password" class="form-input no-icon" placeholder="Repita la clave" required<?= $fieldErrorAttributes('confirm_password') ?> />
                <button type="button" class="btn-toggle-password" id="toggleConfirmPass" aria-controls="inputConfirmPassword" aria-label="Mostrar u ocultar contraseña" aria-pressed="false">
                  <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                    <circle cx="12" cy="12" r="3"></circle>
                  </svg>
                </button>
              </div>
              <?= $fieldError('confirm_password') ?>
            </div>
          </div>

          <!-- Indicador de Fortaleza Dinámico (Vanilla JS) -->
          <div class="strength-wrapper">
            <div class="strength-bar-container">
              <div class="strength-segment" id="seg1"></div>
              <div class="strength-segment" id="seg2"></div>
              <div class="strength-segment" id="seg3"></div>
              <div class="strength-segment" id="seg4"></div>
            </div>
            <div class="strength-rules">
              <div class="rule-item" id="ruleLen"><span class="rule-dot"></span> Mín. 8 caracteres</div>
              <div class="rule-item" id="ruleNum"><span class="rule-dot"></span> Al menos 1 número</div>
              <div class="rule-item" id="ruleSpecial"><span class="rule-dot"></span> Símbolo especial (!@#$)</div>
            </div>
          </div>

          <!-- Nota de Arquitectura y Seguridad de Roles (Sección 3) -->
          <div class="role-notice-box">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="10"></circle>
              <line x1="12" y1="16" x2="12" y2="12"></line>
              <line x1="12" y1="8" x2="12.01" y2="8"></line>
            </svg>
            <div class="role-notice-text">
              <strong>Solicitud de acceso:</strong> al registrarte, tu cuenta queda pendiente de aprobación. Cuando un administrador la apruebe, se habilitarán las funciones del rol <strong>Propietario</strong>.
            </div>
          </div>

          <!-- Checkbox Términos -->
          <label class="checkbox-row">
            <input type="checkbox" id="checkTerms" name="terms" value="1" required<?= ($oldInput['terms'] ?? '') === '1' ? ' checked' : '' ?><?= $fieldErrorAttributes('terms') ?> />
            <span class="custom-checkbox">
              <svg class="checkbox-icon" viewBox="0 0 24 24" fill="none">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </span>
            <span class="checkbox-label-text">
              Acepto los <a href="#">Términos del Servicio</a> y la <a href="#">Política de Privacidad</a> de ServiInmueble.
            </span>
          </label>
          <?= $fieldError('terms') ?>

          <!-- Botón de Envío -->
          <button type="submit" class="btn-primary" id="btnSubmitRegister">
            <span>Registrarse en ServiInmueble</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>

          <div class="login-prompt-link">
            ¿Ya tenés cuenta? <a href="<?= base_url('') ?>" id="linkBackToLogin">Iniciar sesión</a>
          </div>

        </form>
      </section>

    </div>
  </main>

  <!-- FOOTER -->
  <footer class="global-footer">
    <div>
      &copy; 2026 ServiInmueble Corp. Todos los derechos reservados. Arquitectura y Gestión Operativa.
    </div>
    <div class="footer-links">
      <a href="#">Términos del Servicio</a>
      <a href="#">Políticas de Privacidad</a>
      <a href="#">Centro de Ayuda</a>
    </div>
  </footer>

  <!-- JAVASCRIPT VANILLA PURO (Sin librerías, sin dependencias) -->
  <script src="<?= base_url('js/script.js') ?>"></script>
</body>
</html>

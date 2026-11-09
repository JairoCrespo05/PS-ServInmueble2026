<!DOCTYPE html><html lang="es"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ServiInmueble - Iniciar Sesión (US-02)</title>
  <!-- Google Fonts: Manrope & Inter -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin="">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&amp;family=Manrope:wght@600;700;800&amp;display=swap" rel="stylesheet">

 <link rel="stylesheet" href="<?= base_url('css/styles.css') ?>"> 
 

</head>
<body class="login-page">

  <!-- NAVEGACIÓN SUPERIOR PÚBLICA -->
  <header class="topbar">
    <a href="#" class="brand-container">
      <div class="brand-logo-icon">SI</div>
      <div class="brand-text-block">
        <span class="brand-title">ServiInmueble</span>
        <span class="brand-subtitle">Gestión Inmobiliaria</span>
      </div>
    </a>

    <nav class="topbar-nav">
      <a href="<?= base_url() ?>" class="nav-link active">Iniciar Sesión</a>
      <a href="<?= base_url('sing_up') ?>" class="nav-link" id="linkGoToRegister">Registrarse</a>
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

  <!-- CONTENIDO PRINCIPAL / PANTALLA LOGIN -->
  <main class="page-main">
    <div class="auth-container">
      
      <!-- HERO LATERAL ILUSTRATIVO CON TOKENS OFICIALES -->
      <section class="hero-card">
        <div>
          <div class="pill-badge">
            <span class="pill-dot"></span>
            Ecosistema Conectado v2.4
          </div>
          
          <h1 class="hero-title">
            Centralizá la gestión de tus inmuebles, servicios e impuestos en un solo lugar.
          </h1>
          
          <p class="hero-description">
            La forma más fácil y ordenada de controlar tus facturas, evitar vencimientos y tener todos tus comprobantes al día, seas propietario o inquilino.
          </p>

          <ul class="hero-features">
            <li class="hero-feature-item">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              <span class="">Control automático de vencimientos con semáforos de mora</span>
            </li>
            <li class="hero-feature-item">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              <span class="">Asociación de medidores, contratos y servicios (US-06)</span>
            </li>
            <li class="hero-feature-item">
              <svg viewBox="0 0 24 24" fill="none" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
              <span class="">Liquidación y registro ágil de facturas devengadas (US-07)</span>
            </li>
          </ul>
        </div>

        <div class="hero-footer">
          <div class="hero-footer-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
            </svg>
            Acceso seguro protegido por cifrado SSL de 256 bits
          </div>
          <div class="">ServiInmueble Cloud 2026</div>
        </div>
      </section>

      <!-- FORMULARIO LIMPIO DE LOGIN (US-02) -->
      <section class="form-card">
        <div class="form-header">
          <div class="form-meta">
            <span class="form-category">Portal de Acceso</span>
            <span class="status-pill-subtle">Autenticado US-02</span>
          </div>
          <h2 class="form-title">Iniciar Sesión</h2>
          <p class="form-subtitle">Accedé a tu panel de control patrimonial unificado.</p>
        </div>

        <!-- Alerta de Validación Dinámica (Vanilla JS) -->
        <div class="form-alert" id="loginAlert">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"></circle>
            <line x1="12" y1="8" x2="12" y2="12"></line>
            <line x1="12" y1="16" x2="12.01" y2="16"></line>
          </svg>
          <span id="loginAlertText" class="">Por favor completá las credenciales correctamente.</span>
        </div>

        <form id="formLogin" novalidate="">
          
          <!-- Campo: Correo Electrónico -->
          <div class="form-group">
            <div class="form-label-row">
              <label for="inputEmail" class="form-label">Correo Electrónico *</label>
            </div>
            <div class="input-wrapper">
              <div class="input-icon-left">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                  <polyline points="22,6 12,13 2,6"></polyline>
                </svg>
              </div>
              <input type="email" id="inputEmail" name="email" class="form-input" placeholder="ejemplo@serviinmueble.com.ar" required="" autocomplete="email">
            </div>
          </div>

          <!-- Campo: Contraseña -->
          <div class="form-group">
            <div class="form-label-row">
              <label for="inputPassword" class="form-label">Contraseña *</label>
              <a href="#" class="form-forgot-link">¿Olvidaste tu contraseña?</a>
            </div>
            <div class="input-wrapper">
              <div class="input-icon-left">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
              </div>
              <input type="password" id="inputPassword" name="password" class="form-input" placeholder="Ingresá tu contraseña" required="" autocomplete="current-password">
              <button type="button" class="btn-toggle-password" id="btnTogglePassword" aria-label="Mostrar u ocultar contraseña">
                <svg id="eyeIcon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                  <circle cx="12" cy="12" r="3"></circle>
                </svg>
              </button>
            </div>
          </div>

          <!-- Checkbox: Recordarme -->
          <label class="checkbox-row">
            <input type="checkbox" id="checkRemember" name="remember">
            <span class="custom-checkbox">
              <svg class="checkbox-icon" viewBox="0 0 24 24" fill="none">
                <polyline points="20 6 9 17 4 12"></polyline>
              </svg>
            </span>
            <span class="checkbox-label-text">Recordar mi sesión en este equipo</span>
          </label>

          <!-- Botón de Envío -->
          <button type="submit" class="btn-primary" id="btnSubmitLogin">
            <span class="">Ingresar al Sistema</span>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </button>

        </form>

        <div class="divider-text">
          <span class="">Nuevo en el sistema</span>
        </div>

        <div class="register-card-link">
          ¿No tenés una cuenta registrada? 
          <a href="<?= base_url('sing_up') ?>" id="linkRegisterBottom" class="">Registrate aquí →</a>
        </div>

        <div class="support-link-block">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
          </svg>
          ¿Inconvenientes para acceder? <a href="#" class="">Contactá a Soporte Técnico</a>
        </div>

      </section>

    </div>
  </main>

  <!-- PIE DE PÁGINA GLOBAL -->
  <footer class="global-footer">
    <div class="">
      © 2026 ServiInmueble Corp. Todos los derechos reservados. Arquitectura y Gestión Operativa.
    </div>
    <div class="footer-links">
      <a href="#" class="">Términos del Servicio</a>
      <a href="#" class="">Políticas de Privacidad</a>
      <a href="#" class="">Centro de Ayuda</a>
    </div>
  </footer>

  <script src="<?= base_url('js/script.js') ?>"></script>


</body></html>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Organizá inmuebles, servicios y facturas en un solo lugar con ServiInmueble.">
  <title>ServiInmueble | Gestión inmobiliaria organizada</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('css/landing.css') ?>?v=<?= filemtime(FCPATH . 'css/landing.css') ?>">
</head>
<body class="landing-page">
  <header class="landing-header">
    <a class="landing-brand" href="<?= base_url() ?>" aria-label="ServiInmueble, inicio">
      <span class="landing-brand-icon" aria-hidden="true">domain</span>
      <span class="landing-brand-name">Servi<span>Inmueble</span></span>
    </a>

    <nav class="landing-nav" aria-label="Navegación principal">
      <a href="#como-funciona">Cómo funciona</a>
      <a href="#herramientas">Herramientas</a>
      <a href="#equipo">El equipo</a>
    </nav>

    <div class="landing-header-actions">
      <a class="landing-login-link" href="<?= base_url('login') ?>">Iniciar sesión</a>
      <a class="landing-button landing-button-small" href="<?= base_url('sing_up') ?>">Crear cuenta</a>
    </div>
  </header>

  <main>
    <section class="landing-hero" aria-labelledby="landing-title">
      <div class="landing-hero-copy">
        <p class="landing-eyebrow"><span aria-hidden="true"></span> Una forma más clara de organizar tu gestión</p>
        <h1 id="landing-title">Tus inmuebles y servicios, <span>en orden.</span></h1>
        <p class="landing-hero-description">
          ServiInmueble reúne la información de tus propiedades, los servicios asociados y sus facturas para que puedas consultarla desde un mismo lugar.
        </p>
        <div class="landing-hero-actions">
          <a class="landing-button" href="<?= base_url('sing_up') ?>">
            Empezar ahora
            <svg aria-hidden="true" viewBox="0 0 20 20" fill="none"><path d="M4 10h12m-5-5 5 5-5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
          </a>
          <a class="landing-text-link" href="#como-funciona">Conocé cómo funciona <span aria-hidden="true">↓</span></a>
        </div>
        <!--
        <p class="landing-approval-note">
          <svg aria-hidden="true" viewBox="0 0 20 20" fill="none"><circle cx="10" cy="10" r="7.5" stroke="currentColor" stroke-width="1.5"/><path d="M10 9v4m0-6h.01" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>
          Las cuentas nuevas requieren aprobación administrativa antes de acceder a las funciones de propietario.
        </p>
        -->
      </div>

      <div class="landing-showcase" data-carousel role="region" aria-roledescription="carrusel" aria-label="Recorrido por ServiInmueble" tabindex="0">
        <div class="showcase-topline">
          <span class="showcase-brand-mark" aria-hidden="true">domain</span>
          <span>Vista ilustrativa · Un espacio para tu gestión</span>
          <span class="showcase-window-dots" aria-hidden="true"><i></i><i></i><i></i></span>
        </div>

        <div class="showcase-slides" aria-live="polite">
          <article class="showcase-slide is-active" data-slide aria-roledescription="diapositiva" aria-label="1 de 3">
            <div class="showcase-slide-copy">
              <span class="showcase-step">01 / INMUEBLES</span>
              <h2>Tu cartera, organizada</h2>
              <p>Registrá tus inmuebles y consultá la información principal de cada uno.</p>
            </div>
            <div class="showcase-visual property-visual" aria-hidden="true">
              <div class="visual-caption"><span class="visual-caption-dot"></span> Mis inmuebles</div>
              <div class="property-mini-card">
                <span class="mini-icon building-icon"><svg viewBox="0 0 24 24" fill="none"><path d="M4 20V5a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v15M2 20h20M8 8h2m2 0h2M8 12h2m2 0h2m-5 8v-4h4v4" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/></svg></span>
                <span class="mini-lines"><i></i><i></i><i></i></span>
                <span class="mini-arrow">↗</span>
              </div>
              <div class="property-mini-card property-mini-card-secondary">
                <span class="mini-icon house-icon"><svg viewBox="0 0 24 24" fill="none"><path d="m3 10 9-7 9 7v10H3V10Z" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/><path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round"/></svg></span>
                <span class="mini-lines"><i></i><i></i></span>
                <span class="mini-arrow">↗</span>
              </div>
              <span class="visual-spark visual-spark-one"></span><span class="visual-spark visual-spark-two"></span>
            </div>
          </article>

          <article class="showcase-slide" data-slide aria-roledescription="diapositiva" aria-label="2 de 3" hidden>
            <div class="showcase-slide-copy">
              <span class="showcase-step">02 / SERVICIOS</span>
              <h2>Todo vinculado al inmueble</h2>
              <p>Asociá servicios como electricidad, agua, gas, internet o expensas a cada propiedad.</p>
            </div>
            <div class="showcase-visual services-visual" aria-hidden="true">
              <div class="visual-caption"><span class="visual-caption-dot"></span> Servicios asociados</div>
              <div class="service-mini-card"><span class="service-mini-icon electric">ϟ</span><span><b>Electricidad</b><i>Asociado a inmueble</i></span><span class="service-mini-check">✓</span></div>
              <div class="service-mini-card"><span class="service-mini-icon water">≈</span><span><b>Agua</b><i>Asociado a inmueble</i></span><span class="service-mini-check">✓</span></div>
              <div class="service-mini-card"><span class="service-mini-icon internet">⌁</span><span><b>Internet</b><i>Asociado a inmueble</i></span><span class="service-mini-check">✓</span></div>
              <span class="service-visual-orbit orbit-one"></span><span class="service-visual-orbit orbit-two"></span>
            </div>
          </article>

          <article class="showcase-slide" data-slide aria-roledescription="diapositiva" aria-label="3 de 3" hidden>
            <div class="showcase-slide-copy">
              <span class="showcase-step">03 / FACTURAS</span>
              <h2>Un registro fácil de consultar</h2>
              <p>Cargá facturas de tus servicios y revisá sus estados en listados e informes.</p>
            </div>
            <div class="showcase-visual invoices-visual" aria-hidden="true">
              <div class="visual-caption"><span class="visual-caption-dot"></span> Facturación</div>
              <div class="invoice-mini-header"><span>Servicio</span><span>Estado</span></div>
              <div class="invoice-mini-row"><span><i class="invoice-glyph invoice-glyph-blue">ϟ</i><b>Electricidad</b></span><em class="invoice-status-pending">Pendiente</em></div>
              <div class="invoice-mini-row"><span><i class="invoice-glyph invoice-glyph-green">≈</i><b>Agua</b></span><em class="invoice-status-paid">Pagada</em></div>
              <div class="invoice-mini-row"><span><i class="invoice-glyph invoice-glyph-violet">⌁</i><b>Internet</b></span><em class="invoice-status-pending">Pendiente</em></div>
              <div class="invoice-mini-footer"><span>Informe de facturación</span><span>Ver detalle&nbsp; ↗</span></div>
            </div>
          </article>
        </div>

        <div class="showcase-controls">
          <span class="showcase-current" data-slide-count>01 <i>/</i> 03</span>
          <div class="showcase-dots" role="group" aria-label="Elegir diapositiva">
            <button type="button" class="is-active" data-go-to="0" aria-label="Mostrar diapositiva 1" aria-current="true"></button>
            <button type="button" data-go-to="1" aria-label="Mostrar diapositiva 2"></button>
            <button type="button" data-go-to="2" aria-label="Mostrar diapositiva 3"></button>
          </div>
          <div class="showcase-arrows">
            <button type="button" data-previous aria-label="Diapositiva anterior">←</button>
            <button type="button" data-next aria-label="Diapositiva siguiente">→</button>
          </div>
        </div>
      </div>
    </section>

    <section class="landing-proof-strip" aria-label="Áreas de gestión">
      <span>Todo conectado en un mismo lugar</span>
      <div><i class="proof-icon">⌂</i> Inmuebles</div>
      <div><i class="proof-icon">ϟ</i> Servicios</div>
      <div><i class="proof-icon">▤</i> Facturas</div>
      <div><i class="proof-icon">▥</i> Informes</div>
    </section>

    <section class="landing-workflow section-shell" id="como-funciona" aria-labelledby="workflow-title">
      <div class="section-heading">
        <p class="landing-eyebrow"><span aria-hidden="true"></span> Un flujo simple y conectado</p>
        <h2 id="workflow-title">De la propiedad al informe</h2>
        <p>La información se organiza paso a paso: cada servicio y cada factura queda relacionado con el inmueble correspondiente.</p>
      </div>
      <div class="workflow-grid">
        <article class="workflow-card">
          <span class="workflow-number">01</span>
          <span class="workflow-icon"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="m3 10 9-7 9 7v10H3V10Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M9 20v-6h6v6" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/></svg></span>
          <h3>Registrá tus inmuebles</h3>
          <p>Guardá los datos principales y la dirección de cada propiedad que gestionás.</p>
        </article>
        <article class="workflow-card">
          <span class="workflow-number">02</span>
          <span class="workflow-icon"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M4 12h16M12 4v16M7 7l10 10m0-10L7 17" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
          <h3>Asociá sus servicios</h3>
          <p>Vinculá a cada inmueble los servicios disponibles en el catálogo, como agua o electricidad.</p>
        </article>
        <article class="workflow-card">
          <span class="workflow-number">03</span>
          <span class="workflow-icon"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M6 3h9l4 4v14H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/><path d="M14 3v5h5M8 13h8m-8 4h8" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"/></svg></span>
          <h3>Cargá las facturas</h3>
          <p>Registrá el período, vencimiento e importe y consultá el detalle de cada factura.</p>
        </article>
        <article class="workflow-card">
          <span class="workflow-number">04</span>
          <span class="workflow-icon"><svg aria-hidden="true" viewBox="0 0 24 24" fill="none"><path d="M4 19V5m0 14h17M8 15l3-4 3 2 5-7" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/><circle cx="19" cy="6" r="1.5" fill="currentColor"/></svg></span>
          <h3>Consultá tus informes</h3>
          <p>Revisá listados de facturas pendientes y pagadas, con sus datos y totales.</p>
        </article>
      </div>
      <p class="workflow-footnote"><span aria-hidden="true">↳</span> El catálogo de tipos de servicio es administrado por el equipo responsable del sistema.</p>
    </section>

    <section class="landing-tools" id="herramientas" aria-labelledby="tools-title">
      <div class="tools-inner section-shell">
        <div class="tools-copy">
          <p class="landing-eyebrow"><span aria-hidden="true"></span> Pensado para cada rol</p>
          <h2 id="tools-title">Cada persona encuentra lo que necesita.</h2>
          <p>El acceso y las opciones disponibles dependen del rol asignado a cada cuenta.</p>
        </div>
        <div class="role-cards">
          <article class="role-card">
            <span class="role-label"><span aria-hidden="true">⌂</span> Propietario</span>
            <h3>Gestioná tus propiedades</h3>
            <p>Administrá tus inmuebles y servicios asociados. Cargá facturas y consultá informes propios.</p>
            <ul><li>Inmuebles y servicios</li><li>Facturas y sus estados</li><li>Informe de facturación</li></ul>
          </article>
          <article class="role-card role-card-admin">
            <span class="role-label"><span aria-hidden="true">▦</span> Administración</span>
            <h3>Gestioná el acceso y el catálogo</h3>
            <p>Revisá solicitudes de cuenta y mantené los tipos de servicio disponibles en la plataforma.</p>
            <ul><li>Solicitudes de acceso</li><li>Usuarios registrados</li><li>Tipos de servicio</li></ul>
          </article>
        </div>
      </div>
    </section>

    <section class="landing-team section-shell" id="equipo" aria-labelledby="team-title">
      <div class="team-intro">
        <p class="landing-eyebrow"><span aria-hidden="true"></span> Detrás de ServiInmueble</p>
        <h2 id="team-title">Un proyecto hecho en equipo.</h2>
        <p>Somos quienes diseñamos y desarrollamos esta herramienta para organizar la gestión de inmuebles y sus servicios.</p>
      </div>
      <div class="team-members">
        <article class="team-member">
          <span class="team-avatar avatar-blue" aria-hidden="true">MB</span>
          <div><h3>Marcelo Baldome</h3><p>Equipo de desarrollo</p></div>
        </article>
        <article class="team-member">
          <span class="team-avatar avatar-green" aria-hidden="true">RQ</span>
          <div><h3>Rodrigo Quichan</h3><p>Equipo de desarrollo</p></div>
        </article>
        <article class="team-member">
          <span class="team-avatar avatar-violet" aria-hidden="true">JC</span>
          <div><h3>Jairo Crespo</h3><p>Equipo de desarrollo</p></div>
        </article>
      </div>
    </section>

    <section class="landing-final-cta" aria-labelledby="cta-title">
      <div>
        <p class="landing-eyebrow"><span aria-hidden="true"></span> Empezá cuando quieras</p>
        <h2 id="cta-title">Dale un lugar a cada dato.</h2>
        <p>Creá tu cuenta y, una vez aprobada, comenzá a organizar tus inmuebles y servicios.</p>
      </div>
      <a class="landing-button landing-button-light" href="<?= base_url('sing_up') ?>">Crear mi cuenta <span aria-hidden="true">→</span></a>
    </section>
  </main>

  <footer class="landing-footer">
    <a class="landing-brand" href="<?= base_url() ?>" aria-label="ServiInmueble, inicio">
      <span class="landing-brand-icon" aria-hidden="true">domain</span>
      <span class="landing-brand-name">Servi<span>Inmueble</span></span>
    </a>
    <p>Gestión de inmuebles, servicios y facturas.</p>
    <span class="landing-footer-copy">© 2026 ServiInmueble</span>
  </footer>
  <script src="<?= base_url('js/landing.js') ?>?v=<?= filemtime(FCPATH . 'js/landing.js') ?>"></script>
</body>
</html>

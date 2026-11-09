(function() {
  function showAlert(alertBox, alertText, message, type) {
    alertBox.classList.remove('alert-error', 'alert-success');
    alertBox.classList.add(`alert-${type}`);
    alertText.textContent = message;
    alertBox.classList.add('show');
  }

  function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(String(email).toLowerCase());
  }

  function initializeLoginForm() {
    const form = document.getElementById('formLogin');
    if (!form) {
      return;
    }

    const inputPass = document.getElementById('inputPassword');
    const toggleBtn = document.getElementById('btnTogglePassword');
    const eyeIcon = document.getElementById('eyeIcon');
    const alertBox = document.getElementById('loginAlert');
    const alertText = document.getElementById('loginAlertText');
    const inputEmail = document.getElementById('inputEmail');
    const btnSubmit = document.getElementById('btnSubmitLogin');

    toggleBtn.addEventListener('click', function() {
      const isPassword = inputPass.getAttribute('type') === 'password';
      inputPass.setAttribute('type', isPassword ? 'text' : 'password');
      eyeIcon.innerHTML = isPassword
        ? `
          <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
          <line x1="1" y1="1" x2="23" y2="23"></line>
        `
        : `
          <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
          <circle cx="12" cy="12" r="3"></circle>
        `;
    });

    form.addEventListener('submit', function(event) {
      event.preventDefault();
      alertBox.classList.remove('show');

      const email = inputEmail.value.trim();
      const password = inputPass.value;
      if (!email) {
        showAlert(alertBox, alertText, 'Por favor ingresá tu correo electrónico.', 'error');
        inputEmail.focus();
        return;
      }

      if (!validateEmail(email)) {
        showAlert(alertBox, alertText, 'El formato del correo electrónico no es válido.', 'error');
        inputEmail.focus();
        return;
      }

      if (!password) {
        showAlert(alertBox, alertText, 'Por favor ingresá tu contraseña.', 'error');
        inputPass.focus();
        return;
      }

      if (password.length < 6) {
        showAlert(alertBox, alertText, 'La contraseña debe contener al menos 6 caracteres.', 'error');
        inputPass.focus();
        return;
      }

      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<span>Verificando credenciales...</span>';
      setTimeout(function() {
        showAlert(alertBox, alertText, '¡Inicio de sesión exitoso! Redirigiendo al panel...', 'success');
        btnSubmit.innerHTML = '<span>Redirigiendo...</span>';
      }, 800);
    });
  }

  function initializeRegistrationForm() {
    const form = document.getElementById('formRegister');
    if (!form) {
      return;
    }

    const toggleBtn = document.getElementById('toggleRegPass');
    const inputPass = document.getElementById('inputRegPassword');
    const alertBox = document.getElementById('registerAlert');
    const alertText = document.getElementById('registerAlertText');
    const firstName = document.getElementById('inputFirstName');
    const lastName = document.getElementById('inputLastName');
    const email = document.getElementById('inputRegEmail');
    const confirmPass = document.getElementById('inputConfirmPassword');
    const checkTerms = document.getElementById('checkTerms');
    const btnSubmit = document.getElementById('btnSubmitRegister');
    const segments = [
      document.getElementById('seg1'),
      document.getElementById('seg2'),
      document.getElementById('seg3'),
      document.getElementById('seg4')
    ];
    const ruleLen = document.getElementById('ruleLen');
    const ruleNum = document.getElementById('ruleNum');
    const ruleSpecial = document.getElementById('ruleSpecial');

    toggleBtn.addEventListener('click', function() {
      const isPassword = inputPass.getAttribute('type') === 'password';
      inputPass.setAttribute('type', isPassword ? 'text' : 'password');
    });

    inputPass.addEventListener('input', function() {
      const value = inputPass.value;
      const hasLength = value.length >= 8;
      const hasNumber = /\d/.test(value);
      const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
      const score = Number(value.length > 0) + Number(hasLength) + Number(hasNumber) + Number(hasSpecial);

      ruleLen.classList.toggle('valid', hasLength);
      ruleNum.classList.toggle('valid', hasNumber);
      ruleSpecial.classList.toggle('valid', hasSpecial);
      const strengthBar = segments[0].parentElement;
      strengthBar.classList.remove('strength-1', 'strength-2', 'strength-3', 'strength-4');
      if (score > 0) {
        strengthBar.classList.add(`strength-${score}`);
      }
    });

    form.addEventListener('submit', function(event) {
      event.preventDefault();
      alertBox.classList.remove('show');

      if (!firstName.value.trim()) {
        showAlert(alertBox, alertText, 'Por favor ingresá tu nombre.', 'error');
        firstName.focus();
        return;
      }

      if (!lastName.value.trim()) {
        showAlert(alertBox, alertText, 'Por favor ingresá tu apellido.', 'error');
        lastName.focus();
        return;
      }

      if (!email.value.trim() || !validateEmail(email.value.trim())) {
        showAlert(alertBox, alertText, 'Por favor ingresá un correo electrónico válido.', 'error');
        email.focus();
        return;
      }

      if (inputPass.value.length < 8) {
        showAlert(alertBox, alertText, 'La contraseña debe tener como mínimo 8 caracteres.', 'error');
        inputPass.focus();
        return;
      }

      if (inputPass.value !== confirmPass.value) {
        showAlert(alertBox, alertText, 'Las contraseñas no coinciden.', 'error');
        confirmPass.focus();
        return;
      }

      if (!checkTerms.checked) {
        showAlert(alertBox, alertText, 'Debés aceptar los Términos del Servicio y la Política de Privacidad.', 'error');
        return;
      }

      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<span>Creando cuenta...</span>';
      setTimeout(function() {
        showAlert(alertBox, alertText, '¡Cuenta creada con éxito! Redirigiendo al inicio de sesión...', 'success');
        btnSubmit.innerHTML = '<span>Redirigiendo...</span>';
      }, 900);
    });
  }

  initializeLoginForm();
  initializeRegistrationForm();
})();

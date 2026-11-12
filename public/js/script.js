(function() {
  function initializeTimedMessages() {
    document.querySelectorAll('.form-alert.show, .field-error').forEach(function(message) {
      setTimeout(function() {
        message.classList.add('is-dismissed');
        if (message.classList.contains('field-error')) {
          const input = document.querySelector(`[aria-describedby="${message.id}"]`);
          if (input) {
            input.removeAttribute('aria-invalid');
            input.removeAttribute('aria-describedby');
          }
        }
      }, 3000);
    });
  }

  function initializePasswordToggles() {
    document.querySelectorAll('.btn-toggle-password[aria-controls]').forEach(function(toggleBtn) {
      const input = document.getElementById(toggleBtn.getAttribute('aria-controls'));
      if (!input) {
        throw new Error(`No se encontró el campo de contraseña controlado por #${toggleBtn.id}.`);
      }

      toggleBtn.addEventListener('click', function() {
        const isPassword = input.getAttribute('type') === 'password';
        input.setAttribute('type', isPassword ? 'text' : 'password');
        toggleBtn.setAttribute('aria-pressed', String(isPassword));
        toggleBtn.querySelector('svg').innerHTML = isPassword
          ? `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
          `
          : `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          `;
      });
    });
  }

  function initializeRegistrationForm() {
    const form = document.getElementById('formRegister');
    if (!form) {
      return;
    }

    const inputPass = document.getElementById('inputRegPassword');
    const confirmPass = document.getElementById('inputConfirmPassword');
    const segments = [
      document.getElementById('seg1'),
      document.getElementById('seg2'),
      document.getElementById('seg3'),
      document.getElementById('seg4')
    ];
    const ruleLen = document.getElementById('ruleLen');
    const ruleNum = document.getElementById('ruleNum');
    const ruleSpecial = document.getElementById('ruleSpecial');

    inputPass.addEventListener('input', function() {
      const value = inputPass.value;
      const hasLength = value.length >= 8;
      const hasNumber = /\d/.test(value);
      const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
      const score = Number(value.length > 0) + Number(hasLength) + Number(hasNumber) + Number(hasSpecial);

      ruleLen.classList.toggle('valid', hasLength);
      ruleNum.classList.toggle('valid', hasNumber);
      ruleSpecial.classList.toggle('valid', hasSpecial);
      inputPass.setCustomValidity(value && !hasLength
        ? 'La contraseña debe tener al menos 8 caracteres.'
        : value && (!hasNumber || !hasSpecial)
          ? 'La contraseña debe incluir al menos un número y un símbolo especial.'
          : '');
      validateConfirmPassword();
      const strengthBar = segments[0].parentElement;
      strengthBar.classList.remove('strength-1', 'strength-2', 'strength-3', 'strength-4');
      if (score > 0) {
        strengthBar.classList.add(`strength-${score}`);
      }
    });

    function validateConfirmPassword() {
      confirmPass.setCustomValidity(
        confirmPass.value && confirmPass.value !== inputPass.value
          ? 'Las contraseñas no coinciden.'
          : ''
      );
    }

    confirmPass.addEventListener('input', validateConfirmPassword);
  }

  initializePasswordToggles();
  initializeRegistrationForm();
  initializeTimedMessages();
})();

(function() {
  const dialog = document.getElementById('supportModal');
  if (!dialog) {
    return;
  }

  document.querySelectorAll('[data-open-support]').forEach(function(link) {
    link.addEventListener('click', function(event) {
      event.preventDefault();
      dialog.showModal();
    });
  });

  dialog.querySelectorAll('[data-close-support]').forEach(function(button) {
    button.addEventListener('click', function() {
      dialog.close();
    });
  });

  dialog.addEventListener('click', function(event) {
    if (event.target === dialog) {
      dialog.close();
    }
  });
})();

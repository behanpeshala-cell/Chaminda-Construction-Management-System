// Toggle "show password" checkboxes -> input[type=password]
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('[data-toggle-password]').forEach(function (chk) {
    chk.addEventListener('change', function () {
      var target = document.querySelector(chk.getAttribute('data-toggle-password'));
      if (target) target.type = chk.checked ? 'text' : 'password';
    });
  });

  // Confirm before any delete form submits
  document.querySelectorAll('form[data-confirm]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      if (!confirm(f.getAttribute('data-confirm'))) e.preventDefault();
    });
  });

  // Auto-dismiss alerts after 6s
  document.querySelectorAll('.alert').forEach(function (a) {
    setTimeout(function () {
      var alert = bootstrap.Alert.getOrCreateInstance(a);
      alert.close();
    }, 6000);
  });
});

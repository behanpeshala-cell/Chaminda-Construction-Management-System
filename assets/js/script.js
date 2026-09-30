// CCMS Interactive Scripts & Form Validation
document.addEventListener('DOMContentLoaded', function () {
  
  // Toggle "show password" checkboxes -> input[type=password]
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
      if (alert) alert.close();
    }, 6000);
  });

  // -------------------------------------------------------------
  // 1. PREVENT NEGATIVE NUMBERS (-) IN BUDGET & NUMERIC INPUTS
  // -------------------------------------------------------------
  function setupNegativePrevention() {
    // Select budget, amount, price, quantity and number inputs
    var selector = 'input[type="number"], input[min], [data-no-negative], input[name*="budget"], input[name*="amount"], input[name*="price"], input[name*="quantity"]';
    
    document.querySelectorAll(selector).forEach(function (input) {
      // Prevent typing minus (-) or scientific notation 'e'/'E' if min >= 0
      input.addEventListener('keydown', function (e) {
        if (e.key === '-' || e.key === 'e' || e.key === 'E' || e.key === '+') {
          // Check if negative numbers are disallowed (default for budget/amounts)
          var minVal = input.getAttribute('min');
          if (minVal === null || parseFloat(minVal) >= 0 || input.name.indexOf('budget') !== -1 || input.name.indexOf('amount') !== -1) {
            e.preventDefault();
          }
        }
      });

      // Strip minus sign or negative value on input or paste
      input.addEventListener('input', function () {
        var minVal = input.getAttribute('min');
        if (minVal === null || parseFloat(minVal) >= 0 || input.name.indexOf('budget') !== -1 || input.name.indexOf('amount') !== -1) {
          if (input.value && (input.value.indexOf('-') !== -1 || input.value.indexOf('e') !== -1 || input.value.indexOf('E') !== -1)) {
            input.value = input.value.replace(/[-eE]/g, '');
          }
          if (parseFloat(input.value) < 0) {
            input.value = Math.abs(parseFloat(input.value));
          }
        }
      });
    });
  }

  setupNegativePrevention();

  // Re-run for dynamically added row elements (e.g., PO lines)
  var addRowBtn = document.getElementById('addRow');
  if (addRowBtn) {
    addRowBtn.addEventListener('click', function () {
      setTimeout(setupNegativePrevention, 100);
    });
  }

  // -------------------------------------------------------------
  // 2. LIVE EMAIL, PHONE & NIC VALIDATION HANDLERS
  // -------------------------------------------------------------
  var emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
  var phoneRegex = /^\+?[0-9\s\-\(\)]{9,15}$/;
  var nicRegex = /^([0-9]{9}[vVxX]|[0-9]{12})$/;

  function setFieldError(field, errorMsg) {
    field.classList.add('is-invalid');
    var parent = field.parentElement;
    var feedback = parent.querySelector('.invalid-feedback');
    if (!feedback) {
      feedback = document.createElement('div');
      feedback.className = 'invalid-feedback';
      parent.appendChild(feedback);
    }
    feedback.textContent = errorMsg;
  }

  function clearFieldError(field) {
    field.classList.remove('is-invalid');
    var parent = field.parentElement;
    var feedback = parent.querySelector('.invalid-feedback');
    if (feedback) {
      feedback.remove();
    }
  }

  function validateEmailInput(field) {
    var val = field.value.trim();
    if (val === '') {
      if (field.hasAttribute('required')) {
        setFieldError(field, 'Email address is required.');
        return false;
      }
      clearFieldError(field);
      return true;
    }
    if (!emailRegex.test(val)) {
      setFieldError(field, 'Please enter a valid email address (e.g. name@example.com).');
      return false;
    }
    clearFieldError(field);
    return true;
  }

  function validatePhoneInput(field) {
    var val = field.value.trim();
    if (val === '') {
      if (field.hasAttribute('required')) {
        setFieldError(field, 'Phone number is required.');
        return false;
      }
      clearFieldError(field);
      return true;
    }
    var digitsOnly = val.replace(/\D/g, '');
    if (!phoneRegex.test(val) || digitsOnly.length < 9 || digitsOnly.length > 15) {
      setFieldError(field, 'Please enter a valid phone number (9-15 digits, e.g. 0771234567 or +94771234567).');
      return false;
    }
    clearFieldError(field);
    return true;
  }

  function validateNicInput(field) {
    var val = field.value.trim();
    if (val === '') {
      if (field.hasAttribute('required')) {
        setFieldError(field, 'NIC number is required.');
        return false;
      }
      clearFieldError(field);
      return true;
    }
    if (!nicRegex.test(val)) {
      setFieldError(field, 'Please enter a valid Sri Lankan NIC (9 digits + V/X or 12 digits).');
      return false;
    }
    clearFieldError(field);
    return true;
  }

  // Bind live blur & input validation on email, phone & NIC inputs
  document.querySelectorAll('input[type="email"], input[name="email"]').forEach(function (field) {
    field.addEventListener('blur', function () { validateEmailInput(field); });
    field.addEventListener('input', function () { if (field.classList.contains('is-invalid')) validateEmailInput(field); });
  });

  document.querySelectorAll('input[type="tel"], input[name="phone"]').forEach(function (field) {
    field.addEventListener('blur', function () { validatePhoneInput(field); });
    field.addEventListener('input', function () { if (field.classList.contains('is-invalid')) validatePhoneInput(field); });
  });

  document.querySelectorAll('input[name="nic_number"]').forEach(function (field) {
    field.addEventListener('blur', function () { validateNicInput(field); });
    field.addEventListener('input', function () {
      field.value = field.value.toUpperCase();
      if (field.classList.contains('is-invalid')) validateNicInput(field);
    });
  });

  // Validate on form submit
  document.querySelectorAll('form').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      var isValid = true;
      form.querySelectorAll('input[type="email"], input[name="email"]').forEach(function (field) {
        if (!validateEmailInput(field)) isValid = false;
      });
      form.querySelectorAll('input[type="tel"], input[name="phone"]').forEach(function (field) {
        if (!validatePhoneInput(field)) isValid = false;
      });
      form.querySelectorAll('input[name="nic_number"]').forEach(function (field) {
        if (!validateNicInput(field)) isValid = false;
      });

      if (!isValid) {
        e.preventDefault();
        var firstInvalid = form.querySelector('.is-invalid');
        if (firstInvalid) firstInvalid.focus();
      }
    });
  });

});

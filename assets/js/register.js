// register.js – handles dynamic country/city selection and AJAX submission
document.addEventListener('DOMContentLoaded', () => {
  const countrySelect = document.getElementById('countrySelect');
  const citySelect = document.getElementById('citySelect');
  const phoneCodeInput = document.getElementById('phoneCode');
  const form = document.getElementById('registerForm');
  const toast = document.getElementById('toast');

  // Update hidden phone code when country changes
  countrySelect.addEventListener('change', (e) => {
    const selectedOption = countrySelect.options[countrySelect.selectedIndex];
    const code = selectedOption.getAttribute('data-code') || '';
    phoneCodeInput.value = code;
    // Load cities for the selected country (placeholder – requires API endpoint)
    if (code) {
      fetch(`api/cities.php?country_id=${selectedOption.value}`)
        .then(res => res.json())
        .then(data => {
          // Expect data: { success: true, cities: [{id, name}] }
          citySelect.innerHTML = '<option value="" disabled selected>Select City</option>';
          if (data.success && Array.isArray(data.cities)) {
            data.cities.forEach(c => {
              const opt = document.createElement('option');
              opt.value = c.id;
              opt.textContent = c.name;
              citySelect.appendChild(opt);
            });
          }
        })
        .catch(err => {
          console.error('Failed to load cities', err);
        });
    }
  });

  // AJAX form submission using fetch (fallback to normal submit if JS disabled)
  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    // Convert FormData to plain object
    const payload = {};
    formData.forEach((value, key) => {
      payload[key] = value;
    });

    try {
      const response = await fetch(form.action || window.location.href, {
        method: 'POST',
        credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
      });
      const result = await response.json();
      if (result.success) {
        Toast.success('Registration successful! Redirecting...');
        setTimeout(() => {
          window.location.href = 'login.php?registered=1';
        }, 1500);
      } else {
        Toast.error(result.error || 'Registration failed');
      }
    } catch (err) {
      console.error(err);
      Toast.error('Network error during registration');
    }
  });
});

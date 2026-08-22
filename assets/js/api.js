// ============================================================
// GlobeTrotter India — api.js (Client Utilities & Toast Notifier)
// ============================================================

const API = {
  base: '',

  async request(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    };
    if (body && (method === 'POST' || method === 'PUT')) {
      opts.body = JSON.stringify(body);
    }
    const res = await fetch(endpoint, opts);
    const json = await res.json();
    if (!res.ok || json.success === false) {
      throw new Error(json.error || 'Request failed');
    }
    return json.data ?? json;
  },

  get:    (ep)       => API.request(ep, 'GET'),
  post:   (ep, body) => API.request(ep, 'POST', body),
  put:    (ep, body) => API.request(ep, 'PUT', body),
  delete: (ep)       => API.request(ep, 'DELETE'),

  async fetchCities(countryId) {
    if (!countryId) return [];
    try {
      const form = new FormData();
      form.append('country_id', countryId);
      const res = await fetch('get_cities.php', {
        method: 'POST',
        body: form
      });
      const html = await res.text();
      return html;
    } catch (e) {
      console.error('Failed to fetch cities', e);
      return '<option value="">Failed to load cities</option>';
    }
  }
};

// ── Toast Notifications ──────────────────────────────────────
const Toast = {
  container: null,

  init() {
    if (!this.container) {
      let existing = document.getElementById('toast-container');
      if (existing) {
        this.container = existing;
      } else {
        this.container = document.createElement('div');
        this.container.id = 'toast-container';
        document.body.appendChild(this.container);
      }
    }
  },

  show(message, type = 'info', duration = 4000) {
    this.init();
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span>${icons[type] || '✨'}</span><span>${message}</span>`;
    this.container.appendChild(t);

    setTimeout(() => {
      t.style.opacity = '0';
      t.style.transform = 'translateX(100%)';
      t.style.transition = 'all 0.3s ease';
      setTimeout(() => t.remove(), 300);
    }, duration);
  }
};

// ── DOM Ready Initializations ────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  // Mobile Hamburger Toggle
  const hamburger = document.getElementById('hamburger');
  const navMenu   = document.getElementById('navMenu');
  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => {
      navMenu.classList.toggle('show');
    });
  }

  // Country & City Dynamic Dropdown (Used in Register & Trip forms)
  const countrySelect = document.getElementById('countrySelect') || document.getElementById('country');
  const citySelect    = document.getElementById('citySelect') || document.getElementById('city');
  const phonePrefix   = document.getElementById('phoneCode');

  if (countrySelect && citySelect) {
    countrySelect.addEventListener('change', async function() {
      const countryId = this.value;
      const selectedOption = this.options[this.selectedIndex];
      
      // Update phone code prefix if input exists
      if (phonePrefix && selectedOption) {
        const code = selectedOption.getAttribute('data-code');
        if (code) phonePrefix.value = code;
      }

      if (!countryId) {
        citySelect.innerHTML = '<option value="" disabled selected>-- Select City --</option>';
        return;
      }

      citySelect.innerHTML = '<option value="">Loading destinations...</option>';
      const optionsHtml = await API.fetchCities(countryId);
      citySelect.innerHTML = '<option value="" disabled selected>-- Select Destination City --</option>' + optionsHtml;
    });

    // Auto-trigger if a country is already selected on page load (e.g. India)
    if (countrySelect.value) {
      countrySelect.dispatchEvent(new Event('change'));
    }
  }

  // Check URL query parameters for notifications
  const params = new URLSearchParams(window.location.search);
  if (params.get('auth') === 'required') {
    Toast.show('Please sign in to access your travel dashboard.', 'warning');
  }
  if (params.get('registered') === '1') {
    Toast.show('Account created successfully! Welcome to GlobeTrotter.', 'success');
  }
  if (params.get('loggedout') === '1') {
    Toast.show('You have been logged out safely.', 'info');
  }
  if (params.get('created') === '1') {
    Toast.show('New trip planned successfully! ✈️', 'success');
  }
  if (params.get('updated') === '1') {
    Toast.show('Changes saved successfully! ✨', 'success');
  }
});

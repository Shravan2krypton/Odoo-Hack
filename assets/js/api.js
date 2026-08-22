// ============================================================
// GlobeTrotter — api.js  (shared fetch helpers)
// ============================================================

const API = {
  base: 'api/',

  async request(endpoint, method = 'GET', body = null) {
    const opts = {
      method,
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
    };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(this.base + endpoint, opts);
    const json = await res.json();
    if (!res.ok || !json.success) {
      throw new Error(json.error || 'Request failed');
    }
    return json.data;
  },

  get:    (ep)         => API.request(ep, 'GET'),
  post:   (ep, body)   => API.request(ep, 'POST', body),
  put:    (ep, body)   => API.request(ep, 'PUT', body),
  delete: (ep)         => API.request(ep, 'DELETE'),

// Helper to fetch cities for a given country_id
async function fetchCities(countryId) {
  if (!countryId) return [];
  try {
    const data = await API.get(`get_cities.php?country_id=${countryId}`);
    return data.cities || [];
  } catch (e) {
    console.error('Failed to fetch cities', e);
    return [];
  }
}
};

// ── Toast Notifications ──────────────────────────────────────
const Toast = {
  container: null,

  init() {
    if (!this.container) {
      this.container = document.createElement('div');
      this.container.id = 'toast-container';
      document.body.appendChild(this.container);
    }
  },

  show(message, type = 'info', duration = 3500) {
    this.init();
    const icons = { success: '✅', error: '❌', info: 'ℹ️', warning: '⚠️' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<span>${icons[type] || '•'}</span><span>${message}</span>`;
    this.container.appendChild(t);
    setTimeout(() => {
      t.style.transition = 'opacity 0.4s, transform 0.4s';
      t.style.opacity = '0';
      t.style.transform = 'translateX(40px)';
      setTimeout(() => t.remove(), 400);
    }, duration);
  },

  success: (msg) => Toast.show(msg, 'success'),
  error:   (msg) => Toast.show(msg, 'error'),
  info:    (msg) => Toast.show(msg, 'info'),
};

// ── Staggered entrance animations ───────────────────────────
function animateEntrance(selector = '.fade-up') {
  document.querySelectorAll(selector).forEach((el, i) => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(24px)';
    setTimeout(() => {
      el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
      el.style.opacity = '1';
      el.style.transform = 'translateY(0)';
    }, i * 80 + 100);
  });
}

// ── Hamburger nav toggle ─────────────────────────────────────
function initNav() {
  const hamburger = document.getElementById('hamburger');
  const navMenu   = document.getElementById('navMenu');
  if (hamburger && navMenu) {
    hamburger.addEventListener('click', () => navMenu.classList.toggle('open'));
  }
}

// ── Confirm dialog ───────────────────────────────────────────
function confirmAction(message, onConfirm) {
  if (window.confirm(message)) onConfirm();
}

// ── Format date ──────────────────────────────────────────────
function formatDate(dateStr) {
  if (!dateStr) return '';
  return new Date(dateStr).toLocaleDateString('en-US', {
    year: 'numeric', month: 'short', day: 'numeric'
  });
}

// ── Format currency ──────────────────────────────────────────
function formatMoney(amount, currency = 'USD') {
  return new Intl.NumberFormat('en-US', {
    style: 'currency', currency, minimumFractionDigits: 0
  }).format(amount || 0);
}

// ── Particles generator ──────────────────────────────────────
function createParticles(containerId, count = 15) {
  const wrap = document.getElementById(containerId);
  if (!wrap) return;
  const colors = ['#6C63FF', '#43D9AD', '#FF6B6B', '#FFD93D'];
  for (let i = 0; i < count; i++) {
    const p = document.createElement('div');
    p.className = 'particle';
    const size = Math.random() * 8 + 3;
    p.style.cssText = `
      width: ${size}px; height: ${size}px;
      left: ${Math.random() * 100}%;
      animation-duration: ${Math.random() * 12 + 8}s;
      animation-delay: ${Math.random() * 8}s;
      background: ${colors[Math.floor(Math.random() * colors.length)]};
    `;
    wrap.appendChild(p);
  }
}

// ── Auto-init on DOM ready ───────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  initNav();
  animateEntrance('.fade-up');
  createParticles('particles');
});

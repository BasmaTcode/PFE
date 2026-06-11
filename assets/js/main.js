// ================================================================
// main.js — Frontend JavaScript
// Rise & Shine Beauty AI Platform
// ================================================================

// ─── Toast Notifications ──────────────────────────────────────
const Toast = {
  container: null,

  init() {
    this.container = document.getElementById('toastContainer');
  },

  show(message, type = 'info', duration = 4000) {
    if (!this.container) this.init();
    const icons = { success: '✓', error: '✕', info: 'ℹ' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
    this.container.appendChild(toast);
    setTimeout(() => {
      toast.style.animation = 'none';
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(20px)';
      toast.style.transition = 'all 0.3s ease';
      setTimeout(() => toast.remove(), 300);
    }, duration);
  },

  success(msg) { this.show(msg, 'success'); },
  error(msg)   { this.show(msg, 'error'); },
  info(msg)    { this.show(msg, 'info'); }
};

// ─── Modal ────────────────────────────────────────────────────
const Modal = {
  open(id) {
    const el = document.getElementById(id);
    if (el) {
      el.classList.remove('hidden');
      document.body.style.overflow = 'hidden';
    }
  },
  close(id) {
    const el = document.getElementById(id);
    if (el) {
      el.classList.add('hidden');
      document.body.style.overflow = '';
    }
  }
};

// ─── Tabs ─────────────────────────────────────────────────────
function initTabs() {
  document.querySelectorAll('.tabs-list').forEach(list => {
    const triggers = list.querySelectorAll('.tab-trigger');
    triggers.forEach(trigger => {
      trigger.addEventListener('click', () => {
        const target = trigger.dataset.tab;
        // Deactivate all in this tab group
        const tabGroup = trigger.closest('.tabs');
        tabGroup.querySelectorAll('.tab-trigger').forEach(t => {
          t.classList.remove('active');
          t.setAttribute('aria-selected', 'false');
        });
        tabGroup.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        // Activate selected
        trigger.classList.add('active');
        trigger.setAttribute('aria-selected', 'true');
        const content = tabGroup.querySelector(`[data-tab-content="${target}"]`);
        if (content) content.classList.add('active');
      });
    });
  });
}

// ─── Mobile Nav ───────────────────────────────────────────────
function initMobileNav() {
  const btn = document.getElementById('navHamburger');
  const menu = document.getElementById('mobileMenu');
  if (!btn || !menu) return;

  btn.addEventListener('click', () => {
    const isOpen = menu.style.display !== 'none';
    menu.style.display = isOpen ? 'none' : 'block';
    btn.setAttribute('aria-expanded', !isOpen);
    document.body.style.overflow = isOpen ? '' : 'hidden';
  });

  // Close on outside click
  menu.addEventListener('click', (e) => {
    if (e.target === menu) {
      menu.style.display = 'none';
      document.body.style.overflow = '';
    }
  });
}

// ─── Favorites Toggle (AJAX) ──────────────────────────────────
function toggleFavorite(btn, productId, lookId) {
  if (!btn) return;
  const isActive = btn.classList.contains('active');
  const targetType = productId ? 'PRODUCT' : 'LOOK';
  const targetId = productId || lookId;

  fetch((window.BASE_URL || '') + '/api/favorites.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      action: isActive ? 'remove' : 'add',
      targetType,
      targetId
    })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      btn.classList.toggle('active');
      Toast.success(isActive ? 'Retiré des favoris' : 'Ajouté aux favoris ✨');
    } else {
      if (data.error === 'unauthenticated') {
        window.location.href = '/login.php';
      } else {
        Toast.error(data.error || 'Erreur');
      }
    }
  })
  .catch(() => Toast.error('Erreur de connexion'));
}

// ─── Auth Modal ───────────────────────────────────────────────
function openAuthModal(mode = 'LOGIN') {
  Modal.open('authModal');
  if (mode === 'REGISTER') {
    switchAuthTab('REGISTER');
  } else {
    switchAuthTab('LOGIN');
  }
}

function switchAuthTab(mode) {
  document.querySelectorAll('#authModal .tab-trigger').forEach(t => {
    t.classList.toggle('active', t.dataset.tab === mode);
  });
  document.querySelectorAll('#authModal .tab-content').forEach(c => {
    c.classList.toggle('active', c.dataset.tabContent === mode);
  });
}

function submitAuth(event, mode) {
  event.preventDefault();
  const form = event.target;
  const btn = form.querySelector('[type="submit"]');
  btn.disabled = true;
  btn.textContent = 'Chargement...';

  const data = Object.fromEntries(new FormData(form));

  fetch((window.BASE_URL || '') + '/api/auth.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: mode === 'LOGIN' ? 'login' : 'register', ...data })
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      Toast.success(mode === 'LOGIN' ? 'Connexion réussie !' : 'Compte créé avec succès !');
      setTimeout(() => window.location.reload(), 800);
    } else {
      Toast.error(res.error || 'Erreur');
      btn.disabled = false;
      btn.textContent = mode === 'LOGIN' ? 'Se connecter' : 'Créer mon compte';
    }
  })
  .catch(() => {
    Toast.error('Erreur de connexion');
    btn.disabled = false;
    btn.textContent = mode === 'LOGIN' ? 'Se connecter' : 'Créer mon compte';
  });
}

// ─── Password Toggle ──────────────────────────────────────────
function togglePassword(inputId, btn) {
  const input = document.getElementById(inputId);
  if (!input) return;
  const isHidden = input.type === 'password';
  input.type = isHidden ? 'text' : 'password';
  btn.textContent = isHidden ? '🙈' : '👁️';
}

// ─── Overlay Click-to-Close ───────────────────────────────────
function initOverlayClose() {
  document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        overlay.classList.add('hidden');
        document.body.style.overflow = '';
      }
    });
  });
}

// ─── Animate on Scroll ────────────────────────────────────────
function initScrollAnimations() {
  if (!('IntersectionObserver' in window)) return;
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.style.opacity = '1';
        e.target.style.transform = 'translateY(0)';
        obs.unobserve(e.target);
      }
    });
  }, { threshold: 0.1 });

  document.querySelectorAll('.animate-in').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(20px)';
    el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    obs.observe(el);
  });
}

// ─── AJAX API Helper ──────────────────────────────────────────
async function apiCall(url, method = 'GET', body = null) {
  const opts = {
    method,
    headers: { 'Content-Type': 'application/json' }
  };
  if (body) opts.body = JSON.stringify(body);
  const r = await fetch(url, opts);
  return r.json();
}

// ─── Init ─────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  Toast.init();
  initTabs();
  initMobileNav();
  initOverlayClose();
  initScrollAnimations();
});

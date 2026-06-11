// ================================================================
// admin.js — Admin Panel JavaScript
// ================================================================

// ─── Toast ───────────────────────────────────────────────────
function showToast(message, type = 'info') {
  const container = document.getElementById('toastContainer');
  if (!container) return;
  const icons = { success: '✓', error: '✕', info: 'ℹ', warning: '⚠' };
  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  toast.innerHTML = `<span>${icons[type] || icons.info}</span><span>${message}</span>`;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 4000);
}

// ─── Confirm Dialog ───────────────────────────────────────────
let _confirmCallback = null;

function openConfirm(title, text, onConfirm) {
  document.getElementById('confirmTitle').textContent = title || 'Confirmer';
  document.getElementById('confirmText').textContent = text || 'Cette action est irréversible.';
  _confirmCallback = onConfirm;
  document.getElementById('confirmModal').classList.remove('hidden');
}

function closeConfirm() {
  document.getElementById('confirmModal').classList.add('hidden');
  _confirmCallback = null;
}

document.addEventListener('DOMContentLoaded', () => {
  const confirmBtn = document.getElementById('confirmBtn');
  if (confirmBtn) {
    confirmBtn.addEventListener('click', () => {
      if (_confirmCallback) _confirmCallback();
      closeConfirm();
    });
  }

  // Close confirm on overlay click
  const overlay = document.getElementById('confirmModal');
  if (overlay) {
    overlay.addEventListener('click', e => {
      if (e.target === overlay) closeConfirm();
    });
  }
});

// ─── Modal ───────────────────────────────────────────────────
function openModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
  }
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el) {
    el.classList.add('hidden');
    document.body.style.overflow = '';
  }
}

// ─── Delete Record ────────────────────────────────────────────
function deleteRecord(url, message, onSuccess) {
  openConfirm('Supprimer', message || 'Voulez-vous vraiment supprimer cet élément ?', () => {
    fetch(url, { method: 'DELETE', headers: { 'Content-Type': 'application/json' } })
      .then(r => r.json())
      .then(data => {
        if (data.success) {
          showToast('Supprimé avec succès', 'success');
          if (onSuccess) onSuccess();
          else setTimeout(() => window.location.reload(), 600);
        } else {
          showToast(data.error || 'Erreur lors de la suppression', 'error');
        }
      })
      .catch(() => showToast('Erreur de connexion', 'error'));
  });
}

// ─── Toggle Status ────────────────────────────────────────────
function toggleStatus(url, currentStatus, onSuccess) {
  fetch(url, {
    method: 'PATCH',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ action: 'toggleStatus' })
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      showToast('Statut mis à jour', 'success');
      if (onSuccess) onSuccess(data.newStatus);
      else setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(data.error || 'Erreur', 'error');
    }
  })
  .catch(() => showToast('Erreur de connexion', 'error'));
}

// ─── Admin AJAX Form Submit ───────────────────────────────────
function submitAdminForm(formEl, url, method, onSuccess) {
  const data = Object.fromEntries(new FormData(formEl));
  const btn = formEl.querySelector('[type="submit"]');
  if (btn) { btn.disabled = true; btn.textContent = 'Enregistrement...'; }

  fetch(url, {
    method: method || 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
  })
  .then(r => r.json())
  .then(res => {
    if (res.success) {
      showToast('Enregistré avec succès', 'success');
      if (onSuccess) onSuccess(res);
      else setTimeout(() => window.location.reload(), 600);
    } else {
      showToast(res.error || 'Erreur', 'error');
      if (btn) { btn.disabled = false; btn.textContent = 'Enregistrer'; }
    }
  })
  .catch(() => {
    showToast('Erreur de connexion', 'error');
    if (btn) { btn.disabled = false; btn.textContent = 'Enregistrer'; }
  });
}

// ─── Table Search Filter ──────────────────────────────────────
function initTableSearch(searchId, tableId) {
  const searchEl = document.getElementById(searchId);
  const table = document.getElementById(tableId);
  if (!searchEl || !table) return;

  searchEl.addEventListener('input', () => {
    const q = searchEl.value.toLowerCase();
    table.querySelectorAll('tbody tr').forEach(row => {
      const text = row.textContent.toLowerCase();
      row.style.display = text.includes(q) ? '' : 'none';
    });
  });
}

// ─── Period Selector ──────────────────────────────────────────
function initPeriodSelect(selectId, onChange) {
  const el = document.getElementById(selectId);
  if (!el) return;
  el.addEventListener('change', () => onChange(el.value));
}

// ─── Inline Edit ─────────────────────────────────────────────
function makeEditable(cellEl, url, field) {
  const original = cellEl.textContent.trim();
  const input = document.createElement('input');
  input.type = 'text';
  input.value = original;
  input.className = 'form-input';
  input.style.cssText = 'padding:4px 8px; font-size:0.85rem; height:auto;';
  cellEl.textContent = '';
  cellEl.appendChild(input);
  input.focus();

  const save = () => {
    const newVal = input.value.trim();
    if (newVal === original) {
      cellEl.textContent = original;
      return;
    }
    fetch(url, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ field, value: newVal })
    })
    .then(r => r.json())
    .then(data => {
      cellEl.textContent = data.success ? newVal : original;
      if (data.success) showToast('Mis à jour', 'success');
      else showToast(data.error || 'Erreur', 'error');
    })
    .catch(() => { cellEl.textContent = original; showToast('Erreur', 'error'); });
  };

  input.addEventListener('blur', save);
  input.addEventListener('keydown', e => {
    if (e.key === 'Enter') { e.preventDefault(); input.blur(); }
    if (e.key === 'Escape') { cellEl.textContent = original; }
  });
}

// ─── Chart.js integration ────────────────────────────────────
function renderTrendChart(canvasId, labels, datasets) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'line',
    data: {
      labels,
      datasets: datasets.map((ds, i) => ({
        ...ds,
        borderColor: ['#d19a9a', '#c5a059', '#7ab0e0'][i] || '#d19a9a',
        backgroundColor: ['rgba(209,154,154,0.08)', 'rgba(197,160,89,0.08)', 'rgba(122,176,224,0.08)'][i],
        borderWidth: 2,
        pointRadius: 3,
        tension: 0.4,
        fill: true,
      }))
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          labels: { color: 'rgba(74,59,59,0.85)', font: { family: 'Outfit', size: 12 } }
        }
      },
      scales: {
        x: {
          grid: { color: 'rgba(74,59,59,0.06)' },
          ticks: { color: 'rgba(74,59,59,0.6)', font: { family: 'Outfit', size: 11 } }
        },
        y: {
          grid: { color: 'rgba(74,59,59,0.06)' },
          ticks: { color: 'rgba(74,59,59,0.6)', font: { family: 'Outfit', size: 11 } }
        }
      }
    }
  });
}

function renderDoughnutChart(canvasId, labels, data) {
  const canvas = document.getElementById(canvasId);
  if (!canvas || typeof Chart === 'undefined') return;

  new Chart(canvas, {
    type: 'doughnut',
    data: {
      labels,
      datasets: [{
        data,
        backgroundColor: ['#d19a9a', '#c5a059', '#7ab0e0', '#6fcb9f', '#e0b96a', '#9e7a44'],
        borderWidth: 2,
        borderColor: '#ffffff',
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: { color: 'rgba(74,59,59,0.85)', font: { family: 'Outfit', size: 12 }, padding: 16 }
        }
      },
      cutout: '65%'
    }
  });
}

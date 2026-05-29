/**
 * DOPFood – Utilities
 */

/**
 * Format số tiền VND: 45000 → "45.000 ₫"
 */
export function fmt(amount) {
  const n = parseFloat(amount) || 0;
  return n.toLocaleString('vi-VN') + ' ₫';
}

/**
 * Global toast notification
 * Types: 'success' | 'error' | 'warn' | 'info'
 */
window.showToast = function (msg, type = 'info', duration = 3000) {
  const container = document.getElementById('toast-container');
  if (!container) return;

  const colors = {
    success: 'bg-green-500',
    error:   'bg-red-500',
    warn:    'bg-amber-500',
    info:    'bg-gray-800',
  };

  const toast = document.createElement('div');
  toast.className = `${colors[type] ?? colors.info} text-white px-4 py-3 rounded-xl shadow-lg
                     text-sm font-medium flex items-center gap-2 animate-slide-in max-w-sm`;
  toast.innerHTML = `<span>${msg}</span>`;
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateY(-8px)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, duration);
};

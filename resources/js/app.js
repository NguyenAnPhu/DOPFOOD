// DOPFood Frontend Entry Point
import './utils.js';          // Global helpers (showToast, fmt) – must be first
import './auth.js';           // Auth state manager (sets window.DOPAuth)
import './pages/home.js';
import './pages/menu-detail.js';
import './pages/order.js';
import './pages/payment.js';
import './pages/history.js';
import './router.js';

// Khởi tạo: kiểm tra session hiện tại khi app load
document.addEventListener('DOMContentLoaded', async () => {
  await window.DOPAuth?.init();
});

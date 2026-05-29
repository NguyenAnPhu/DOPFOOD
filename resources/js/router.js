/**
 * DOPFood – Client-side Router (Hash-based SPA)
 *
 * Routes:
 *  #/            → Home (Trang chủ)
 *  #/menus       → Thư viện Menu
 *  #/menus/:id   → Chi tiết Menu
 *  #/order/:link → Trang đặt hàng (Host & Guest View)
 *  #/pay/:link   → Trang thanh toán
 */

const routes = {
  '/':          'page-home',
  '/menus':     'page-home',
  '/order':     'page-order',
  '/pay':       'page-payment',
  '/menu':      'page-menu-detail',
  '/history':   'page-history',
};

function navigate(path) {
  const root  = path.split('/')[1] || '';
  const pageId = routes[`/${root}`] || 'page-home';

  // Ẩn tất cả pages
  document.querySelectorAll('.page').forEach(el => {
    el.classList.add('hidden');
  });

  // Hiện page tương ứng
  const target = document.getElementById(pageId);
  if (target) {
    target.classList.remove('hidden');
    // Phát sự kiện để page tự load data
    target.dispatchEvent(new CustomEvent('page:enter', {
      detail: { path, segments: path.split('/').filter(Boolean) }
    }));
  }

  // Cập nhật nav active state
  document.querySelectorAll('[data-nav-link]').forEach(el => {
    const href = el.getAttribute('data-nav-link');
    el.classList.toggle('nav-active', path.startsWith(href) && href !== '/');
    if (href === '/' && path === '/') el.classList.add('nav-active');
  });
}

// Hash-based routing
function getPath() {
  return window.location.hash.replace('#', '') || '/';
}

window.addEventListener('hashchange', () => navigate(getPath()));
window.addEventListener('DOMContentLoaded', () => navigate(getPath()));

// Global helper: navigate programmatically
window.DOPRouter = { navigate: (path) => { window.location.hash = path; } };

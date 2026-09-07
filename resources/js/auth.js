/**
 * DOPFood – Auth State Manager
 * Quản lý trạng thái đăng nhập, đồng bộ UI.
 */

import { api } from './api.js';

// ─── State ───────────────────────────────────────────────────────────────────

let _user = null;

export const auth = {
  // Getter
  get user()      { return _user; },
  get isLoggedIn(){ return _user !== null; },

  /**
   * Kiểm tra session hiện tại (gọi khi app khởi động).
   */
  async init() {
    try {
      const res = await api.get('/auth/me');
      _user = res.user ?? null;
    } catch {
      _user = null;
    }
    updateNavUI();
  },

  /**
   * Đăng nhập – tự động lấy CSRF trước.
   */
  async login(email, password, remember = false) {
    const res = await api.post('/auth/login', { email, password, remember });
    _user = res.user;
    updateNavUI();
    return res;
  },

  /**
   * Đăng ký tài khoản mới.
   */
  async register(data) {
    const res = await api.post('/auth/register', data);
    _user = res.user;
    updateNavUI();
    return res;
  },

  /**
   * Đăng xuất.
   */
  async logout() {
    try { await api.post('/auth/logout', {}); } catch { /* ignore */ }
    _user = null;
    updateNavUI();
  },

  /**
   * Cập nhật thông tin ngân hàng.
   * @param {FormData|object} data - FormData (hỗ trợ upload QR) hoặc plain object
   */
  async updateBank(data) {
    let res;
    if (data instanceof FormData) {
      // Dùng fetch trực tiếp với FormData (không set Content-Type để browser tự set boundary)
      const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
      const raw = await fetch('/api/user/bank', {
        method: 'PATCH',
        headers: {
          'Accept':            'application/json',
          'X-Requested-With':  'XMLHttpRequest',
          'X-CSRF-TOKEN':      csrf,
        },
        credentials: 'include',
        body: data,
      });
      if (!raw.ok) {
        const json = await raw.json().catch(() => ({ message: `HTTP ${raw.status}` }));
        const err = new Error(json.message || `HTTP ${raw.status}`);
        err.status = raw.status; err.errors = json.errors ?? null;
        throw err;
      }
      res = await raw.json();
    } else {
      res = await api.patch('/user/bank', data);
    }
    if (_user) Object.assign(_user, res.user ?? data);
    return res;
  },

  /**
   * Cập nhật profile.
   */
  async updateProfile(data) {
    const res = await api.patch('/user/profile', data);
    if (_user) Object.assign(_user, data);
    updateNavUI();
    return res;
  },
};

// ─── UI sync ─────────────────────────────────────────────────────────────────

function updateNavUI() {
  const guestZone  = document.getElementById('nav-guest');
  const userZone   = document.getElementById('nav-user');
  const userNameEl = document.getElementById('nav-user-name');
  const historyLink = document.getElementById('nav-history');

  const heroRegBtn = document.getElementById('hero-register-btn');
  const createMenuBtn = document.getElementById('btn-create-menu');
  const createMenuItemBtn = document.getElementById('btn-create-menu-item');

  // Menu library visibility
  const guestPlaceholder = document.getElementById('guest-menu-placeholder');
  const menuLibSection   = document.getElementById('menu-library-section');

  // Dùng classList hidden thay vì style.display:
  // - Nhất quán với Tailwind `.hidden` (đã được tăng ưu tiên trong blade)
  // - Tránh lỗi `.hidden{display:none!important}` đè lên style inline còn sót
  if (auth.isLoggedIn) {
    guestZone?.classList.add('hidden');
    userZone?.classList.remove('hidden');
    historyLink?.classList.remove('hidden');
    heroRegBtn?.classList.add('hidden');
    createMenuBtn?.classList.remove('hidden');
    createMenuItemBtn?.classList.remove('hidden');

    // Hiện thư viện menu, ẩn placeholder guest
    guestPlaceholder?.classList.add('hidden');
    menuLibSection?.classList.remove('hidden');

    if (userNameEl) userNameEl.textContent = _user.name;
    const avatarEl = document.getElementById('nav-user-avatar');
    if (avatarEl && _user.name) avatarEl.textContent = _user.name.charAt(0).toUpperCase();

    // Trigger load saved menus cho trang home
    const homePage = document.getElementById('page-home');
    if (homePage && !homePage.classList.contains('hidden')) {
      homePage.dispatchEvent(new CustomEvent('auth:changed', { detail: { loggedIn: true } }));
    }
  } else {
    guestZone?.classList.remove('hidden');
    userZone?.classList.add('hidden');
    historyLink?.classList.add('hidden');
    heroRegBtn?.classList.remove('hidden');
    createMenuBtn?.classList.add('hidden');
    createMenuItemBtn?.classList.add('hidden');

    // Ẩn thư viện menu, hiện placeholder guest
    guestPlaceholder?.classList.remove('hidden');
    menuLibSection?.classList.add('hidden');
  }
}

// Export for global use
window.DOPAuth = auth;

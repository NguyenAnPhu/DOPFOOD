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
  async login(email, password) {
    const res = await api.post('/auth/login', { email, password });
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

  if (auth.isLoggedIn) {
    if (guestZone) guestZone.style.display = 'none';
    if (userZone) userZone.style.display = 'flex';
    if (historyLink) historyLink.style.display = 'block';
    if (heroRegBtn) heroRegBtn.style.display = 'none';
    if (createMenuBtn) createMenuBtn.style.display = 'block';
    if (createMenuItemBtn) createMenuItemBtn.style.display = 'block';

    // Hiện thư viện menu, ẩn placeholder guest
    if (guestPlaceholder) guestPlaceholder.classList.add('hidden');
    if (menuLibSection) menuLibSection.classList.remove('hidden');

    if (userNameEl) userNameEl.textContent = _user.name;

    // Trigger load saved menus cho trang home
    const homePage = document.getElementById('page-home');
    if (homePage && !homePage.classList.contains('hidden')) {
      homePage.dispatchEvent(new CustomEvent('auth:changed', { detail: { loggedIn: true } }));
    }
  } else {
    if (guestZone) guestZone.style.display = 'flex';
    if (userZone) userZone.style.display = 'none';
    if (historyLink) historyLink.style.display = 'none';
    if (heroRegBtn) heroRegBtn.style.display = 'inline-flex';
    if (createMenuBtn) createMenuBtn.style.display = 'none';
    if (createMenuItemBtn) createMenuItemBtn.style.display = 'none';

    // Ẩn thư viện menu, hiện placeholder guest
    if (guestPlaceholder) guestPlaceholder.classList.remove('hidden');
    if (menuLibSection) menuLibSection.classList.add('hidden');
  }
}

// Export for global use
window.DOPAuth = auth;

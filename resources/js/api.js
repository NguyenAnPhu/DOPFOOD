/**
 * DOPFood – API Client
 * ─────────────────────────────────────────────────────────────────
 * Auth strategy:
 *   - Web SPA (same-domain): Cookie-based session (auth:web guard)
 *     • credentials: 'include' → gửi session cookie tự động
 *     • X-XSRF-TOKEN: đọc từ cookie (khi dùng Sanctum)
 *     • X-CSRF-TOKEN: đọc từ <meta name="csrf-token"> (không cần Sanctum)
 *   - Mobile (future): Bearer token + auth:sanctum
 * ─────────────────────────────────────────────────────────────────
 */

const BASE = '/api';

/** Lấy CSRF token từ meta tag do Laravel inject vào Blade */
function getCsrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

/** Đọc cookie theo tên (dùng khi có Sanctum) */
function getCookie(name) {
  const m = document.cookie.match(new RegExp('(^|;\\s*)' + name + '=([^;]*)'));
  return m ? decodeURIComponent(m[2]) : null;
}

async function request(method, path, body = null) {
  const headers = {
    'Content-Type':      'application/json',
    'Accept':            'application/json',
    'X-Requested-With':  'XMLHttpRequest',
    // Ưu tiên XSRF cookie (Sanctum), fallback sang meta csrf-token (web guard)
    'X-XSRF-TOKEN': getCookie('XSRF-TOKEN') ?? '',
    'X-CSRF-TOKEN':  getCsrfToken(),
  };

  const opts = { method, headers, credentials: 'include' };
  if (body !== null) opts.body = JSON.stringify(body);

  const res = await fetch(`${BASE}${path}`, opts);

  if (res.status === 204) return null;

  const json = await res.json().catch(() => ({ message: 'Lỗi parse JSON' }));

  if (!res.ok) {
    const err   = new Error(json.message || `HTTP ${res.status}`);
    err.status  = res.status;
    err.data    = json;
    err.errors  = json.errors ?? null;
    throw err;
  }

  return json;
}

export const api = {
  get:    (path)       => request('GET',    path),
  post:   (path, body) => request('POST',   path, body),
  put:    (path, body) => request('PUT',    path, body),
  patch:  (path, body) => request('PATCH',  path, body),
  delete: (path)       => request('DELETE', path),
};

// ─── Guest Session (không cần đăng nhập) ────────────────────────────────────

export const guestSession = {
  // Token theo orderId (key: dopfood_token_{orderId})
  getForOrder:  (orderId) => localStorage.getItem(`dopfood_token_${orderId}`),
  setForOrder:  (orderId, t) => {
    localStorage.setItem(`dopfood_token_${orderId}`, t);
    localStorage.setItem('dopfood_session_token', t); // backward compat
  },

  // Fallback legacy (dùng khi chưa có orderId)
  get:   () => localStorage.getItem('dopfood_session_token'),
  set:   (t) => localStorage.setItem('dopfood_session_token', t),
  clear: () => localStorage.removeItem('dopfood_session_token'),

  getProfile: ()    => {
    try { return JSON.parse(localStorage.getItem('dopfood_guest_profile') || 'null'); } catch { return null; }
  },
  setProfile: (p)   => localStorage.setItem('dopfood_guest_profile', JSON.stringify(p)),
};

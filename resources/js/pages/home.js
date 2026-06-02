/**
 * DOPFood – Trang chủ
 * Chỉ load saved menus (theo user đã đăng nhập).
 */
import { api } from '../api.js';
import { fmt } from '../utils.js';

const PAGE = document.getElementById('page-home');
let allMenus = [];

// Load khi trang home được vào
PAGE?.addEventListener('page:enter', () => {
  if (window.DOPAuth?.isLoggedIn) loadSavedMenus();
});

// Load khi auth state thay đổi (login/register) và trang home đang hiện
PAGE?.addEventListener('auth:changed', () => {
  if (window.DOPAuth?.isLoggedIn) loadSavedMenus();
});

async function loadSavedMenus() {
  const grid = document.getElementById('menu-grid');
  const skeleton = document.getElementById('menu-skeleton');
  const empty = document.getElementById('menu-empty');

  skeleton?.classList.remove('hidden');
  if (grid) grid.innerHTML = '';
  empty?.classList.add('hidden');

  try {
    const data = await api.get('/user/saved-menus');
    allMenus = Array.isArray(data) ? data : (data.data ?? []);
    renderMenus(allMenus);
  } catch (e) {
    showToast('Không tải được danh sách menu: ' + e.message, 'error');
    empty?.classList.remove('hidden');
  } finally {
    skeleton?.classList.add('hidden');
  }
}

// ── Source badge helper ────────────────────────────────────────────────────────
function sourceBadge(source) {
  if (source === 'created') {
    return `<span class="inline-flex items-center gap-1 bg-orange-100 text-orange-700 text-[10px] font-semibold px-2 py-0.5 rounded-full">
              👑 Quán của bạn
            </span>`;
  }
  return `<span class="inline-flex items-center gap-1 bg-blue-50 text-blue-600 text-[10px] font-semibold px-2 py-0.5 rounded-full">
            🧾 Đã đặt đơn
          </span>`;
}

function renderMenus(menus) {
  const grid = document.getElementById('menu-grid');
  const empty = document.getElementById('menu-empty');
  if (!grid) return;

  if (!menus.length) {
    grid.innerHTML = '';
    empty?.classList.remove('hidden');
    return;
  }
  empty?.classList.add('hidden');

  grid.innerHTML = menus.map(menu => `
    <div class="menu-card group relative bg-white rounded-2xl border border-gray-100
                shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
      <div class="h-1.5 bg-gradient-to-r from-orange-400 to-amber-400"></div>
      <div class="p-5 h-full flex flex-col">
        <div class="flex items-start gap-3 mb-3">
          <div class="w-11 h-11 rounded-xl bg-orange-50 flex items-center justify-center text-xl flex-shrink-0
                      group-hover:bg-orange-100 transition-colors">🍽️</div>
          <div class="flex-1 min-w-0">
            <div class="flex items-center gap-2 flex-wrap mb-0.5">
              <h3 class="font-semibold text-gray-900 truncate w-full">${esc(menu.name)}</h3>
              ${sourceBadge(menu.source)}
            </div>
          </div>
        </div>
        ${menu.description ? `<p class="text-xs text-gray-500 truncate mb-2">${esc(menu.description)}</p>` : ''}
        <div class="flex items-center gap-3 text-xs text-gray-400 mb-4 flex-wrap">
          ${menu.phone ? `<span>📞 ${esc(menu.phone)}</span>` : ''}
          ${menu.address ? `<span class="truncate max-w-[140px]">📍 ${esc(menu.address)}</span>` : ''}
        </div>
        <div class="flex items-center justify-between gap-2 mt-auto">
          <button onclick="window.DOPRouter?.navigate('/menu/${menu.menu_id}')"
                  class="text-xs text-gray-400 hover:text-orange-500 transition-colors flex items-center gap-1">
            🍜 ${menu.items_count ?? '?'} món
          </button>
          <div class="flex gap-1.5">
            <button onclick="window.DOPRouter?.navigate('/menu/${menu.menu_id}')"
                    class="btn-ghost text-xs px-2.5 py-1.5 border border-gray-200 rounded-lg">Chi tiết</button>
            <button id="btn-order-${menu.menu_id}"
                    class="inline-flex items-center gap-1 bg-orange-500 hover:bg-orange-600 text-white text-xs
                           font-semibold px-3 py-1.5 rounded-lg transition-colors"
                    onclick="createOrder(${menu.menu_id}, this)">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
              </svg>
              Tạo đơn
            </button>
          </div>
        </div>
      </div>
    </div>
  `).join('');
}

// ── Search ────────────────────────────────────────────────────────────────────
document.getElementById('menu-search')?.addEventListener('input', function () {
  const q = this.value.toLowerCase().trim();
  renderMenus(q ? allMenus.filter(m =>
    m.name.toLowerCase().includes(q) || (m.description ?? '').toLowerCase().includes(q)
  ) : allMenus);
});

// ── Create order ──────────────────────────────────────────────────────────────
window.createOrder = async function (menuId, btn) {
  if (btn) { btn.disabled = true; btn.textContent = '…'; }
  try {
    const order = await api.post('/orders', { menu_id: menuId, split_type: 'even' });

    showToast('✅ Tạo đơn thành công!', 'success');
    // Reload saved menus (menu này sẽ được update/thêm snapshot)
    setTimeout(() => {
      loadSavedMenus();
      window.DOPRouter?.navigate(`/order/${order.share_link}`);
    }, 600);
  } catch (e) {
    showToast(e.status === 401
      ? 'Vui lòng đăng nhập để tạo đơn!'
      : 'Lỗi tạo đơn: ' + e.message, 'error');
    if (e.status === 401) window.openAuthModal?.('login');
    if (btn) { btn.disabled = false; btn.innerHTML = `<svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg> Tạo đơn`; }
  }
};

// ── Create Menu ───────────────────────────────────────────────────────────────
window.openCreateMenuModal = function () {
  if (!window.DOPAuth?.isLoggedIn) return window.openAuthModal?.('login');
  document.getElementById('form-menu')?.reset();
  document.getElementById('menu-error')?.classList.add('hidden');
  document.getElementById('modal-menu').style.display = 'flex';
};

window.handleMenuSave = async function (e) {
  e.preventDefault();
  const btn = document.getElementById('btn-menu-save');
  const errEl = document.getElementById('menu-error');
  btn.disabled = true; btn.textContent = 'Đang lưu…';
  errEl.classList.add('hidden');

  try {
    const payload = {
      name: document.getElementById('menu-name').value,
      description: document.getElementById('menu-desc').value,
      phone: document.getElementById('menu-phone').value,
      address: document.getElementById('menu-address').value,
    };
    await api.post('/menus', payload);
    document.getElementById('modal-menu').style.display = 'none';
    window.showToast?.('✅ Tạo quán thành công!', 'success');
    loadSavedMenus(); // reload list (menu mới đã được auto-save vào saved menus)
  } catch (err) {
    errEl.textContent = err.errors ? Object.values(err.errors).flat().join(' · ') : err.message;
    errEl.classList.remove('hidden');
  } finally {
    btn.disabled = false; btn.textContent = '💾 Lưu';
  }
};

function esc(s) {
  return String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
}

// Export loadSavedMenus for external calls (e.g. after joining an order)
window._loadSavedMenus = loadSavedMenus;

/**
 * DOPFood – Chi tiết Menu
 */
import { api } from '../api.js';
import { fmt } from '../utils.js';

const PAGE = document.getElementById('page-menu-detail');
let currentMenu = null;

PAGE?.addEventListener('page:enter', (e) => {
  const menuId = e.detail.segments[1];
  if (menuId) loadMenuDetail(menuId);
});

async function loadMenuDetail(menuId) {
  const listEl = document.getElementById('md-items-list');
  if (listEl) listEl.innerHTML = `<div class="col-span-full text-center py-8 text-gray-400 animate-pulse">Đang tải…</div>`;

  try {
    currentMenu = await api.get(`/menus/${menuId}`);

    setText('md-menu-name',    currentMenu.name);
    setText('md-menu-desc',    currentMenu.description ?? '');
    setText('md-menu-phone',   currentMenu.phone   ? `📞 ${currentMenu.phone}`   : '');
    setText('md-menu-address', currentMenu.address ? `📍 ${currentMenu.address}` : '');

    renderItems(currentMenu.items ?? []);

    document.getElementById('md-btn-create')?.setAttribute('data-menu-id', menuId);
  } catch (e) {
    if (listEl) listEl.innerHTML = `<div class="col-span-full text-center py-8 text-red-400">Lỗi: ${e.message}</div>`;
  }
}

function renderItems(items) {
  const el = document.getElementById('md-items-list');
  if (!el) return;

  if (!items.length) {
    el.innerHTML = `<div class="col-span-full text-center py-12 text-gray-400">
      <span class="text-4xl block mb-2">🍽️</span>Menu chưa có món nào.
    </div>`;
    return;
  }

  el.innerHTML = items.map((item, i) => `
    <div class="flex items-center gap-3 p-3 rounded-xl border border-gray-100 bg-gray-50
                hover:bg-orange-50 hover:border-orange-200 transition-colors animate-fade-in"
         style="animation-delay: ${i * 30}ms">
      <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 text-xl">
        🍜
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-medium text-gray-800 text-sm truncate">${esc(item.name)}</p>
      </div>
      <div class="text-orange-600 font-bold text-sm whitespace-nowrap">${fmt(item.price)}</div>
    </div>
  `).join('');
}

document.getElementById('md-btn-create')?.addEventListener('click', async function () {
  const menuId = this.getAttribute('data-menu-id');
  if (!menuId) return;
  this.disabled = true; this.textContent = 'Đang tạo…';

  try {
    const order = await api.post('/orders', { menu_id: parseInt(menuId), split_type: 'even' });
    
    // Auto-join the host
    const u = window.DOPAuth?.user;
    if (u) {
      try {
        const { guestSession } = await import('../api.js');
        const res = await api.post(`/orders/${order.id}/join`, { guest_name: u.name, guest_phone: u.phone ?? '' });
        guestSession.set(res.session_token);
        guestSession.setProfile({ name: u.name, phone: u.phone ?? '' });
      } catch (e) { console.error('Auto join failed', e); }
    }

    showToast('✅ Tạo đơn thành công!', 'success');
    setTimeout(() => window.DOPRouter?.navigate(`/order/${order.share_link}`), 600);
  } catch (e) {
    showToast(e.status === 401 ? 'Vui lòng đăng nhập để tạo đơn!' : 'Lỗi: ' + e.message, 'error');
    if (e.status === 401) window.openAuthModal?.('login');
    this.disabled = false; this.textContent = '🚀 Tạo đơn từ Menu này';
  }
});

function setText(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }
function esc(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }

// ── Create Menu Item ─────────────────────────────────────────────────────────
window.openCreateMenuItemModal = function() {
  if (!window.DOPAuth?.isLoggedIn) return window.openAuthModal?.('login');
  if (!currentMenu) return;
  document.getElementById('form-menu-item')?.reset();
  document.getElementById('mi-error')?.classList.add('hidden');
  document.getElementById('mi-menu-id').value = currentMenu.id;
  document.getElementById('modal-menu-item').style.display = 'flex';
};

window.handleMenuItemSave = async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-mi-save');
  const errEl = document.getElementById('mi-error');
  btn.disabled = true; btn.textContent = 'Đang lưu…';
  errEl.classList.add('hidden');

  try {
    const menuId = document.getElementById('mi-menu-id').value;
    const payload = {
      name: document.getElementById('mi-name').value,
      price: document.getElementById('mi-price').value,
    };
    await api.post(`/menus/${menuId}/items`, payload);
    document.getElementById('modal-menu-item').style.display = 'none';
    window.showToast?.('✅ Thêm món thành công!', 'success');
    loadMenuDetail(menuId); // reload list
  } catch (err) {
    errEl.textContent = err.errors ? Object.values(err.errors).flat().join(' · ') : err.message;
    errEl.classList.remove('hidden');
  } finally {
    btn.disabled = false; btn.textContent = '💾 Lưu';
  }
};

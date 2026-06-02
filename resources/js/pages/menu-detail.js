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

  const banner = document.getElementById('md-snapshot-banner');
  if (banner) banner.classList.add('hidden');

  try {
    let menuData = null;
    let isSnapshotView = false;

    // Nếu đã đăng nhập, thử lấy bản lưu cục bộ trước
    if (window.DOPAuth?.isLoggedIn) {
      try {
        const saved = await api.get(`/user/saved-menus/${menuId}`);
        if (saved && saved.source === 'ordered') {
          menuData = saved;
          isSnapshotView = true;
        } else {
          menuData = await api.get(`/menus/${menuId}`);
          menuData.canEdit = true; // Chủ quán
        }
      } catch (err) {
        // 404 (chưa có snapshot) -> lấy menu gốc
        menuData = await api.get(`/menus/${menuId}`);
      }
    } else {
      // Chưa đăng nhập -> lấy menu gốc
      menuData = await api.get(`/menus/${menuId}`);
    }

    currentMenu = menuData;

    setText('md-menu-name',    currentMenu.name);
    setText('md-menu-desc',    currentMenu.description ?? '');
    setText('md-menu-phone',   currentMenu.phone   ? `📞 ${currentMenu.phone}`   : '');
    setText('md-menu-address', currentMenu.address ? `📍 ${currentMenu.address}` : '');

    // Hiển thị nút "Thêm món" nếu có quyền sửa
    const btnCreateItem = document.getElementById('btn-create-menu-item');
    if (btnCreateItem) {
      if (currentMenu.canEdit) btnCreateItem.classList.remove('hidden');
      else btnCreateItem.classList.add('hidden');
    }

    renderItems(currentMenu.items ?? [], currentMenu.canEdit);

    document.getElementById('md-btn-create')?.setAttribute('data-menu-id', menuId);

    // Hiển thị banner nếu đang xem bản snapshot
    if (isSnapshotView && banner) {
      banner.classList.remove('hidden');
      document.getElementById('md-btn-sync').onclick = async () => {
        const btn = document.getElementById('md-btn-sync');
        btn.disabled = true; btn.textContent = 'Đang đồng bộ…';
        try {
          await api.post(`/user/saved-menus/${menuId}/sync`);
          showToast('✅ Đã đồng bộ món mới nhất từ quán!', 'success');
          loadMenuDetail(menuId);
        } catch (e) {
          showToast('Lỗi: ' + e.message, 'error');
        } finally {
          btn.disabled = false; btn.textContent = '🔄 Đồng bộ món mới';
        }
      };
    }
  } catch (e) {
    if (listEl) listEl.innerHTML = `<div class="col-span-full text-center py-8 text-red-400">Lỗi: ${e.message}</div>`;
  }
}

function renderItems(items, canEdit = false) {
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
                hover:bg-orange-50 hover:border-orange-200 transition-colors animate-fade-in group"
         style="animation-delay: ${i * 30}ms">
      <div class="w-10 h-10 rounded-lg bg-white border border-gray-200 flex items-center justify-center flex-shrink-0 text-xl">
        🍜
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-medium text-gray-800 text-sm truncate">${esc(item.name)}</p>
      </div>
      <div class="text-orange-600 font-bold text-sm whitespace-nowrap mr-2">${fmt(item.price)}</div>
      ${canEdit ? `
      <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
        <button onclick="editMenuItem(${item.id}, '${esc(item.name)}', ${item.price})" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-blue-600 hover:border-blue-300 flex items-center justify-center transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
        </button>
        <button onclick="deleteMenuItem(${item.id})" class="w-8 h-8 rounded-lg bg-white border border-gray-200 text-gray-500 hover:text-red-600 hover:border-red-300 flex items-center justify-center transition-colors">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
        </button>
      </div>
      ` : ''}
    </div>
  `).join('');
}

document.getElementById('md-btn-create')?.addEventListener('click', async function () {
  const menuId = this.getAttribute('data-menu-id');
  if (!menuId) return;
  this.disabled = true; this.textContent = 'Đang tạo…';

  try {
    const order = await api.post('/orders', { menu_id: parseInt(menuId), split_type: 'even' });
    
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

// ── Create/Edit Menu Item ─────────────────────────────────────────────────────────
window.openCreateMenuItemModal = function() {
  if (!window.DOPAuth?.isLoggedIn) return window.openAuthModal?.('login');
  if (!currentMenu) return;
  document.getElementById('form-menu-item')?.reset();
  document.getElementById('mi-item-id').value = '';
  document.getElementById('mi-error')?.classList.add('hidden');
  document.getElementById('mi-menu-id').value = currentMenu.id;
  document.getElementById('modal-menu-item').style.display = 'flex';
};

window.editMenuItem = function(id, name, price) {
  if (!currentMenu) return;
  document.getElementById('mi-error')?.classList.add('hidden');
  document.getElementById('mi-menu-id').value = currentMenu.id;
  document.getElementById('mi-item-id').value = id;
  document.getElementById('mi-name').value = name;
  document.getElementById('mi-price').value = price;
  document.getElementById('modal-menu-item').style.display = 'flex';
};

window.deleteMenuItem = async function(id) {
  if (!confirm('Bạn có chắc muốn xóa món này?')) return;
  try {
    await api.delete(`/menus/${currentMenu.id}/items/${id}`);
    showToast('✅ Đã xóa món ăn!', 'success');
    loadMenuDetail(currentMenu.id);
  } catch (e) {
    showToast('Lỗi: ' + e.message, 'error');
  }
};

window.handleMenuItemSave = async function(e) {
  e.preventDefault();
  const btn = document.getElementById('btn-mi-save');
  const errEl = document.getElementById('mi-error');
  btn.disabled = true; btn.textContent = 'Đang lưu…';
  errEl.classList.add('hidden');

  try {
    const menuId = document.getElementById('mi-menu-id').value;
    const itemId = document.getElementById('mi-item-id').value;
    const payload = {
      name: document.getElementById('mi-name').value,
      price: document.getElementById('mi-price').value,
    };
    
    if (itemId) {
      await api.put(`/menus/${menuId}/items/${itemId}`, payload);
      window.showToast?.('✅ Cập nhật món thành công!', 'success');
    } else {
      await api.post(`/menus/${menuId}/items`, payload);
      window.showToast?.('✅ Thêm món thành công!', 'success');
    }
    
    document.getElementById('modal-menu-item').style.display = 'none';
    loadMenuDetail(menuId); // reload list
  } catch (err) {
    errEl.textContent = err.errors ? Object.values(err.errors).flat().join(' · ') : err.message;
    errEl.classList.remove('hidden');
  } finally {
    btn.disabled = false; btn.textContent = '💾 Lưu';
  }
};

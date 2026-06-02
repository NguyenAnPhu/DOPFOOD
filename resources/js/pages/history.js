/**
 * DOPFood – Trang Lịch sử Đơn Hàng
 * GET /api/orders – danh sách đơn host đã tạo
 */

import { api } from '../api.js';
import { fmt } from '../utils.js';

const PAGE = document.getElementById('page-history');

PAGE?.addEventListener('page:enter', () => {
  loadHistory();
});

async function loadHistory() {
  const listEl = document.getElementById('history-list');
  if (!listEl) return;

  listEl.innerHTML = `<div class="text-center py-12 text-gray-400">
    <div class="animate-spin text-3xl mb-3">⏳</div><p>Đang tải lịch sử…</p>
  </div>`;

  try {
    const res = await api.get('/orders');
    const orders = res.data ?? res;
    renderHistory(orders);
  } catch (e) {
    listEl.innerHTML = `<div class="text-center py-12 text-red-400">
      <p class="text-4xl mb-2">😕</p>
      <p>${e.status === 401 ? 'Vui lòng đăng nhập để xem lịch sử.' : 'Lỗi: ' + e.message}</p>
      ${e.status === 401 ? '<button onclick="window.openAuthModal(\'login\')" class="btn-primary mt-4 text-sm px-5">Đăng nhập</button>' : ''}
    </div>`;
  }
}

function renderHistory(orders) {
  const listEl = document.getElementById('history-list');
  if (!listEl) return;

  if (!orders.length) {
    listEl.innerHTML = `<div class="text-center py-16 text-gray-400">
      <div class="text-5xl mb-3">📋</div>
      <p class="font-medium">Chưa có đơn hàng nào</p>
      <p class="text-sm mt-1">Tạo đơn đầu tiên từ trang chủ!</p>
      <button onclick="window.DOPRouter?.navigate('/')" class="btn-primary mt-4 text-sm">🏠 Về trang chủ</button>
    </div>`;
    return;
  }

  listEl.innerHTML = orders.map(order => `
    <div class="card p-4 hover:shadow-md transition-shadow cursor-pointer group"
         onclick="navigateToOrder('${order.share_link}', '${order.status}')">
      <div class="flex items-start gap-3">
        <!-- Icon -->
        <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center flex-shrink-0
                    group-hover:bg-orange-100 transition-colors text-xl">🍽️</div>

        <!-- Info -->
        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <h3 class="font-semibold text-gray-900 truncate">${escHtml(order.menu?.name ?? 'Menu không xác định')}</h3>
            ${order.host_id === (window.DOPAuth?.user?.id) ? '<span class="status-badge badge-orange text-xs">👑 Host</span>' : '<span class="status-badge badge-gray text-xs">👤 Khách</span>'}
            <span class="status-badge ${historyStatusClass(order.status)} text-xs">${historyStatusLabel(order.status)}</span>
          </div>
          <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
            <span>👥 ${order.participants_count ?? 0} người tham gia</span>
            <span>📅 ${formatDate(order.created_at)}</span>
            <span>⚖️ ${splitLabel(order.split_type)}</span>
          </div>
        </div>

        <!-- Amount -->
        <div class="text-right flex-shrink-0">
          <div class="font-bold text-gray-900">${order.total_amount > 0 ? fmt(order.total_amount) : '–'}</div>
          <div class="text-xs text-gray-400 mt-0.5">${order.share_link}</div>
        </div>

        <!-- Delete button -->
        <button
          class="text-gray-300 hover:text-red-500 transition-colors self-center ml-1 p-1.5 rounded-lg hover:bg-red-50"
          onclick="event.stopPropagation(); deleteOrder(${order.id}, '${escHtml(order.menu?.name ?? 'đơn hàng')}')"
          title="Xóa đơn hàng">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
          </svg>
        </button>

        <!-- Arrow -->
        <div class="text-gray-300 group-hover:text-orange-400 transition-colors self-center ml-1">
          <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
          </svg>
        </div>
      </div>
    </div>
  `).join('');
}

window.navigateToOrder = function (shareLink, status) {
  if (status === 'completed' || status === 'closed') {
    window.DOPRouter?.navigate(`/pay/${shareLink}`);
  } else {
    window.DOPRouter?.navigate(`/order/${shareLink}`);
  }
};

window.deleteOrder = async function (orderId, menuName) {
  if (!confirm(`Bạn có chắc muốn xóa đơn "${menuName}" khỏi lịch sử của mình?`)) {
    return;
  }

  try {
    await api.delete(`/orders/${orderId}`);
    showToast('🗑️ Đã xóa đơn hàng!', 'success');
    loadHistory(); // Reload danh sách
  } catch (e) {
    showToast('Lỗi: ' + (e.message ?? 'Không thể xóa đơn hàng'), 'error');
  }
};

// ─── Helpers ─────────────────────────────────────────────────────────────────

function historyStatusLabel(s) {
  return { ordering: '🟡 Đang đặt', locked: '🔒 Đã khóa', completed: '✅ Chờ TT', closed: '🎉 Hoàn tất', cancelled: '❌ Đã hủy' }[s] ?? s;
}
function historyStatusClass(s) {
  return { ordering: 'badge-yellow', locked: 'badge-blue', completed: 'badge-green', closed: 'badge-gray', cancelled: 'badge-gray text-red-600' }[s] ?? '';
}
function splitLabel(s) {
  return { none: 'Host bao', even: 'Chia đều', individual: 'Theo món' }[s] ?? s;
}
function formatDate(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  return d.toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}
function escHtml(str) {
  return String(str).replace(/[&<>"']/g, c =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
  );
}

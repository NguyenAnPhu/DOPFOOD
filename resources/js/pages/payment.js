/**
 * DOPFood – Trang Thanh Toán
 * - Hiển thị số tiền mỗi người cần trả.
 * - QR chuyển khoản: dùng ảnh do Host upload (qr_image_url từ user profile).
 * - Nút "Tôi đã chuyển tiền": click đơn giản → cập nhật trạng thái, KHÔNG cần ảnh bằng chứng.
 * - Ẩn nút "Tôi đã chuyển tiền" với Host.
 */

import { api, guestSession } from '../api.js';
import { fmt } from '../utils.js';

const PAGE = document.getElementById('page-payment');

let currentOrder = null;
let pollTimer    = null;

PAGE?.addEventListener('page:enter', (e) => {
  stopPolling();
  const shareLink = e.detail.segments[1]; // /pay/:link
  if (shareLink) loadPayment(shareLink);
});

async function loadPayment(shareLink) {
  try {
    currentOrder = await api.get(`/orders/${shareLink}`);
    renderPayment();
    startPolling(shareLink);
  } catch (e) {
    document.getElementById('pay-error')?.classList.remove('hidden');
  }
}

function renderPayment() {
  const o = currentOrder;
  // Dùng token theo orderId để tìm đúng participant
  const sessionToken = guestSession.getForOrder(o.id) ?? guestSession.get();
  const me = o.participants?.find(p => p.session_token === sessionToken);

  // Xác định có phải Host không
  const isHost = !!(window.DOPAuth?.isLoggedIn && window.DOPAuth?.user &&
                    o.host_id === window.DOPAuth.user.id);

  setText('pay-menu-name',    o.menu?.name ?? '');
  setText('pay-total-amount', fmt(o.total_amount ?? 0));

  if (me && !isHost) {
    // Guest thấy phần thanh toán của mình
    renderMyPayment(me, o);
  } else {
    // Host ẩn phần "của bạn"
    document.getElementById('pay-my-section')?.classList.add('hidden');
  }

  renderAllParticipants(o.participants ?? [], o, isHost);
}

function renderMyPayment(participant, order) {
  const section = document.getElementById('pay-my-section');
  if (!section) return;
  section.classList.remove('hidden');

  setText('pay-my-share',  fmt(participant.total_share));
  setText('pay-my-status', payStatusLabel(participant.payment_status));
  setAttr('pay-my-status', 'class', `pay-status-badge ${payStatusClass(participant.payment_status)}`);

  if (participant.payment_status === 'pending') {
    const qrSection = document.getElementById('pay-qr-section');
    qrSection?.classList.remove('hidden');

    const amount = Math.round(participant.total_share);
    const img    = document.getElementById('pay-qr-img');

    // Ưu tiên ảnh QR do Host upload, fallback thông tin TK
    if (order.qr_image_url) {
      if (img) { img.src = order.qr_image_url; img.alt = 'QR Chuyển khoản'; }
    } else if (order.bank_account_number) {
      const addInfo = encodeURIComponent(`DOPFOOD ${order.share_link} ${participant.guest_name}`);
      const qrUrl   = `https://img.vietqr.io/image/${order.bank_name}-${order.bank_account_number}-compact2.png` +
                      `?amount=${amount}&addInfo=${addInfo}&accountName=${encodeURIComponent(order.bank_account_name ?? '')}`;
      if (img) { img.src = qrUrl; img.alt = 'VietQR'; }
    } else {
      document.getElementById('pay-qr-section')?.classList.add('hidden');
    }

    setText('pay-bank-name',     order.bank_name ?? '');
    setText('pay-bank-account',  order.bank_account_number ?? '');
    setText('pay-bank-holder',   order.bank_account_name ?? '');
    setText('pay-qr-amount',     fmt(amount));
    setText('pay-transfer-note', `DOPFOOD ${order.share_link} ${participant.guest_name}`);

    // Nút "Tôi đã chuyển tiền" – click đơn giản, không cần upload ảnh
    const btnSubmit = document.getElementById('btn-submit-payment');
    if (btnSubmit) {
      btnSubmit.setAttribute('data-participant-id', participant.id);
      btnSubmit.classList.remove('hidden');
    }
  } else {
    document.getElementById('pay-qr-section')?.classList.add('hidden');
    document.getElementById('btn-submit-payment')?.classList.add('hidden');
  }
}

function renderAllParticipants(participants, order, isHost) {
  const el = document.getElementById('pay-all-participants');
  if (!el) return;

  // Xác định host's participant: dùng user name hoặc host_id nếu có
  const hostUser = window.DOPAuth?.user;
  const hostName = (order.host_id && hostUser && order.host_id === hostUser.id)
    ? hostUser.name : null;

  el.innerHTML = participants.map(p => `
    <div class="flex items-center gap-3 py-3 border-b border-gray-100 last:border-0">
      <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-400 to-orange-600
                  flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
        ${escHtml(p.guest_name.charAt(0).toUpperCase())}
      </div>
      <div class="flex-1 min-w-0">
        <p class="font-medium text-gray-800 text-sm">
          ${escHtml(p.guest_name)}
          ${hostName && p.guest_name === hostName ? '<span class="text-xs text-orange-500 font-normal ml-1">👑 Host</span>' : ''}
        </p>
        <p class="text-xs text-gray-400">${p.guest_phone ?? ''}</p>
      </div>
      <div class="text-right">
        <p class="font-semibold text-orange-600 text-sm">${fmt(p.total_share)}</p>
        <span class="inline-flex items-center text-xs font-medium px-2 py-0.5 rounded-full ${payStatusClass(p.payment_status)}">
          ${payStatusLabel(p.payment_status)}
        </span>
      </div>
      ${(isHost && p.payment_status === 'submitted') ? `
        <button
          class="btn-approve ml-2 text-xs bg-green-500 hover:bg-green-600 text-white px-3 py-1.5
                 rounded-lg transition-colors font-medium"
          onclick="approvePayment(${order.id}, ${p.id})">
          ✓ Xác nhận
        </button>
      ` : ''}
    </div>
  `).join('');

  // Đếm thanh toán: trừ Host ra (Host không cần thanh toán cho chính mình)
  const guests   = participants.filter(p => !(hostName && p.guest_name === hostName));
  const total    = guests.length;
  const approved = guests.filter(p => p.payment_status === 'approved').length;
  const pct      = total > 0 ? Math.round((approved / total) * 100) : 0;

  setText('pay-progress-text', `Đã thu: ${approved}/${total} người (${pct}%)`);
  const bar = document.getElementById('pay-progress-bar');
  if (bar) bar.style.width = `${pct}%`;
}

// ─── Actions ─────────────────────────────────────────────────────────────────

document.getElementById('btn-submit-payment')?.addEventListener('click', async function () {
  const participantId = this.getAttribute('data-participant-id');
  if (!participantId || !currentOrder) return;

  this.disabled = true; this.textContent = 'Đang gửi…';

  try {
    await api.patch(`/orders/${currentOrder.id}/participants/${participantId}/payment`, {
      session_token: guestSession.getForOrder(currentOrder.id) ?? guestSession.get(),
    });
    await reload();
    showToast('✅ Đã xác nhận đã chuyển tiền!', 'success');
  } catch (er) {
    showToast('Lỗi: ' + er.message, 'error');
    this.disabled = false; this.textContent = '💸 Tôi đã chuyển tiền';
  }
});

window.approvePayment = async function (orderId, participantId) {
  try {
    await api.patch(`/orders/${orderId}/participants/${participantId}/approve`, {});
    await reload();
    showToast('✅ Đã xác nhận nhận tiền!', 'success');
  } catch (er) {
    showToast('Lỗi: ' + er.message, 'error');
  }
};

async function reload() {
  if (!currentOrder) return;
  currentOrder = await api.get(`/orders/${currentOrder.share_link}`);
  renderPayment();
}

function startPolling(link) {
  pollTimer = setInterval(async () => {
    try {
      currentOrder = await api.get(`/orders/${link}`);
      renderPayment();
    } catch { /* silent */ }
  }, 5000);
}

function stopPolling() {
  if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function payStatusLabel(s) {
  return { pending: '⏳ Chờ TT', submitted: '📨 Đã gửi', approved: '✅ Đã TT' }[s] ?? s;
}
function payStatusClass(s) {
  return {
    pending:   'bg-gray-100 text-gray-600',
    submitted: 'bg-blue-50 text-blue-700',
    approved:  'bg-green-50 text-green-700',
  }[s] ?? '';
}
function setText(id, val)       { const el = document.getElementById(id); if (el) el.textContent = val; }
function setAttr(id, attr, val) { const el = document.getElementById(id); if (el) el.setAttribute(attr, val); }
function escHtml(str) {
  return String(str).replace(/[&<>"']/g, c =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c])
  );
}

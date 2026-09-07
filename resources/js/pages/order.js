/**
 * DOPFood – Trang Đặt Hàng
 */
import { api, guestSession } from '../api.js';
import { fmt } from '../utils.js';

const PAGE = document.getElementById('page-order');
let currentOrder        = null;
let myParticipant       = null;
let pollTimer           = null;
let hasNavigatedToPay   = false;  // chỉ redirect 1 lần
let isHostFormDirty     = false;

PAGE?.addEventListener('page:enter', (e) => {
  stopPolling();
  hasNavigatedToPay = false;  // reset khi vào lại trang order
  isHostFormDirty = false;
  document.getElementById('order-error')?.classList.add('hidden');
  // Reset trạng thái nút (chữ '…'/'Đang xác nhận…' + disabled còn sót từ đơn trước)
  resetOrderActionButtons();
  const shareLink = e.detail.segments[1];
  if (shareLink) loadOrder(shareLink);
});

// Khôi phục lại nhãn & trạng thái mặc định cho các nút thao tác
// (tránh lỗi nút bị disabled / hiển thị sai chữ sau khi chuyển đơn)
function resetOrderActionButtons() {
  document.querySelectorAll('[data-action-label]').forEach(btn => {
    btn.disabled = false;
    btn.textContent = btn.dataset.actionLabel;
  });
}

// ── Load ─────────────────────────────────────────────────────────────────────
async function loadOrder(link) {
  try {
    currentOrder = await api.get(`/orders/${link}`);
    await renderPage();
    startPolling(link);
  } catch (e) {
    document.getElementById('order-error')?.classList.remove('hidden');
  }
}

// ── Render ────────────────────────────────────────────────────────────────────
async function renderPage() {
  const o = currentOrder;
  // Dùng token theo từng đơn – tránh nhầm lẫn giữa các đơn khác nhau
  const token = guestSession.getForOrder(o.id) ?? guestSession.get();
  myParticipant = o.participants?.find(p => p.session_token === token) ?? null;

  // Redirect if completed – chỉ redirect 1 lần, không lặp vòng
  if (o.status === 'completed' || o.status === 'closed') {
    if (!hasNavigatedToPay) {
      hasNavigatedToPay = true;
      stopPolling();
      window.DOPRouter?.navigate(`/pay/${o.share_link}`);
    }
    return;
  }

  // Header
  setText('order-menu-name', o.menu?.name ?? '');
  const badge = document.getElementById('order-status-badge');
  if (badge) { badge.textContent = statusLabel(o.status); badge.className = `status-badge ${statusClass(o.status)}`; }

  // Share link
  const url = `${location.origin}${location.pathname}#/order/${o.share_link}`;
  setVal('order-share-link', url);

  // Host badge: chỉ hiện khi đã đăng nhập và đúng là host – không bao giờ hiện cho khách
  const isHost = !!(window.DOPAuth?.isLoggedIn && window.DOPAuth?.user && o.host_id && o.host_id == window.DOPAuth.user.id);
  document.getElementById('order-host-badge')?.classList.toggle('hidden', !isHost);

  const locked = o.status === 'locked';

  if (!myParticipant) {
    // ── Bug 1 fix: Host tự động tham gia thay vì hiện form join ──
    if (isHost && window.DOPAuth?.user) {
      try {
        const u = window.DOPAuth.user;
        // Dùng token riêng cho đơn này (không dùng lại token đơn cũ)
        const token = guestSession.getForOrder(o.id) || crypto.randomUUID();
        const res = await api.post(`/orders/${o.id}/join`, {
          guest_name: u.name,
          guest_phone: u.phone ?? '',
          session_token: token,
        });
        // Backend trả {message, participant} – lấy token từ participant
        const savedToken = res.participant?.session_token ?? res.session_token ?? token;
        guestSession.setForOrder(o.id, savedToken);
        guestSession.setProfile({ name: u.name, phone: u.phone ?? '' });
        myParticipant = res.participant ?? res;
        currentOrder = await api.get(`/orders/${o.share_link}`);
        renderPage();
        return;
      } catch (e) {
        // Nếu host đã join rồi (409), tìm participant theo tên trong danh sách
        if (e.status === 409 && e.data?.participant) {
          const existing = e.data.participant;
          guestSession.setForOrder(o.id, existing.session_token);
          guestSession.setProfile({ name: existing.guest_name, phone: existing.guest_phone ?? '' });
          currentOrder = await api.get(`/orders/${o.share_link}`);
          renderPage();
          return;
        }
        console.error('Host auto-join failed', e);
      }
    }

    // ── Bug 2 fix: Khách luôn thấy form nhập thông tin (ẩn danh hoặc đăng nhập) ──
    show('order-join-section');
    hide('order-guest-section');
    hide('order-add-item-section');
    hide('order-host-section');

    // Nếu đã đăng nhập (nhưng không phải host) → vẫn cho chọn: dùng tài khoản hoặc ẩn danh
    if (window.DOPAuth?.isLoggedIn) {
      const u = window.DOPAuth.user;
      show('join-auth-view');
      hide('form-join');
      setText('join-auth-avatar', u.name.charAt(0).toUpperCase());
      setText('join-auth-name', u.name);
      setText('join-auth-phone', u.phone ?? 'Chưa cập nhật SĐT');
    } else {
      // Khách chưa đăng nhập → nhập tên/SĐT ẩn danh
      hide('join-auth-view');
      show('form-join');
      // Pre-fill nếu đã từng nhập
      const profile = guestSession.getProfile();
      if (profile) {
        const nameEl  = document.getElementById('join-name');
        const phoneEl = document.getElementById('join-phone');
        if (nameEl  && !nameEl.value)  nameEl.value  = profile.name  ?? '';
        if (phoneEl && !phoneEl.value) phoneEl.value = profile.phone ?? '';
      }
    }
  } else {
    hide('order-join-section');
    show('order-guest-section');

    // Show menu items picker only when not locked
    if (!locked) show('order-add-item-section'); else hide('order-add-item-section');

    renderMenuItems(o.menu?.items ?? []);
    renderMyCart(myParticipant, locked);

    // Host section: chỉ hiện cho đúng host
    if (isHost) show('order-host-section'); else hide('order-host-section');
    if (isHost) renderHostControls(o);
  }

  // Participants sidebar
  const count = o.participants?.length ?? 0;
  setText('order-participant-count', count);
  renderParticipants(o.participants ?? [], isHost);
}

// ── Menu items ────────────────────────────────────────────────────────────────
function renderMenuItems(items) {
  const el = document.getElementById('order-menu-items');
  if (!el) return;
  el.innerHTML = items.map(item => `
    <button class="menu-item-btn w-full flex items-center gap-2.5 p-2.5 rounded-xl border border-gray-100
                   hover:border-orange-300 hover:bg-orange-50 transition-all text-left group"
            onclick="addToCart(${item.id}, '${escJs(item.name)}', ${item.price})">
      <div class="w-8 h-8 rounded-lg bg-orange-50 flex items-center justify-center text-base flex-shrink-0
                  group-hover:bg-orange-100 transition-colors">🍜</div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800 truncate">${esc(item.name)}</p>
        <p class="text-xs text-orange-500 font-semibold">${fmt(item.price)}</p>
      </div>
      <svg class="add-icon w-4 h-4 text-orange-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
      </svg>
    </button>
  `).join('');
}

// ── My cart ───────────────────────────────────────────────────────────────────
function renderMyCart(p, locked) {
  const el    = document.getElementById('my-cart-items');
  const total = document.getElementById('my-cart-total');
  const btnReady = document.getElementById('btn-ready');

  if (!el) return;
  const items = p?.items ?? [];

  if (!items.length) {
    el.innerHTML = `<p class="text-center text-gray-400 py-4 text-sm">Chưa có món nào. Chọn từ danh sách trên! 👆</p>`;
  } else {
    el.innerHTML = items.map(item => `
      <div class="flex items-center gap-2 py-2 border-b border-gray-50 last:border-0">
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 truncate">${esc(item.menu_item?.name ?? '–')}</p>
          ${item.note ? `<p class="text-xs text-gray-400 italic">📝 ${esc(item.note)}</p>` : ''}
        </div>
        ${!locked ? `
        <div class="flex items-center gap-1 flex-shrink-0">
          <button onclick="updateQty(${item.id},${item.quantity-1})"
                  class="w-6 h-6 rounded-full bg-gray-100 hover:bg-orange-100 text-gray-600 hover:text-orange-600 flex items-center justify-center text-xs font-bold transition-colors">−</button>
          <span class="w-5 text-center text-sm font-semibold">${item.quantity}</span>
          <button onclick="updateQty(${item.id},${item.quantity+1})"
                  class="w-6 h-6 rounded-full bg-gray-100 hover:bg-orange-100 text-gray-600 hover:text-orange-600 flex items-center justify-center text-xs font-bold transition-colors">+</button>
        </div>` : `<span class="text-xs text-gray-500 flex-shrink-0">x${item.quantity}</span>`}
        <span class="text-sm font-semibold text-orange-600 w-20 text-right flex-shrink-0">${fmt(item.price_at_order * item.quantity)}</span>
      </div>
    `).join('');
  }

  const totalVal = items.reduce((s, i) => s + i.price_at_order * i.quantity, 0);
  if (total) total.textContent = fmt(totalVal);

  const breakdownEl = document.getElementById('my-cart-breakdown');
  if (breakdownEl) {
    if (['locked', 'completed'].includes(currentOrder?.status) && p) {
      breakdownEl.classList.remove('hidden');
      renderMyBreakdown(p, totalVal);
    } else {
      breakdownEl.classList.add('hidden');
    }
  }

  if (btnReady) {
    if (p?.status === 'ready' || locked) {
      btnReady.classList.add('hidden');
    } else {
      btnReady.classList.remove('hidden');
    }
  }
}

/**
 * Hiển thị chi tiết phí của tôi (khớp quy tắc backend Order::recalculateShares):
 * Phí ship, VAT, giảm giá được chia cho TẤT CẢ thành viên (kể cả Host):
 *   - 'individual': chia theo tỷ lệ tiền món của từng người
 *   - 'even': chia đều cho mọi người
 *   - 'none': Host bao toàn bộ (thành viên không phải trả phí)
 */
function renderMyBreakdown(p, itemsTotal) {
  const o = currentOrder;
  if (!o) return;

  const tax = Number(o.tax_amount ?? 0);
  const ship = Number(o.shipping_fee ?? 0);
  const discount = Number(o.discount_amount ?? 0);
  const all = o.participants ?? [];
  const n = all.length || 1;

  let shipShare = 0, taxShare = 0, discountShare = 0;

  if (o.split_type === 'even') {
    shipShare = ship / n;
    taxShare = tax / n;
    discountShare = discount / n;
  } else if (o.split_type === 'individual') {
    const orderSubtotal = all.reduce((s, x) => s + (x.items ?? []).reduce((a, i) => a + i.price_at_order * i.quantity, 0), 0);
    const ratio = orderSubtotal > 0 ? itemsTotal / orderSubtotal : 0;
    shipShare = ship * ratio;
    taxShare = tax * ratio;
    discountShare = discount * ratio;
  }
  // 'none' → tất cả bằng 0 (Host bao)

  shipShare = Math.round(shipShare);
  taxShare = Math.round(taxShare);
  discountShare = Math.round(discountShare);

  setText('my-cart-shipping', shipShare    > 0 ? `+ ${fmt(shipShare)}` : '0 ₫');
  setText('my-cart-tax',      taxShare     > 0 ? `+ ${fmt(taxShare)}`  : '0 ₫');
  setText('my-cart-discount', discountShare> 0 ? `- ${fmt(discountShare)}` : '0 ₫');
  setText('my-cart-final',    fmt(p.total_share ?? 0));
}

// ── Participants ──────────────────────────────────────────────────────────────
function renderParticipants(participants, isHost) {
  const el = document.getElementById('order-participants');
  if (!el) return;

  if (!participants.length) {
    el.innerHTML = `<p class="text-sm text-gray-400 text-center py-3">Chưa có ai. Chia sẻ link để mời!</p>`;
    return;
  }

  el.innerHTML = participants.map(p => {
    // Participant của Host (user_id khớp host_id, tên chứa '(Host)', hoặc trùng tên user Host) → không cho xóa
    const hostName = (currentOrder?.host?.name ?? '').trim().toLowerCase();
    const guestName = (p.guest_name ?? '').trim();
    const isPHost = !!(currentOrder?.host_id && p.user_id && currentOrder.host_id == p.user_id)
      || /\s\(host\)/i.test(guestName)
      || (hostName && guestName.toLowerCase() === hostName);

    return `
    <div class="relative flex items-center gap-2.5 py-2.5 pr-8 border-b border-gray-50 last:border-0">
      <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-orange-600
                  flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
        ${esc(p.guest_name.charAt(0).toUpperCase())}
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-sm font-medium text-gray-800">${esc(p.guest_name)}</p>
        <p class="text-xs text-gray-400">${(p.items?.length ?? 0)} món</p>
      </div>
      <span class="status-badge text-xs ${p.status === 'ready' ? 'badge-green' : 'badge-yellow'}">
        ${p.status === 'ready' ? '✅ Xong' : '💬 Đang chọn'}
      </span>
      ${isHost && !isPHost ? `
        <!-- Icon thùng rác ở góc phải trên – chỉ Host thấy -->
        <button onclick="removeParticipant(${p.id}, '${escJs(p.guest_name)}')"
                class="absolute top-1.5 right-1 p-1 rounded-md text-gray-300 hover:text-red-500 hover:bg-red-50 transition-colors"
                title="Xóa thành viên" aria-label="Xóa ${esc(p.guest_name)}">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0"/>
          </svg>
        </button>
      ` : ''}
    </div>
  `}).join('');
}

/**
 * Host xóa một thành viên khỏi đơn hàng.
 */
window.removeParticipant = async function (participantId, guestName) {
  if (!currentOrder) return;
  if (!confirm(`Xóa "${guestName}" khỏi đơn hàng?`)) return;
  try {
    await api.delete(`/orders/${currentOrder.id}/participants/${participantId}`);
    await refresh();
    showToast(`🗑️ Đã xóa ${guestName} khỏi đơn!`, 'success');
  } catch (er) {
    showToast('Lỗi: ' + er.message, 'error');
  }
};

// ── Host controls ─────────────────────────────────────────────────────────────
function renderHostControls(o) {
  if (!isHostFormDirty) {
    setVal('fee-shipping', o.shipping_fee ?? 0);
    setVal('fee-tax',      o.tax_amount   ?? 0);
    setVal('fee-discount', o.discount_amount ?? 0);
    const r = document.querySelector(`input[name="split_type"][value="${o.split_type}"]`);
    if (r) r.checked = true;
  }

  const btnLock     = document.getElementById('btn-lock-order');
  const btnUnlock   = document.getElementById('btn-unlock-order');
  const btnComplete = document.getElementById('btn-complete-order');
  const btnCancel   = document.getElementById('btn-cancel-order');

  btnLock?.classList.toggle('hidden',     o.status !== 'ordering');
  btnUnlock?.classList.toggle('hidden',   o.status !== 'locked');
  btnComplete?.classList.toggle('hidden', o.status !== 'locked');
  btnCancel?.classList.toggle('hidden',   !['ordering', 'locked'].includes(o.status));
}

// ── Actions ───────────────────────────────────────────────────────────────────

// Join
document.getElementById('btn-join-other')?.addEventListener('click', () => {
  hide('join-auth-view');
  show('form-join');
});

document.getElementById('btn-join-auth')?.addEventListener('click', async function () {
  const u = window.DOPAuth?.user;
  if (!u) return;
  const btn = this;
  btn.disabled = true; btn.textContent = 'Đang tham gia…';

  try {
    const token = guestSession.getForOrder(currentOrder.id) || crypto.randomUUID();
    const res = await api.post(`/orders/${currentOrder.id}/join`, {
      guest_name: u.name,
      guest_phone: u.phone ?? '',
      session_token: token,
    });
    // Backend trả {message, participant}
    const participant = res.participant ?? res;
    const savedToken  = participant.session_token ?? token;
    guestSession.setForOrder(currentOrder.id, savedToken);
    guestSession.setProfile({ name: u.name, phone: u.phone ?? '' });
    myParticipant = participant;
    showToast(`👋 Xin chào ${u.name}! Bắt đầu chọn món nhé.`, 'success');
    await refresh();
  } catch (er) {
    showToast('Lỗi tham gia: ' + er.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = '🚀 Tham gia ngay';
  }
});

document.getElementById('form-join')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const name  = document.getElementById('join-name')?.value.trim();
  const phone = document.getElementById('join-phone')?.value.trim();
  if (!name) return;

  const btn = this.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Đang tham gia…';

  try {
    const token = guestSession.getForOrder(currentOrder.id) || crypto.randomUUID();
    const res = await api.post(`/orders/${currentOrder.id}/join`, {
      guest_name: name,
      guest_phone: phone,
      session_token: token,
    });
    // Backend trả {message, participant}
    const participant = res.participant ?? res;
    const savedToken  = participant.session_token ?? token;
    guestSession.setForOrder(currentOrder.id, savedToken);
    guestSession.setProfile({ name, phone });
    myParticipant = participant;
    showToast(`👋 Xin chào ${name}! Bắt đầu chọn món nhé.`, 'success');
    await refresh();
  } catch (er) {
    showToast('Lỗi tham gia: ' + er.message, 'error');
  } finally {
    btn.disabled = false; btn.textContent = '🚀 Tham gia đặt món';
  }
});

// Add to cart
window.addToCart = async function (menuItemId, name, price) {
  if (!myParticipant?.id) { showToast('Vui lòng tham gia đơn trước!', 'warn'); return; }
  if (currentOrder?.status === 'locked') { showToast('Đơn đã bị khóa.', 'warn'); return; }

  try {
    await api.post(`/orders/${currentOrder.id}/items`, {
      participant_id: myParticipant.id,   // Backend cần participant_id
      menu_item_id:   menuItemId,
      quantity:       1,
    });
    await refresh();
    showToast(`✅ Đã thêm: ${name}`, 'success');
  } catch (er) { showToast('Lỗi thêm món: ' + er.message, 'error'); }
};

// Update qty
window.updateQty = async function (itemId, qty) {
  if (!currentOrder) return;
  try {
    if (qty <= 0) {
      await api.delete(`/orders/${currentOrder.id}/items/${itemId}`);
    } else {
      await api.put(`/orders/${currentOrder.id}/items/${itemId}`, {
        quantity: qty,
        session_token: guestSession.getForOrder(currentOrder.id) ?? guestSession.get(),
      });
    }
    await refresh();
  } catch (er) { showToast('Lỗi cập nhật: ' + er.message, 'error'); }
};

// Ready
document.getElementById('btn-ready')?.addEventListener('click', async function () {
  if (!myParticipant?.id) return;
  this.disabled = true; this.textContent = 'Đang xác nhận…';
  try {
    await api.patch(`/orders/${currentOrder.id}/participants/${myParticipant.id}/ready`, {
      session_token: guestSession.getForOrder(currentOrder.id) ?? guestSession.get(),
    });
    await refresh();
    showToast('✅ Đã xác nhận hoàn tất chọn món!', 'success');
  } catch (er) {
    showToast(er.message, 'error');
    this.disabled = false; this.textContent = '✅ Hoàn tất chọn món';
  }
});

// Copy share link
document.getElementById('btn-copy-link')?.addEventListener('click', () => {
  const v = document.getElementById('order-share-link')?.value;
  if (v) navigator.clipboard.writeText(v).then(() => showToast('📋 Đã copy link!', 'success'));
});

// Lock
document.getElementById('btn-lock-order')?.addEventListener('click', async function () {
  if (!confirm('Chốt đơn? Khách sẽ không thể thêm/sửa món nữa.')) return;
  await changeStatus('locked', this);
});

// Unlock
document.getElementById('btn-unlock-order')?.addEventListener('click', async function () {
  await changeStatus('ordering', this);
});

// Complete
document.getElementById('btn-complete-order')?.addEventListener('click', async function () {
  if (!confirm('Hoàn tất đơn và chuyển sang thanh toán?')) return;
  await changeStatus('completed', this);
});

// Cancel
document.getElementById('btn-cancel-order')?.addEventListener('click', async function () {
  if (!confirm('Bạn có chắc chắn muốn hủy đơn hàng này không? Khách sẽ không thể đặt món hoặc thanh toán được nữa.')) return;
  await changeStatus('cancelled', this);
});

// Fees
document.getElementById('form-fees')?.addEventListener('input', () => {
  isHostFormDirty = true;
});

document.getElementById('form-fees')?.addEventListener('submit', async function (e) {
  e.preventDefault();
  const btn = this.querySelector('button[type=submit]');
  btn.disabled = true; btn.textContent = 'Đang lưu…';
  try {
    await api.patch(`/orders/${currentOrder.id}/fees`, {
      shipping_fee:    parseFloat(document.getElementById('fee-shipping')?.value) || 0,
      tax_amount:      parseFloat(document.getElementById('fee-tax')?.value)      || 0,
      discount_amount: parseFloat(document.getElementById('fee-discount')?.value) || 0,
      split_type:      document.querySelector('input[name="split_type"]:checked')?.value ?? 'even',
    });
    isHostFormDirty = false;
    await refresh();
    showToast('✅ Đã lưu cấu hình phí!', 'success');
  } catch (er) { showToast('Lỗi: ' + er.message, 'error'); }
  finally { btn.disabled = false; btn.textContent = '💾 Lưu phí & tính lại'; }
});

async function changeStatus(status, btn) {
  const orig = btn?.textContent;
  if (btn) { btn.disabled = true; btn.textContent = '…'; }
  try {
    await api.patch(`/orders/${currentOrder.id}/status`, { status });
    await refresh();
    if (status === 'completed') window.DOPRouter?.navigate(`/pay/${currentOrder.share_link}`);
  } catch (er) {
    showToast('Lỗi: ' + er.message, 'error');
    if (btn) { btn.disabled = false; btn.textContent = orig; }
  }
}

// ── Polling ───────────────────────────────────────────────────────────────────
async function refresh() {
  if (!currentOrder) return;
  currentOrder = await api.get(`/orders/${currentOrder.share_link}`);
  await renderPage();
}

function startPolling(link) {
  pollTimer = setInterval(async () => {
    try {
      const prev = currentOrder?.status;
      currentOrder = await api.get(`/orders/${link}`);
      // Nếu đơn đã xong và chưa redirect, navigate rồi dừng polling
      if (['completed', 'closed'].includes(currentOrder.status) && !hasNavigatedToPay) {
        hasNavigatedToPay = true;
        stopPolling();
        window.DOPRouter?.navigate(`/pay/${link}`);
        return;
      }
      if (!['completed', 'closed'].includes(currentOrder.status)) {
        await renderPage();
      }
    } catch { /* silent */ }
  }, 5000);
}

function stopPolling() { if (pollTimer) { clearInterval(pollTimer); pollTimer = null; } }

// ── Helpers ───────────────────────────────────────────────────────────────────
function statusLabel(s) {
  return { ordering: '🟡 Đang đặt', locked: '🔒 Đã khóa', completed: '✅ Hoàn tất', closed: '🎉 Đã đóng', cancelled: '❌ Đã hủy' }[s] ?? s;
}
function statusClass(s) {
  return { ordering: 'badge-yellow', locked: 'badge-blue', completed: 'badge-green', closed: 'badge-gray', cancelled: 'badge-gray text-red-600' }[s] ?? '';
}
function show(id) { document.getElementById(id)?.classList.remove('hidden'); }
function hide(id) { document.getElementById(id)?.classList.add('hidden'); }
function setText(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }
function setVal(id, v)  { const el = document.getElementById(id); if (el) el.value = v; }
function esc(s) { return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
function escJs(s) { return String(s).replace(/'/g, "\\'"); }

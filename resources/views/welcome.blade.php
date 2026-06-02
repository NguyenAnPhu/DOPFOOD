<!DOCTYPE html>
<html lang="vi" class="scroll-smooth">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="DOPFood – Ứng dụng đặt món & chia tiền nhóm thông minh cho văn phòng và bạn bè" />
  <title>DOPFood – Đớp cùng Pei</title>

  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet" />

  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="fav/favicon-96x96.png" sizes="96x96" />
  <link rel="icon" type="image/svg+xml" href="fav/favicon.svg" />
  <link rel="shortcut icon" href="fav/favicon.ico" />
  <link rel="apple-touch-icon" sizes="180x180" href="fav/apple-touch-icon.png" />
  <link rel="manifest" href="fav/site.webmanifest" />
  <!-- end Favicon -->

  <meta name="theme-color" content="#ffffff">

  @vite(['resources/css/app.css', 'resources/js/app.js'])

  <style>
    *, *::before, *::after { box-sizing: border-box; }
    body { font-family: 'Inter', sans-serif; background: #f9fafb; color: #111827; min-height: 100vh; }
    .page { min-height: calc(100vh - 64px); }

    /* ── Nav ── */
    .nav-active { color: #f97316 !important; }

    /* ── Status Badges ── */
    .status-badge {
      display: inline-flex; align-items: center; padding: 2px 10px;
      border-radius: 99px; font-size: 11px; font-weight: 600; white-space: nowrap;
    }
    .badge-yellow { background: #fef9c3; color: #92400e; }
    .badge-blue   { background: #dbeafe; color: #1e40af; }
    .badge-green  { background: #dcfce7; color: #166534; }
    .badge-gray   { background: #f3f4f6; color: #6b7280; }
    .badge-orange { background: #fff7ed; color: #c2410c; }

    /* ── Buttons ── */
    .btn-primary {
      display: inline-flex; align-items: center; justify-content: center; gap: 6px;
      background: #f97316; color: #fff; padding: 10px 20px; border-radius: 12px;
      font-size: 14px; font-weight: 600; border: none; cursor: pointer;
      transition: all 0.15s; box-shadow: 0 2px 8px rgba(249,115,22,.2);
    }
    .btn-primary:hover { background: #ea6c0a; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(249,115,22,.3); }
    .btn-primary:active { transform: translateY(0); }
    .btn-primary:disabled { background: #d1d5db; color: #9ca3af; box-shadow: none; transform: none; cursor: not-allowed; }

    .btn-secondary {
      display: inline-flex; align-items: center; justify-content: center; gap: 6px;
      background: #fff; color: #374151; padding: 10px 20px; border-radius: 12px;
      font-size: 14px; font-weight: 500; border: 1.5px solid #e5e7eb; cursor: pointer;
      transition: all 0.15s;
    }
    .btn-secondary:hover { border-color: #f97316; color: #f97316; }
    .btn-secondary:disabled { opacity: .5; cursor: not-allowed; }

    .btn-ghost {
      display: inline-flex; align-items: center; gap: 5px;
      background: transparent; color: #6b7280; padding: 8px 12px; border-radius: 8px;
      font-size: 13px; font-weight: 500; border: none; cursor: pointer; transition: all 0.15s;
    }
    .btn-ghost:hover { background: #f3f4f6; color: #111827; }

    /* ── Card ── */
    .card { background: #fff; border-radius: 16px; border: 1px solid #f0f0f0; box-shadow: 0 1px 3px rgba(0,0,0,.04); }

    /* ── Form ── */
    .form-input {
      width: 100%; padding: 10px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
      font-size: 14px; font-family: inherit; color: #111827; background: #fff;
      transition: border-color .15s, box-shadow .15s; outline: none;
    }
    .form-input:focus { border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,.1); }
    .form-input.error { border-color: #ef4444; }
    .form-label { display: block; font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 5px; }
    .form-error { font-size: 12px; color: #ef4444; margin-top: 3px; }

    /* ── Section title ── */
    .section-title {
      font-size: 14px; font-weight: 700; color: #111827;
      display: flex; align-items: center; gap: 8px; margin-bottom: 12px;
    }
    .section-title::after { content: ''; flex: 1; height: 1px; background: #f3f4f6; }

    /* ── Modal ── */
    .modal-overlay {
      position: fixed; inset: 0; z-index: 60;
      background: rgba(0,0,0,.45); backdrop-filter: blur(4px);
      display: flex; align-items: center; justify-content: center; padding: 16px;
    }
    .modal-box {
      background: #fff; border-radius: 20px; box-shadow: 0 25px 50px rgba(0,0,0,.15);
      width: 100%; max-width: 420px; overflow: hidden;
      animation: modal-in .2s ease;
    }
    @keyframes modal-in { from { opacity:0; transform: scale(.95) translateY(8px); } to { opacity:1; transform: scale(1) translateY(0); } }

    /* ── Tab ── */
    .tab-btn { padding: 8px 20px; font-size: 14px; font-weight: 600; border: none; background: transparent; cursor: pointer; border-bottom: 2px solid transparent; color: #6b7280; transition: all .15s; }
    .tab-btn.active { color: #f97316; border-bottom-color: #f97316; }

    /* ── Animations ── */
    @keyframes slide-up { from { opacity:0; transform: translateY(16px); } to { opacity:1; transform: translateY(0); } }
    @keyframes fade-in  { from { opacity:0; } to { opacity:1; } }
    @keyframes spin { to { transform: rotate(360deg); } }
    .animate-slide-up  { animation: slide-up .3s ease; }
    .animate-fade-in   { animation: fade-in .25s ease; }
    .animate-spin      { animation: spin 1s linear infinite; display: inline-block; }

    /* ── Split radio ── */
    .split-opt input:checked + div { border-color: #f97316; background: #fff7ed; color: #c2410c; }

    /* ── Progress ── */
    .progress-bar { height: 6px; background: #f3f4f6; border-radius: 99px; overflow: hidden; }
    .progress-fill { height: 100%; background: linear-gradient(90deg, #34d399, #10b981); border-radius: 99px; transition: width .5s ease; }

    /* ── Scrollbar ── */
    ::-webkit-scrollbar { width: 4px; height: 4px; }
    ::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 99px; }

    /* ── Menu item hover ── */
    .menu-item-btn:hover .add-icon { opacity: 1; }
    .add-icon { opacity: 0; transition: opacity .15s; }
  </style>
</head>

<body>

  <!-- ── Toast ── -->
  <div id="toast-container" class="fixed bottom-6 right-4 z-50 flex flex-col gap-2 pointer-events-none" style="max-width:360px"></div>

  <!-- ═══════════════════════════════════════════════════════
       TOP NAV
  ═══════════════════════════════════════════════════════ -->
  <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-100 shadow-sm">
    <div class="max-w-5xl mx-auto px-4 h-fit flex items-center gap-3">

      <!-- Logo -->
      <a href="#/" class="flex items-center gap-2 flex-shrink-0 mr-2 py-2">
        <img src="/logo.png" alt="DOPFood Logo" class="w-16 h-16 object-contain" />
      </a>

      <!-- Nav links -->
      <nav class="hidden sm:flex items-center gap-0.5 flex-1">
        <a href="#/" data-nav-link="/" class="btn-ghost nav-link">🏠 Trang chủ</a>
        <a href="#/history" id="nav-history" data-nav-link="/history" class="btn-ghost nav-link hidden">📋 Lịch sử</a>
      </nav>

      <!-- Auth zone -->
      <div class="flex items-center gap-2 ml-auto">

        <!-- Guest zone (chưa đăng nhập) -->
        <div id="nav-guest" class="flex items-center gap-2">
          <button onclick="openAuthModal('login')" class="btn-ghost text-sm">Đăng nhập</button>
          <button onclick="openAuthModal('register')"
                  class="btn-primary text-sm px-4 py-2">Đăng ký</button>
        </div>

        <!-- User zone (đã đăng nhập) -->
        <div id="nav-user" class="hidden flex items-center gap-2">
          <button onclick="openBankModal()"
                  class="btn-ghost text-sm" title="Cài ngân hàng">🏦</button>
          <div class="relative" id="user-menu-wrapper">
            <button onclick="toggleUserMenu()"
                    class="flex items-center gap-2 btn-ghost pr-3">
              <div class="w-7 h-7 rounded-full bg-gradient-to-br from-orange-400 to-orange-600
                          flex items-center justify-center text-white text-xs font-bold" id="nav-user-avatar">M</div>
              <span id="nav-user-name" class="text-sm font-medium text-gray-800 truncate">–</span>
              <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>
            <!-- Dropdown -->
            <div id="user-dropdown" class="hidden absolute right-0 top-full mt-1 w-44 bg-white rounded-xl shadow-lg border border-gray-100 py-1 z-50">
              <button onclick="openBankModal(); toggleUserMenu()" class="w-full text-left px-4 py-2.5 text-sm text-gray-700 hover:bg-gray-50">🏦 Cài ngân hàng</button>
              <div class="border-t border-gray-100 my-1"></div>
              <button onclick="handleLogout()" class="w-full text-left px-4 py-2.5 text-sm text-red-500 hover:bg-red-50">👋 Đăng xuất</button>
            </div>
          </div>
        </div>

      </div>
    </div>
  </header>

  <!-- ═══════════════════════════════════════════════════════
       MAIN
  ═══════════════════════════════════════════════════════ -->
  <main class="max-w-5xl mx-auto px-4 py-6">

    <!-- ───────────────────────────────────────
         PAGE: HOME
    ─────────────────────────────────────── -->
    <div id="page-home" class="page hidden animate-fade-in">

      <!-- Hero -->
      <div class="relative overflow-hidden rounded-3xl mb-8 p-7 sm:p-10 text-white shadow-xl shadow-orange-200
                  bg-gradient-to-br from-orange-500 via-orange-500 to-amber-400">
        <div class="absolute -top-10 -right-10 w-48 h-48 bg-white/10 rounded-full pointer-events-none"></div>
        <div class="absolute -bottom-14 -left-4 w-36 h-36 bg-white/5 rounded-full pointer-events-none"></div>
        <div class="relative">
          <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur rounded-full px-3 py-1 text-xs font-semibold mb-4">
            🚀 Gọi chung · Chia nhanh · Trả dễ
          </div>
          <h1 class="text-3xl sm:text-4xl font-extrabold leading-tight mb-2">
            Đặt món nhóm<br/>thông minh
          </h1>
          <p class="text-orange-100 text-sm mb-6 max-w-md leading-relaxed">
            Tạo đơn, mời bạn bè chọn món realtime, tự động chia tiền &amp; thanh toán qua QR ngân hàng ngay lập tức.
          </p>
          <div class="flex flex-wrap gap-3">
            <button onclick="scrollToMenus()"
                    class="inline-flex items-center gap-2 bg-white text-orange-600 font-bold px-5 py-2.5 rounded-xl text-sm hover:bg-orange-50 transition-colors shadow-sm">
              🍜 Chọn Menu &amp; Tạo đơn
            </button>
            <button onclick="openAuthModal('register')"
                    id="hero-register-btn"
                    class="inline-flex items-center gap-2 bg-white/20 backdrop-blur text-white font-medium px-5 py-2.5 rounded-xl text-sm border border-white/30 hover:bg-white/30 transition-colors">
              ✨ Đăng ký miễn phí
            </button>
          </div>
        </div>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-3 gap-3 mb-8">
        <div class="card p-4 text-center">
          <div class="text-2xl font-extrabold text-orange-500" id="stat-menus">–</div>
          <div class="text-xs text-gray-500 mt-0.5">Menu có sẵn</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-extrabold text-orange-500">🔄</div>
          <div class="text-xs text-gray-500 mt-0.5">Realtime</div>
        </div>
        <div class="card p-4 text-center">
          <div class="text-2xl font-extrabold text-orange-500">📲</div>
          <div class="text-xs text-gray-500 mt-0.5">VietQR</div>
        </div>
      </div>

      <!-- Guest placeholder (chưa đăng nhập) -->
      <div id="guest-menu-placeholder" class="hidden">
        <div class="rounded-2xl border-2 border-dashed border-orange-200 bg-orange-50/60 p-10 text-center">
          <div class="text-5xl mb-4">🍽️</div>
          <h2 class="text-xl font-bold text-gray-800 mb-2">Thư viện Menu của bạn</h2>
          <p class="text-sm text-gray-500 mb-6 max-w-sm mx-auto leading-relaxed">
            Đăng nhập để xem danh sách quán bạn đã tạo và từng đặt đơn.
            Thông tin menu được lưu riêng cho từng tài khoản.
          </p>
          <div class="flex items-center justify-center gap-3">
            <button onclick="openAuthModal('login')" class="btn-primary px-6 py-2.5 text-sm">Đăng nhập</button>
            <button onclick="openAuthModal('register')" class="btn-secondary px-6 py-2.5 text-sm">Đăng ký miễn phí</button>
          </div>
        </div>
      </div>

      <!-- Menu Library (chỉ hiện khi đã đăng nhập) -->
      <div id="menu-library-section" class="hidden">
        <div class="flex items-center justify-between mb-4 gap-3">
          <div class="flex items-center gap-3 shrink-0">
            <h2 class="text-lg font-bold text-gray-900">🍽️ Thư viện Menu</h2>
            <button id="btn-create-menu" onclick="openCreateMenuModal()" class="hidden btn-secondary text-xs px-3 py-1.5 shadow-sm">+ Tạo quán mới</button>
          </div>
          <div class="relative flex-1 max-w-xs">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                 fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
              <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z"/>
            </svg>
            <input id="menu-search" type="search" placeholder="Tìm tên quán…" class="form-input pl-9! text-sm" />
          </div>
        </div>

        <!-- Skeleton -->
        <div id="menu-skeleton" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
          <div class="card p-5 animate-pulse"><div class="h-4 bg-gray-100 rounded w-3/4 mb-3"></div><div class="h-3 bg-gray-100 rounded w-1/2 mb-4"></div><div class="h-9 bg-gray-100 rounded"></div></div>
          <div class="card p-5 animate-pulse"><div class="h-4 bg-gray-100 rounded w-2/3 mb-3"></div><div class="h-3 bg-gray-100 rounded w-1/3 mb-4"></div><div class="h-9 bg-gray-100 rounded"></div></div>
          <div class="card p-5 animate-pulse"><div class="h-4 bg-gray-100 rounded w-3/4 mb-3"></div><div class="h-3 bg-gray-100 rounded w-1/2 mb-4"></div><div class="h-9 bg-gray-100 rounded"></div></div>
        </div>

        <!-- Empty -->
        <div id="menu-empty" class="hidden text-center py-16 card p-8">
          <div class="text-5xl mb-3">🍽️</div>
          <p class="font-medium text-gray-600">Chưa có menu nào được lưu</p>
          <p class="text-sm text-gray-400 mt-1">Tạo quán mới hoặc đặt đơn từ một quán để lưu vào thư viện!</p>
        </div>

        <!-- Grid -->
        <div id="menu-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
      </div>
    </div>


    <!-- ───────────────────────────────────────
         PAGE: MENU DETAIL
    ─────────────────────────────────────── -->
    <div id="page-menu-detail" class="page hidden animate-fade-in">
      <button onclick="history.back()" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-orange-500 transition-colors mb-5 font-medium">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        Quay lại
      </button>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-1">
          <div class="card p-6 sticky top-20">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-2xl mb-4 shadow shadow-orange-200">🍽️</div>
            <h1 id="md-menu-name" class="text-xl font-bold text-gray-900 mb-1">–</h1>
            <p id="md-menu-desc" class="text-sm text-gray-500 mb-3"></p>
            <div class="space-y-1 text-sm text-gray-500 mb-5">
              <p id="md-menu-phone"></p>
              <p id="md-menu-address"></p>
            </div>

            <!-- Báo hiệu đang xem bản lưu cục bộ -->
            <div id="md-snapshot-banner" class="hidden mb-5 p-3 bg-blue-50 border border-blue-100 rounded-xl">
              <div class="flex items-center gap-2 text-blue-700 text-xs font-medium mb-2">
                💾 Đang xem bản lưu
              </div>
              <button id="md-btn-sync" class="w-full text-xs bg-white border border-blue-200 text-blue-600 px-3 py-2 rounded-lg font-medium hover:bg-blue-50 transition-colors shadow-sm">
                🔄 Đồng bộ món mới
              </button>
            </div>

            <button id="md-btn-create" class="btn-primary w-full">🚀 Tạo đơn từ Menu này</button>
          </div>
        </div>
        <div class="lg:col-span-2">
          <div class="flex items-center gap-3 mb-4">
            <div class="section-title flex-1 !mb-0">Danh sách món ăn</div>
            <button id="btn-create-menu-item" onclick="openCreateMenuItemModal()" class="hidden btn-secondary text-xs px-3 py-1.5 shadow-sm">+ Thêm món</button>
          </div>
          <div id="md-items-list" class="grid grid-cols-1 sm:grid-cols-2 gap-2"></div>
        </div>
      </div>
    </div>

    <!-- ───────────────────────────────────────
         PAGE: ORDER
    ─────────────────────────────────────── -->
    <div id="page-order" class="page hidden animate-fade-in">

      <div id="order-error" class="hidden text-center py-16 card p-8">
        <div class="text-4xl mb-2">😕</div>
        <p class="text-gray-500">Không tìm thấy đơn hàng này.</p>
        <button onclick="window.DOPRouter?.navigate('/')" class="btn-secondary mt-4 text-sm">🏠 Về trang chủ</button>
      </div>

      <!-- Header -->
      <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-5">
        <div>
          <h2 id="order-menu-name" class="text-xl font-bold text-gray-900">–</h2>
          <div class="flex items-center gap-2 mt-1.5 flex-wrap">
            <span id="order-status-badge" class="status-badge badge-yellow">–</span>
            <span id="order-host-badge" class="hidden status-badge badge-orange">👑 Bạn là Host</span>
          </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
          <input id="order-share-link" type="text" readonly
                 class="form-input text-xs bg-gray-50 text-gray-400 w-44 sm:w-52 cursor-pointer"
                 onclick="this.select()" />
          <button id="btn-copy-link" class="btn-secondary text-xs px-3 py-2">📋</button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        <!-- LEFT -->
        <div class="lg:col-span-2 space-y-5">

          <!-- Join section -->
          <div id="order-join-section" class="card p-6 hidden">
            <div class="section-title">👋 Tham gia đặt món</div>
            <p class="text-sm text-gray-500 mb-4" id="join-desc">Nhập tên để tham gia đơn hàng. Hệ thống sẽ nhớ bạn qua trình duyệt.</p>
            
            <!-- Auth View -->
            <div id="join-auth-view" class="hidden space-y-4">
              <div class="flex items-center gap-3 p-3 bg-orange-50 rounded-xl border border-orange-100">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white font-bold" id="join-auth-avatar"></div>
                <div>
                  <p class="text-sm font-semibold text-gray-900" id="join-auth-name"></p>
                  <p class="text-xs text-gray-500" id="join-auth-phone"></p>
                </div>
              </div>
              <button id="btn-join-auth" class="btn-primary w-full">🚀 Tham gia ngay</button>
              <button id="btn-join-other" class="btn-ghost w-full text-sm">Chơi hệ ẩn danh (Nhập tên khác)</button>
            </div>

            <!-- Guest Form View -->
            <form id="form-join" class="space-y-3">
              <div>
                <label for="join-name" class="form-label">Tên của bạn <span class="text-red-400">*</span></label>
                <input id="join-name" type="text" class="form-input" placeholder="VD: Nguyễn Văn A" required />
              </div>
              <div>
                <label for="join-phone" class="form-label">Số điện thoại <span class="text-gray-400 font-normal">(tùy chọn)</span></label>
                <input id="join-phone" type="tel" class="form-input" placeholder="0912 345 678" />
              </div>
              <button type="submit" class="btn-primary w-full">🚀 Tham gia đặt món</button>
            </form>
          </div>

          <!-- Menu items to pick -->
          <div id="order-add-item-section" class="card p-5 hidden">
            <div class="section-title">🍜 Chọn thêm món</div>
            <div id="order-menu-items" class="space-y-1.5 max-h-72 overflow-y-auto pr-0.5"></div>
          </div>

          <!-- My Cart -->
          <div id="order-guest-section" class="card p-5 hidden">
            <div class="section-title">🛒 Giỏ hàng của bạn</div>
            <div id="my-cart-items" class="min-h-[60px]"></div>
            <div class="flex items-center justify-between pt-3 border-t border-gray-100 mt-2">
              <span class="text-sm font-semibold text-gray-700">Tạm tính</span>
              <span id="my-cart-total" class="font-bold text-gray-900 text-base">0 ₫</span>
            </div>
            <div id="my-cart-breakdown" class="hidden mt-2 pt-2 border-t border-dashed border-gray-200 space-y-1.5">
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Phí ship (chia sẻ)</span>
                <span id="my-cart-shipping" class="text-gray-700 font-medium">0 ₫</span>
              </div>
              <div class="flex items-center justify-between text-sm">
                <span class="text-gray-500">Giảm giá (chia sẻ)</span>
                <span id="my-cart-discount" class="text-gray-700 font-medium">0 ₫</span>
              </div>
              <div class="flex items-center justify-between pt-2 mt-2 border-t border-gray-200">
                <span class="text-sm font-bold text-gray-900">Thành tiền</span>
                <span id="my-cart-final" class="font-black text-orange-600 text-lg">0 ₫</span>
              </div>
            </div>
            <button id="btn-ready" class="btn-primary w-full mt-4">✅ Hoàn tất chọn món</button>
          </div>

        </div>

        <!-- RIGHT -->
        <div class="space-y-5">

          <!-- Participants -->
          <div class="card p-5">
            <div class="section-title">👥 Thành viên (<span id="order-participant-count">0</span>)</div>
            <div id="order-participants"></div>
          </div>

          <!-- Host Controls -->
          <div id="order-host-section" class="card p-5 space-y-4 hidden">
            <div class="section-title">⚙️ Điều khiển Host</div>

            <form id="form-fees" class="space-y-3">
              <div class="grid grid-cols-3 gap-2">
                <div>
                  <label class="form-label text-xs">Ship (₫)</label>
                  <input id="fee-shipping" type="number" min="0" class="form-input text-sm" placeholder="0" />
                </div>
                <div>
                  <label class="form-label text-xs">VAT (₫)</label>
                  <input id="fee-tax" type="number" min="0" class="form-input text-sm" placeholder="0" />
                </div>
                <div>
                  <label class="form-label text-xs">Giảm (₫)</label>
                  <input id="fee-discount" type="number" min="0" class="form-input text-sm" placeholder="0" />
                </div>
              </div>

              <div>
                <label class="form-label text-xs mb-2">Chia tiền</label>
                <div class="grid grid-cols-3 gap-1.5">
                  <label class="split-opt cursor-pointer">
                    <input type="radio" name="split_type" value="none" class="sr-only" />
                    <div class="py-2 text-center text-xs font-medium border-2 border-gray-200 rounded-lg hover:border-orange-200 transition-all">🎁 Host bao</div>
                  </label>
                  <label class="split-opt cursor-pointer">
                    <input type="radio" name="split_type" value="even" class="sr-only" checked />
                    <div class="py-2 text-center text-xs font-medium border-2 border-gray-200 rounded-lg hover:border-orange-200 transition-all">⚖️ Chia đều</div>
                  </label>
                  <label class="split-opt cursor-pointer">
                    <input type="radio" name="split_type" value="individual" class="sr-only" />
                    <div class="py-2 text-center text-xs font-medium border-2 border-gray-200 rounded-lg hover:border-orange-200 transition-all">🍱 Theo món</div>
                  </label>
                </div>
              </div>

              <button type="submit" class="btn-secondary w-full text-sm py-2">💾 Lưu phí & tính lại</button>
            </form>

            <div class="pt-3 border-t border-gray-100 space-y-2">
              <button id="btn-lock-order" class="btn-secondary w-full hidden text-sm">
                🔒 Chốt đơn (Khóa chọn món)
              </button>
              <button id="btn-unlock-order" class="btn-ghost w-full hidden text-sm text-amber-600">
                🔓 Mở lại chọn món
              </button>
              <button id="btn-complete-order" class="btn-primary w-full hidden">
                🎉 Hoàn tất → Thanh toán
              </button>
              <button id="btn-cancel-order" class="btn-ghost w-full hidden text-sm text-red-500 hover:bg-red-50 transition-colors">
                ❌ Hủy đơn hàng
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <!-- ───────────────────────────────────────
         PAGE: PAYMENT
    ─────────────────────────────────────── -->
    <div id="page-payment" class="page hidden animate-fade-in">

      <div id="pay-error" class="hidden text-center py-16 card p-8">
        <div class="text-4xl mb-2">😕</div>
        <p class="text-gray-500">Không tìm thấy đơn hàng.</p>
        <button onclick="window.DOPRouter?.navigate('/')" class="btn-secondary mt-4 text-sm">🏠 Về trang chủ</button>
      </div>

      <!-- Header -->
      <div class="card overflow-hidden mb-5">
        <div class="bg-gradient-to-r from-green-500 to-emerald-500 px-6 py-5 text-white">
          <p class="text-xs font-semibold opacity-80 mb-1">✅ Đơn hàng hoàn tất – Tiến hành thanh toán</p>
          <h2 id="pay-menu-name" class="text-2xl font-bold">–</h2>
        </div>
        <div class="px-6 py-3.5 flex items-center justify-between border-b border-gray-100">
          <span class="text-sm text-gray-500">Tổng đơn</span>
          <span id="pay-total-amount" class="text-xl font-extrabold text-gray-900">–</span>
        </div>
        <!-- Progress -->
        <div class="px-6 py-3" id="pay-progress-container">
          <div class="flex justify-between text-xs text-gray-500 mb-1.5">
            <span id="pay-progress-text">Đang tải…</span>
          </div>
          <div class="progress-bar"><div id="pay-progress-bar" class="progress-fill" style="width:0%"></div></div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        <!-- My payment + QR -->
        <div id="pay-my-section" class="hidden space-y-4">
          <div class="card p-6">
            <div class="section-title">💰 Phần của bạn</div>
            <div class="text-center py-3">
              <div class="text-4xl font-black text-orange-500 mb-1.5" id="pay-my-share">–</div>
              <span id="pay-my-status" class="status-badge badge-gray">–</span>
            </div>

            <div id="pay-qr-section" class="hidden mt-4 p-4 bg-orange-50 rounded-xl border border-orange-100">
              <p class="text-xs font-semibold text-orange-700 text-center mb-3">📲 Quét QR chuyển khoản</p>
              <div class="flex justify-center mb-4">
                <div class="bg-white p-2.5 rounded-xl border border-orange-100 shadow-sm">
                  <img id="pay-qr-img" src="" alt="VietQR" class="w-44 h-44 object-contain"
                       onerror="this.closest('.flex').innerHTML='<p class=\'text-xs text-gray-400 text-center p-4 w-48\'>QR không tải được</p>'" />
                </div>
              </div>
              <div class="space-y-1.5 text-xs">
                <div class="flex justify-between bg-white rounded-lg px-3 py-2"><span class="text-gray-500">Ngân hàng</span><span id="pay-bank-name" class="font-semibold">–</span></div>
                <div class="flex justify-between bg-white rounded-lg px-3 py-2"><span class="text-gray-500">Số TK</span><span id="pay-bank-account" class="font-semibold font-mono">–</span></div>
                <div class="flex justify-between bg-white rounded-lg px-3 py-2"><span class="text-gray-500">Chủ TK</span><span id="pay-bank-holder" class="font-semibold">–</span></div>
                <div class="flex justify-between bg-white rounded-lg px-3 py-2"><span class="text-gray-500">Số tiền</span><span id="pay-qr-amount" class="font-bold text-orange-600">–</span></div>
                <div class="flex justify-between bg-white rounded-lg px-3 py-2 gap-2"><span class="text-gray-500 flex-shrink-0">Nội dung CK</span><span id="pay-transfer-note" class="font-medium text-right font-mono break-all">–</span></div>
              </div>
              <button id="btn-submit-payment" class="btn-primary w-full mt-4 hidden">💸 Tôi đã chuyển tiền</button>
            </div>
          </div>
        </div>

        <!-- All participants -->
        <div class="card p-6">
          <div class="section-title">📊 Tổng hợp thanh toán</div>
          <div id="pay-all-participants"></div>
        </div>

      </div>
    </div>

    <!-- ───────────────────────────────────────
         PAGE: HISTORY
    ─────────────────────────────────────── -->
    <div id="page-history" class="page hidden animate-fade-in">
      <div class="flex items-center justify-between mb-5">
        <h2 class="text-xl font-bold text-gray-900">📋 Lịch sử Đơn Hàng</h2>
        <button onclick="window.DOPRouter?.navigate('/')" class="btn-secondary text-sm">+ Tạo đơn mới</button>
      </div>
      <div id="history-list" class="space-y-3"></div>
    </div>

  </main>

  <!-- ═══════════════════════════════════════════════════════
       MODAL: AUTH (Login / Register)
  ═══════════════════════════════════════════════════════ -->
  <div id="modal-auth" class="modal-overlay" style="display:none" onclick="closeModalOnOverlay(event, 'modal-auth')">
    <div class="modal-box">
      <!-- Tab header -->
      <div class="flex border-b border-gray-100 px-2 pt-4">
        <button id="tab-login"    class="tab-btn active" onclick="switchAuthTab('login')">Đăng nhập</button>
        <button id="tab-register" class="tab-btn"        onclick="switchAuthTab('register')">Đăng ký</button>
        <button onclick="closeModal('modal-auth')" class="ml-auto w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400 transition-colors mb-1">✕</button>
      </div>

      <!-- Login Form -->
      <div id="form-login-wrap" class="p-6">
        <form id="form-login" class="space-y-4" onsubmit="handleLogin(event)">
          <div>
            <label for="login-email" class="form-label">Email</label>
            <input id="login-email" type="email" class="form-input" placeholder="name@example.com" required autocomplete="email" />
          </div>
          <div>
            <label for="login-password" class="form-label">Mật khẩu</label>
            <input id="login-password" type="password" class="form-input" placeholder="••••••••" required autocomplete="current-password" />
          </div>
          <p id="login-error" class="form-error hidden"></p>
          <button type="submit" id="btn-login-submit" class="btn-primary w-full">Đăng nhập</button>
          <p class="text-center text-xs text-gray-400">
            Dùng: <code class="bg-gray-100 px-1.5 py-0.5 rounded">host@dopfood.test</code> / <code class="bg-gray-100 px-1.5 py-0.5 rounded">password</code>
          </p>
        </form>
      </div>

      <!-- Register Form -->
      <div id="form-register-wrap" class="p-6 hidden">
        <form id="form-register" class="space-y-3" onsubmit="handleRegister(event)">
          <div>
            <label for="reg-name" class="form-label">Họ và tên <span class="text-red-400">*</span></label>
            <input id="reg-name" type="text" class="form-input" placeholder="Nguyễn Văn A" required />
          </div>
          <div>
            <label for="reg-email" class="form-label">Email <span class="text-red-400">*</span></label>
            <input id="reg-email" type="email" class="form-input" placeholder="name@example.com" required autocomplete="email" />
          </div>
          <div>
            <label for="reg-phone" class="form-label">Số điện thoại</label>
            <input id="reg-phone" type="tel" class="form-input" placeholder="0912 345 678" />
          </div>
          <div>
            <label for="reg-password" class="form-label">Mật khẩu <span class="text-red-400">*</span></label>
            <input id="reg-password" type="password" class="form-input" placeholder="Tối thiểu 8 ký tự" required autocomplete="new-password" />
          </div>
          <div>
            <label for="reg-password-confirm" class="form-label">Xác nhận mật khẩu <span class="text-red-400">*</span></label>
            <input id="reg-password-confirm" type="password" class="form-input" placeholder="Nhập lại mật khẩu" required autocomplete="new-password" />
          </div>
          <p id="register-error" class="form-error hidden"></p>
          <button type="submit" id="btn-register-submit" class="btn-primary w-full">Tạo tài khoản</button>
        </form>
      </div>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════
       MODAL: BANK SETTINGS
  ═══════════════════════════════════════════════════════ -->
  <div id="modal-bank" class="modal-overlay" style="display:none" onclick="closeModalOnOverlay(event, 'modal-bank')">
    <div class="modal-box">
      <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-gray-100">
        <div>
          <h3 class="font-bold text-gray-900">🏦 Cài đặt Ngân hàng</h3>
          <p class="text-xs text-gray-500 mt-0.5">Thông tin & mã QR hiển thị khi khách thanh toán</p>
        </div>
        <button onclick="closeModal('modal-bank')" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">✕</button>
      </div>
      <form id="form-bank" class="p-6 space-y-4" onsubmit="handleBankSave(event)" enctype="multipart/form-data">
        <div>
          <label class="form-label">Tên ngân hàng <span class="text-xs text-gray-400">(VD: MBBank, VCB, TCB)</span></label>
          <input id="bank-name" type="text" class="form-input" placeholder="VD: MBBank" />
        </div>
        <div>
          <label class="form-label">Số tài khoản</label>
          <input id="bank-account-number" type="text" class="form-input font-mono" placeholder="VD: 0912345678" />
        </div>
        <div>
          <label class="form-label">Tên chủ tài khoản <span class="text-xs text-gray-400">(IN HOA KHÔNG DẤU)</span></label>
          <input id="bank-account-name" type="text" class="form-input uppercase" placeholder="VD: NGUYEN VAN A"
                 oninput="this.value = this.value.toUpperCase()" />
        </div>
        <!-- QR Upload -->
        <div>
          <label class="form-label">Ảnh mã QR chuyển khoản <span class="text-xs text-gray-400">(tùy chọn)</span></label>
          <div id="qr-upload-area" class="relative border-2 border-dashed border-gray-200 rounded-xl p-4 text-center
                                          hover:border-orange-300 transition-colors cursor-pointer"
               onclick="document.getElementById('bank-qr-file').click()">
            <img id="qr-preview-img" src="" alt="" class="hidden mx-auto mb-2 w-32 h-32 object-contain rounded-lg border border-gray-100" />
            <div id="qr-upload-placeholder">
              <div class="text-3xl mb-1">📲</div>
              <p class="text-xs text-gray-500">Click để tải ảnh QR lên</p>
              <p class="text-xs text-gray-400 mt-0.5">JPG, PNG, WEBP – tối đa 5MB</p>
            </div>
            <input id="bank-qr-file" type="file" accept="image/jpeg,image/png,image/webp" class="hidden"
                   onchange="previewQrImage(this)" />
          </div>
          <p class="text-xs text-gray-400 mt-1">Ảnh QR sẽ hiển thị cho khách khi thanh toán. Nếu không có, hệ thống dùng thông tin TK bên trên.</p>
        </div>
        <p id="bank-error" class="form-error hidden"></p>
        <div class="flex gap-2 pt-1">
          <button type="button" onclick="closeModal('modal-bank')" class="btn-secondary flex-1">Hủy</button>
          <button type="submit" id="btn-bank-save" class="btn-primary flex-1">💾 Lưu</button>
        </div>
      </form>
    </div>
  </div>


  <!-- ═══════════════════════════════════════════════════════
       MODAL: CREATE MENU
  ═══════════════════════════════════════════════════════ -->
  <div id="modal-menu" class="modal-overlay" style="display:none" onclick="closeModalOnOverlay(event, 'modal-menu')">
    <div class="modal-box">
      <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">🏠 Tạo Quán Mới</h3>
        <button onclick="closeModal('modal-menu')" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">✕</button>
      </div>
      <form id="form-menu" class="p-6 space-y-4" onsubmit="handleMenuSave(event)">
        <div>
          <label class="form-label">Tên quán <span class="text-red-400">*</span></label>
          <input id="menu-name" type="text" class="form-input" required placeholder="VD: Phở Bát Đàn" />
        </div>
        <div>
          <label class="form-label">Mô tả</label>
          <input id="menu-desc" type="text" class="form-input" placeholder="VD: Bán ăn sáng, trưa..." />
        </div>
        <div>
          <label class="form-label">Số điện thoại</label>
          <input id="menu-phone" type="text" class="form-input" placeholder="VD: 09..." />
        </div>
        <div>
          <label class="form-label">Địa chỉ</label>
          <input id="menu-address" type="text" class="form-input" placeholder="VD: 49 Bát Đàn..." />
        </div>
        <p id="menu-error" class="form-error hidden"></p>
        <div class="flex gap-2 pt-1">
          <button type="button" onclick="closeModal('modal-menu')" class="btn-secondary flex-1">Hủy</button>
          <button type="submit" id="btn-menu-save" class="btn-primary flex-1">💾 Lưu</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════
       MODAL: CREATE MENU ITEM
  ═══════════════════════════════════════════════════════ -->
  <div id="modal-menu-item" class="modal-overlay" style="display:none" onclick="closeModalOnOverlay(event, 'modal-menu-item')">
    <div class="modal-box">
      <div class="flex items-center justify-between px-6 pt-5 pb-3 border-b border-gray-100">
        <h3 class="font-bold text-gray-900">🍜 Thêm Món Mới</h3>
        <button onclick="closeModal('modal-menu-item')" class="w-8 h-8 rounded-full hover:bg-gray-100 flex items-center justify-center text-gray-400">✕</button>
      </div>
      <form id="form-menu-item" class="p-6 space-y-4" onsubmit="handleMenuItemSave(event)">
        <input type="hidden" id="mi-menu-id" />
        <input type="hidden" id="mi-item-id" />
        <div>
          <label class="form-label">Tên món <span class="text-red-400">*</span></label>
          <input id="mi-name" type="text" class="form-input" required placeholder="VD: Phở Bò Tái Nạm" />
        </div>
        <div>
          <label class="form-label">Giá (₫) <span class="text-red-400">*</span></label>
          <input id="mi-price" type="number" min="0" class="form-input" required placeholder="VD: 50000" />
        </div>
        <p id="mi-error" class="form-error hidden"></p>
        <div class="flex gap-2 pt-1">
          <button type="button" onclick="closeModal('modal-menu-item')" class="btn-secondary flex-1">Hủy</button>
          <button type="submit" id="btn-mi-save" class="btn-primary flex-1">💾 Lưu</button>
        </div>
      </form>
    </div>
  </div>

  <!-- ═══════════════════════════════════════════════════════
       INLINE SCRIPTS (global UI helpers)
  ═══════════════════════════════════════════════════════ -->
  <script>
    // ── Modal helpers ──────────────────────────────────────
    // Dùng style.display thay vì classList.hidden vì custom CSS .modal-overlay{display:flex}
    // sẽ override Tailwind .hidden khi nằm sau trong cascade.
    function openModal(id)  {
      const el = document.getElementById(id);
      if (el) el.style.display = 'flex';
    }
    function closeModal(id) {
      const el = document.getElementById(id);
      if (el) el.style.display = 'none';
    }
    function closeModalOnOverlay(e, id) { if (e.target === e.currentTarget) closeModal(id); }

    function openAuthModal(tab = 'login') {
      openModal('modal-auth');
      switchAuthTab(tab);
    }

    function openBankModal() {
      const user = window.DOPAuth?.user;
      if (!user) { openAuthModal('login'); return; }
      document.getElementById('bank-name').value           = user.bank_name ?? '';
      document.getElementById('bank-account-number').value = user.bank_account_number ?? '';
      document.getElementById('bank-account-name').value   = user.bank_account_name ?? '';
      // Reset file input
      document.getElementById('bank-qr-file').value = '';
      // Hiện preview QR hiện tại nếu có
      const previewImg = document.getElementById('qr-preview-img');
      const placeholder = document.getElementById('qr-upload-placeholder');
      if (user.qr_image_url) {
        previewImg.src = user.qr_image_url;
        previewImg.classList.remove('hidden');
        if (placeholder) placeholder.classList.add('hidden');
      } else {
        previewImg.src = '';
        previewImg.classList.add('hidden');
        if (placeholder) placeholder.classList.remove('hidden');
      }
      openModal('modal-bank');
    }

    function switchAuthTab(tab) {
      const isLogin = tab === 'login';
      document.getElementById('tab-login')?.classList.toggle('active', isLogin);
      document.getElementById('tab-register')?.classList.toggle('active', !isLogin);
      document.getElementById('form-login-wrap')?.classList.toggle('hidden', !isLogin);
      document.getElementById('form-register-wrap')?.classList.toggle('hidden', isLogin);
    }

    // ── Auth form handlers ─────────────────────────────────
    async function handleLogin(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-login-submit');
      const errEl = document.getElementById('login-error');
      btn.disabled = true; btn.textContent = 'Đang đăng nhập…';
      errEl.classList.add('hidden');

      try {
        await window.DOPAuth.login(
          document.getElementById('login-email').value,
          document.getElementById('login-password').value
        );
        closeModal('modal-auth');
        showToast(`👋 Xin chào ${window.DOPAuth.user.name}!`, 'success');
      } catch(er) {
        errEl.textContent = er.errors?.email?.[0] ?? er.message;
        errEl.classList.remove('hidden');
      } finally {
        btn.disabled = false; btn.textContent = 'Đăng nhập';
      }
    }

    async function handleRegister(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-register-submit');
      const errEl = document.getElementById('register-error');
      const pass = document.getElementById('reg-password').value;
      const confirm = document.getElementById('reg-password-confirm').value;
      if (pass !== confirm) { errEl.textContent = 'Mật khẩu không khớp.'; errEl.classList.remove('hidden'); return; }

      btn.disabled = true; btn.textContent = 'Đang tạo tài khoản…';
      errEl.classList.add('hidden');

      try {
        await window.DOPAuth.register({
          name:                  document.getElementById('reg-name').value,
          email:                 document.getElementById('reg-email').value,
          phone:                 document.getElementById('reg-phone').value,
          password:              pass,
          password_confirmation: confirm,
        });
        closeModal('modal-auth');
        showToast(`🎉 Đăng ký thành công! Xin chào ${window.DOPAuth.user.name}!`, 'success');
      } catch(er) {
        const msgs = er.errors ? Object.values(er.errors).flat().join(' · ') : er.message;
        errEl.textContent = msgs;
        errEl.classList.remove('hidden');
      } finally {
        btn.disabled = false; btn.textContent = 'Tạo tài khoản';
      }
    }

    async function handleLogout() {
      toggleUserMenu();
      await window.DOPAuth?.logout();
      showToast('👋 Đã đăng xuất.', 'info');
      window.DOPRouter?.navigate('/');
    }

    // ── Bank form ──────────────────────────────────────────
    function previewQrImage(input) {
      const file = input.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = (evt) => {
        const img = document.getElementById('qr-preview-img');
        const placeholder = document.getElementById('qr-upload-placeholder');
        if (img) { img.src = evt.target.result; img.classList.remove('hidden'); }
        if (placeholder) placeholder.classList.add('hidden');
      };
      reader.readAsDataURL(file);
    }

    async function handleBankSave(e) {
      e.preventDefault();
      const btn = document.getElementById('btn-bank-save');
      const errEl = document.getElementById('bank-error');
      btn.disabled = true; btn.textContent = 'Đang lưu…';
      errEl.classList.add('hidden');

      try {
        // Dùng FormData để hỗ trợ upload file QR
        const fd = new FormData();
        fd.append('bank_name',           document.getElementById('bank-name').value);
        fd.append('bank_account_number', document.getElementById('bank-account-number').value);
        fd.append('bank_account_name',   document.getElementById('bank-account-name').value);
        const qrFile = document.getElementById('bank-qr-file').files[0];
        if (qrFile) fd.append('qr_image', qrFile);

        await window.DOPAuth.updateBank(fd);
        closeModal('modal-bank');
        showToast('✅ Đã lưu thông tin ngân hàng!', 'success');
      } catch(er) {
        errEl.textContent = er.message;
        errEl.classList.remove('hidden');
      } finally {
        btn.disabled = false; btn.textContent = '💾 Lưu';
      }
    }

    // ── User dropdown ──────────────────────────────────────
    function toggleUserMenu() {
      document.getElementById('user-dropdown')?.classList.toggle('hidden');
    }
    document.addEventListener('click', (e) => {
      if (!document.getElementById('user-menu-wrapper')?.contains(e.target)) {
        document.getElementById('user-dropdown')?.classList.add('hidden');
      }
    });

    // ── Nav active links ───────────────────────────────────
    document.querySelectorAll('.nav-link').forEach(el => {
      el.addEventListener('click', () => {
        document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('nav-active'));
        el.classList.add('nav-active');
      });
    });

    // ── Scroll to menus ────────────────────────────────────
    function scrollToMenus() {
      document.getElementById('menu-library-section')?.scrollIntoView({ behavior: 'smooth' });
    }

    // ── Stats counter (updated after home.js renders) ─────
    const _statObs = new MutationObserver(() => {
      const c = document.querySelectorAll('#menu-grid .menu-card').length;
      const el = document.getElementById('stat-menus');
      if (el && c > 0) el.textContent = c;
    });
    document.addEventListener('DOMContentLoaded', () => {
      const grid = document.getElementById('menu-grid');
      if (grid) _statObs.observe(grid, { childList: true });
    });
  </script>

</body>
</html>

<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;
use App\Http\Controllers\OrderParticipantController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DOPFood – API Routes
|--------------------------------------------------------------------------
|
| Prefix: /api  (tự động bởi RouteServiceProvider)
|
| Auth Strategy:
|   - Web SPA (same-domain): Cookie-based session via 'auth:web' guard
|   - Mobile App (future): Bearer token via 'auth:sanctum' (khi cài Sanctum)
|   - Public routes: không cần auth
|
*/

// =============================================================================
// AUTH ROUTES – Đăng ký / Đăng nhập / Đăng xuất
// =============================================================================

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);  // POST /api/auth/register
    Route::post('/login',    [AuthController::class, 'login']);     // POST /api/auth/login
    Route::post('/logout',   [AuthController::class, 'logout']);    // POST /api/auth/logout
    Route::get('/me',        [AuthController::class, 'me']);        // GET  /api/auth/me
});

/*
|--------------------------------------------------------------------------
| DOPFood – API Routes
|--------------------------------------------------------------------------
|
| Prefix: /api  (tự động bởi RouteServiceProvider)
|
| Auth: Laravel Sanctum
|   - Routes trong group 'auth:sanctum' yêu cầu đăng nhập.
|   - Routes public (menus, orders qua share_link, join, items) không cần auth.
|
*/

// =============================================================================
// PUBLIC ROUTES – Không cần đăng nhập
// =============================================================================

// --- Menu Library ---
Route::prefix('menus')->group(function () {
    Route::get('/', [MenuController::class, 'index']);          // GET  /api/menus
    Route::get('/{id}', [MenuController::class, 'show']);       // GET  /api/menus/{id}
});

// --- Order: Xem qua share link (public cho cả Guest) ---
Route::get('/orders/{shareLink}', [OrderController::class, 'show']); // GET /api/orders/{shareLink}

// --- Tham gia đơn hàng (Guest không cần tài khoản) ---
Route::post('/orders/{orderId}/join', [OrderParticipantController::class, 'join']); // POST /api/orders/{orderId}/join

// --- Guest thao tác trong đơn (định danh qua session_token, không cần Auth) ---
Route::prefix('orders/{orderId}')->group(function () {
    // Thêm / sửa / xóa món ăn trong giỏ
    Route::post('/items', [OrderItemController::class, 'store']);          // POST   /api/orders/{orderId}/items
    Route::put('/items/{id}', [OrderItemController::class, 'update']);     // PUT    /api/orders/{orderId}/items/{id}
    Route::delete('/items/{id}', [OrderItemController::class, 'destroy']); // DELETE /api/orders/{orderId}/items/{id}

    // Xác nhận hoàn tất chọn món
    Route::patch(
        '/participants/{id}/ready',
        [OrderParticipantController::class, 'ready']
    );  // PATCH /api/orders/{orderId}/participants/{id}/ready

    // Gửi ảnh bill chuyển khoản
    Route::patch(
        '/participants/{id}/payment',
        [OrderParticipantController::class, 'submitPayment']
    );  // PATCH /api/orders/{orderId}/participants/{id}/payment
});

// =============================================================================
// PROTECTED ROUTES – Yêu cầu đăng nhập
// Guard: 'auth:web' → session cookie (SPA cùng domain)
// Sau khi cài laravel/sanctum → đổi thành 'auth:sanctum' để hỗ trợ cả mobile token
// =============================================================================

Route::middleware('auth:web')->group(function () {

    // --- User Profile & Bank Settings ---
    Route::get('/user/profile', [UserController::class, 'show']);            // GET   /api/user/profile
    Route::get('/user/saved-menus', [UserController::class, 'savedMenus']);
    Route::get('/user/saved-menus/{menuId}', [UserController::class, 'showSavedMenu']);
    Route::post('/user/saved-menus/{menuId}/sync', [UserController::class, 'syncSavedMenu']);  // GET   /api/user/saved-menus
    Route::patch('/user/profile', [UserController::class, 'updateProfile']); // PATCH /api/user/profile
    Route::patch('/user/bank', [UserController::class, 'updateBank']);        // PATCH /api/user/bank

    // --- Menu CRUD (Host tạo, sửa, xóa menu) ---
    Route::prefix('menus')->group(function () {
        Route::post('/', [MenuController::class, 'store']);              // POST   /api/menus
        Route::put('/{id}', [MenuController::class, 'update']);          // PUT    /api/menus/{id}
        Route::delete('/{id}', [MenuController::class, 'destroy']);      // DELETE /api/menus/{id}

        Route::post('/{menuId}/items', [MenuItemController::class, 'store']);              // POST   /api/menus/{menuId}/items
        Route::put('/{menuId}/items/{id}', [MenuItemController::class, 'update']);         // PUT    /api/menus/{menuId}/items/{id}
        Route::delete('/{menuId}/items/{id}', [MenuItemController::class, 'destroy']);     // DELETE /api/menus/{menuId}/items/{id}
    });

    // --- Order Management (Host) ---
    Route::prefix('orders')->group(function () {
        Route::get('/', [OrderController::class, 'index']);                // GET  /api/orders
        Route::post('/', [OrderController::class, 'store']);               // POST /api/orders

        Route::patch('/{id}/status', [OrderController::class, 'updateStatus']); // PATCH /api/orders/{id}/status
        Route::patch('/{id}/fees',   [OrderController::class, 'updateFees']);   // PATCH /api/orders/{id}/fees
        Route::delete('/{id}',       [OrderController::class, 'destroy']);      // DELETE /api/orders/{id}

        Route::patch(
            '/{orderId}/participants/{id}/approve',
            [OrderParticipantController::class, 'approvePayment']
        );  // PATCH /api/orders/{orderId}/participants/{id}/approve
    });
});

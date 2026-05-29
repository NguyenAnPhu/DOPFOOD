<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\UserSavedMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Lịch sử đơn hàng (Host đã tạo hoặc tất cả theo query).
     *
     * GET /api/orders
     */
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['menu', 'host'])
            ->withCount('participants')
            ->where('is_hidden', false);

        // Filter: Auth user chỉ xem đơn của mình làm Host
        $query->where('host_id', $request->user()->id);

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(15);

        return response()->json($orders);
    }

    /**
     * Tạo đơn hàng mới từ một menu.
     * Tự động sinh share_link duy nhất và snapshot thông tin ngân hàng Host.
     *
     * POST /api/orders
     */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Sinh share_link ngắn, duy nhất (8 ký tự)
        do {
            $shareLink = Str::random(8);
        } while (Order::where('share_link', $shareLink)->exists());

        $data['share_link'] = $shareLink;

        // Nếu Host đã đăng nhập và chưa cung cấp thông tin bank → lấy từ profile
        if ($request->user() && empty($data['bank_name'])) {
            $user = $request->user();
            $data['host_id']              = $user->id;
            $data['bank_name']            = $data['bank_name'] ?? $user->bank_name;
            $data['bank_account_number']  = $data['bank_account_number'] ?? $user->bank_account_number;
            $data['bank_account_name']    = $data['bank_account_name'] ?? $user->bank_account_name;
            $data['qr_image_url']         = $data['qr_image_url'] ?? $user->qr_image_url;
        }

        $order = Order::create($data);

        // Upsert snapshot menu vào thư viện của Host:
        // - Nếu chưa có → tạo mới
        // - Nếu đã có (cùng menu_id) → cập nhật/merge items mới nhất
        if ($request->user() && isset($data['menu_id'])) {
            $menu = Menu::with('items')->find($data['menu_id']);
            if ($menu) {
                UserSavedMenu::upsertFromMenu(
                    $request->user()->id,
                    $menu,
                    'ordered'
                );
            }
        }

        return response()->json(
            $order->load(['menu.items']),
            201
        );
    }

    /**
     * Xem chi tiết đơn hàng qua share_link (dành cho cả Host lẫn Guest).
     *
     * GET /api/orders/{shareLink}
     */
    public function show(string $shareLink): JsonResponse
    {
        $order = Order::where('share_link', $shareLink)
            ->with([
                'menu.items',
                'participants.items.menuItem',
            ])
            ->firstOrFail();

        return response()->json($order);
    }

    /**
     * Cập nhật trạng thái đơn hàng (chỉ Host được thực hiện).
     * Các chuyển trạng thái hợp lệ:
     *   ordering → locked → completed → closed
     *
     * PATCH /api/orders/{id}/status
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => ['required', Rule::in(['ordering', 'locked', 'completed', 'closed', 'cancelled'])],
        ]);

        $allowedTransitions = [
            'ordering'  => ['locked', 'cancelled'],
            'locked'    => ['ordering', 'completed', 'cancelled'],
            'completed' => ['closed'],
            'closed'    => [],
            'cancelled' => [],
        ];

        $newStatus = $request->status;

        if (! in_array($newStatus, $allowedTransitions[$order->status])) {
            return response()->json([
                'message' => "Không thể chuyển trạng thái từ '{$order->status}' sang '{$newStatus}'.",
            ], 422);
        }

        $order->status = $newStatus;
        $order->save();

        // Khi Host hoàn tất đơn → tính toán chia tiền cho toàn bộ participants
        if ($newStatus === 'completed') {
            $order->recalculateShares();
            $order->refresh();
        }

        return response()->json($order->load('participants'));
    }

    /**
     * Cập nhật phí phát sinh: ship, VAT, giảm giá, split_type
     * → Tự động tính lại total_share cho từng participant.
     *
     * PATCH /api/orders/{id}/fees
     */
    public function updateFees(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        // Chỉ có thể cập nhật khi đơn chưa hoàn tất
        if (in_array($order->status, ['completed', 'closed'])) {
            return response()->json([
                'message' => 'Không thể chỉnh sửa phí khi đơn đã hoàn tất.',
            ], 422);
        }

        $validated = $request->validate([
            'shipping_fee'    => ['nullable', 'numeric', 'min:0'],
            'tax_amount'      => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'split_type'      => ['nullable', Rule::in(['none', 'even', 'individual'])],
        ]);

        $order->update($validated);

        // Tính lại chia tiền theo phí mới nếu đơn đang ở trạng thái locked/completed
        $order->recalculateShares();
        $order->refresh();

        return response()->json($order->load('participants'));
    }

    /**
     * Xóa đơn hàng (chỉ Host của đơn mới được xóa).
     *
     * DELETE /api/orders/{id}
     */
    public function destroy(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);

        // Chỉ Host mới được xóa đơn
        if (!$request->user() || $order->host_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Bạn không có quyền xóa đơn hàng này.',
            ], 403);
        }

        // Xóa đơn hàng từ lịch sử của Host (ẩn đi) thay vì xóa thật
        $order->is_hidden = true;
        $order->save();

        return response()->json(['message' => 'Đã xóa đơn hàng thành công.']);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderItemRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderParticipant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderItemController extends Controller
{
    /**
     * Thêm món ăn vào giỏ cá nhân của một participant.
     * Tự động snapshot price_at_order từ menu_item tại thời điểm đặt.
     *
     * POST /api/orders/{orderId}/items
     */
    public function store(StoreOrderItemRequest $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        // Chỉ cho thêm món khi đơn đang mở
        if ($order->status !== 'ordering') {
            return response()->json([
                'message' => 'Đơn hàng đã bị khóa, không thể thêm món.',
            ], 422);
        }

        // Xác nhận participant thuộc đúng đơn hàng này
        $participant = OrderParticipant::where('order_id', $orderId)
            ->where('id', $request->participant_id)
            ->firstOrFail();

        // Participant đã "sẵn sàng" thì không được thêm món nữa
        if ($participant->status === 'ready') {
            return response()->json([
                'message' => 'Bạn đã xác nhận xong món, không thể thêm thêm.',
            ], 422);
        }

        // Lấy giá món tại thời điểm đặt (snapshot)
        $menuItem = $order->menu->items()->findOrFail($request->menu_item_id);

        // Nếu đã có item cùng menu_item_id + participant_id → cộng dồn số lượng
        $existing = OrderItem::where('order_id', $orderId)
            ->where('participant_id', $request->participant_id)
            ->where('menu_item_id', $request->menu_item_id)
            ->first();

        if ($existing) {
            $existing->quantity += $request->quantity;
            if ($request->filled('note')) {
                $existing->note = $request->note;
            }
            $existing->save();

            return response()->json([
                'message' => 'Đã cập nhật số lượng món.',
                'item'    => $existing->load('menuItem'),
            ]);
        }

        $item = OrderItem::create([
            'order_id'       => $orderId,
            'participant_id' => $request->participant_id,
            'menu_item_id'   => $request->menu_item_id,
            'quantity'       => $request->quantity,
            'price_at_order' => $menuItem->price,   // snapshot
            'note'           => $request->note,
        ]);

        return response()->json([
            'message' => 'Thêm món thành công!',
            'item'    => $item->load('menuItem'),
        ], 201);
    }

    /**
     * Cập nhật số lượng hoặc ghi chú của một order item.
     *
     * PUT /api/orders/{orderId}/items/{id}
     */
    public function update(Request $request, int $orderId, int $id): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'ordering') {
            return response()->json([
                'message' => 'Đơn hàng đã bị khóa, không thể chỉnh sửa món.',
            ], 422);
        }

        $item = OrderItem::where('order_id', $orderId)->findOrFail($id);

        // Kiểm tra participant chưa ready
        if ($item->participant->status === 'ready') {
            return response()->json([
                'message' => 'Bạn đã xác nhận xong món, không thể chỉnh sửa.',
            ], 422);
        }

        $validated = $request->validate([
            'quantity' => ['sometimes', 'integer', 'min:1', 'max:99'],
            'note'     => ['nullable', 'string', 'max:500'],
        ]);

        $item->update($validated);

        return response()->json([
            'message' => 'Cập nhật món thành công!',
            'item'    => $item->load('menuItem'),
        ]);
    }

    /**
     * Xóa một order item khỏi giỏ của participant.
     *
     * DELETE /api/orders/{orderId}/items/{id}
     */
    public function destroy(int $orderId, int $id): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        if ($order->status !== 'ordering') {
            return response()->json([
                'message' => 'Đơn hàng đã bị khóa, không thể xóa món.',
            ], 422);
        }

        $item = OrderItem::where('order_id', $orderId)->findOrFail($id);

        // Kiểm tra participant chưa ready
        if ($item->participant->status === 'ready') {
            return response()->json([
                'message' => 'Bạn đã xác nhận xong món, không thể xóa.',
            ], 422);
        }

        $item->delete();

        return response()->json(['message' => 'Đã xóa món khỏi giỏ hàng.']);
    }
}

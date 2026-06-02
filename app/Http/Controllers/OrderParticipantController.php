<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinOrderRequest;
use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderParticipant;
use App\Models\UserSavedMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderParticipantController extends Controller
{
    /**
     * Guest tham gia một đơn hàng.
     * - Nếu session_token đã tồn tại trong đơn → trả về participant hiện tại (auto-resume).
     * - Nếu chưa có → tạo mới participant.
     *
     * POST /api/orders/{orderId}/join
     */
    public function join(JoinOrderRequest $request, int $orderId): JsonResponse
    {
        $order = Order::findOrFail($orderId);

        // Chỉ cho phép tham gia khi đơn đang mở
        if ($order->status !== 'ordering') {
            return response()->json([
                'message' => 'Đơn hàng đã bị khóa hoặc kết thúc, không thể tham gia.',
            ], 422);
        }

        // Nhận diện lại bằng session_token (Cookie định danh)
        if ($request->filled('session_token')) {
            $existing = $order->participants()
                ->where('session_token', $request->session_token)
                ->first();

            if ($existing) {
                // Cập nhật user_id nếu participant chưa có và user đang đăng nhập
                if (!$existing->user_id && $request->user()) {
                    $existing->user_id = $request->user()->id;
                    $existing->save();
                }

                // Nếu user đã đăng nhập, vẫn upsert snapshot menu (cập nhật nếu trùng quán)
                $this->maybeSaveMenuForUser($request, $order);

                return response()->json([
                    'message'     => 'Đã tham gia đơn hàng này, tiếp tục phiên cũ.',
                    'participant' => $existing->load('items.menuItem'),
                ]);
            }
        }

        $data = $request->validated();

        // Gắn user_id nếu user đã đăng nhập
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        $participant = $order->participants()->create($data);

        // Nếu user đã đăng nhập → lưu snapshot menu vào thư viện
        $this->maybeSaveMenuForUser($request, $order);

        return response()->json([
            'message'     => 'Tham gia đơn hàng thành công!',
            'participant' => $participant,
        ], 201);
    }

    /**
     * Nếu user đang đăng nhập → upsert snapshot menu (tạo mới hoặc merge nếu trùng quán).
     */
    private function maybeSaveMenuForUser(JoinOrderRequest $request, Order $order): void
    {
        $user = $request->user();
        if (!$user || !$order->menu_id) {
            return;
        }
        $menu = Menu::with('items')->find($order->menu_id);
        if ($menu) {
            UserSavedMenu::upsertFromMenu($user->id, $menu, 'ordered');
        }
    }

    /**
     * Guest xác nhận đã hoàn tất chọn món → đổi status sang 'ready'.
     * Khi sẵn sàng, Host thấy được trạng thái ✅ realtime.
     *
     * PATCH /api/orders/{orderId}/participants/{id}/ready
     */
    public function ready(Request $request, int $orderId, int $id): JsonResponse
    {
        $participant = OrderParticipant::where('order_id', $orderId)->findOrFail($id);

        // Xác thực session_token để đảm bảo chỉ đúng người mới mark ready
        $token = $request->input('session_token');
        if ($token && $participant->session_token !== $token) {
            return response()->json([
                'message' => 'Bạn không có quyền xác nhận cho thành viên này.',
            ], 403);
        }

        if ($participant->status === 'ready') {
            return response()->json(['message' => 'Bạn đã xác nhận xong món trước đó.']);
        }

        $participant->status = 'ready';
        $participant->save();

        return response()->json([
            'message'     => 'Đã xác nhận hoàn tất chọn món!',
            'participant' => $participant,
        ]);
    }

    /**
     * Guest gửi bằng chứng thanh toán (upload ảnh bill chuyển khoản).
     * Đổi payment_status: pending → submitted.
     *
     * PATCH /api/orders/{orderId}/participants/{id}/payment
     */
    public function submitPayment(Request $request, int $orderId, int $id): JsonResponse
    {
        $participant = OrderParticipant::where('order_id', $orderId)->findOrFail($id);

        if ($participant->payment_status !== 'pending') {
            return response()->json([
                'message' => 'Thanh toán đã được xử lý trước đó.',
            ], 422);
        }

        $participant->payment_status = 'submitted';
        $participant->save();

        return response()->json([
            'message'     => 'Đã xác nhận đã chuyển tiền!',
            'participant' => $participant,
        ]);
    }

    /**
     * Host xác nhận đã nhận tiền từ Guest.
     * Đổi payment_status: submitted → approved.
     * CHỈ HOST mới có quyền thực hiện.
     *
     * PATCH /api/orders/{orderId}/participants/{id}/approve
     */
    public function approvePayment(Request $request, int $orderId, int $id): JsonResponse
    {
        $participant = OrderParticipant::where('order_id', $orderId)->findOrFail($id);
        $order = $participant->order;

        // Chỉ Host mới được approve thanh toán
        if (!$request->user() || $order->host_id !== $request->user()->id) {
            return response()->json([
                'message' => 'Chỉ Host mới có quyền xác nhận thanh toán.',
            ], 403);
        }

        if ($participant->payment_status === 'approved') {
            $participant->payment_status = 'pending';
        } else {
            $participant->payment_status = 'approved';
        }
        $participant->save();

        // Kiểm tra xem tất cả participants đã thanh toán chưa → tự động close đơn
        // Loại trừ participant của Host ra khỏi kiểm tra
        $allPaid = $order->participants()
            ->where('user_id', '!=', $order->host_id)
            ->orWhereNull('user_id')
            ->where('payment_status', '!=', 'approved')
            ->doesntExist();

        if ($allPaid && $order->status === 'completed') {
            $order->status = 'closed';
            $order->save();
        }

        return response()->json([
            'message'      => 'Đã cập nhật trạng thái thanh toán!',
            'participant'  => $participant,
            'order_closed' => $allPaid,
        ]);
    }
}

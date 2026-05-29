<?php

namespace App\Http\Controllers;

use App\Models\UserSavedMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Xem thông tin profile và cài đặt ngân hàng của user đang đăng nhập.
     *
     * GET /api/user/profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json([
            'id'                  => $user->id,
            'name'                => $user->name,
            'phone'               => $user->phone,
            'email'               => $user->email,
            'bank_name'           => $user->bank_name,
            'bank_account_number' => $user->bank_account_number,
            'bank_account_name'   => $user->bank_account_name,
            'created_at'          => $user->created_at,
        ]);
    }

    /**
     * Cập nhật thông tin ngân hàng mặc định của Host.
     * Thông tin này sẽ được snapshot tự động vào mỗi đơn hàng khi tạo mới.
     *
     * PATCH /api/user/bank
     */
    public function updateBank(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'bank_name'           => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:30'],
            'bank_account_name'   => ['nullable', 'string', 'max:100'],
            'qr_image'            => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
        ]);

        $updateData = [
            'bank_name'           => $validated['bank_name'] ?? $user->bank_name,
            'bank_account_number' => $validated['bank_account_number'] ?? $user->bank_account_number,
            'bank_account_name'   => $validated['bank_account_name'] ?? $user->bank_account_name,
        ];

        // Xử lý upload ảnh QR nếu có
        if ($request->hasFile('qr_image')) {
            // Xóa ảnh cũ nếu tồn tại
            if ($user->qr_image_url) {
                $oldPath = str_replace('/storage/', '', $user->qr_image_url);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('qr_image')->store("qr-images/{$user->id}", 'public');
            $updateData['qr_image_url'] = Storage::url($path);
        }

        $user->update($updateData);

        return response()->json([
            'message' => 'Đã cập nhật thông tin ngân hàng thành công!',
            'user'    => [
                'bank_name'           => $user->bank_name,
                'bank_account_number' => $user->bank_account_number,
                'bank_account_name'   => $user->bank_account_name,
                'qr_image_url'        => $user->qr_image_url,
            ],
        ]);
    }

    /**
     * Cập nhật thông tin cơ bản (tên, số điện thoại).
     *
     * PATCH /api/user/profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['sometimes', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:15'],
        ]);

        $user->update($validated);

        return response()->json([
            'message' => 'Đã cập nhật thông tin thành công!',
            'user'    => $user->only(['id', 'name', 'phone', 'email']),
        ]);
    }

    /**
     * Danh sách menu đã lưu của user (từ snapshot).
     * Bao gồm: menu do user tạo + menu của quán user đã đặt đơn.
     *
     * GET /api/user/saved-menus
     */
    public function savedMenus(Request $request): JsonResponse
    {
        $saved = UserSavedMenu::where('user_id', $request->user()->id)
            ->orderByRaw("CASE source WHEN 'created' THEN 0 ELSE 1 END")
            ->orderBy('last_synced_at', 'desc')
            ->get()
            ->map(fn ($s) => [
                'saved_menu_id'  => $s->id,
                'menu_id'        => $s->menu_id,
                'name'           => $s->snapshot_name,
                'description'    => $s->snapshot_description,
                'phone'          => $s->snapshot_phone,
                'address'        => $s->snapshot_address,
                'items'          => $s->snapshot_items ?? [],
                'items_count'    => count($s->snapshot_items ?? []),
                'source'         => $s->source,
                'last_synced_at' => $s->last_synced_at,
                'created_at'     => $s->created_at,
            ]);

        return response()->json($saved);
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMenuRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\UserSavedMenu;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    /**
     * Danh sách menus – hỗ trợ tìm kiếm theo tên, lọc menu không tạm thời.
     *
     * GET /api/menus
     */
    public function index(Request $request): JsonResponse
    {
        $query = Menu::withCount('items')
            ->where('is_temp', false);

        // Tìm kiếm theo tên quán
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $menus = $query->latest()->paginate(12);

        return response()->json($menus);
    }

    /**
     * Tạo menu mới (kèm danh sách món ăn nếu có).
     *
     * POST /api/menus
     */
    public function store(StoreMenuRequest $request): JsonResponse
    {
        $menu = Menu::create($request->safe()->except('items'));

        // Tạo các món ăn đính kèm nếu có
        if ($request->has('items')) {
            $items = collect($request->items)->map(fn ($item) => new MenuItem([
                'name'      => $item['name'],
                'price'     => $item['price'],
                'image_url' => $item['image_url'] ?? null,
            ]));

            $menu->items()->saveMany($items);
        }

        // Tự động lưu snapshot menu vào thư viện của Host vừa tạo
        if ($request->user()) {
            UserSavedMenu::upsertFromMenu(
                $request->user()->id,
                $menu->load('items'),
                'created'
            );
        }

        return response()->json(
            $menu->load('items'),
            201
        );
    }

    /**
     * Chi tiết một menu kèm toàn bộ danh sách món ăn.
     *
     * GET /api/menus/{id}
     */
    public function show(int $id): JsonResponse
    {
        $menu = Menu::with('items')->findOrFail($id);

        return response()->json($menu);
    }

    /**
     * Cập nhật thông tin menu (không bao gồm items – dùng MenuItemController).
     *
     * PUT /api/menus/{id}
     */
    public function update(StoreMenuRequest $request, int $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->update($request->safe()->except('items'));

        return response()->json($menu->load('items'));
    }

    /**
     * Xóa menu (cascade xóa menu_items theo migration).
     *
     * DELETE /api/menus/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $menu = Menu::findOrFail($id);
        $menu->delete();

        return response()->json(['message' => 'Menu đã được xóa thành công.']);
    }
}

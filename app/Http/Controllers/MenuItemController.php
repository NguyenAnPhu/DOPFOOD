<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Thêm món ăn mới vào một menu.
     *
     * POST /api/menus/{menuId}/items
     */
    public function store(Request $request, int $menuId): JsonResponse
    {
        $menu = Menu::findOrFail($menuId);

        $validated = $request->validate([
            'name'      => ['required', 'string', 'max:150'],
            'price'     => ['required', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ]);

        $item = $menu->items()->create($validated);

        return response()->json($item, 201);
    }

    /**
     * Cập nhật thông tin món ăn.
     *
     * PUT /api/menus/{menuId}/items/{id}
     */
    public function update(Request $request, int $menuId, int $id): JsonResponse
    {
        // Đảm bảo item thuộc đúng menu
        $item = MenuItem::where('menu_id', $menuId)->findOrFail($id);

        $validated = $request->validate([
            'name'      => ['sometimes', 'string', 'max:150'],
            'price'     => ['sometimes', 'numeric', 'min:0'],
            'image_url' => ['nullable', 'string', 'max:255'],
        ]);

        $item->update($validated);

        return response()->json($item);
    }

    /**
     * Xóa món ăn khỏi menu.
     *
     * DELETE /api/menus/{menuId}/items/{id}
     */
    public function destroy(int $menuId, int $id): JsonResponse
    {
        $item = MenuItem::where('menu_id', $menuId)->findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Món ăn đã được xóa thành công.']);
    }
}

<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\UserSavedMenu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * Seed menus và menu_items với dữ liệu mẫu thực tế (nhà hàng Việt Nam).
     * Đồng thời seed user_saved_menus cho Host & Guest mẫu.
     */
    public function run(): void
    {
        $menus = [
            [
                'name'        => 'Bún bò Huế Mụ Rớt',
                'description' => 'Quán bún bò Huế truyền thống, nước lèo đậm đà, thịt bắp mềm.',
                'phone'       => '0905123456',
                'address'     => '12 Trần Phú, Hải Châu, Đà Nẵng',
                'is_temp'     => false,
                'items'       => [
                    ['name' => 'Bún bò đặc biệt',       'price' => 55000,  'image_url' => null],
                    ['name' => 'Bún bò thường',          'price' => 45000,  'image_url' => null],
                    ['name' => 'Bún giò heo',            'price' => 50000,  'image_url' => null],
                    ['name' => 'Bún riêu cua',           'price' => 50000,  'image_url' => null],
                    ['name' => 'Chả giò chiên (5 cái)',  'price' => 25000,  'image_url' => null],
                    ['name' => 'Trà đá',                 'price' => 5000,   'image_url' => null],
                    ['name' => 'Nước ngọt lon',          'price' => 12000,  'image_url' => null],
                ],
            ],
            [
                'name'        => 'Cơm văn phòng Bà Năm',
                'description' => 'Cơm bình dân văn phòng, đủ món, giao nhanh trong 30 phút.',
                'phone'       => '0938765432',
                'address'     => '45 Lý Thường Kiệt, Quận 10, TP.HCM',
                'is_temp'     => false,
                'items'       => [
                    ['name' => 'Cơm sườn nướng',             'price' => 45000,  'image_url' => null],
                    ['name' => 'Cơm gà chiên mắm',           'price' => 42000,  'image_url' => null],
                    ['name' => 'Cơm thịt kho trứng',         'price' => 38000,  'image_url' => null],
                    ['name' => 'Cơm cá kho tộ',              'price' => 40000,  'image_url' => null],
                    ['name' => 'Cơm canh chua tôm',          'price' => 43000,  'image_url' => null],
                    ['name' => 'Canh chua (phần lẻ)',         'price' => 15000,  'image_url' => null],
                    ['name' => 'Trứng chiên (phần lẻ)',       'price' => 10000,  'image_url' => null],
                    ['name' => 'Nước chanh muối',             'price' => 15000,  'image_url' => null],
                    ['name' => 'Trà đá',                     'price' => 5000,   'image_url' => null],
                ],
            ],
            [
                'name'        => 'Trà Sữa The Alley',
                'description' => 'Trà sữa cao cấp, trân châu hoàng kim tươi mỗi ngày.',
                'phone'       => '1800888888',
                'address'     => 'Tầng 1, Vincom Center, 72 Lê Thánh Tôn, Q.1, TP.HCM',
                'is_temp'     => false,
                'items'       => [
                    ['name' => 'Trà sữa truyền thống M',     'price' => 55000,  'image_url' => null],
                    ['name' => 'Trà sữa truyền thống L',     'price' => 65000,  'image_url' => null],
                    ['name' => 'Trà sữa matcha M',           'price' => 60000,  'image_url' => null],
                    ['name' => 'Trà sữa matcha L',           'price' => 70000,  'image_url' => null],
                    ['name' => 'Trà đào cam sả M',           'price' => 50000,  'image_url' => null],
                    ['name' => 'Trà đào cam sả L',           'price' => 60000,  'image_url' => null],
                    ['name' => 'Trân châu hoàng kim (thêm)', 'price' => 10000,  'image_url' => null],
                    ['name' => 'Thạch pudding (thêm)',       'price' => 10000,  'image_url' => null],
                ],
            ],
            [
                'name'        => "Pizza 4P's",
                'description' => 'Pizza phong cách Nhật-Ý, sử dụng nguyên liệu tươi nhập khẩu.',
                'phone'       => '02873001004',
                'address'     => '8/15 Lê Thánh Tôn, Bến Nghé, Q.1, TP.HCM',
                'is_temp'     => false,
                'items'       => [
                    ['name' => 'Pizza Margherita (S)',         'price' => 159000, 'image_url' => null],
                    ['name' => 'Pizza Margherita (M)',         'price' => 229000, 'image_url' => null],
                    ['name' => 'Pizza Burrata & Salmon (M)',   'price' => 349000, 'image_url' => null],
                    ['name' => 'Pizza Burrata & Salmon (L)',   'price' => 459000, 'image_url' => null],
                    ['name' => 'Pizza 4 Cheese (M)',           'price' => 279000, 'image_url' => null],
                    ['name' => 'Pasta Carbonara',              'price' => 199000, 'image_url' => null],
                    ['name' => 'Tiramisu',                     'price' => 119000, 'image_url' => null],
                    ['name' => 'Sparkling Water (500ml)',      'price' => 45000,  'image_url' => null],
                ],
            ],
            [
                'name'        => 'Gà rán KFC Delivery',
                'description' => 'Gà rán giòn rụm, đặt online giao tận nơi siêu nhanh.',
                'phone'       => '1800599985',
                'address'     => '1 Phùng Khắc Khoan, Đa Kao, Q.1, TP.HCM',
                'is_temp'     => false,
                'items'       => [
                    ['name' => 'Gà rán 1 miếng',              'price' => 47000,  'image_url' => null],
                    ['name' => 'Gà rán 3 miếng',              'price' => 129000, 'image_url' => null],
                    ['name' => 'Burger Zinger Stacker',        'price' => 59000,  'image_url' => null],
                    ['name' => 'Combo Gà rán + Pepsi',         'price' => 75000,  'image_url' => null],
                    ['name' => 'Khoai tây chiên (M)',          'price' => 32000,  'image_url' => null],
                    ['name' => 'Khoai tây chiên (L)',          'price' => 42000,  'image_url' => null],
                    ['name' => 'Gà popcorn',                  'price' => 39000,  'image_url' => null],
                    ['name' => 'Pepsi (M)',                    'price' => 20000,  'image_url' => null],
                ],
            ],
        ];

        $createdMenus = [];

        foreach ($menus as $menuData) {
            $items = $menuData['items'];
            unset($menuData['items']);

            $menu = Menu::create($menuData);

            foreach ($items as $item) {
                $menu->items()->create($item);
            }

            $createdMenus[] = $menu->load('items');
        }

        // ── Seed user_saved_menus cho tài khoản mẫu ──────────────────────────
        $host   = User::where('email', 'host@dopfood.test')->first();
        $guest1 = User::where('email', 'guest1@dopfood.test')->first();
        $guest2 = User::where('email', 'guest2@dopfood.test')->first();

        foreach ($createdMenus as $idx => $menu) {
            // Host: 3 quán đầu là "tạo", 2 quán cuối là "đặt đơn"
            if ($host) {
                UserSavedMenu::upsertFromMenu($host->id, $menu, $idx < 3 ? 'created' : 'ordered');
            }
            // Guest1: thấy 3 quán đầu (đã đặt đơn)
            if ($guest1 && $idx < 3) {
                UserSavedMenu::upsertFromMenu($guest1->id, $menu, 'ordered');
            }
            // Guest2: thấy 2 quán cuối (đã đặt đơn)
            if ($guest2 && $idx >= 3) {
                UserSavedMenu::upsertFromMenu($guest2->id, $menu, 'ordered');
            }
        }
    }
}

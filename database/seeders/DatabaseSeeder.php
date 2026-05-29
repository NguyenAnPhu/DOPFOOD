<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * Thứ tự chạy (phụ thuộc lẫn nhau):
     *  1. UserSeeder   → Tạo tài khoản Host & Guest mẫu
     *  2. MenuSeeder   → Tạo thư viện menu & món ăn
     *  3. OrderSeeder  → Tạo phiên đặt hàng mẫu (dùng User & Menu từ trên)
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            MenuSeeder::class,
            OrderSeeder::class,
        ]);
    }
}

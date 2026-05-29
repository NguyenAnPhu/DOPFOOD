<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed bảng users với tài khoản mẫu cho dev & testing.
     *
     * Tài khoản:
     *  - host@dopfood.test   / password  → Host đầy đủ thông tin ngân hàng
     *  - guest1@dopfood.test / password  → Guest thông thường
     *  - guest2@dopfood.test / password  → Guest thông thường
     *  - admin@dopfood.test  / password  → Admin / Super user
     */
    public function run(): void
    {
        // Host chính – có đầy đủ thông tin ngân hàng để test VietQR
        User::firstOrCreate(
            ['email' => 'host@dopfood.test'],
            [
                'name'                 => 'Nguyễn Thanh Minh',
                'phone'                => '0912345678',
                'email'                => 'host@dopfood.test',
                'password'             => Hash::make('password'),
                'bank_name'            => 'MBBank',
                'bank_account_number'  => '0912345678',
                'bank_account_name'    => 'NGUYEN THANH MINH',
                'email_verified_at'    => now(),
            ]
        );

        // Guest 1 – Không có thông tin ngân hàng
        User::firstOrCreate(
            ['email' => 'guest1@dopfood.test'],
            [
                'name'              => 'Trần Thị Lan',
                'phone'             => '0923456789',
                'email'             => 'guest1@dopfood.test',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Guest 2
        User::firstOrCreate(
            ['email' => 'guest2@dopfood.test'],
            [
                'name'              => 'Lê Văn Hùng',
                'phone'             => '0934567890',
                'email'             => 'guest2@dopfood.test',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Admin / Super user (để mở rộng sau này)
        User::firstOrCreate(
            ['email' => 'admin@dopfood.test'],
            [
                'name'              => 'Admin DOPFood',
                'phone'             => '0900000000',
                'email'             => 'admin@dopfood.test',
                'password'          => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );
    }
}

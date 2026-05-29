<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Thêm qr_image_url vào bảng orders – snapshot ảnh QR của Host tại thời điểm tạo đơn.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('qr_image_url')->nullable()->after('bank_account_name')
                  ->comment('URL ảnh QR chuyển khoản của Host (snapshot tại thời điểm tạo đơn)');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('qr_image_url');
        });
    }
};

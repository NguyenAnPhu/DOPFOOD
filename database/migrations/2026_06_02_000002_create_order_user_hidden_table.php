<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng order_user_hidden – lưu danh sách đơn hàng mà từng user đã ẩn/xóa
 * khỏi lịch sử CỦA HỌ. Đơn hàng vẫn hiện cho các user khác.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_user_hidden', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->cascadeOnDelete();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            $table->timestamp('created_at')->useCurrent();

            $table->unique(['order_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_user_hidden');
    }
};

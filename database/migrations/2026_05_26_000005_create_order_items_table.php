<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng order_items: Các món ăn được chọn chi tiết trong một phiên.
     * price_at_order lưu snapshot giá tại thời điểm đặt, tránh sai sót khi giá menu thay đổi.
     */
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade')
                  ->comment('Liên kết ngoại tới orders(id)');

            $table->foreignId('participant_id')
                  ->constrained('order_participants')
                  ->onDelete('cascade')
                  ->comment('Liên kết ngoại tới order_participants(id)');

            $table->foreignId('menu_item_id')
                  ->constrained('menu_items')
                  ->onDelete('restrict')
                  ->comment('Liên kết ngoại tới menu_items(id)');

            $table->unsignedInteger('quantity')->default(1)->comment('Số lượng món ăn');

            // Snapshot giá tại thời điểm đặt
            $table->decimal('price_at_order', 12, 2)->comment('Giá món tại thời điểm đặt (snapshot)');

            $table->text('note')->nullable()->comment('Ghi chú chi tiết cho món ăn (VD: Không hành, Nhiều đá)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};

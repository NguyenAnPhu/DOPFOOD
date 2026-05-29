<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng user_saved_menus – lưu snapshot thông tin menu tại thời điểm user
 * lần đầu tiếp xúc (tạo menu hoặc đặt đơn).
 *
 * Khi user gặp lại cùng quán (tham gia đơn trùng menu_id), snapshot sẽ được
 * cập nhật (merge items mới nhất). Nếu không gặp lại → snapshot giữ nguyên.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_saved_menus', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();

            // Giữ menu_id để đối soát khi cùng quán, nhưng nullable
            // đề phòng menu gốc bị xóa (snapshot vẫn còn)
            $table->unsignedBigInteger('menu_id')->nullable();
            $table->foreign('menu_id')
                  ->references('id')
                  ->on('menus')
                  ->nullOnDelete();

            // Snapshot thông tin quán tại thời điểm lưu
            $table->string('snapshot_name');
            $table->text('snapshot_description')->nullable();
            $table->string('snapshot_phone', 20)->nullable();
            $table->string('snapshot_address')->nullable();

            // Snapshot toàn bộ items dưới dạng JSON:
            // [{ "id": 1, "name": "Phở bò", "price": 50000, "image_url": null }, ...]
            $table->json('snapshot_items')->nullable();

            // Nguồn: 'created' = user tự tạo menu, 'ordered' = user đặt đơn từ menu này
            $table->enum('source', ['created', 'ordered'])->default('ordered');

            // Lần cuối được cập nhật (merge) khi tham gia đơn trùng quán
            $table->timestamp('last_synced_at')->nullable();

            $table->timestamps();

            // Mỗi user chỉ có 1 bản ghi per menu_id
            $table->unique(['user_id', 'menu_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_saved_menus');
    }
};

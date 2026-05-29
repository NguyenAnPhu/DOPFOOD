<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng menu_items: Danh sách món ăn chi tiết thuộc một Menu.
     */
    public function up(): void
    {
        Schema::create('menu_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')
                  ->constrained('menus')
                  ->onDelete('cascade')
                  ->comment('Liên kết ngoại tới menus(id)');
            $table->string('name', 150)->comment('Tên món ăn');
            $table->decimal('price', 12, 2)->comment('Giá gốc của món ăn');
            $table->string('image_url', 255)->nullable()->comment('Link hình ảnh mô tả món ăn');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_items');
    }
};

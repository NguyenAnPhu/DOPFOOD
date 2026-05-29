<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng menus: Thư viện thực đơn / quán ăn.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->comment('Tên Menu hoặc Tên Quán ăn');
            $table->text('description')->nullable()->comment('Mô tả ngắn về thực đơn / quán ăn');
            $table->string('phone', 15)->nullable()->comment('SĐT Quán ăn');
            $table->text('address')->nullable()->comment('Địa chỉ Quán ăn');
            $table->boolean('is_temp')->default(false)->comment('true nếu là menu tạm thời, không lưu vĩnh viễn');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};

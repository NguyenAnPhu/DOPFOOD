<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng orders: Quản lý phiên đặt hàng chung (Group Order Session).
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            // Người tạo đơn (Host) – nullable vì Host có thể không đăng nhập
            $table->foreignId('host_id')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null')
                  ->comment('Liên kết tới users(id), NULL nếu Host không đăng nhập');

            // Menu đang được dùng cho phiên này
            $table->foreignId('menu_id')
                  ->constrained('menus')
                  ->onDelete('restrict')
                  ->comment('Liên kết tới menus(id)');

            // Trạng thái phiên đặt hàng
            $table->enum('status', ['ordering', 'locked', 'completed', 'closed'])
                  ->default('ordering')
                  ->comment('ordering: đang chọn | locked: đã khóa | completed: hoàn tất đơn | closed: đã thu đủ tiền');

            // Phương thức chia tiền
            $table->enum('split_type', ['none', 'even', 'individual'])
                  ->default('even')
                  ->comment('none: Host bao | even: chia đều | individual: chia theo món');

            // Phí phát sinh
            $table->decimal('shipping_fee', 12, 2)->default(0)->comment('Phí giao hàng thực tế');
            $table->decimal('tax_amount', 12, 2)->default(0)->comment('Số tiền thuế VAT phát sinh');
            $table->decimal('discount_amount', 12, 2)->default(0)->comment('Số tiền giảm giá được áp dụng');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Tổng số tiền cần thanh toán cho toàn bộ đơn hàng');

            // Link chia sẻ duy nhất
            $table->string('share_link', 100)->unique()->comment('UUID/Hash cho link mời đặt chung');

            // Thông tin ngân hàng Host tại thời điểm tạo đơn (snapshot để tránh thay đổi sau)
            $table->string('bank_name', 50)->nullable()->comment('Lưu thông tin ngân hàng nhận tiền của Host tại thời điểm tạo đơn');
            $table->string('bank_account_number', 30)->nullable()->comment('Số tài khoản ngân hàng của Host');
            $table->string('bank_account_name', 100)->nullable()->comment('Tên chủ tài khoản của Host (in hoa không dấu)');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

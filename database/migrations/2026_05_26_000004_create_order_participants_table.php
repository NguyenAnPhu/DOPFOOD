<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Bảng order_participants: Danh sách thành viên tham gia gom đơn.
     */
    public function up(): void
    {
        Schema::create('order_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignId('order_id')
                  ->constrained('orders')
                  ->onDelete('cascade')
                  ->comment('Liên kết tới orders(id)');

            // Thông tin định danh Guest (tự nhập khi tham gia)
            $table->string('guest_name', 100)->comment('Tên của thành viên tham gia');
            $table->string('guest_phone', 15)->nullable()->comment('Số điện thoại thành viên');

            // Token nhận diện phiên làm việc qua Cookie/Session
            $table->string('session_token', 100)->nullable()->index()->comment('Token định danh cookie trình duyệt');

            // Trạng thái chọn món
            $table->enum('status', ['ordering', 'ready'])
                  ->default('ordering')
                  ->comment('ordering: đang chọn | ready: đã xong, chờ Host chốt');

            // Số tiền phải trả sau khi chia hóa đơn
            $table->decimal('total_share', 12, 2)->default(0)->comment('Số tiền chính xác người này phải thanh toán');

            // Trạng thái thanh toán
            $table->enum('payment_status', ['pending', 'submitted', 'approved'])
                  ->default('pending')
                  ->comment('pending: chờ thanh toán | submitted: đã chuyển & gửi ảnh | approved: Host đã xác nhận');

            // Ảnh bằng chứng thanh toán do Guest upload
            $table->string('payment_evidence_url', 255)->nullable()->comment('Đường dẫn ảnh bill chuyển khoản');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_participants');
    }
};

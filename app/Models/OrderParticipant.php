<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderParticipant extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong database.
     */
    protected $table = 'order_participants';

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'order_id',
        'user_id',
        'guest_name',
        'guest_phone',
        'session_token',
        'status',
        'total_share',
        'payment_status',
        'payment_evidence_url',
    ];

    /**
     * Các kiểu dữ liệu cần cast.
     */
    protected $casts = [
        'total_share' => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Đơn hàng mà participant này tham gia.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * User đã đăng nhập liên kết với participant này (nullable).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Các món ăn participant này đã chọn.
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'participant_id');
    }
}

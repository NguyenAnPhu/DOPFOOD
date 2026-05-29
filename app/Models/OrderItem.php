<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong database.
     */
    protected $table = 'order_items';

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'order_id',
        'participant_id',
        'menu_item_id',
        'quantity',
        'price_at_order',
        'note',
    ];

    /**
     * Các kiểu dữ liệu cần cast.
     */
    protected $casts = [
        'price_at_order' => 'decimal:2',
        'quantity'       => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Đơn hàng chứa item này.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Participant đã chọn item này.
     */
    public function participant(): BelongsTo
    {
        return $this->belongsTo(OrderParticipant::class, 'participant_id');
    }

    /**
     * Món ăn gốc trong menu.
     */
    public function menuItem(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'menu_item_id');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Tổng tiền của item này (price_at_order × quantity).
     */
    public function getSubtotalAttribute(): float
    {
        return $this->price_at_order * $this->quantity;
    }
}

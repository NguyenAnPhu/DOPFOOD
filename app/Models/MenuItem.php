<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    /**
     * Tên bảng trong database.
     */
    protected $table = 'menu_items';

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'menu_id',
        'name',
        'price',
        'image_url',
    ];

    /**
     * Các kiểu dữ liệu cần cast.
     */
    protected $casts = [
        'price' => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Menu chứa món ăn này.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Các dòng order_items liên quan đến món ăn này.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'menu_item_id');
    }
}

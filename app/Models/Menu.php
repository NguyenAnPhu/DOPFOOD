<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'name',
        'description',
        'phone',
        'address',
        'is_temp',
    ];

    /**
     * Các kiểu dữ liệu cần cast.
     */
    protected $casts = [
        'is_temp' => 'boolean',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Danh sách món ăn thuộc menu này.
     */
    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'menu_id');
    }

    /**
     * Các đơn hàng sử dụng menu này.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'menu_id');
    }
}

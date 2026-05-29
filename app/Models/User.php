<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'qr_image_url',
    ];

    /**
     * Các trường ẩn khi serialize.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Các kiểu dữ liệu cần cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Các đơn hàng mà User này là Host.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'host_id');
    }
}

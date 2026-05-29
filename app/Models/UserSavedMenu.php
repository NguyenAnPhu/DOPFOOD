<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserSavedMenu extends Model
{
    protected $table = 'user_saved_menus';

    protected $fillable = [
        'user_id',
        'menu_id',
        'snapshot_name',
        'snapshot_description',
        'snapshot_phone',
        'snapshot_address',
        'snapshot_items',
        'source',
        'last_synced_at',
    ];

    protected $casts = [
        'snapshot_items' => 'array',
        'last_synced_at' => 'datetime',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Menu gốc (có thể null nếu đã bị xóa) */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }

    // -------------------------------------------------------------------------
    // Static Helpers
    // -------------------------------------------------------------------------

    /**
     * Tạo hoặc cập nhật (upsert) snapshot cho user + menu.
     *
     * Nếu chưa có → tạo mới với toàn bộ items hiện tại.
     * Nếu đã có (trùng menu_id) → merge/update items mới nhất
     * (khi tham gia đơn trùng quán), cập nhật last_synced_at.
     *
     * @param  int    $userId
     * @param  \App\Models\Menu  $menu   Menu đã eager-load 'items'
     * @param  string $source  'created' | 'ordered'
     */
    public static function upsertFromMenu(int $userId, Menu $menu, string $source): self
    {
        // Build snapshot items từ menu hiện tại
        $items = $menu->items->map(fn ($item) => [
            'id'        => $item->id,
            'name'      => $item->name,
            'price'     => (float) $item->price,
            'image_url' => $item->image_url,
        ])->values()->all();

        $existing = self::where('user_id', $userId)
                        ->where('menu_id', $menu->id)
                        ->first();

            $updateData = [
                'last_synced_at'       => now(),
                // Nâng source lên 'created' nếu thích hợp
                'source'               => $source === 'created' ? 'created' : $existing->source,
                'snapshot_name'        => $menu->name,
                'snapshot_description' => $menu->description,
                'snapshot_phone'       => $menu->phone,
                'snapshot_address'     => $menu->address,
                'snapshot_items'       => $items, // Luôn cập nhật đầy đủ items mới nhất tại thời điểm đặt đơn
            ];

            $existing->update($updateData);
            return $existing->fresh();
        }

        // Chưa có → tạo mới
        return self::create([
            'user_id'              => $userId,
            'menu_id'              => $menu->id,
            'snapshot_name'        => $menu->name,
            'snapshot_description' => $menu->description,
            'snapshot_phone'       => $menu->phone,
            'snapshot_address'     => $menu->address,
            'snapshot_items'       => $items,
            'source'               => $source,
            'last_synced_at'       => now(),
        ]);
    }
}

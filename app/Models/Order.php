<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    /**
     * Các trường có thể gán hàng loạt.
     */
    protected $fillable = [
        'host_id',
        'menu_id',
        'status',
        'split_type',
        'shipping_fee',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'share_link',
        'bank_name',
        'bank_account_number',
        'bank_account_name',
        'qr_image_url',
    ];

    /**
     * Các kiểu dữ liệu cần cast.
     */
    protected $casts = [
        'shipping_fee'     => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'total_amount'     => 'decimal:2',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    /**
     * Host (User) tạo đơn hàng này.
     */
    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    /**
     * Menu được sử dụng trong đơn hàng này.
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }

    /**
     * Danh sách thành viên tham gia đơn hàng.
     */
    public function participants(): HasMany
    {
        return $this->hasMany(OrderParticipant::class, 'order_id');
    }

    /**
     * Tất cả các order items trong phiên này (tổng hợp tất cả participants).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // -------------------------------------------------------------------------
    // Business Logic
    // -------------------------------------------------------------------------

    /**
     * Tính toán và cập nhật số tiền mỗi participant phải trả
     * dựa theo split_type, shipping_fee, tax_amount, discount_amount.
     *
     * @return void
     */
    public function recalculateShares(): void
    {
        $participants = $this->participants()->with('items')->get();

        if ($participants->isEmpty()) {
            return;
        }

        // Tổng tiền món ăn của toàn đơn (trước phí)
        $subtotal = $this->items()->sum(\DB::raw('price_at_order * quantity'));

        // Tổng bill cuối = subtotal + ship + tax - discount
        $grandTotal = $subtotal + $this->shipping_fee + $this->tax_amount - $this->discount_amount;
        $grandTotal = max(0, $grandTotal);

        // Cập nhật total_amount của đơn
        $this->total_amount = $grandTotal;
        $this->save();

        $count = $participants->count();

        foreach ($participants as $participant) {
            $share = match ($this->split_type) {
                // Host bao toàn bộ, guest trả 0
                'none' => 0,

                // Chia đều tổng bill (sau phí) cho mọi người
                'even' => $count > 0 ? round($grandTotal / $count, 0) : 0,

                // Chia theo món: tiền món của ai nấy trả
                // Phí ship, tax, discount phân bổ theo tỷ lệ giá trị món
                'individual' => $this->calculateIndividualShare($participant, $subtotal),

                default => 0,
            };

            $participant->total_share = $share;
            $participant->save();
        }
    }

    /**
     * Tính phần chia tiền theo tỷ lệ món ăn cho một participant.
     *
     * @param  \App\Models\OrderParticipant  $participant
     * @param  float  $subtotal  Tổng tiền món toàn đơn
     * @return float
     */
    private function calculateIndividualShare(OrderParticipant $participant, float $subtotal): float
    {
        // Tổng tiền món riêng của participant này
        $personalSubtotal = $participant->items()
            ->sum(\DB::raw('price_at_order * quantity'));

        if ($subtotal <= 0) {
            return 0;
        }

        // Tỷ lệ tiền món của người này trong tổng
        $ratio = $personalSubtotal / $subtotal;

        // Phần phí/giảm giá phân bổ theo tỷ lệ
        $feeShare = ($this->shipping_fee + $this->tax_amount - $this->discount_amount) * $ratio;

        return round($personalSubtotal + $feeShare, 0);
    }
}

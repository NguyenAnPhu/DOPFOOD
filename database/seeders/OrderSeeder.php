<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderParticipant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    /**
     * Seed orders, order_participants, order_items với dữ liệu mẫu thực tế.
     * Tạo 3 phiên đặt hàng: đang chọn, đã chốt (completed), đã đóng (closed).
     */
    public function run(): void
    {
        // Lấy Host đã tạo từ UserSeeder
        $host = User::where('email', 'host@dopfood.test')->first();

        if (! $host) {
            $this->command->warn('Host user not found. Please run UserSeeder first.');
            return;
        }

        // Lấy menu để dùng
        $comVanPhong   = Menu::where('name', 'Cơm văn phòng Bà Năm')->first();
        $traSua        = Menu::where('name', 'Trà Sữa The Alley')->first();
        $bunBo         = Menu::where('name', 'Bún bò Huế Mụ Rớt')->first();

        if (! $comVanPhong || ! $traSua || ! $bunBo) {
            $this->command->warn('Menus not found. Please run MenuSeeder first.');
            return;
        }

        // =====================================================================
        // PHIÊN 1: Đang diễn ra (ordering) – Cơm văn phòng trưa nay
        // =====================================================================
        $order1 = Order::create([
            'host_id'             => $host->id,
            'menu_id'             => $comVanPhong->id,
            'status'              => 'ordering',
            'split_type'          => 'individual',
            'shipping_fee'        => 20000,
            'tax_amount'          => 0,
            'discount_amount'     => 0,
            'total_amount'        => 0,
            'share_link'          => Str::random(8),
            'bank_name'           => $host->bank_name,
            'bank_account_number' => $host->bank_account_number,
            'bank_account_name'   => $host->bank_account_name,
        ]);

        // Host (Minh) đã join đơn của mình
        $p1_host = OrderParticipant::create([
            'order_id'      => $order1->id,
            'guest_name'    => 'Minh (Host)',
            'guest_phone'   => '0912345678',
            'session_token' => Str::random(40),
            'status'        => 'ready',
            'total_share'   => 0,
            'payment_status' => 'pending',
        ]);
        $comSuon = $comVanPhong->items()->where('name', 'Cơm sườn nướng')->first();
        $nuocChanh = $comVanPhong->items()->where('name', 'Nước chanh muối')->first();
        if ($comSuon) {
            OrderItem::create([
                'order_id'       => $order1->id,
                'participant_id' => $p1_host->id,
                'menu_item_id'   => $comSuon->id,
                'quantity'       => 1,
                'price_at_order' => $comSuon->price,
                'note'           => 'Cho thêm dưa cải',
            ]);
        }
        if ($nuocChanh) {
            OrderItem::create([
                'order_id'       => $order1->id,
                'participant_id' => $p1_host->id,
                'menu_item_id'   => $nuocChanh->id,
                'quantity'       => 1,
                'price_at_order' => $nuocChanh->price,
                'note'           => null,
            ]);
        }

        // Guest 1 – Lan đang chọn món
        $p1_lan = OrderParticipant::create([
            'order_id'      => $order1->id,
            'guest_name'    => 'Lan',
            'guest_phone'   => '0923456789',
            'session_token' => Str::random(40),
            'status'        => 'ordering',
            'total_share'   => 0,
            'payment_status' => 'pending',
        ]);
        $comGa = $comVanPhong->items()->where('name', 'Cơm gà chiên mắm')->first();
        if ($comGa) {
            OrderItem::create([
                'order_id'       => $order1->id,
                'participant_id' => $p1_lan->id,
                'menu_item_id'   => $comGa->id,
                'quantity'       => 1,
                'price_at_order' => $comGa->price,
                'note'           => 'Không hành',
            ]);
        }

        // Guest 2 – Hùng đã sẵn sàng
        $p1_hung = OrderParticipant::create([
            'order_id'      => $order1->id,
            'guest_name'    => 'Hùng',
            'guest_phone'   => '0934567890',
            'session_token' => Str::random(40),
            'status'        => 'ready',
            'total_share'   => 0,
            'payment_status' => 'pending',
        ]);
        $comCa = $comVanPhong->items()->where('name', 'Cơm cá kho tộ')->first();
        $traDa = $comVanPhong->items()->where('name', 'Trà đá')->first();
        if ($comCa) {
            OrderItem::create([
                'order_id'       => $order1->id,
                'participant_id' => $p1_hung->id,
                'menu_item_id'   => $comCa->id,
                'quantity'       => 1,
                'price_at_order' => $comCa->price,
                'note'           => null,
            ]);
        }
        if ($traDa) {
            OrderItem::create([
                'order_id'       => $order1->id,
                'participant_id' => $p1_hung->id,
                'menu_item_id'   => $traDa->id,
                'quantity'       => 1,
                'price_at_order' => $traDa->price,
                'note'           => null,
            ]);
        }

        // =====================================================================
        // PHIÊN 2: Đã hoàn tất (completed) – Trà sữa chiều hôm qua
        // =====================================================================
        $order2 = Order::create([
            'host_id'             => $host->id,
            'menu_id'             => $traSua->id,
            'status'              => 'completed',
            'split_type'          => 'individual',
            'shipping_fee'        => 15000,
            'tax_amount'          => 0,
            'discount_amount'     => 10000,
            'total_amount'        => 0,
            'share_link'          => Str::random(8),
            'bank_name'           => $host->bank_name,
            'bank_account_number' => $host->bank_account_number,
            'bank_account_name'   => $host->bank_account_name,
        ]);

        $p2_host = OrderParticipant::create([
            'order_id'       => $order2->id,
            'guest_name'     => 'Minh (Host)',
            'guest_phone'    => '0912345678',
            'session_token'  => Str::random(40),
            'status'         => 'ready',
            'total_share'    => 0,
            'payment_status' => 'approved',
        ]);
        $traSuaM = $traSua->items()->where('name', 'Trà sữa truyền thống M')->first();
        if ($traSuaM) {
            OrderItem::create([
                'order_id'       => $order2->id,
                'participant_id' => $p2_host->id,
                'menu_item_id'   => $traSuaM->id,
                'quantity'       => 1,
                'price_at_order' => $traSuaM->price,
                'note'           => 'Ít đường, ít đá',
            ]);
        }

        $p2_thu = OrderParticipant::create([
            'order_id'       => $order2->id,
            'guest_name'     => 'Thu',
            'guest_phone'    => '0945678901',
            'session_token'  => Str::random(40),
            'status'         => 'ready',
            'total_share'    => 0,
            'payment_status' => 'submitted',
            'payment_evidence_url' => null,
        ]);
        $matchaL = $traSua->items()->where('name', 'Trà sữa matcha L')->first();
        $tranChau = $traSua->items()->where('name', 'Trân châu hoàng kim (thêm)')->first();
        if ($matchaL) {
            OrderItem::create([
                'order_id'       => $order2->id,
                'participant_id' => $p2_thu->id,
                'menu_item_id'   => $matchaL->id,
                'quantity'       => 1,
                'price_at_order' => $matchaL->price,
                'note'           => null,
            ]);
        }
        if ($tranChau) {
            OrderItem::create([
                'order_id'       => $order2->id,
                'participant_id' => $p2_thu->id,
                'menu_item_id'   => $tranChau->id,
                'quantity'       => 1,
                'price_at_order' => $tranChau->price,
                'note'           => null,
            ]);
        }

        $p2_nam = OrderParticipant::create([
            'order_id'       => $order2->id,
            'guest_name'     => 'Nam',
            'guest_phone'    => '0956789012',
            'session_token'  => Str::random(40),
            'status'         => 'ready',
            'total_share'    => 0,
            'payment_status' => 'pending',
        ]);
        $traDao = $traSua->items()->where('name', 'Trà đào cam sả M')->first();
        if ($traDao) {
            OrderItem::create([
                'order_id'       => $order2->id,
                'participant_id' => $p2_nam->id,
                'menu_item_id'   => $traDao->id,
                'quantity'       => 2,
                'price_at_order' => $traDao->price,
                'note'           => 'Cho 2 ly, 1 không đá',
            ]);
        }

        // Tính lại chia tiền cho order2
        $order2->recalculateShares();
        $order2->refresh();

        // =====================================================================
        // PHIÊN 3: Đã đóng (closed) – Bún bò tuần trước
        // =====================================================================
        $order3 = Order::create([
            'host_id'             => $host->id,
            'menu_id'             => $bunBo->id,
            'status'              => 'closed',
            'split_type'          => 'even',
            'shipping_fee'        => 0,
            'tax_amount'          => 0,
            'discount_amount'     => 0,
            'total_amount'        => 0,
            'share_link'          => Str::random(8),
            'bank_name'           => $host->bank_name,
            'bank_account_number' => $host->bank_account_number,
            'bank_account_name'   => $host->bank_account_name,
        ]);

        $p3_minh = OrderParticipant::create([
            'order_id'       => $order3->id,
            'guest_name'     => 'Minh (Host)',
            'guest_phone'    => '0912345678',
            'session_token'  => Str::random(40),
            'status'         => 'ready',
            'total_share'    => 0,
            'payment_status' => 'approved',
        ]);
        $bunBoDacBiet = $bunBo->items()->where('name', 'Bún bò đặc biệt')->first();
        if ($bunBoDacBiet) {
            OrderItem::create([
                'order_id'       => $order3->id,
                'participant_id' => $p3_minh->id,
                'menu_item_id'   => $bunBoDacBiet->id,
                'quantity'       => 1,
                'price_at_order' => $bunBoDacBiet->price,
                'note'           => null,
            ]);
        }

        $p3_linh = OrderParticipant::create([
            'order_id'       => $order3->id,
            'guest_name'     => 'Linh',
            'guest_phone'    => '0967890123',
            'session_token'  => Str::random(40),
            'status'         => 'ready',
            'total_share'    => 0,
            'payment_status' => 'approved',
        ]);
        $bunGio = $bunBo->items()->where('name', 'Bún giò heo')->first();
        if ($bunGio) {
            OrderItem::create([
                'order_id'       => $order3->id,
                'participant_id' => $p3_linh->id,
                'menu_item_id'   => $bunGio->id,
                'quantity'       => 1,
                'price_at_order' => $bunGio->price,
                'note'           => 'Thêm giò',
            ]);
        }

        $p3_khanh = OrderParticipant::create([
            'order_id'       => $order3->id,
            'guest_name'     => 'Khánh',
            'guest_phone'    => '0978901234',
            'session_token'  => Str::random(40),
            'status'         => 'ready',
            'total_share'    => 0,
            'payment_status' => 'approved',
        ]);
        $bunRieu = $bunBo->items()->where('name', 'Bún riêu cua')->first();
        if ($bunRieu) {
            OrderItem::create([
                'order_id'       => $order3->id,
                'participant_id' => $p3_khanh->id,
                'menu_item_id'   => $bunRieu->id,
                'quantity'       => 1,
                'price_at_order' => $bunRieu->price,
                'note'           => 'Không rau',
            ]);
        }

        // Tính lại chia tiền cho order3
        $order3->recalculateShares();
        $order3->refresh();
    }
}

<?php

namespace Tests\Unit;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderParticipant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderShareTest extends TestCase
{
    use RefreshDatabase;

    private function addParticipant(Order $order, string $name, ?int $userId, array $items): OrderParticipant
    {
        $p = OrderParticipant::create([
            'order_id'      => $order->id,
            'user_id'       => $userId,
            'guest_name'    => $name,
            'guest_phone'   => null,
            'session_token' => 'tok-' . str()->random(10),
            'status'        => 'ready',
            'total_share'   => 0,
            'payment_status' => 'pending',
        ]);

        foreach ($items as [$menuItemId, $qty, $price]) {
            OrderItem::create([
                'order_id'       => $order->id,
                'participant_id' => $p->id,
                'menu_item_id'   => $menuItemId,
                'quantity'       => $qty,
                'price_at_order' => $price,
            ]);
        }

        return $p;
    }

    public function test_individual_split_shares_fees_and_discount_among_all_members(): void
    {
        $host = User::factory()->create(['name' => 'Host User']);
        $menu = Menu::create(['name' => 'Test Menu']);
        $comSuon = MenuItem::create(['menu_id' => $menu->id, 'name' => 'Cơm sườn', 'price' => 60000]);
        $comGa   = MenuItem::create(['menu_id' => $menu->id, 'name' => 'Cơm gà', 'price' => 52000]);

        $order = Order::create([
            'host_id'         => $host->id,
            'menu_id'         => $menu->id,
            'status'          => 'locked',
            'split_type'      => 'individual',
            'shipping_fee'    => 30000,
            'tax_amount'      => 10000,
            'discount_amount' => 20000,
            'total_amount'    => 0,
            'share_link'      => 'share001',
        ]);

        $this->addParticipant($order, 'Minh (Host)', $host->id, [[$comSuon->id, 1, 60000]]);
        $this->addParticipant($order, 'Khách', null, [[$comGa->id, 1, 52000]]);

        $order->recalculateShares();
        $order->refresh();

        $hostShare = $order->participants()->where('user_id', $host->id)->first()->total_share;
        $guestShare = $order->participants()->whereNull('user_id')->first()->total_share;

        // Phí ròng chia theo tỷ lệ món: (30.000 + 10.000 − 20.000) × (60/112) = 10.714
        $this->assertEquals(70714, (int) $hostShare);
        // (30.000 + 10.000 − 20.000) × (52/112) = 9.286
        $this->assertEquals(61286, (int) $guestShare);

        // Tổng các phần = tổng bill
        $sum = $order->participants()->sum('total_share');
        $this->assertEquals($order->total_amount, $sum);
        $this->assertEquals(132000, (int) $order->total_amount);
    }

    public function test_individual_split_shipping_only_is_shared_proportionally(): void
    {
        $host = User::factory()->create(['name' => 'Host User']);
        $menu = Menu::create(['name' => 'Test Menu']);
        $itemA = MenuItem::create(['menu_id' => $menu->id, 'name' => 'A', 'price' => 50000]);
        $itemB = MenuItem::create(['menu_id' => $menu->id, 'name' => 'B', 'price' => 30000]);

        $order = Order::create([
            'host_id'         => $host->id,
            'menu_id'         => $menu->id,
            'status'          => 'locked',
            'split_type'      => 'individual',
            'shipping_fee'    => 20000,
            'tax_amount'      => 0,
            'discount_amount' => 0,
            'total_amount'    => 0,
            'share_link'      => 'share002',
        ]);

        $this->addParticipant($order, 'Host', $host->id, [[$itemA->id, 1, 50000]]);
        $this->addParticipant($order, 'Guest', null, [[$itemB->id, 1, 30000]]);

        $order->recalculateShares();

        $hostShare = $order->participants()->where('user_id', $host->id)->first()->total_share;
        $guestShare = $order->participants()->whereNull('user_id')->first()->total_share;

        // Host: 50.000 + 20.000 × (50/80) = 62.500
        $this->assertEquals(62500, (int) $hostShare);
        // Guest: 30.000 + 20.000 × (30/80) = 37.500
        $this->assertEquals(37500, (int) $guestShare);
        $this->assertEquals(100000, (int) $order->total_amount);
    }

    public function test_individual_split_discount_is_shared_with_host(): void
    {
        $host = User::factory()->create(['name' => 'Host User']);
        $menu = Menu::create(['name' => 'Test Menu']);
        $itemA = MenuItem::create(['menu_id' => $menu->id, 'name' => 'A', 'price' => 50000]);
        $itemB = MenuItem::create(['menu_id' => $menu->id, 'name' => 'B', 'price' => 30000]);

        $order = Order::create([
            'host_id'         => $host->id,
            'menu_id'         => $menu->id,
            'status'          => 'locked',
            'split_type'      => 'individual',
            'shipping_fee'    => 0,
            'tax_amount'      => 0,
            'discount_amount' => 10000,
            'total_amount'    => 0,
            'share_link'      => 'share003',
        ]);

        $this->addParticipant($order, 'Host', $host->id, [[$itemA->id, 1, 50000]]);
        $this->addParticipant($order, 'Guest', null, [[$itemB->id, 1, 30000]]);

        $order->recalculateShares();

        $hostShare = $order->participants()->where('user_id', $host->id)->first()->total_share;
        $guestShare = $order->participants()->whereNull('user_id')->first()->total_share;

        // Host cũng được hưởng giảm giá theo tỷ lệ: 50.000 − 10.000 × (50/80) = 43.750
        $this->assertEquals(43750, (int) $hostShare);
        // Guest: 30.000 − 10.000 × (30/80) = 26.250
        $this->assertEquals(26250, (int) $guestShare);
        $this->assertEquals(70000, (int) $order->total_amount);
    }

    public function test_even_split_divides_grand_total_equally(): void
    {
        $host = User::factory()->create(['name' => 'Host User']);
        $menu = Menu::create(['name' => 'Test Menu']);
        $itemA = MenuItem::create(['menu_id' => $menu->id, 'name' => 'A', 'price' => 50000]);
        $itemB = MenuItem::create(['menu_id' => $menu->id, 'name' => 'B', 'price' => 30000]);

        $order = Order::create([
            'host_id'         => $host->id,
            'menu_id'         => $menu->id,
            'status'          => 'locked',
            'split_type'      => 'even',
            'shipping_fee'    => 20000,
            'tax_amount'      => 0,
            'discount_amount' => 10000,
            'total_amount'    => 0,
            'share_link'      => 'share004',
        ]);

        $this->addParticipant($order, 'Host', $host->id, [[$itemA->id, 1, 50000]]);
        $this->addParticipant($order, 'Guest', null, [[$itemB->id, 1, 30000]]);

        $order->recalculateShares();

        $shares = $order->participants()->pluck('total_share')->map(fn ($s) => (int) $s);

        // (50.000 + 30.000 + 20.000 − 10.000) / 2 = 45.000 mỗi người
        $this->assertTrue($shares->every(fn ($s) => $s === 45000));
        $this->assertEquals(90000, (int) $order->total_amount);
    }
}
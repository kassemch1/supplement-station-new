<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Subscription;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

// The order lists, the dashboard numbers and the subscriber counts
class OrdersAndStatsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function order(string $createdAt, float $total, string $status = 'delivered'): Order
    {
        return Order::factory()->create(['created_at' => $createdAt, 'total_amount' => $total, 'subtotal_amount' => $total, 'status' => $status]);
    }

    // ---------- orders ----------

    public function test_each_list_shows_its_status_in_store_time(): void
    {
        $pending = $this->order('2026-07-01 10:00:00', 10, 'pending');
        $delivered = $this->order('2026-07-02 10:00:00', 20);
        $cancelled = $this->order('2026-07-03 10:00:00', 30, 'cancelled');

        $this->get('/admin/orders/pending')->assertOk()->assertSee('Pending Orders')->assertSee('Jul 01, 2026 - 13:00')->assertDontSee('Jul 02, 2026');
        $this->get('/admin/orders/delivered')->assertOk()->assertSee('Delivered Orders')->assertSee('#' . $delivered->id, false)->assertDontSee('Jul 01, 2026');
        $this->get('/admin/orders/cancelled')->assertOk()->assertSee('Cancelled Orders')->assertSee('#' . $cancelled->id, false)
            ->assertSee('action="' . route('admin.orders.cancelled') . '"', false);    // its sort form stays on this list
        $this->get('/admin/orders/pending?sort=sideways')->assertOk();
    }

    public function test_an_order_without_a_coupon_shows_no_discount(): void
    {
        $this->order('2026-07-01 10:00:00', 10, 'pending');                     // saved as 0.00
        $withCoupon = $this->order('2026-07-02 10:00:00', 45, 'pending');
        $withCoupon->update(['coupon_code' => 'SAVE5', 'coupon_discount_amount' => 5]);

        $this->get('/admin/orders/pending')->assertOk()
            ->assertDontSee('-$0.00')
            ->assertSee('-$5.00')
            // a phone hides the empty coupon cells of the card
            ->assertSee('ad-orders__discount is-empty', false);
    }

    public function test_status_changes_and_deleting(): void
    {
        $order = $this->order('2026-07-01 10:00:00', 10, 'pending');

        $this->post("/admin/orders/{$order->id}/update-status", ['status' => 'delivered'])->assertRedirect();
        $this->assertSame('delivered', $order->fresh()->status);
        $this->postJson("/admin/orders/{$order->id}/update-status", ['status' => 'shipped-to-mars'])->assertStatus(422);
        $this->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Delivered');

        $this->delete("/admin/orders/{$order->id}")->assertRedirect();
        $this->assertModelMissing($order);
    }

    // ---------- dashboard ----------

    public function test_dashboard_today_week_and_month_are_beirut_periods_and_skip_cancelled_orders(): void
    {
        Carbon::setTestNow('2026-10-26 08:00:00');           // Monday 11:00 in Beirut
        $this->order('2026-10-25 22:30:00', 77, 'pending');  // 00:30 Beirut today
        $this->order('2026-10-25 21:30:00', 5);              // 23:30 Beirut yesterday (Sunday, last week)
        $this->order('2026-10-26 07:00:00', 1000, 'cancelled');
        $this->order('2026-09-15 10:00:00', 40);             // last month

        $response = $this->get('/admin')->assertOk();
        $view = fn ($key) => $response->viewData($key);

        $this->assertSame(1, $view('todayOrders'));
        $this->assertEquals(77, $view('todayRevenue'));
        $this->assertEquals(77, $view('thisWeekRevenue'));
        $this->assertEquals(5, $view('lastWeekRevenue'));
        $this->assertEquals(82, $view('thisMonthTotal'));
        $this->assertEquals(40, $view('lastMonthTotal'));
        $this->assertEquals(122, $view('totalAmount'), 'cancelled orders are not sales');
        $this->assertEquals(105, $view('monthlyGrowthPercentage'));
        $this->assertEquals(42, $view('monthlyGrowth'));
        $this->assertSame(1, $view('pendingCount'));
        $this->assertSame(2, $view('deliveredCount'));
        $this->assertEquals(25, $view('pendingPercentage'));
    }

    public function test_the_chart_has_every_month_in_store_time(): void
    {
        Carbon::setTestNow('2026-10-26 08:00:00');
        $this->order('2026-06-30 22:30:00', 10);   // 01:30 on July 1st in Beirut
        $this->order('2026-07-15 10:00:00', 5);
        $this->order('2026-09-02 10:00:00', 7);
        $this->order('2026-09-03 10:00:00', 100, 'cancelled');

        $response = $this->get('/admin')->assertOk();

        $this->assertSame(['July 2026', 'August 2026', 'September 2026', 'October 2026'], $response->viewData('months'));
        $this->assertEquals([15, 0, 7, 0], $response->viewData('totals'));
        $this->assertStringContainsString('chart.js@4.4.1', $response->getContent());
    }

    public function test_growth_from_nothing_and_an_empty_shop(): void
    {
        Carbon::setTestNow('2026-10-26 08:00:00');
        $response = $this->get('/admin')->assertOk();
        $this->assertSame([], $response->viewData('months'));
        $this->assertEquals(0, $response->viewData('monthlyGrowthPercentage'));

        $this->order('2026-10-10 10:00:00', 50);
        $response = $this->get('/admin');
        $this->assertEquals(100, $response->viewData('monthlyGrowthPercentage'));
        $this->assertEquals(50, $response->viewData('monthlyGrowth'), 'the amount is shown even without last month');
    }

    public function test_dashboard_query_count_does_not_grow_with_the_orders(): void
    {
        $count = function (string $url) {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->get($url)->assertOk();
            DB::disableQueryLog();

            return count(DB::getQueryLog());
        };

        Order::factory()->count(3)->create();
        $few = [$count('/admin'), $count('/admin?range=all')];
        Order::factory()->count(40)->create();
        $this->assertSame($few, [$count('/admin'), $count('/admin?range=all')]);
        $this->assertLessThanOrEqual(12, $few[1]);
    }

    // ---------- dashboard filter ----------

    private function line(Order $order, string $name, int $qty, float $price): void
    {
        DB::table('order_items')->insert(['order_id' => $order->id, 'product_id' => null, 'product_name' => $name, 'quantity' => $qty, 'price' => $price,
            'created_at' => $order->created_at, 'updated_at' => $order->created_at]);
    }

    private function ship(Order $order, string $city): void
    {
        DB::table('shipping_details')->insert(['order_id' => $order->id, 'name' => 'Client ' . $order->id, 'phone' => '03000000', 'address' => 'Street', 'city' => $city,
            'created_at' => $order->created_at, 'updated_at' => $order->created_at]);
    }

    public function test_the_period_filter_compares_with_the_period_before(): void
    {
        Carbon::setTestNow('2026-10-26 08:00:00');                  // Oct 26, 11:00 Beirut
        $a = $this->order('2026-10-20 10:00:00', 100);               // in the last 7 days
        $b = $this->order('2026-10-25 22:30:00', 50, 'pending');     // Oct 26 00:30 Beirut, in
        $this->order('2026-10-21 10:00:00', 999, 'cancelled');      // in, not a sale
        $this->order('2026-10-15 10:00:00', 40);                     // the 7 days before
        $this->order('2026-10-19 20:59:59', 7);                      // Oct 19 23:59:59 Beirut: the 7 days before
        $this->order('2026-09-01 10:00:00', 1000);                   // outside both
        $this->line($a, 'Whey', 2, 30);
        $this->line($a, 'Creatine', 1, 40);
        $this->line($b, 'Creatine', 3, 10);
        $this->ship($a, 'Beirut ');
        $this->ship($b, 'beirut');

        $report = $this->get('/admin?range=7d')->assertOk()->assertSee('7 days')->viewData('report');

        $this->assertSame(['2026-10-20', '2026-10-26'], [$report['from'], $report['to']]);
        $this->assertSame('day', $report['unit']);
        $this->assertCount(7, $report['series']['labels']);
        $this->assertEquals(150, $report['kpis']['revenue']['value']);
        $this->assertEquals(47, $report['kpis']['revenue']['prev']);
        $this->assertSame(2, $report['kpis']['orders']['value']);
        $this->assertEquals(75, $report['kpis']['average']['value']);
        $this->assertSame(6, $report['kpis']['items']['value']);
        $this->assertSame(['pending' => 1, 'delivered' => 1, 'cancelled' => 1], $report['status']);
        $this->assertSame([100.0, 0, 0, 0, 0, 0, 50.0], array_map(fn ($v) => $v ?: 0, $report['series']['revenue']));
        $this->assertSame(['name' => 'Creatine', 'qty' => 4, 'revenue' => 70.0], $report['products'][0]);
        $this->assertSame(1, count($report['cities']), 'city names are trimmed and case-insensitive');
        $this->assertSame(2, $report['cities'][0]['orders']);
        $this->assertSame(1, $report['weekday'][0], 'Oct 26 2026 is a Monday');
    }

    public function test_today_is_hourly_and_custom_and_bad_ranges_are_safe(): void
    {
        Carbon::setTestNow('2026-10-26 08:00:00');
        $this->order('2026-10-26 06:15:00', 20);                     // 08:15 Beirut (summer time ended Oct 25)

        $today = $this->get('/admin?range=today')->viewData('report');
        $this->assertSame('hour', $today['unit']);
        $this->assertCount(24, $today['series']['labels']);
        $this->assertEquals(20, $today['series']['revenue'][8]);

        $custom = $this->get('/admin?range=custom&from=2026-10-30&to=2026-10-01')->assertOk()->viewData('report');
        $this->assertSame(['custom', '2026-10-01', '2026-10-30'], [$custom['range'], $custom['from'], $custom['to']], 'swapped dates are put in order');

        $this->assertSame('30d', $this->get('/admin?range=custom&from=nope')->assertOk()->viewData('report')['range']);
        $this->assertSame('30d', $this->get('/admin?range=forever')->assertOk()->viewData('report')['range']);
        $this->assertSame('month', $this->get('/admin?range=this_year')->assertOk()->viewData('report')['unit']);
        $all = $this->get('/admin?range=all')->assertOk()->viewData('report');
        $this->assertNull($all['compare']);
        $this->assertNull($all['kpis']['revenue']['delta']);
    }

    // ---------- new-order bell ----------

    public function test_pending_summary_feeds_the_bell(): void
    {
        $old = $this->order('2026-07-01 10:00:00', 10, 'pending');
        $this->order('2026-07-02 10:00:00', 20);
        $new = $this->order('2026-07-03 10:00:00', 30.5, 'pending');
        $this->ship($new, 'Tyre');

        $this->getJson('/admin/orders/pending-summary')->assertOk()
            ->assertJson(['pending' => 2, 'latest_id' => $new->id, 'new_orders' => []])
            ->assertJsonPath('latest_pending.0.id', $new->id)
            ->assertJsonPath('latest_pending.0.total', '$30.50')
            ->assertJsonPath('latest_pending.0.city', 'Tyre')
            ->assertJsonPath('latest_pending.1.name', 'Customer')
            ->assertJsonPath('csrf', fn ($token) => is_string($token) && strlen($token) === 40);

        $this->getJson('/admin/orders/pending-summary?after=' . $old->id)
            ->assertJsonCount(2, 'new_orders')
            ->assertJsonPath('new_orders.0.url', route('admin.orders.details', $new->id));
        $this->getJson('/admin/orders/pending-summary?after=' . $new->id)->assertJsonCount(0, 'new_orders');
    }

    public function test_pending_summary_needs_a_login(): void
    {
        auth()->logout();
        $this->getJson('/admin/orders/pending-summary')->assertUnauthorized();
    }

    // ---------- subscribers ----------

    public function test_subscriber_cards_count_everyone_not_just_this_page(): void
    {
        Subscription::factory()->count(22)->create(['created_at' => now()->subDays(60)]);
        Subscription::factory()->count(3)->inactive()->create(['created_at' => now()->subDays(60)]);
        Subscription::factory()->count(2)->create();

        $response = $this->get('/admin/subscribers')->assertOk();

        $this->assertSame(['total' => 27, 'active' => 24, 'inactive' => 3, 'recent' => 2], $response->viewData('stats'));
        $this->assertCount(20, $response->viewData('subscribers'));
        $this->get('/admin/subscribers?status=inactive')->assertOk()->assertViewHas('stats', fn ($stats) => $stats['total'] === 27);
    }

    public function test_subscriber_admin_actions(): void
    {
        $this->post('/admin/subscribers', ['email' => 'new@example.com', 'is_active' => '1'])->assertRedirect('/admin/subscribers');
        $this->post('/admin/subscribers', ['email' => 'new@example.com'])->assertSessionHasErrors('email');
        $subscriber = Subscription::sole();

        $this->put("/admin/subscribers/{$subscriber->id}", ['email' => 'renamed@example.com', 'is_active' => '1'])->assertRedirect();
        $this->put("/admin/subscribers/{$subscriber->id}", ['email' => 'renamed@example.com'])->assertSessionHasNoErrors();
        $this->assertSame(['renamed@example.com', false], [$subscriber->fresh()->email, $subscriber->fresh()->is_active]);

        $this->postJson("/admin/subscribers/{$subscriber->id}/toggle-status")->assertJson(['success' => true, 'is_active' => true]);
        $others = Subscription::factory()->count(2)->create();
        $this->postJson('/admin/subscribers/bulk-action', ['action' => 'deactivate', 'subscriber_ids' => $others->pluck('id')])
            ->assertJson(['message' => 'Successfully deactivated 2 subscriber(s).']);
        $this->postJson('/admin/subscribers/bulk-action', ['action' => 'explode', 'subscriber_ids' => [1]])->assertStatus(422);
        $this->postJson('/admin/subscribers/bulk-action', ['action' => 'delete', 'subscriber_ids' => $others->pluck('id')])
            ->assertJson(['message' => 'Successfully deleted 2 subscriber(s).']);
        $this->delete("/admin/subscribers/{$subscriber->id}")->assertRedirect();
        $this->assertSame(0, Subscription::count());
    }

    public function test_export_follows_the_filters_and_cannot_carry_spreadsheet_formulas(): void
    {
        Subscription::factory()->create(['email' => 'active@example.com']);
        Subscription::factory()->inactive()->create(['email' => 'gone@example.com']);
        Subscription::factory()->create(['email' => '=HYPERLINK("http://evil.test")@example.com']);

        $all = $this->get('/admin/subscribers/export')->assertOk()->streamedContent();
        $this->assertStringContainsString('active@example.com', $all);
        $this->assertStringContainsString('gone@example.com', $all);
        $this->assertStringContainsString("\"'=HYPERLINK", $all, 'a leading = is neutralised');

        $active = $this->get('/admin/subscribers/export?status=active')->streamedContent();
        $this->assertStringNotContainsString('gone@example.com', $active);
        $inactive = $this->get('/admin/subscribers/export?status=inactive&search=gone')->streamedContent();
        $this->assertStringContainsString('gone@example.com', $inactive);
        $this->assertStringNotContainsString('active@example.com', $inactive);
        // any other status value means "all", like the list page
        $this->assertStringContainsString('gone@example.com', $this->get('/admin/subscribers/export?status=whatever')->streamedContent());
    }
}

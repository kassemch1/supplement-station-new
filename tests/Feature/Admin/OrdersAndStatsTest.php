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

    public function test_dashboard_takes_two_queries_whatever_the_number_of_orders(): void
    {
        Order::factory()->count(30)->create();
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->get('/admin')->assertOk();

        $orderQueries = collect(DB::getQueryLog())->filter(fn ($q) => str_contains($q['query'], '"orders"'));
        $this->assertCount(2, $orderQueries);
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

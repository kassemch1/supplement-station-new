<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ShippingDetail;
use App\Models\Subscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Order search, the Customers pages, the stock switch and the dashboard's sold-out card
class CustomersAndStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin();
    }

    private function order(string $name, string $phone, array $attributes = [], ?string $when = null): Order
    {
        $order = Order::factory()->create(array_merge(['status' => 'delivered', 'total_amount' => 50], $attributes));
        ShippingDetail::factory()->create(['order_id' => $order->id, 'name' => $name, 'phone' => $phone]);
        if ($when) {
            $order->forceFill(['created_at' => $when])->save();
        }

        return $order;
    }

    public function test_the_phone_key_is_the_same_however_the_number_was_typed(): void
    {
        foreach (['03 123 456', '03123456', '+961 3 123 456', '+96103123456', '0096103123456', '3123456', '+-961 3123456'] as $typed) {
            $this->assertSame('03123456', ShippingDetail::phoneKey($typed), $typed);
        }
        $this->assertSame('71207714', ShippingDetail::phoneKey('+961 71 207 714'));
        $this->assertSame('76034133', ShippingDetail::phoneKey('7603-4133'));
        $this->assertNull(ShippingDetail::phoneKey(''));
        $this->assertNull(ShippingDetail::phoneKey(null));

        $detail = ShippingDetail::factory()->create(['phone' => '+961 70 958 449']);
        $this->assertSame('70958449', $detail->fresh()->phone_key);
    }

    public function test_orders_are_found_by_number_phone_or_name(): void
    {
        $karim = $this->order('Karim Haddad', '+961 3 123 456', ['status' => 'pending']);
        $maya = $this->order('Maya Khoury', '71 207 714', ['status' => 'pending']);
        $delivered = $this->order('Karim Haddad', '03123456');

        $find = fn (string $status, string $search) => $this->get("/admin/orders/$status?search=" . urlencode($search))->assertOk()->viewData('orders')->pluck('id')->all();

        $this->assertSame([$karim->id], $find('pending', '#' . $karim->id));
        $this->assertSame([$maya->id], $find('pending', (string) $maya->id));
        $this->assertSame([$karim->id], $find('pending', '03 123 456'));
        $this->assertSame([$maya->id], $find('pending', '+961 71207714'));
        $this->assertSame([$maya->id], $find('pending', '207'));
        $this->assertSame([$karim->id], $find('pending', 'karim'));
        $this->assertSame([], $find('pending', '++'));
        $this->assertSame([], $find('pending', '50%'));

        // nothing in this list: the page says where the search does find orders
        $this->get('/admin/orders/delivered?search=maya')->assertOk()
            ->assertSee('No delivered orders match', false)->assertSee('1 in Pending Orders');
        $this->assertSame([$delivered->id], $find('delivered', 'Karim'));

        // the list shows who ordered
        $this->get('/admin/orders/pending')->assertSee('Karim Haddad')->assertSee('71 207 714');
    }

    public function test_customers_are_grouped_by_phone_with_their_totals(): void
    {
        $this->order('Karim', '03 123 456', ['total_amount' => 100], now()->subDays(100)->toDateTimeString());
        $this->order('Karim H.', '+961 3 123 456', ['total_amount' => 40], now()->subDays(90)->toDateTimeString());
        $this->order('Karim Haddad', '03123456', ['total_amount' => 60], now()->subDays(80)->toDateTimeString());
        $this->order('Karim Haddad', '03123456', ['total_amount' => 999, 'status' => 'cancelled']);
        $this->order('Maya', '71207714', ['total_amount' => 25, 'status' => 'pending']);

        $response = $this->get('/admin/customers')->assertOk();
        $customers = collect($response->viewData('customers')->items())->keyBy('phone_key');

        $this->assertCount(2, $customers);
        $this->assertSame(3, (int) $customers['03123456']->orders_count);   // cancelled not counted
        $this->assertSame(1, (int) $customers['03123456']->cancelled_count);
        $this->assertEquals(200, (float) $customers['03123456']->spent);
        $this->assertSame(1, (int) $customers['71207714']->pending_count);
        $response->assertSee('Karim Haddad');   // the name on the latest order

        $stats = $response->viewData('stats');
        $this->assertSame(2, $stats['total']);
        $this->assertSame(1, $stats['returning']);
        $this->assertSame(1, $stats['regulars']);
        $this->assertSame(1, $stats['lapsed']);   // Karim: last real order 80 days ago (the cancelled one is newer)

        $keys = fn (string $query) => collect($this->get('/admin/customers?' . $query)->assertOk()->viewData('customers')->items())->pluck('phone_key')->all();
        $this->assertSame(['03123456'], $keys('segment=regulars'));
        $this->assertSame(['71207714'], $keys('segment=once'));
        $this->assertSame(['03123456', '71207714'], $keys('sort=spent'));
        $this->assertSame(['71207714'], $keys('search=' . urlencode('+961 71 207 714')));
        $this->assertSame(['03123456'], $keys('search=haddad'));
        $this->assertSame([], $keys('search=nobody'));
    }

    public function test_a_customer_page_lists_all_their_orders(): void
    {
        $first = $this->order('Karim', '03 123 456', ['total_amount' => 30]);
        $second = $this->order('Karim Haddad', '+961 3 123 456', ['total_amount' => 70, 'status' => 'pending']);
        $this->order('Someone Else', '71207714');
        $first->shippingDetail->update(['email' => 'karim@example.com']);
        Subscription::factory()->create(['email' => 'karim@example.com', 'is_active' => true]);

        $response = $this->get('/admin/customers/03123456')->assertOk()
            ->assertSee('#' . $first->id)->assertSee('#' . $second->id)->assertDontSee('Someone Else')
            ->assertSee('$100.00')->assertSee('Subscribed');
        $this->assertSame(['Karim Haddad', 'Karim'], $response->viewData('customer')['names']->all());

        $this->get('/admin/customers/99999999')->assertNotFound();
        $this->get('/admin/customers/abc')->assertNotFound();
        $this->get('/admin')->assertSee(route('admin.customers.index'), false);   // in the side menu
    }

    public function test_the_stock_switch_saves_and_records_when_it_sold_out(): void
    {
        $product = Product::factory()->create(['stock' => 1]);
        $this->assertNull($product->fresh()->sold_out_at);

        $this->get('/manageProducts')->assertSee('data-stock-toggle="' . route('manageProducts.stock', $product->id) . '"', false);

        $this->postJson("/manageProducts/{$product->id}/stock", ['stock' => 0])->assertOk()->assertJsonPath('stock', false);
        $this->assertSame(0, (int) $product->fresh()->stock);
        $this->assertNotNull($soldOutAt = $product->fresh()->sold_out_at);

        // saving it again while sold out keeps the first date
        $this->travel(2)->days();
        $product->fresh()->update(['name' => 'Renamed']);
        $this->assertEquals($soldOutAt, $product->fresh()->sold_out_at);

        $this->postJson("/manageProducts/{$product->id}/stock", ['stock' => 1])->assertOk()->assertJsonPath('stock', true);
        $this->assertSame(1, (int) $product->fresh()->stock);
        $this->assertNull($product->fresh()->sold_out_at);

        $this->postJson("/manageProducts/{$product->id}/stock", ['stock' => 'maybe'])->assertStatus(422);
        $this->postJson('/manageProducts/9999/stock', ['stock' => 1])->assertNotFound();
        auth()->logout();
        $this->postJson("/manageProducts/{$product->id}/stock", ['stock' => 0])->assertUnauthorized();
    }

    public function test_the_dashboard_lists_sold_out_products_best_sellers_first(): void
    {
        $quiet = Product::factory()->create(['name' => 'Quiet Bar', 'stock' => 0]);
        $popular = Product::factory()->create(['name' => 'Popular Whey', 'stock' => 0]);
        Product::factory()->create(['name' => 'Available Creatine', 'stock' => 1]);
        $order = Order::factory()->create(['status' => 'delivered']);
        OrderItem::factory()->create(['order_id' => $order->id, 'product_id' => $popular->id, 'quantity' => 4]);

        $response = $this->get('/admin')->assertOk();
        $soldOut = $response->viewData('soldOut');
        $this->assertSame(2, $soldOut['count']);
        $this->assertSame(['Popular Whey', 'Quiet Bar'], $soldOut['items']->pluck('name')->all());
        $this->assertSame(4, $soldOut['items']->first()->sold_recently);
        $response->assertSee('4 sold')->assertDontSee('Available Creatine')
            ->assertSee(route('manageProducts.index', ['stock' => 'out-of-stock']), false);

        Product::query()->update(['stock' => 1]);
        $this->get('/admin')->assertOk()->assertDontSee('db-card db-soldout', false);
    }
}

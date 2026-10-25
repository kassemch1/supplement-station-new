<?php

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use App\Services\CurrentCart;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase1Test extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function plainProduct(): int
    {
        return DB::table('products')->whereNull('deleted_at')->where('stock', 1)
            ->whereNotIn('id', DB::table('product_options')->pluck('product_id'))->value('id');
    }

    // A product with one option type that has both an in-stock and a sold-out value
    private function optionProduct(): array
    {
        $row = DB::table('product_options as a')
            ->join('product_options as b', fn ($j) => $j->on('a.product_id', '=', 'b.product_id')->on('a.option_id', '=', 'b.option_id'))
            ->join('products as p', 'p.id', '=', 'a.product_id')
            ->join('options as o', 'o.id', '=', 'a.option_id')
            ->where('a.stock', 1)->where('b.stock', 0)->where('p.stock', 1)->whereNull('p.deleted_at')
            ->select('a.product_id', 'o.option_name', 'a.option_value as in_stock', 'b.option_value as sold_out', 'a.id as in_stock_id')
            ->first();
        $optionTypes = DB::table('product_options')->where('product_id', $row->product_id)->distinct()->count('option_id');
        $this->assertSame(1, $optionTypes, 'test product should have a single option type');
        return (array) $row;
    }

    private function add(int $productId, array $options = [], int $qty = 1)
    {
        return $this->postJson('/cart/add', ['product_id' => $productId, 'quantity' => $qty, 'selected_options' => json_encode((object) $options)]);
    }

    private function order(array $extra = [])
    {
        return $this->postJson('/place-order', array_merge(['billing_first_name' => 'P1 Test', 'billing_phone' => '03123456',
            'billing_address_1' => 'St', 'billing_city' => 'Beirut', 'checkout_token' => (string) \Illuminate\Support\Str::uuid()], $extra));
    }

    // ---------- stock and options are enforced by the server ----------

    public function test_sold_out_product_is_rejected(): void
    {
        $sold = DB::table('products')->whereNull('deleted_at')->where('stock', 0)
            ->whereNotIn('id', DB::table('product_options')->pluck('product_id'))->value('id');
        $before = DB::table('cart_items')->where('product_id', $sold)->count();
        $this->add($sold)->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'sold out'));
        $this->assertSame($before, DB::table('cart_items')->where('product_id', $sold)->count());
    }

    public function test_sold_out_option_is_rejected_and_in_stock_option_works(): void
    {
        $p = $this->optionProduct();
        $this->add($p['product_id'], [$p['option_name'] => $p['sold_out']])->assertStatus(422)
            ->assertJsonPath('message', fn ($m) => str_contains($m, 'sold out') && str_contains($m, $p['sold_out']));

        $this->add($p['product_id'], [$p['option_name'] => $p['in_stock']])->assertOk();
        $line = DB::table('cart_items')->where('product_id', $p['product_id'])->latest('id')->first();
        $this->assertSame([$p['in_stock_id']], DB::table('cart_item_options')->where('cart_item_id', $line->id)->pluck('product_option_id')->all(),
            'cart line is linked to the exact option row');
    }

    public function test_missing_unknown_and_foreign_option_values_are_rejected(): void
    {
        $p = $this->optionProduct();
        $this->add($p['product_id'], [])->assertStatus(422)->assertJsonPath('message', "Please choose a {$p['option_name']}.");
        $this->add($p['product_id'], [$p['option_name'] => 'Made Up Flavor'])->assertStatus(422);
        $this->add($p['product_id'], [$p['option_name'] => $p['in_stock'], 'Size' => '10kg'])->assertStatus(422);
        $this->add($this->plainProduct(), ['Flavor' => 'Vanilla'])->assertStatus(422);
        $this->postJson('/cart/add', ['product_id' => $p['product_id'], 'quantity' => 1, 'selected_options' => 'not json'])->assertStatus(422);
    }

    public function test_archived_product_and_bad_quantity_are_rejected(): void
    {
        $pid = $this->plainProduct();
        Product::find($pid)->delete();
        $this->add($pid)->assertStatus(422)->assertJsonPath('message', 'This product is no longer available.');
        $this->add($this->plainProduct(), [], 101)->assertStatus(422);
        $this->add($this->plainProduct(), [], 0)->assertStatus(422);
    }

    // ---------- cart lines are targeted exactly ----------

    public function test_remove_and_update_hit_the_exact_line(): void
    {
        $opts = DB::table('product_options as po')->join('options as o', 'o.id', '=', 'po.option_id')
            ->join('products as p', 'p.id', '=', 'po.product_id')->where('po.stock', 1)->where('p.stock', 1)->whereNull('p.deleted_at')
            ->select('po.product_id', 'o.option_name', 'po.option_value')->get()->groupBy('product_id')
            ->first(fn ($g) => $g->pluck('option_name')->unique()->count() === 1 && $g->count() >= 3);
        [$pid, $name] = [$opts[0]->product_id, $opts[0]->option_name];
        $values = $opts->pluck('option_value')->take(3)->values();

        foreach ($values as $v) {
            $this->add($pid, [$name => $v])->assertOk();
        }
        $lines = collect($this->getJson('/api/cart')->json('items'))->keyBy(fn ($i) => json_decode($i['selected_options'], true)[$name]);
        $this->assertCount(3, $lines);

        // new pages: by cart line id
        $this->postJson('/cart/remove', ['cart_item_id' => $lines[$values[1]]['id'], 'product_id' => $pid])->assertOk();
        $left = collect($this->getJson('/api/cart')->json('items'))->map(fn ($i) => json_decode($i['selected_options'], true)[$name])->all();
        $this->assertEqualsCanonicalizing([$values[0], $values[2]], $left, 'only the clicked flavor was removed');

        $this->postJson('/cart/update', ['cart_item_id' => $lines[$values[2]]['id'], 'product_id' => $pid, 'quantity' => 7])->assertOk();
        $this->assertSame(7, (int) DB::table('cart_items')->find($lines[$values[2]]['id'])->quantity);
        $this->assertSame(1, (int) DB::table('cart_items')->find($lines[$values[0]]['id'])->quantity);

        // old cached pages: product_id + options still targets the right line
        $this->postJson('/cart/remove', ['product_id' => $pid, 'selected_options' => json_encode([$name => $values[2]])])->assertOk();
        $this->assertNull(DB::table('cart_items')->find($lines[$values[2]]['id']));
        $this->assertNotNull(DB::table('cart_items')->find($lines[$values[0]]['id']));
    }

    public function test_update_never_creates_lines_and_cannot_touch_other_carts(): void
    {
        $this->add($this->plainProduct())->assertOk();
        $count = DB::table('cart_items')->count();
        $this->postJson('/cart/update', ['product_id' => $this->plainProduct(), 'quantity' => 3, 'selected_options' => '{"Flavor":"x"}'])->assertNotFound();
        $this->assertSame($count, DB::table('cart_items')->count());

        $foreign = DB::table('cart_items')->whereNotIn('cart_id', [CurrentCart::get()->id])->value('id');
        $this->postJson('/cart/update', ['cart_item_id' => $foreign, 'quantity' => 50])->assertNotFound();
        $this->postJson('/cart/remove', ['cart_item_id' => $foreign])->assertNotFound();
        $this->assertNotNull(DB::table('cart_items')->find($foreign));
    }

    // ---------- the cart outlives the 2-hour session ----------

    public function test_cart_follows_its_30_day_cookie_not_the_session(): void
    {
        $res = $this->add($this->plainProduct(), [], 2)->assertOk();
        $cookie = collect($res->headers->getCookies())->first(fn ($c) => $c->getName() === CurrentCart::COOKIE);
        $this->assertNotNull($cookie, 'cart cookie is set');
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $cookie->getExpiresTime(), 120);
        $token = session('session_id');
        $this->assertTrue(Carbon::parse(Cart::where('session_id', $token)->value('expires_at'))->gt(now()->addDays(29)));

        // Session expired (new, empty session) but the browser still sends the cookie
        $this->flushSession();
        $items = $this->withCredentials()->withCookie(CurrentCart::COOKIE, $token)->getJson('/api/cart')->json('items');
        $this->assertCount(1, $items);
        $this->assertSame(2, $items[0]['quantity']);
    }

    public function test_existing_session_cart_is_kept_after_the_update(): void
    {
        // A visitor whose cart was created by the old code (keyed by their session) before this deploy
        $cart = Cart::create(['session_id' => 'legacy-session-token', 'expires_at' => now()->addDay()]);
        DB::table('cart_items')->insert(['cart_id' => $cart->id, 'product_id' => $this->plainProduct(), 'quantity' => 4,
            'selected_options' => '[]', 'created_at' => now(), 'updated_at' => now()]);

        $res = $this->withSession(['session_id' => 'legacy-session-token'])->getJson('/api/cart');
        $this->assertSame(4, $res->json('items.0.quantity'));
        $cookie = collect($res->headers->getCookies())->first(fn ($c) => $c->getName() === CurrentCart::COOKIE);
        $this->assertNotNull($cookie, 'legacy visitor now gets the cart cookie');
    }

    // ---------- checkout re-checks stock and coupons ----------

    public function test_checkout_refuses_items_that_sold_out_after_adding(): void
    {
        $pid = $this->plainProduct();
        $this->add($pid)->assertOk();
        DB::table('products')->where('id', $pid)->update(['stock' => 0]);

        $this->assertNotNull($this->getJson('/api/cart')->json('items.0.unavailable_reason'));
        $before = DB::table('orders')->count();
        $this->order()->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'sold out'));
        $this->assertSame($before, DB::table('orders')->count());
    }

    public function test_single_use_coupon_cannot_be_used_twice(): void
    {
        $id = DB::table('coupons')->insertGetId(['code' => 'P1ONCE', 'name' => 'once', 'type' => 'single_use', 'discount_percentage' => 10,
            'minimum_order_amount' => 0, 'max_uses' => 1, 'used_count' => 0, 'is_active' => 1, 'created_at' => now(), 'updated_at' => now()]);

        // Two customers apply the coupon before either orders
        $this->add($this->plainProduct())->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'P1ONCE'])->assertOk();
        $customerA = session()->all();

        $this->flushSession();
        $this->add($this->plainProduct())->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'P1ONCE'])->assertOk();
        $customerB = session()->all();

        $this->flushSession();
        $this->withSession($customerA)->withCredentials()->withCookie(CurrentCart::COOKIE, $customerA['session_id'])->order()->assertOk();
        $this->flushSession();
        $res = $this->withSession($customerB)->withCredentials()->withCookie(CurrentCart::COOKIE, $customerB['session_id'])->order();
        $res->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'P1ONCE'));

        $this->assertSame(1, (int) DB::table('coupons')->where('id', $id)->value('used_count'));
        $this->assertSame(1, DB::table('orders')->where('coupon_id', $id)->count());
    }

    // ---------- sold-out product pages ----------

    public function test_sold_out_product_page_is_shown_not_redirected(): void
    {
        $sold = DB::table('products')->whereNull('deleted_at')->where('stock', 0)->value('id');
        $html = $this->get($this->productUrl($sold))->assertOk()->getContent();
        $this->assertStringContainsString('Sold out', $html);
        $this->assertStringNotContainsString('<i class="far fa-shopping-bag"></i> Add to cart', $html);
        $this->get($this->productUrl($this->plainProduct()))->assertOk()->assertSee('Add to cart');
    }

    // ---------- admin ----------

    public function test_admin_can_list_and_restore_archived_products(): void
    {
        $this->actingAs(User::first());
        $pid = $this->plainProduct();
        $name = Product::find($pid)->name;
        $this->deleteJson('/manageProducts/destroy', ['id' => $pid])->assertOk();

        $this->get('/manageProducts?stock=archived&search='.urlencode($name))->assertOk()->assertSee($name)->assertSee('Restore');
        $this->post("/manageProducts/$pid/restore")->assertRedirect();
        $this->assertNull(DB::table('products')->where('id', $pid)->value('deleted_at'));
        $this->get($this->productUrl($pid))->assertOk();
    }

    public function test_cancelled_orders_are_kept_listed_and_excluded_from_sales(): void
    {
        $this->actingAs(User::first());
        $order = DB::table('orders')->where('status', 'delivered')->orderByDesc('total_amount')->first();
        $totalBefore = $this->get('/admin')->viewData('totalAmount');

        $this->postJson("/admin/orders/{$order->id}/update-status", ['status' => 'cancelled'])->assertRedirect();
        $this->assertSame('cancelled', DB::table('orders')->where('id', $order->id)->value('status'));
        $this->get('/admin/orders/cancelled')->assertOk()->assertSee('Cancelled Orders')->assertSee('#'.$order->id, false);
        $this->get("/admin/orders/{$order->id}")->assertOk()->assertSee('Cancelled');
        $this->assertEqualsWithDelta($totalBefore - $order->total_amount, $this->get('/admin')->viewData('totalAmount'), 0.01);

        $this->postJson("/admin/orders/{$order->id}/update-status", ['status' => 'shipped-to-mars'])->assertStatus(422);
        $this->get('/admin/orders/pending?sort=sideways')->assertOk();
    }

    public function test_dashboard_today_is_beirut_today(): void
    {
        $this->actingAs(User::first());
        Carbon::setTestNow('2026-10-26 08:00:00'); // 11:00 in Beirut
        DB::table('orders')->insert(['total_amount' => 77, 'subtotal_amount' => 77, 'status' => 'pending',
            'created_at' => '2026-10-25 22:30:00', 'updated_at' => now()]); // 00:30 Beirut, 26 Oct
        DB::table('orders')->insert(['total_amount' => 5, 'subtotal_amount' => 5, 'status' => 'pending',
            'created_at' => '2026-10-25 21:30:00', 'updated_at' => now()]); // 23:30 Beirut, 25 Oct

        $res = $this->get('/admin')->assertOk();
        $this->assertSame(1, $res->viewData('todayOrders'));
        $this->assertEquals(77, $res->viewData('todayRevenue'));
        Carbon::setTestNow();
    }

    public function test_admin_order_list_shows_beirut_time(): void
    {
        $this->actingAs(User::first());
        $id = DB::table('orders')->insertGetId(['total_amount' => 1, 'subtotal_amount' => 1, 'status' => 'pending',
            'created_at' => '2026-07-01 10:00:00', 'updated_at' => now()]);
        $this->get('/admin/orders/pending?sort=desc')->assertSee('Jul 01, 2026 - 13:00'); // UTC+3 in summer
    }

    public function test_no_zero_subtotals_remain(): void
    {
        $this->assertSame(0, DB::table('orders')->where('subtotal_amount', 0)->count());
    }
}

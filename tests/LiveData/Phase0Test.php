<?php

use App\Models\Category;
use App\Mail\CheckoutMail;
use App\Mail\NewOrderMail;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class Phase0Test extends TestCase
{
    use DatabaseTransactions;

    private function plainProductId(): int
    {
        return DB::table('products')->whereNull('deleted_at')->where('stock', 1)
            ->whereNotIn('id', DB::table('product_options')->pluck('product_id'))->value('id');
    }

    private function orderPayload(array $extra = []): array
    {
        return array_merge(['billing_first_name' => 'Phase0 Test', 'billing_phone' => '03123456',
            'billing_address_1' => 'Street', 'billing_city' => 'Beirut', 'billing_email' => 'p0@example.com'], $extra);
    }

    private function addPlainToCart(int $productId, int $qty = 1): void
    {
        $this->postJson('/cart/add', ['product_id' => $productId, 'quantity' => $qty, 'selected_options' => '{}'])->assertOk();
    }

    // ---------- order history ----------

    public function test_archiving_a_product_keeps_order_history_and_hides_it_from_the_shop(): void
    {
        Mail::fake();
        $pid = $this->plainProductId();
        $name = Product::find($pid)->name;

        $this->addPlainToCart($pid, 2);
        $orderId = $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-archive']))->assertOk()->json('order_id');
        $line = DB::table('order_items')->where('order_id', $orderId)->first();
        $this->assertSame($name, $line->product_name, 'new order line stores the product name');
        $this->assertNotNull($line->product_image, 'new order line stores the product image');

        // product is also in someone else's cart, a special offer and the banner
        DB::table('special_offers')->where('product_id', $pid)->delete();
        DB::table('special_offers')->insert(['product_id' => $pid, 'position' => 99, 'created_at' => now(), 'updated_at' => now()]);
        DB::table('banners')->update(['product_id' => $pid]);
        $this->withSession([])->postJson('/cart/add', ['product_id' => $pid, 'quantity' => 1, 'selected_options' => '{}']);

        $this->actingAs(User::first())->deleteJson('/manageProducts/destroy', ['id' => $pid])->assertOk();

        $this->assertNotNull(DB::table('products')->where('id', $pid)->value('deleted_at'), 'product is archived, not deleted');
        $this->assertSame(1, DB::table('order_items')->where('order_id', $orderId)->count(), 'order line survives');
        $this->assertSame(0, DB::table('cart_items')->where('product_id', $pid)->count(), 'removed from carts');
        $this->assertSame(0, DB::table('special_offers')->where('product_id', $pid)->count(), 'removed from special offers');
        $this->assertNull(DB::table('banners')->value('product_id'), 'unlinked from the banner');

        // Phase 4: the archived product page sends visitors to its category (not permanent: it can be restored)
        $this->get($this->productUrl($pid))->assertStatus(302)->assertRedirect(Category::find(DB::table('products')->where('id', $pid)->value('category_id'))?->url() ?? route('shop'));
        $this->get('/shop?shop-search='.urlencode($name))->assertDontSee($name);
        $this->get('/')->assertOk();
        $this->getJson('/api/recent-purchases')->assertOk()->assertJsonMissing(['product_id' => $pid]);

        $this->get("/admin/orders/$orderId")->assertOk()->assertSee($name);
        $this->get("/admin/orders/$orderId/details")->assertOk()->assertSee($name);
    }

    public function test_removing_a_product_row_never_deletes_order_lines(): void
    {
        $line = DB::table('order_items')->whereNotNull('product_id')->first();
        Product::withTrashed()->find($line->product_id)->forceDelete();

        $after = DB::table('order_items')->find($line->id);
        $this->assertNotNull($after, 'order line still exists');
        $this->assertNull($after->product_id);
        $this->assertNotNull($after->product_name);
        $this->actingAs(User::first())->get("/admin/orders/{$line->order_id}")->assertOk()->assertSee(e($after->product_name), false);
    }

    public function test_category_with_products_cannot_be_deleted(): void
    {
        $this->actingAs(User::first());
        $cat = DB::table('products')->whereNull('deleted_at')->value('category_id');
        $this->deleteJson('/manageCategories/destroy', ['id' => $cat])->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'still has'));
        $this->assertTrue(DB::table('categories')->where('id', $cat)->exists());

        $empty = DB::table('categories')->insertGetId(['name' => 'P0 empty', 'created_at' => now(), 'updated_at' => now()]);
        $this->deleteJson('/manageCategories/destroy', ['id' => $empty])->assertOk();
        $this->assertFalse(DB::table('categories')->where('id', $empty)->exists());
    }

    public function test_recovered_orders_and_migration_is_idempotent(): void
    {
        $this->assertSame(9, DB::table('orders')->whereNotExists(fn ($q) => $q->select(DB::raw(1))->from('order_items')->whereColumn('order_items.order_id', 'orders.id'))->count());
        $before = DB::table('order_items')->count();
        (include database_path('migrations/2026_10_25_000001_restore_lost_order_items.php'))->up();
        $this->assertSame($before, DB::table('order_items')->count(), 're-running inserts nothing');
        $this->actingAs(User::first())->get('/admin/orders/12')->assertOk()->assertSee('Bcaa + Creatine');
    }

    // ---------- duplicate orders + email ----------

    public function test_same_checkout_token_creates_one_order(): void
    {
        Mail::fake();
        $this->addPlainToCart($this->plainProductId());
        $before = DB::table('orders')->count();

        $first = $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-dup']))->assertOk()->json('order_id');
        $second = $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-dup']))->assertOk()->json('order_id');

        $this->assertSame($first, $second, 'retry returns the same order');
        $this->assertSame($before + 1, DB::table('orders')->count());
        Mail::assertSent(CheckoutMail::class, 1);
        Mail::assertSent(NewOrderMail::class, fn ($m) => $m->hasTo(config('mail.admin_address')) && $m->order->id === $first);
        Mail::assertSent(NewOrderMail::class, 1);

        // A fresh checkout page (new token) with a new cart is a new order
        $this->addPlainToCart($this->plainProductId());
        $third = $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-new']))->assertOk()->json('order_id');
        $this->assertNotSame($first, $third);
    }

    public function test_concurrent_duplicate_returns_the_winning_order(): void
    {
        Mail::fake();
        $this->addPlainToCart($this->plainProductId());
        // Another request with the same token committed between our check and our insert
        $winner = DB::table('orders')->insertGetId(['total_amount' => 1, 'subtotal_amount' => 1, 'status' => 'pending',
            'checkout_token' => 'p0-race', 'created_at' => now(), 'updated_at' => now()]);
        $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-race']))->assertOk()->assertJsonPath('order_id', $winner);
    }

    public function test_mail_failure_does_not_fail_a_placed_order(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);
        $this->addPlainToCart($this->plainProductId());
        $before = DB::table('orders')->count();

        $res = $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-mailfail']));

        $res->assertOk()->assertJsonPath('success', true);
        $this->assertSame($before + 1, DB::table('orders')->count());
    }

    public function test_empty_cart_and_bad_email_are_rejected_cleanly(): void
    {
        $this->postJson('/place-order', $this->orderPayload())->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'cart is empty'));
        $this->addPlainToCart($this->plainProductId());
        $this->postJson('/place-order', $this->orderPayload(['billing_email' => 'not-an-email']))->assertStatus(422)->assertJsonValidationErrors('billing_email');
    }

    public function test_checkout_page_has_token_and_double_submit_guard(): void
    {
        $html = $this->get('/checkout')->assertOk()->getContent();
        $this->assertMatchesRegularExpression('/name="checkout_token" value="[0-9a-f-]{36}"/', $html);
        // the double-submit guard is in the checkout page script
        $this->assertStringContainsString("data('submitting')", file_get_contents(public_path('assets/build/checkout.js')));
        $this->assertStringContainsString('assets/build/checkout.js', $html);
    }

    // ---------- security ----------

    public function test_removed_and_redirected_routes(): void
    {
        $this->get('/foo')->assertNotFound();
        $this->get('/test')->assertNotFound();
        $this->get('/shop/Creatine')->assertRedirect(route('shop.category', 'creatine'))->assertStatus(301);
        $this->get('/shop')->assertOk();
        $this->get('/Shop/search?search=whey')->assertRedirect(route('shop', ['shop-search' => 'whey']));
    }

    public function test_image_delete_needs_delete_request(): void
    {
        $image = DB::table('product_images')->value('id');
        $this->actingAs(User::first())->get("/productImage/$image/destroy")->assertNotFound();
        $this->assertTrue(DB::table('product_images')->where('id', $image)->exists());
        $this->delete("/productImage/$image")->assertRedirect();
        $this->assertFalse(DB::table('product_images')->where('id', $image)->exists());
    }

    public function test_login_is_throttled(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->post('/auth', ['email' => 'p0@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        }
        $this->post('/auth', ['email' => 'p0@example.com', 'password' => 'wrong'])
            ->assertSessionHasErrors(['email' => 'Too many login attempts. Please try again in 15 minute(s).']);
    }

    public function test_public_forms_are_rate_limited(): void
    {
        Mail::fake();
        for ($i = 0; $i < 3; $i++) {
            $this->post('/contact/send', ['name' => 'a', 'email' => 'a@example.com', 'phone' => '1', 'message' => 'x'])->assertRedirect();
        }
        $this->post('/contact/send', ['name' => 'a', 'email' => 'a@example.com', 'phone' => '1', 'message' => 'x'])->assertStatus(429);

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/product/'.$this->plainProductId().'/review', ['name' => 'a', 'message' => 'x', 'rating' => 5])->assertOk();
        }
        $this->postJson('/product/'.$this->plainProductId().'/review', ['name' => 'a', 'message' => 'x', 'rating' => 5])->assertStatus(429);
    }

    public function test_rate_limits_are_per_route_so_a_big_cart_can_still_check_out(): void
    {
        Mail::fake();
        for ($i = 0; $i < 12; $i++) {
            $this->addPlainToCart($this->plainProductId());
        }
        $this->postJson('/place-order', $this->orderPayload(['checkout_token' => 'p0-bigcart']))->assertOk();
        $this->post('/contact/send', ['name' => 'a', 'email' => 'a@example.com', 'phone' => '1', 'message' => 'x'])->assertRedirect();
    }

    public function test_error_log_is_blocked_by_htaccess(): void
    {
        $this->assertStringContainsString('<FilesMatch "^(error_log|', file_get_contents(public_path('.htaccess')));
        $this->assertFileDoesNotExist(public_path('error_log'));
    }
}

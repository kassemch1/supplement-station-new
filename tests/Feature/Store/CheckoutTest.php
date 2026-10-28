<?php

namespace Tests\Feature\Store;

use App\Mail\CheckoutMail;
use App\Mail\NewOrderMail;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Subscription;
use App\Services\CurrentCart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_an_order_copies_the_cart_and_applies_the_coupon(): void
    {
        $whey = Product::factory()->withImage()->withOptions('Flavor', ['Vanilla' => true])->create(['price' => 50, 'discount' => 10]);
        $shaker = Product::factory()->create(['price' => 10]);
        $coupon = Coupon::factory()->create(['code' => 'SAVE20', 'discount_percentage' => 20]);

        $this->addToCart($whey, ['Flavor' => 'Vanilla'], 2)->assertOk();
        $this->addToCart($shaker)->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'save20'])->assertOk();

        $orderId = $this->placeOrder()->assertOk()->assertJson(['success' => true])->json('order_id');

        $order = Order::with('orderItems', 'shippingDetail')->find($orderId);
        $this->assertEquals(100, $order->subtotal_amount);          // 2 x 45 + 10
        $this->assertEquals(20, $order->coupon_discount_amount);
        $this->assertEquals(80, $order->total_amount);
        $this->assertSame('SAVE20', $order->coupon_code);
        $this->assertSame('pending', $order->status);
        $this->assertSame('Beirut', $order->shippingDetail->city);

        $line = $order->orderItems->firstWhere('product_id', $whey->id);
        $this->assertSame($whey->name, $line->product_name);
        $this->assertSame($whey->images()->value('url'), $line->product_image);
        $this->assertEquals(45, $line->price);
        $this->assertSame(2, $line->quantity);
        $this->assertSame(['Flavor' => 'Vanilla'], $line->selectedOptions());

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertSame(0, CartItem::count(), 'cart emptied');
        $this->assertFalse(session()->has('applied_coupon'));
    }

    public function test_emails_show_the_real_subtotal_and_answer_the_right_person(): void
    {
        Coupon::factory()->create(['code' => 'TEN', 'discount_percentage' => 10]);
        $this->addToCart(Product::factory()->create(['price' => 40]))->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'TEN'])->assertOk();

        $orderId = $this->placeOrder(['billing_email' => 'buyer@example.com', 'billing_first_name' => 'Rami'])->assertOk()->json('order_id');

        Mail::assertSent(CheckoutMail::class, function (CheckoutMail $mail) {
            $html = $mail->render();

            return $mail->hasTo('buyer@example.com')
                && $mail->hasReplyTo(config('mail.admin_address'))
                && str_contains($html, '$40.00')      // subtotal (it used to show the total twice)
                && str_contains($html, '-$4.00')
                && str_contains($html, '$36.00');
        });
        Mail::assertSent(NewOrderMail::class, fn (NewOrderMail $mail) => $mail->hasTo(config('mail.admin_address'))
            && $mail->hasReplyTo('buyer@example.com', 'Rami')
            && $mail->order->id === $orderId);
    }

    public function test_the_order_email_goes_to_the_addresses_saved_in_settings(): void
    {
        Setting::write(NewOrderMail::RECIPIENTS_SETTING, 'owner@example.com, partner@example.com');
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder()->assertOk();

        Mail::assertSent(NewOrderMail::class, fn (NewOrderMail $mail) => $mail->hasTo('owner@example.com')
            && $mail->hasTo('partner@example.com')
            && !$mail->hasTo(config('mail.admin_address')));
    }

    public function test_a_store_pickup_needs_no_address_and_the_emails_say_pickup(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        // the hidden address fields may still hold an address remembered from an earlier order
        $orderId = $this->placeOrder(['delivery_method' => 'pickup', 'billing_city' => 'Hamra', 'billing_address_1' => ''])
            ->assertOk()->json('order_id');

        $order = Order::with('shippingDetail')->find($orderId);
        $this->assertTrue($order->isPickup());
        $this->assertSame('', $order->shippingDetail->city);
        $this->assertSame('', $order->shippingDetail->address);

        Mail::assertSent(NewOrderMail::class, fn (NewOrderMail $mail) => str_ends_with($mail->envelope()->subject, 'Store pickup')
            && str_contains($mail->render(), 'Pick up from the store'));
        Mail::assertSent(CheckoutMail::class, fn (CheckoutMail $mail) => str_contains($mail->render(), config('store.address'))
            && !str_contains($mail->render(), 'Shipping Address'));

        $this->admin()->get("/admin/orders/{$orderId}/details")->assertOk()->assertSee('Store pickup');
        $this->getJson('/admin/orders/pending-summary')->assertJsonPath('latest_pending.0.city', 'Store pickup');
    }

    public function test_a_delivery_still_needs_the_address(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();

        $this->placeOrder(['billing_city' => '', 'billing_address_1' => ''])->assertStatus(422)
            ->assertJsonValidationErrors(['billing_city' => 'Please write your city or area.', 'billing_address_1' => 'Please write your address.']);
        $this->placeOrder(['delivery_method' => 'drone'])->assertStatus(422)->assertJsonValidationErrors('delivery_method');
        $this->assertSame(0, Order::count());

        // pages cached before pickup existed send no delivery_method: a delivery, as before
        $orderId = $this->placeOrder()->assertOk()->json('order_id');
        $this->assertSame(Order::DELIVERY, Order::find($orderId)->delivery_method);
    }

    public function test_an_order_without_email_still_tells_the_store(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder(['billing_email' => null])->assertOk();

        Mail::assertNotSent(CheckoutMail::class);
        Mail::assertSent(NewOrderMail::class, fn (NewOrderMail $mail) => $mail->envelope()->replyTo === []);
    }

    public function test_the_checkout_email_joins_the_newsletter_when_the_box_is_ticked(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder(['billing_email' => ' Buyer@Example.com ', 'newsletter' => '1'])->assertOk();

        $subscription = Subscription::where('email', 'buyer@example.com')->firstOrFail();
        $this->assertTrue($subscription->is_active);
        $this->assertNotNull($subscription->subscribed_at);

        // a second order with the same email adds no second row
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder(['billing_email' => 'buyer@example.com', 'newsletter' => '1'])->assertOk();
        $this->assertSame(1, Subscription::count());
    }

    public function test_no_newsletter_without_the_box_or_after_unsubscribing(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder(['billing_email' => 'no-thanks@example.com'])->assertOk();
        $this->assertSame(0, Subscription::count());

        Subscription::factory()->create(['email' => 'left@example.com', 'is_active' => false]);
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder(['billing_email' => 'left@example.com', 'newsletter' => '1'])->assertOk();
        $this->assertFalse(Subscription::where('email', 'left@example.com')->value('is_active'));
    }

    public function test_the_checkout_page_offers_the_newsletter_box(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        $this->get('/checkout')->assertOk()->assertSee('name="newsletter"', false)->assertSee('Email me new products and offers');
    }

    public function test_placing_an_order_does_not_query_once_per_line(): void
    {
        $count = function (int $lines) {
            $this->flushSession();
            foreach (range(1, $lines) as $i) {
                $this->addToCart(Product::factory()->withImage()->withOptions('Size', ['1kg' => true])->create(), ['Size' => '1kg'])->assertOk();
            }
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->placeOrder()->assertOk();

            return count(DB::getQueryLog());
        };

        $this->assertSame($count(1), $count(6));
    }

    // ---------- one order per checkout ----------

    public function test_the_same_checkout_token_creates_one_order(): void
    {
        $product = Product::factory()->create();
        $this->addToCart($product)->assertOk();

        $first = $this->placeOrder(['checkout_token' => 'dup-token'])->assertOk()->json('order_id');
        $second = $this->placeOrder(['checkout_token' => 'dup-token'])->assertOk()->json('order_id');

        $this->assertSame($first, $second);
        $this->assertSame(1, Order::count());
        Mail::assertSent(CheckoutMail::class, 1);
        Mail::assertSent(NewOrderMail::class, 1);

        $this->addToCart($product)->assertOk();
        $this->assertNotSame($first, $this->placeOrder(['checkout_token' => 'new-token'])->assertOk()->json('order_id'));
    }

    public function test_a_concurrent_duplicate_gets_the_order_that_won(): void
    {
        $this->addToCart(Product::factory()->create())->assertOk();
        $winner = Order::factory()->create(['checkout_token' => 'race-token']);

        $this->placeOrder(['checkout_token' => 'race-token'])->assertOk()->assertJsonPath('order_id', $winner->id);
        $this->assertSame(1, Order::count());
    }

    public function test_a_failing_mail_server_does_not_fail_a_placed_order(): void
    {
        config(['mail.default' => 'smtp', 'mail.mailers.smtp.host' => '127.0.0.1', 'mail.mailers.smtp.port' => 1, 'mail.mailers.smtp.timeout' => 2]);
        Mail::swap(app('mail.manager'));   // the real mailer, not the fake from setUp
        $this->addToCart(Product::factory()->create())->assertOk();

        $this->placeOrder()->assertOk()->assertJsonPath('success', true);
        $this->assertSame(1, Order::count());
    }

    // ---------- refused orders ----------

    public function test_empty_cart_bad_email_and_bad_phone_are_refused(): void
    {
        $this->placeOrder()->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'cart is empty'));

        $this->addToCart(Product::factory()->create())->assertOk();
        $this->placeOrder(['billing_email' => 'not-an-email'])->assertStatus(422)->assertJsonValidationErrors('billing_email');
        $this->placeOrder(['billing_phone' => '123'])->assertStatus(422)
            ->assertJsonValidationErrors(['billing_phone' => 'The phone number must be a valid Lebanese phone number.']);
        $this->assertSame(0, Order::count());
    }

    public function test_items_that_sold_out_after_adding_are_refused(): void
    {
        $product = Product::factory()->create();
        $this->addToCart($product)->assertOk();
        $product->update(['stock' => false]);

        $this->placeOrder()->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'sold out'));
        $this->assertSame(0, Order::count());
    }

    public function test_a_single_use_coupon_works_for_one_order_only(): void
    {
        $coupon = Coupon::factory()->singleUse()->create(['code' => 'ONCE']);
        $product = Product::factory()->create();

        // Two customers apply it before either orders
        $this->addToCart($product)->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'ONCE'])->assertOk();
        $customerA = session()->all();
        $this->flushSession();
        $this->addToCart($product)->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'ONCE'])->assertOk();
        $customerB = session()->all();

        $this->flushSession();
        $this->withSession($customerA)->withCredentials()->withCookie(CurrentCart::COOKIE, $customerA['session_id'])->placeOrder()->assertOk();
        $this->flushSession();
        $this->withSession($customerB)->withCredentials()->withCookie(CurrentCart::COOKIE, $customerB['session_id'])->placeOrder()
            ->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'ONCE'));

        $this->assertSame(1, $coupon->fresh()->used_count);
        $this->assertFalse($coupon->fresh()->is_active);
        $this->assertSame(1, Order::where('coupon_id', $coupon->id)->count());
        $this->assertFalse(session()->has('applied_coupon'), 'the refused coupon is taken off the cart');
    }

    public function test_a_coupon_below_its_minimum_at_checkout_is_refused(): void
    {
        Coupon::factory()->create(['code' => 'MIN50', 'minimum_order_amount' => 50]);
        $product = Product::factory()->create(['price' => 60]);
        $this->addToCart($product)->assertOk();
        $this->postJson('/coupon/apply', ['coupon_code' => 'MIN50'])->assertOk();
        $product->update(['price' => 30]);

        $this->placeOrder()->assertStatus(422)->assertJsonPath('message', fn ($m) => str_contains($m, 'MIN50'));
        $this->assertSame(0, Order::count());
        $this->assertSame(1, CartItem::count(), 'the cart is kept');
    }

    // ---------- the page ----------

    public function test_the_checkout_page_has_its_token_and_double_submit_guard(): void
    {
        $html = $this->get('/checkout')->assertOk()->getContent();

        $this->assertMatchesRegularExpression('/name="checkout_token" value="[0-9a-f-]{36}"/', $html);
        $this->assertStringContainsString('assets/build/checkout.js', $html);
        $this->assertStringContainsString("data('submitting')", file_get_contents(public_path('assets/build/checkout.js')));
    }

    public function test_rate_limits_are_per_form_so_a_big_cart_can_still_check_out(): void
    {
        foreach (range(1, 12) as $i) {
            $this->addToCart(Product::factory()->create())->assertOk();
        }
        $this->placeOrder()->assertOk();
        $this->post('/contact/send', ['name' => 'a', 'email' => 'a@example.com', 'phone' => '1', 'message' => 'x'])->assertRedirect();
    }
}

<?php

use App\Models\Cart;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartSessionTest extends TestCase
{
    use DatabaseTransactions;

    public function test_cart_is_created_once_and_expiry_extended(): void
    {
        $product = DB::table('products')->whereNotIn('id', DB::table('product_options')->pluck('product_id'))->value('id');

        $this->postJson('/cart/add', ['product_id' => $product, 'quantity' => 1, 'selected_options' => '{}'])->assertOk();
        $sessionId = session('session_id');
        $this->assertSame(1, Cart::where('session_id', $sessionId)->count());
        $cart = Cart::where('session_id', $sessionId)->first();
        $this->assertTrue(Carbon::parse($cart->expires_at)->gt(now()->addDays(2)->subMinute()));

        // Pretend the cart is about to expire; the next add should push expiry out again, not create a new cart
        DB::table('carts')->where('id', $cart->id)->update(['expires_at' => now()->addHour()]);
        $this->postJson('/cart/add', ['product_id' => $product, 'quantity' => 2, 'selected_options' => '{}'])->assertOk();
        $this->assertSame(1, Cart::where('session_id', $sessionId)->count());
        $this->assertTrue(Carbon::parse(Cart::find($cart->id)->expires_at)->gt(now()->addDays(2)->subMinute()));
        $this->assertSame(3, (int) DB::table('cart_items')->where('cart_id', $cart->id)->sum('quantity'));
    }

    public function test_duplicate_cart_insert_is_rejected_and_handled(): void
    {
        Cart::create(['session_id' => 'race-test', 'expires_at' => now()]);
        // A plain second insert must now fail on the unique index...
        $this->expectNotToPerformAssertions();
        try {
            DB::transaction(fn () => DB::table('carts')->insert(['session_id' => 'race-test', 'expires_at' => now()]));
            $this->fail('duplicate cart was inserted');
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
        }
        // ...and the middleware's createOrFirst path recovers by loading the existing cart
        $cart = Cart::query()->createOrFirst(['session_id' => 'race-test'], ['expires_at' => now()->addDays(2)]);
        if (Cart::where('session_id', 'race-test')->count() !== 1 || $cart->wasRecentlyCreated) {
            $this->fail('race path created a duplicate');
        }
    }
}

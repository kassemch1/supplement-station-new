<?php

namespace Tests\Feature\Store;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Services\CurrentCart;
use Carbon\Carbon;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    private function flavored(): Product
    {
        return Product::factory()->withOptions('Flavor', ['Vanilla' => true, 'Mango' => false, 'Chocolate' => true])->create();
    }

    // ---------- the server enforces stock and options ----------

    public function test_sold_out_product_is_rejected(): void
    {
        $product = Product::factory()->soldOut()->create();

        $this->addToCart($product)->assertStatus(422)->assertJsonPath('message', "{$product->name} is sold out.");
        $this->assertSame(0, CartItem::count());
    }

    public function test_sold_out_option_is_rejected_and_in_stock_option_is_linked_to_its_row(): void
    {
        $product = $this->flavored();

        $this->addToCart($product, ['Flavor' => 'Mango'])->assertStatus(422)
            ->assertJsonPath('message', "{$product->name} (Flavor: Mango) is sold out.");

        $this->addToCart($product, ['Flavor' => 'Vanilla'])->assertOk()->assertJson(['success' => true]);
        $line = CartItem::sole();
        $vanilla = $product->productOptions()->where('option_value', 'Vanilla')->value('id');
        $this->assertSame([$vanilla], DB::table('cart_item_options')->where('cart_item_id', $line->id)->pluck('product_option_id')->all());
    }

    public function test_missing_unknown_and_foreign_options_are_rejected(): void
    {
        $product = $this->flavored();
        $plain = Product::factory()->create();

        $this->addToCart($product)->assertStatus(422)->assertJsonPath('message', 'Please choose a Flavor.');
        $this->addToCart($product, ['Flavor' => 'Made Up'])->assertStatus(422);
        $this->addToCart($product, ['Flavor' => 'Vanilla', 'Size' => '10kg'])->assertStatus(422);
        $this->addToCart($plain, ['Flavor' => 'Vanilla'])->assertStatus(422);
        $this->postJson('/cart/add', ['product_id' => $product->id, 'quantity' => 1, 'selected_options' => 'not json'])->assertStatus(422);
        $this->assertSame(0, CartItem::count());
    }

    public function test_archived_unknown_product_and_bad_quantity_are_rejected(): void
    {
        $product = Product::factory()->create();
        $archived = Product::factory()->create();
        $archived->delete();

        $this->addToCart($archived)->assertStatus(422)->assertJsonPath('message', 'This product is no longer available.');
        $this->addToCart(999999)->assertStatus(422);
        $this->addToCart($product, [], 101)->assertStatus(422)->assertJsonPath('success', false);
        $this->addToCart($product, [], 0)->assertStatus(422);
    }

    public function test_same_product_and_options_share_a_line_up_to_100(): void
    {
        $product = $this->flavored();

        $this->addToCart($product, ['Flavor' => 'Vanilla'], 60)->assertOk();
        $this->addToCart($product, ['Flavor' => 'Vanilla'], 60)->assertOk();
        $this->addToCart($product, ['Flavor' => 'Chocolate'])->assertOk();

        $this->assertSame(2, CartItem::count());
        $this->assertSame(100, CartItem::where('selected_options', json_encode(['Flavor' => 'Vanilla']))->value('quantity'));
    }

    // ---------- the cart as the page sees it ----------

    public function test_cart_lists_prices_pictures_and_lines_that_sold_out(): void
    {
        $product = Product::factory()->withImage()->create(['price' => 50, 'discount' => 10]);
        $this->addToCart($product, [], 2)->assertOk();
        $product->update(['stock' => false]);

        $item = $this->getJson('/api/cart')->assertOk()->json('items.0');

        $this->assertEquals(45, $item['discounted_price']);
        $this->assertEquals(90, $item['subtotal']);
        $this->assertSame($product->images()->value('url'), $item['product_image']);
        $this->assertSame("{$product->name} is sold out.", $item['unavailable_reason']);
        $this->assertArrayNotHasKey('product_options', $item['product'], 'the stock check data is not sent to the page');
        // the old mini-cart script read the cart from /cart
        $this->getJson('/cart')->assertOk()->assertJsonCount(1, 'items');
    }

    public function test_cart_queries_do_not_grow_with_the_number_of_lines(): void
    {
        $count = function () {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->getJson('/api/cart')->assertOk();

            return count(DB::getQueryLog());
        };

        $this->addToCart($this->flavored(), ['Flavor' => 'Vanilla'])->assertOk();
        $one = $count();
        foreach (range(1, 5) as $i) {
            $this->addToCart(Product::factory()->withImage()->withOptions('Size', ['1kg' => true])->create(), ['Size' => '1kg'])->assertOk();
        }

        $this->assertSame($one, $count());
    }

    // ---------- the exact line is changed ----------

    public function test_remove_and_update_hit_the_exact_line(): void
    {
        $product = $this->flavored();
        $this->addToCart($product, ['Flavor' => 'Vanilla'])->assertOk();
        $this->addToCart($product, ['Flavor' => 'Chocolate'])->assertOk();
        $vanilla = CartItem::where('selected_options', json_encode(['Flavor' => 'Vanilla']))->first();
        $chocolate = CartItem::where('selected_options', json_encode(['Flavor' => 'Chocolate']))->first();

        $this->postJson('/cart/update', ['cart_item_id' => $chocolate->id, 'quantity' => 7])->assertOk();
        $this->assertSame(7, $chocolate->fresh()->quantity);
        $this->assertSame(1, $vanilla->fresh()->quantity);

        $this->postJson('/cart/remove', ['cart_item_id' => $vanilla->id])->assertOk();
        $this->assertNull($vanilla->fresh());
        $this->assertNotNull($chocolate->fresh());

        // a page cached before cart line ids sends the product and its options
        $this->postJson('/cart/remove', ['product_id' => $product->id, 'selected_options' => json_encode(['Flavor' => 'Chocolate'])])->assertOk();
        $this->assertSame(0, CartItem::count());
    }

    public function test_update_never_creates_lines_and_cannot_touch_other_carts(): void
    {
        $product = Product::factory()->create();
        $this->addToCart($product)->assertOk();
        $this->postJson('/cart/update', ['product_id' => $product->id, 'quantity' => 3, 'selected_options' => '{"Flavor":"x"}'])->assertNotFound();
        $this->assertSame(1, CartItem::count());

        $other = Cart::create(['session_id' => 'someone-else', 'expires_at' => now()->addDay()]);
        $foreign = $other->items()->create(['product_id' => $product->id, 'quantity' => 1, 'selected_options' => '[]']);
        $this->postJson('/cart/update', ['cart_item_id' => $foreign->id, 'quantity' => 50])->assertNotFound();
        $this->postJson('/cart/remove', ['cart_item_id' => $foreign->id])->assertNotFound();
        $this->assertSame(1, $foreign->fresh()->quantity);
    }

    // ---------- the cart lasts 30 days ----------

    public function test_cart_is_created_once_and_its_expiry_moves_forward(): void
    {
        $product = Product::factory()->create();
        $this->addToCart($product)->assertOk();
        $cart = Cart::sole();
        $this->assertTrue(Carbon::parse($cart->expires_at)->gt(now()->addDays(29)));

        $cart->update(['expires_at' => now()->addHour()]);
        $this->addToCart($product, [], 2)->assertOk();

        $this->assertSame(1, Cart::count());
        $this->assertTrue(Carbon::parse($cart->fresh()->expires_at)->gt(now()->addDays(29)));
        $this->assertSame(3, (int) $cart->items()->sum('quantity'));
    }

    public function test_cart_follows_its_30_day_cookie_not_the_session(): void
    {
        $response = $this->addToCart(Product::factory()->create(), [], 2)->assertOk();
        $cookie = collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === CurrentCart::COOKIE);
        $this->assertNotNull($cookie);
        $this->assertEqualsWithDelta(now()->addDays(30)->timestamp, $cookie->getExpiresTime(), 120);
        $token = session('session_id');

        // The 2-hour session ended, the browser still has the cookie
        $this->flushSession();
        $items = $this->withCredentials()->withCookie(CurrentCart::COOKIE, $token)->getJson('/api/cart')->json('items');
        $this->assertCount(1, $items);
        $this->assertSame(2, $items[0]['quantity']);
    }

    public function test_a_cart_from_before_the_cookie_is_kept(): void
    {
        $cart = Cart::create(['session_id' => 'legacy-session-token', 'expires_at' => now()->addDay()]);
        $cart->items()->create(['product_id' => Product::factory()->create()->id, 'quantity' => 4, 'selected_options' => '[]']);

        $response = $this->withSession(['session_id' => 'legacy-session-token'])->getJson('/api/cart');

        $this->assertSame(4, $response->json('items.0.quantity'));
        $this->assertNotNull(collect($response->headers->getCookies())->first(fn ($c) => $c->getName() === CurrentCart::COOKIE));
    }

    public function test_two_carts_cannot_share_a_token(): void
    {
        Cart::create(['session_id' => 'race-test', 'expires_at' => now()]);

        try {
            DB::table('carts')->insert(['session_id' => 'race-test', 'expires_at' => now()]);
            $this->fail('a second cart with the same token was inserted');
        } catch (UniqueConstraintViolationException) {
        }

        $this->assertSame(1, Cart::where('session_id', 'race-test')->count());
    }
}

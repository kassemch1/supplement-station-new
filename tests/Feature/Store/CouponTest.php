<?php

namespace Tests\Feature\Store;

use App\Models\Coupon;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_a_valid_coupon_is_applied_shown_and_removed(): void
    {
        Coupon::factory()->create(['code' => 'SUMMER10', 'name' => 'Summer', 'discount_percentage' => 10]);
        $this->addToCart(Product::factory()->create(['price' => 80]))->assertOk();

        $this->postJson('/coupon/apply', ['coupon_code' => ' summer10 '])->assertOk()
            ->assertJson(['success' => true, 'cart_total' => 80, 'final_total' => 72, 'coupon' => ['code' => 'SUMMER10', 'discount_amount' => 8]])
            ->assertJsonPath('message', 'Coupon applied successfully! You saved $8.00');
        $this->getJson('/coupon/applied')->assertJsonPath('coupon.code', 'SUMMER10');

        $this->postJson('/coupon/apply', ['coupon_code' => 'SUMMER10'])->assertStatus(400)
            ->assertJsonPath('message', 'Please remove the existing coupon before applying a new one.');

        $this->postJson('/coupon/remove')->assertOk()->assertJson(['cart_total' => 80, 'final_total' => 80]);
        $this->getJson('/coupon/applied')->assertJson(['success' => false]);
        $this->postJson('/coupon/remove')->assertStatus(400);
    }

    public function test_unusable_coupons_say_why(): void
    {
        $this->addToCart(Product::factory()->create(['price' => 20]))->assertOk();
        Coupon::factory()->create(['code' => 'OFF', 'is_active' => false]);
        Coupon::factory()->singleUse()->create(['code' => 'USED', 'used_count' => 1]);
        Coupon::factory()->create(['code' => 'FULL', 'max_uses' => 2, 'used_count' => 2]);
        Coupon::factory()->create(['code' => 'BIG', 'minimum_order_amount' => 50]);

        $message = fn (string $code) => $this->postJson('/coupon/apply', ['coupon_code' => $code])->assertStatus(400)->json('message');

        $this->assertSame('Invalid coupon code.', $message('NOPE'));
        $this->assertSame('This coupon has been deactivated.', $message('OFF'));
        $this->assertSame('This coupon is no longer valid.', $message('USED'));
        $this->assertSame('This coupon has reached its maximum usage limit.', $message('FULL'));
        $this->assertSame('Minimum order amount of $50.00 required to use this coupon.', $message('BIG'));
    }

    public function test_a_coupon_needs_something_in_the_cart(): void
    {
        Coupon::factory()->create(['code' => 'EMPTY']);

        $this->postJson('/coupon/apply', ['coupon_code' => 'EMPTY'])->assertStatus(400)->assertJsonPath('message', 'Your cart is empty.');
    }

    // The admin types the expiry in Beirut time and it is stored as typed: a coupon "until 23:59"
    // must work until 23:59 in Beirut, not until 23:59 UTC (02:59 in Beirut in summer)
    public function test_expiry_is_beirut_time(): void
    {
        $coupon = Coupon::factory()->create(['code' => 'NIGHT']);
        $coupon->forceFill(['expires_at' => '2026-07-01 23:59:00'])->save();
        $this->assertSame('2026-07-01 23:59:00', $coupon->getRawOriginal('expires_at'), 'saved as typed');
        $this->addToCart(Product::factory()->create())->assertOk();

        Carbon::setTestNow('2026-07-01 20:30:00');   // 23:30 in Beirut
        $this->assertTrue($coupon->fresh()->isValid());
        $this->assertTrue(Coupon::active()->whereKey($coupon->id)->exists());

        Carbon::setTestNow('2026-07-01 21:30:00');   // 00:30 in Beirut, the next day
        $this->assertTrue($coupon->fresh()->isExpired());
        $this->assertFalse(Coupon::active()->whereKey($coupon->id)->exists());
        $this->postJson('/coupon/apply', ['coupon_code' => 'NIGHT'])->assertStatus(400)
            ->assertJsonPath('message', 'This coupon expired on Jul 01, 2026 11:59 PM (Beirut time).');
    }
}

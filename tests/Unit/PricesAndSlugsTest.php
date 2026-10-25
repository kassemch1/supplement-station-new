<?php

namespace Tests\Unit;

use App\Models\Coupon;
use App\Models\Product;
use App\Support\Slugs;
use PHPUnit\Framework\TestCase;

// Plain calculations, no database
class PricesAndSlugsTest extends TestCase
{
    public function test_the_discount_is_a_percentage_of_the_price(): void
    {
        $this->assertEqualsWithDelta(45.0, (new Product(['price' => 50, 'discount' => 10]))->price_after_discount, 0.0001);
        $this->assertSame(19.99, (new Product(['price' => 19.99, 'discount' => 0]))->price_after_discount);
        $this->assertEqualsWithDelta(0.0, (new Product(['price' => 30, 'discount' => 100]))->price_after_discount, 0.0001);
    }

    public function test_coupon_discount_and_minimum(): void
    {
        $coupon = new Coupon(['discount_percentage' => 15, 'minimum_order_amount' => 50]);

        $this->assertSame(15.0, $coupon->calculateDiscount(100));
        $this->assertSame(4.5, $coupon->calculateDiscount(29.99));   // rounded to cents
        $this->assertTrue($coupon->canApplyToOrder(50));
        $this->assertFalse($coupon->canApplyToOrder(49.99));
    }

    public function test_coupon_use_limits(): void
    {
        $this->assertTrue((new Coupon(['type' => 'single_use', 'used_count' => 1]))->isUsedUp());
        $this->assertFalse((new Coupon(['type' => 'single_use', 'used_count' => 0]))->isUsedUp());
        $this->assertTrue((new Coupon(['type' => 'reusable', 'max_uses' => 3, 'used_count' => 3]))->isUsedUp());
        $this->assertFalse((new Coupon(['type' => 'reusable', 'max_uses' => null, 'used_count' => 500]))->isUsedUp());
    }

    public function test_slugs_are_readable_and_never_look_like_ids(): void
    {
        $this->assertSame('optimum-nutrition-whey-gold-standard-2-27kg', Slugs::fromName('Optimum Nutrition Whey Gold Standard (2.27kg)', 'product'));
        $this->assertSame('bcaa-creatine', Slugs::fromName('Bcaa + Creatine', 'product'));
        $this->assertSame('product-100', Slugs::fromName('100', 'product'));
        $this->assertSame('category', Slugs::fromName('!!!', 'category'));
        $this->assertLessThanOrEqual(80, strlen(Slugs::fromName(str_repeat('very long name ', 20), 'product')));
    }
}

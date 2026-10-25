<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CouponFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => strtoupper(Str::random(8)),
            'name' => 'Test coupon',
            'type' => 'reusable',
            'discount_percentage' => 10,
            'minimum_order_amount' => 0,
            'max_uses' => null,
            'used_count' => 0,
            'is_active' => true,
            'expires_at' => null,
        ];
    }

    public function singleUse(): static
    {
        return $this->state(['type' => 'single_use', 'max_uses' => 1]);
    }
}

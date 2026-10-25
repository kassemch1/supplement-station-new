<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ShippingDetail;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 10, 200);

        return ['subtotal_amount' => $total, 'total_amount' => $total, 'coupon_discount_amount' => 0, 'status' => 'pending'];
    }

    public function status(string $status): static
    {
        return $this->state(['status' => $status]);
    }

    // With a customer and one order line, like an order placed through the checkout
    public function placed(): static
    {
        return $this->afterCreating(function (Order $order) {
            ShippingDetail::factory()->create(['order_id' => $order->id]);
            OrderItem::factory()->create(['order_id' => $order->id, 'price' => $order->total_amount]);
        });
    }
}

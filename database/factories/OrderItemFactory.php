<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_id' => Product::factory(),
            'product_name' => fn (array $attributes) => Product::withTrashed()->find($attributes['product_id'])?->name,
            'product_image' => null,
            'quantity' => 1,
            'price' => 20,
            'selected_options' => '[]',
        ];
    }
}

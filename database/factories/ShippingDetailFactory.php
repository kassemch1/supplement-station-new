<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingDetailFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'name' => $this->faker->name(),
            'phone' => '03123456',
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->streetAddress(),
            'city' => 'Beirut',
        ];
    }
}

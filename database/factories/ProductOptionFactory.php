<?php

namespace Database\Factories;

use App\Models\Option;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'option_id' => Option::factory(),
            'option_value' => ucfirst($this->faker->unique()->word()),
            'stock' => true,
        ];
    }
}

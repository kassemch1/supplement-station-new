<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'name' => $this->faker->firstName(), 'message' => $this->faker->sentence(), 'rating' => 5];
    }
}

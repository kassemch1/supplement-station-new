<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Option;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductOption;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucwords($this->faker->unique()->words(3, true)),
            'description' => $this->faker->sentence(12),
            'price' => $this->faker->randomFloat(2, 5, 120),
            'discount' => 0,
            'stock' => true,
            'category_id' => Category::factory(),
        ];
    }

    public function soldOut(): static
    {
        return $this->state(['stock' => false]);
    }

    public function withImage(): static
    {
        return $this->afterCreating(fn (Product $product) => ProductImage::factory()->create(['product_id' => $product->id]));
    }

    // One option type and its values, e.g. withOptions('Flavor', ['Vanilla' => true, 'Mango' => false]) (true = in stock)
    public function withOptions(string $name, array $values): static
    {
        return $this->afterCreating(function (Product $product) use ($name, $values) {
            $option = Option::firstOrCreate(['option_name' => $name]);
            foreach ($values as $value => $inStock) {
                ProductOption::create(['product_id' => $product->id, 'option_id' => $option->id, 'option_value' => $value, 'stock' => $inStock]);
            }
        });
    }
}

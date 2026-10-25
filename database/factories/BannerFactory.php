<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class BannerFactory extends Factory
{
    public function definition(): array
    {
        return ['product_id' => Product::factory(), 'image' => 'storage/banner_picture/banner.webp'];
    }
}

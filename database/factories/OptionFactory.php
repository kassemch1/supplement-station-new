<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class OptionFactory extends Factory
{
    public function definition(): array
    {
        return ['option_name' => ucfirst($this->faker->unique()->word())];
    }
}

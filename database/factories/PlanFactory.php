<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PlanFactory extends Factory
{
    public function definition(): array
    {
        return ['type' => 'Monthly', 'price' => 30, 'point1' => 'Gym access', 'point2' => null, 'point3' => null, 'point4' => null, 'point5' => null];
    }
}

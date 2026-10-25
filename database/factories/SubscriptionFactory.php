<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubscriptionFactory extends Factory
{
    public function definition(): array
    {
        return ['email' => $this->faker->unique()->safeEmail(), 'is_active' => true, 'subscribed_at' => now()];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}

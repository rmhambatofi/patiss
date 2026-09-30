<?php

namespace Database\Factories;

use App\Models\DeliveryTier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeliveryTier>
 */
class DeliveryTierFactory extends Factory
{
    public function definition(): array
    {
        return [
            'max_km' => fake()->randomElement([10, 25, 40, 60]),
            'price_cents' => fake()->numberBetween(5, 30) * 100,
        ];
    }
}

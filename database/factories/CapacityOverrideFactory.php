<?php

namespace Database\Factories;

use App\Models\CapacityOverride;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CapacityOverride>
 */
class CapacityOverrideFactory extends Factory
{
    public function definition(): array
    {
        return [
            'date' => fake()->unique()->dateTimeBetween('+1 week', '+6 months')->format('Y-m-d'),
            'capacity' => fake()->numberBetween(0, 4),
        ];
    }
}

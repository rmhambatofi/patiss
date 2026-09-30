<?php

namespace Database\Factories;

use App\Models\Commune;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Commune>
 */
class CommuneFactory extends Factory
{
    public function definition(): array
    {
        $department = fake()->randomElement(['75', '77', '78', '91', '92', '93', '94', '95', '60']);

        return [
            'insee_code' => $department.fake()->unique()->numerify('###'),
            'name' => fake()->unique()->city(),
            'postal_codes' => [$department.fake()->numerify('###')],
            'department_code' => $department,
            'latitude' => fake()->latitude(48.1, 49.8),
            'longitude' => fake()->longitude(1.4, 3.6),
            'distance_km' => fake()->randomFloat(2, 0, 120),
        ];
    }
}

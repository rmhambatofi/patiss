<?php

namespace Database\Factories;

use App\Enums\ContactOccasion;
use App\Models\ContactMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ContactMessage>
 */
class ContactMessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'contact' => fake()->safeEmail(),
            'occasion' => fake()->randomElement(ContactOccasion::cases()),
            'message' => fake()->paragraph(),
        ];
    }
}

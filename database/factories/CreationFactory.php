<?php

namespace Database\Factories;

use App\Enums\CreationCategory;
use App\Models\Creation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Creation>
 */
class CreationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->words(2, true)),
            'category' => fake()->randomElement(CreationCategory::cases()),
            'description' => fake()->sentence(8),
            'image_path' => null,
            'image_url' => 'https://images.example.test/'.fake()->uuid().'.jpg',
            'sort_order' => 0,
            'is_featured' => false,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }

    public function featured(): static
    {
        return $this->state(['is_featured' => true]);
    }

    public function withoutImage(): static
    {
        return $this->state(['image_path' => null, 'image_url' => null]);
    }
}

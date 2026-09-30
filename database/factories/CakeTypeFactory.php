<?php

namespace Database\Factories;

use App\Enums\Allergen;
use App\Models\CakeType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<CakeType>
 */
class CakeTypeFactory extends Factory
{
    public function definition(): array
    {
        $name = ucfirst(fake()->unique()->words(2, true));

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->sentence(12),
            'from_price_cents' => fake()->numberBetween(30, 120) * 100,
            'portion_formats' => [6, 8, 12],
            'customizable_options' => ['forme', 'photo', 'inscription', 'couleurs'],
            'min_lead_days' => 7,
            'base_allergens' => [Allergen::Gluten, Allergen::Eggs, Allergen::Milk],
            'sort_order' => 0,
            'is_published' => true,
        ];
    }

    public function unpublished(): static
    {
        return $this->state(['is_published' => false]);
    }
}

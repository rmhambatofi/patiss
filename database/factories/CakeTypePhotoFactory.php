<?php

namespace Database\Factories;

use App\Models\CakeType;
use App\Models\CakeTypePhoto;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * Uses a fake path on the public disk; no file is written.
 *
 * @extends Factory<CakeTypePhoto>
 */
class CakeTypePhotoFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cake_type_id' => CakeType::factory(),
            'path' => 'cake-types/'.Str::random(40).'.jpg',
            'alt' => fake()->sentence(4),
            'sort_order' => 0,
        ];
    }
}

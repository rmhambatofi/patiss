<?php

namespace Database\Factories;

use App\Enums\Allergen;
use App\Models\CakeRequest;
use App\Models\DepositLink;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DepositLink>
 */
class DepositLinkFactory extends Factory
{
    public function definition(): array
    {
        return [
            'cake_request_id' => CakeRequest::factory(),
            'token_hash' => fn () => DepositLink::hashToken(Str::random(64)),
            'total_cents' => 8500,
            'deposit_cents' => 2550,
            'allergens' => [Allergen::Gluten, Allergen::Eggs, Allergen::Milk],
            'slot' => 'Retrait le samedi entre 10 h et 12 h',
            'message' => null,
            'expires_at' => now()->addDays(3),
            'status' => DepositLink::STATUS_ACTIVE,
        ];
    }
}

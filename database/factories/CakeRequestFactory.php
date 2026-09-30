<?php

namespace Database\Factories;

use App\Enums\Fulfilment;
use App\Enums\RequestStatus;
use App\Enums\Source;
use App\Models\CakeRequest;
use App\Models\CakeType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CakeRequest>
 */
class CakeRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'reference' => fn () => CakeRequest::generateReference(),
            'kind' => CakeRequest::KIND_TYPE,
            'cake_type_id' => CakeType::factory(),
            'portions' => 8,
            'customization' => fake()->sentence(),
            'event_date' => now()->addDays(20)->toDateString(),
            'fulfilment' => Fulfilment::Pickup,
            'commune_id' => null,
            'delivery_fee_cents' => null,
            'allergies' => null,
            'allergy_consent_at' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => '06 '.fake()->numerify('## ## ## ##'),
            'source' => Source::Google,
            'out_of_lead_time' => false,
            'status' => RequestStatus::Nouvelle,
            'last_activity_at' => now(),
        ];
    }

    public function free(): static
    {
        return $this->state([
            'kind' => CakeRequest::KIND_LIBRE,
            'cake_type_id' => null,
        ]);
    }
}

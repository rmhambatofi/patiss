<?php

namespace App\Http\Requests;

use App\Enums\Fulfilment;
use App\Enums\Source;
use App\Models\CakeType;
use App\Models\Commune;
use App\Services\DeliveryZoneResolver;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

/**
 * Validates a type request (route has a cakeType) or a free request (R6, R9).
 */
class StoreCakeRequest extends FormRequest
{
    public const HONEYPOT = 'site_web';

    public const PHOTO_RULES = ['file', 'mimes:jpeg,png,webp,heic,heif', 'max:10240'];

    /**
     * A filled honeypot means a bot: pretend success, validate and store nothing (KTD11).
     */
    public function validateResolved(): void
    {
        abort_if($this->cakeType()?->isVisible() === false, 404);

        if (filled($this->input(self::HONEYPOT))) {
            throw new HttpResponseException(redirect()->route('request.thanks'));
        }

        parent::validateResolved();
    }

    public function authorize(): bool
    {
        return true;
    }

    public function cakeType(): ?CakeType
    {
        $type = $this->route('cakeType');

        return $type instanceof CakeType ? $type : null;
    }

    public function rules(): array
    {
        $type = $this->cakeType();

        $rules = [
            'event_date' => [
                'required',
                'date_format:Y-m-d',
                'after_or_equal:today',
                'before_or_equal:'.today()->addMonths(12)->toDateString(),
            ],
            'fulfilment' => ['required', Rule::enum(Fulfilment::class)],
            'commune_insee' => [
                Rule::requiredIf($this->input('fulfilment') === Fulfilment::Delivery->value),
                'nullable',
                'string',
                $this->deliverableCommune(...),
            ],
            'allergies' => ['nullable', 'string', 'max:2000'],
            'allergy_consent' => Rule::when(filled($this->input('allergies')), ['accepted']),
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+().\s-]{6,}$/'],
            'source' => ['required', Rule::enum(Source::class)],
            'photo_a_reproduire' => ['nullable', ...self::PHOTO_RULES],
            'inspirations' => ['nullable', 'array', 'max:4'],
            'inspirations.*' => self::PHOTO_RULES,
        ];

        if ($type) {
            $rules['portions'] = ['required', 'integer', Rule::in(array_map('intval', $type->portion_formats ?? []))];
            $rules['customization'] = ['nullable', 'string', 'max:5000'];
        } else {
            $rules['portions'] = ['required', 'integer', 'min:1', 'max:1000'];
            $rules['description'] = ['required', 'string', 'max:5000'];
        }

        return $rules;
    }

    public function attributes(): array
    {
        return [
            'portions' => 'nombre de parts',
            'customization' => 'personnalisation',
            'description' => 'description',
            'event_date' => "date de l'événement",
            'fulfilment' => 'retrait ou livraison',
            'commune_insee' => 'commune',
            'allergies' => 'allergies',
            'allergy_consent' => 'consentement',
            'name' => 'nom',
            'email' => 'e-mail',
            'phone' => 'téléphone',
            'source' => 'comment avez-vous connu l’artisan',
            'photo_a_reproduire' => 'photo à reproduire',
            'inspirations' => "photos d'inspiration",
            'inspirations.*' => "photo d'inspiration",
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Ce champ est obligatoire.',
            'email' => 'Cette adresse e-mail n’est pas valide.',
            'integer' => 'Indiquez un nombre entier.',
            'min' => 'Indiquez au moins :min.',
            'max.string' => ':max caractères au plus.',
            'date_format' => 'Cette date n’est pas valide.',
            'uploaded' => 'Le fichier n’a pas pu être envoyé : 10 Mo au plus.',
            'phone.regex' => 'Ce numéro de téléphone n’est pas valide.',
            'event_date.after_or_equal' => 'La date ne peut pas être dans le passé.',
            'event_date.before_or_equal' => 'Les demandes sont possibles jusqu’à 12 mois à l’avance.',
            'commune_insee.required' => 'Choisissez votre commune dans la liste pour la livraison.',
            'allergy_consent.accepted' => 'Cochez la case pour autoriser le traitement des allergies déclarées.',
            'source.required' => 'Dites-nous comment vous avez connu l’artisan.',
            'portions.in' => 'Choisissez l’un des formats proposés.',
            'inspirations.max' => 'Au plus 4 photos d’inspiration (5 fichiers en tout).',
            'photo_a_reproduire.max' => 'Chaque photo doit faire au plus 10 Mo.',
            'inspirations.*.max' => 'Chaque photo doit faire au plus 10 Mo.',
            'photo_a_reproduire.mimes' => 'Formats acceptés : JPEG, PNG, WebP ou HEIC.',
            'inspirations.*.mimes' => 'Formats acceptés : JPEG, PNG, WebP ou HEIC.',
        ];
    }

    public function commune(): ?Commune
    {
        $insee = $this->input('commune_insee');

        return filled($insee) ? Commune::where('insee_code', $insee)->first() : null;
    }

    /**
     * Delivery needs a known commune inside a delivery tier (R16), whatever the browser sent.
     */
    private function deliverableCommune(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->input('fulfilment') !== Fulfilment::Delivery->value) {
            return;
        }

        if (app(DeliveryZoneResolver::class)->resolve($this->commune()) === null) {
            $fail('Livraison impossible pour cette commune : retrait uniquement');
        }
    }
}

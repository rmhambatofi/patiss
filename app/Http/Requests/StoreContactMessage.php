<?php

namespace App\Http\Requests;

use App\Enums\ContactOccasion;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactMessage extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:80'],
            'contact' => ['required', 'string', 'max:255', $this->emailOrPhone()],
            'occasion' => ['required', Rule::enum(ContactOccasion::class)],
            'message' => ['required', 'string', 'max:3000'],
            // Honeypot : rempli uniquement par les robots.
            'website' => ['prohibited'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'prénom',
            'contact' => 'e-mail ou téléphone',
            'occasion' => 'occasion',
            'message' => 'message',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'website.prohibited' => 'Votre message n’a pas pu être envoyé.',
        ];
    }

    private function emailOrPhone(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            $isEmail = filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
            $isPhone = preg_match('/^\+?[\d\s().-]{8,20}$/', (string) $value) === 1
                && strlen(preg_replace('/\D/', '', (string) $value)) >= 8;

            if (! $isEmail && ! $isPhone) {
                $fail('Indiquez une adresse e-mail ou un numéro de téléphone.');
            }
        };
    }
}

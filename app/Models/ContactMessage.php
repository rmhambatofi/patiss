<?php

namespace App\Models;

use App\Enums\ContactOccasion;
use Database\Factories\ContactMessageFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Message reçu par le formulaire de contact de la vitrine.
 */
class ContactMessage extends Model
{
    /** @use HasFactory<ContactMessageFactory> */
    use HasFactory;

    protected $fillable = ['first_name', 'contact', 'occasion', 'message'];

    protected function casts(): array
    {
        return [
            'contact' => 'encrypted',
            'message' => 'encrypted',
            'occasion' => ContactOccasion::class,
            'read_at' => 'datetime',
        ];
    }

    /** The contact field accepts an e-mail or a phone number; only an e-mail can be replied to directly. */
    public function contactEmail(): ?string
    {
        return filter_var($this->contact, FILTER_VALIDATE_EMAIL) ? $this->contact : null;
    }
}

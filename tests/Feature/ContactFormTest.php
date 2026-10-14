<?php

use App\Enums\ContactOccasion;
use App\Models\ContactMessage;
use App\Settings\ShopSettings;

function validContact(array $overrides = []): array
{
    return $overrides + [
        'first_name' => 'Camille',
        'contact' => 'camille@mail.fr',
        'occasion' => 'mariage',
        'message' => 'Un layer cake pour 40 personnes le 12 juin, fleurs fraîches si possible.',
    ];
}

it('keeps the message for the atelier, e-mails the artisan and thanks the sender by name', function () {
    $settings = app(ShopSettings::class);
    $settings->notification_email = 'atelier@example.test';
    $settings->save();

    $this->from('/')->post('/contact', validContact())
        ->assertRedirect(route('home').'#contact')
        ->assertSessionHas('contact_sent', 'Camille');

    $message = ContactMessage::sole();
    expect($message->first_name)->toBe('Camille')
        ->and($message->contact)->toBe('camille@mail.fr')
        ->and($message->occasion)->toBe(ContactOccasion::Mariage)
        ->and($message->message)->toContain('layer cake pour 40 personnes')
        ->and($message->read_at)->toBeNull();

    $mail = sentMails()->sole();
    expect($mail['to'])->toBe(['atelier@example.test'])
        ->and($mail['subject'])->toBe('Message de Camille (Mariage)')
        ->and($mail['body'])->toContain('camille@mail.fr')->toContain('layer cake pour 40 personnes');

    $this->flushSession();
    $this->get('/')->assertOk()->assertDontSee('Merci, Camille.');
    $this->withSession(['contact_sent' => 'Camille'])->get('/')
        ->assertOk()
        ->assertSee('Merci, Camille.')
        ->assertSee('Votre message est bien parti.')
        ->assertDontSee('name="first_name"', false);
});

it('accepts a phone number instead of an e-mail and skips the e-mail without a notification address', function () {
    $this->post('/contact', validContact(['contact' => '06 12 34 56 78']))
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect(ContactMessage::sole()->contact)->toBe('06 12 34 56 78')
        ->and(sentMails())->toBeEmpty();
});

it('rejects an empty form, an unknown occasion and a contact that is neither an e-mail nor a phone', function () {
    $this->from('/')->post('/contact', [])
        ->assertRedirect('/')
        ->assertSessionHasErrors(['first_name', 'contact', 'occasion', 'message']);

    $this->post('/contact', validContact(['contact' => 'camille', 'occasion' => 'noel']))
        ->assertSessionHasErrors(['contact', 'occasion']);

    expect(ContactMessage::count())->toBe(0);
});

it('ignores robots that fill the honeypot', function () {
    $this->post('/contact', validContact(['website' => 'https://spam.example']))
        ->assertSessionHasErrors('website');

    expect(ContactMessage::count())->toBe(0);
});

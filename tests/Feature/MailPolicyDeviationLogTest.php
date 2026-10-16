<?php

use App\Mail\ContactMessageForArtisan;
use App\Models\ContactMessage;
use App\Support\MailPolicy;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

function sendOneContactMail(): void
{
    $message = ContactMessage::factory()->create(['first_name' => 'Camille', 'message' => 'Un fraisier pour dimanche.']);
    Mail::to('atelier@example.test')->send(new ContactMessageForArtisan($message));
}

it('logs a warning on each sending when smtp was requested outside local and production', function () {
    config(['mail.policy_reason' => MailPolicy::REASON_SMTP_REROUTED_TO_LOG]);
    Log::spy();

    sendOneContactMail();

    Log::shouldHaveReceived('warning')->once()->withArgs(fn (string $message, array $context): bool => str_contains($message, 'MAIL_MAILER=smtp ignoré')
        && $context['to'] === ['atelier@example.test']
        && ! str_contains(json_encode($context), 'Un fraisier pour dimanche.'));
    Log::shouldNotHaveReceived('error');
    expect(sentMails())->toHaveCount(1);
});

it('logs an error on each sending when production has no configured smtp', function () {
    config(['mail.policy_reason' => MailPolicy::REASON_SMTP_NOT_CONFIGURED]);
    Log::spy();

    sendOneContactMail();

    Log::shouldHaveReceived('error')->once()->withArgs(fn (string $message, array $context): bool => str_contains($message, 'SMTP non configuré en production')
        && $context['to'] === ['atelier@example.test']
        && ! str_contains(json_encode($context), 'Un fraisier pour dimanche.'));
    Log::shouldNotHaveReceived('warning');
});

it('stays silent when the mailer follows MAIL_MAILER', function () {
    config(['mail.policy_reason' => null]);
    Log::spy();

    sendOneContactMail();

    Log::shouldNotHaveReceived('warning');
    Log::shouldNotHaveReceived('error');
});

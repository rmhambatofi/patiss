<?php

use App\Support\MailPolicy;

it('keeps the log mailer outside production', function (?string $env) {
    expect(MailPolicy::resolve('log', $env, false))->toBe(['mailer' => 'log', 'reason' => null]);
})->with(['local', 'staging', 'testing', null, '']);

it('honours smtp in the local environment only', function () {
    expect(MailPolicy::resolve('smtp', 'local', false))->toBe(['mailer' => 'smtp', 'reason' => null]);
});

it('reroutes smtp to the log, with a warning reason, when APP_ENV is neither local nor production', function (?string $env) {
    expect(MailPolicy::resolve('smtp', $env, true))
        ->toBe(['mailer' => 'log', 'reason' => MailPolicy::REASON_SMTP_REROUTED_TO_LOG]);
})->with(['staging', 'recette', 'testing', null, '']);

it('always uses smtp in production, whatever MAIL_MAILER says among log and smtp', function (?string $requested) {
    expect(MailPolicy::resolve($requested, 'production', true))->toBe(['mailer' => 'smtp', 'reason' => null]);
})->with(['smtp', 'log', null]);

it('flags an unconfigured smtp in production with an error reason', function () {
    expect(MailPolicy::resolve('smtp', 'production', false))
        ->toBe(['mailer' => 'smtp', 'reason' => MailPolicy::REASON_SMTP_NOT_CONFIGURED]);
});

it('leaves any other mailer untouched in every environment', function (?string $env) {
    expect(MailPolicy::resolve('array', $env, false))->toBe(['mailer' => 'array', 'reason' => null])
        ->and(MailPolicy::resolve('ses', $env, false))->toBe(['mailer' => 'ses', 'reason' => null]);
})->with(['local', 'staging', 'production', null]);

it('considers smtp configured with a MAIL_URL or with host, username and password', function () {
    expect(MailPolicy::smtpConfigured('smtp://u:p@mail.example.test:465', null, null, null))->toBeTrue()
        ->and(MailPolicy::smtpConfigured(null, 'mail.example.test', 'contact@example.test', 'secret'))->toBeTrue()
        ->and(MailPolicy::smtpConfigured(null, 'mail.example.test', 'contact@example.test', null))->toBeFalse()
        ->and(MailPolicy::smtpConfigured(null, '127.0.0.1', null, null))->toBeFalse()
        ->and(MailPolicy::smtpConfigured(null, null, null, null))->toBeFalse();
});

it('forwards e-mails to the dev webhook everywhere but in production', function () {
    expect(MailPolicy::forwardsToDevWebhook('local'))->toBeTrue()
        ->and(MailPolicy::forwardsToDevWebhook('staging'))->toBeTrue()
        ->and(MailPolicy::forwardsToDevWebhook('testing'))->toBeTrue()
        ->and(MailPolicy::forwardsToDevWebhook(null))->toBeTrue()
        ->and(MailPolicy::forwardsToDevWebhook('production'))->toBeFalse();
});

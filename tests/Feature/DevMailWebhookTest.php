<?php

use App\Mail\ContactMessageForArtisan;
use App\Models\ContactMessage;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

function sendOneMail(): void
{
    $message = ContactMessage::factory()->create(['first_name' => 'Camille', 'message' => 'Un fraisier pour dimanche.']);
    Mail::to('atelier@example.test')->send(new ContactMessageForArtisan($message));
}

it('forwards every sent e-mail as HTML to the dev webhook in the local environment, and still logs it', function () {
    $this->app['env'] = 'local';
    config(['mail.dev_webhook_url' => 'https://hook.example.test/inbox']);
    Http::fake();

    sendOneMail();

    Http::assertSent(fn (Request $request): bool => $request->url() === 'https://hook.example.test/inbox'
        && $request->method() === 'POST'
        && $request->hasHeader('Content-Type', 'text/html')
        && $request->hasHeader('X-Mail-To', 'atelier@example.test')
        && str_contains($request->body(), 'Un fraisier pour dimanche.')
        && str_contains($request->body(), '<'));
    expect(sentMails())->toHaveCount(1);
});

it('never calls the webhook outside the local environment nor without a configured URL', function () {
    Http::fake();

    $this->app['env'] = 'production';
    config(['mail.dev_webhook_url' => 'https://hook.example.test/inbox']);
    sendOneMail();

    $this->app['env'] = 'local';
    config(['mail.dev_webhook_url' => null]);
    sendOneMail();

    Http::assertNothingSent();
    expect(sentMails())->toHaveCount(2);
});

it('keeps the e-mail when the webhook is unreachable', function () {
    $this->app['env'] = 'local';
    config(['mail.dev_webhook_url' => 'https://hook.example.test/inbox']);
    Http::fake(['hook.example.test/*' => Http::response('down', 503)]);

    sendOneMail();

    Http::assertSentCount(1);
    expect(sentMails())->toHaveCount(1);
});

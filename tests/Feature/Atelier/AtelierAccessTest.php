<?php

use App\Models\User;
use Filament\Auth\Pages\Login;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;

/**
 * The login code e-mails caught by the "array" mailer, newest last.
 *
 * @return list<string>
 */
function sentLoginCodes(): array
{
    return app('mailer')->getSymfonyTransport()->messages()
        ->map(fn ($message) => $message->getOriginalMessage()->getTextBody())
        ->map(fn (string $body) => preg_match('/code de connexion est (\d{6})/', $body, $m) ? $m[1] : null)
        ->filter()
        ->values()
        ->all();
}

it('redirects a guest who opens the atelier to the login page', function () {
    $this->get('/atelier')->assertRedirect('/atelier/login');
    $this->get('/atelier/cake-types')->assertRedirect('/atelier/login');
    $this->get('/atelier/login')->assertOk()->assertSee('Connexion')->assertDontSee('/atelier/register');
});

it('does not log in on a correct password alone and e-mails a code without the queue', function () {
    // A queued code would never leave if the cron worker is down: it must be sent synchronously.
    config(['queue.default' => 'database']);
    Queue::fake();
    $user = User::factory()->create(['email' => 'artisan@example.test', 'has_email_authentication' => true]);

    Livewire::test(Login::class)
        ->fillForm(['email' => 'artisan@example.test', 'password' => 'password'])
        ->call('authenticate')
        ->assertHasNoFormErrors();

    $this->assertGuest();
    Queue::assertNothingPushed();
    expect(sentLoginCodes())->toHaveCount(1);
    $this->get('/atelier/cake-types')->assertRedirect('/atelier/login');
});

it('logs in once the e-mailed code is entered', function () {
    $user = User::factory()->create(['email' => 'artisan@example.test', 'has_email_authentication' => true]);

    $login = Livewire::test(Login::class)
        ->fillForm(['email' => 'artisan@example.test', 'password' => 'password'])
        ->call('authenticate');
    $this->assertGuest();

    $login->set('data.multiFactor.email_code.code', '000000')
        ->call('authenticate')
        ->assertHasErrors();
    $this->assertGuest();

    $login->set('data.multiFactor.email_code.code', sentLoginCodes()[0])
        ->call('authenticate')
        ->assertHasNoErrors();
    $this->assertAuthenticatedAs($user);
});

it('sends a signed-in user who has not set up the e-mail code to the set-up page', function () {
    $user = User::factory()->create(['has_email_authentication' => false]);

    $this->actingAs($user)
        ->get('/atelier/cake-types')
        ->assertRedirect('/atelier/multi-factor-authentication/set-up');
    $this->get('/atelier/multi-factor-authentication/set-up')->assertOk();
});

it('lets a user with the e-mail code set up reach the resources', function () {
    $user = User::factory()->create(['has_email_authentication' => true]);

    $this->actingAs($user)->get('/atelier/cake-types')->assertOk();
    $this->actingAs($user)->get('/atelier/delivery-tiers')->assertOk();
    $this->actingAs($user)->get('/atelier/capacity-overrides')->assertOk();
    $this->actingAs($user)->get('/atelier/settings')->assertOk();
});

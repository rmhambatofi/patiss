<?php

use App\Models\DepositLink;
use App\Services\StripeGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\Fakes\FakeStripeGateway;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Build a JPEG whose APP1 segment carries an EXIF GPS block (latitude 49° N).
 */
function jpegWithGpsExif(): string
{
    ob_start();
    imagejpeg(imagecreatetruecolor(40, 30));
    $jpeg = ob_get_clean();

    // TIFF header (little endian), IFD0 pointing to a GPS IFD at 26, then the GPS IFD and its rationals at 56.
    $tiff = 'II'.pack('v', 0x2A).pack('V', 8)
        .pack('v', 1).pack('vvVV', 0x8825, 4, 1, 26).pack('V', 0)
        .pack('v', 2)
        .pack('vvV', 0x0001, 2, 2)."N\0\0\0"
        .pack('vvVV', 0x0002, 5, 3, 56)
        .pack('V', 0)
        .pack('VVVVVV', 49, 1, 1, 1, 0, 1);
    $payload = "Exif\0\0".$tiff;
    $app1 = "\xFF\xE1".pack('n', strlen($payload) + 2).$payload;

    return substr($jpeg, 0, 2).$app1.substr($jpeg, 2);
}

/**
 * Replaces the Stripe gateway with an in-memory fake for the whole test.
 */
function fakeStripe(): FakeStripeGateway
{
    $fake = new FakeStripeGateway;
    app()->instance(StripeGateway::class, $fake);

    return $fake;
}

/**
 * Posts a Stripe event signed as Stripe does: "t=<ts>,v1=<hmac_sha256(ts.payload)>".
 */
function postStripeWebhook(array $event, string $secret = 'whsec_test_secret'): TestResponse
{
    $payload = json_encode($event);
    $timestamp = time();
    $signature = hash_hmac('sha256', $timestamp.'.'.$payload, $secret);

    return test()->call('POST', '/stripe/webhook', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_STRIPE_SIGNATURE' => "t={$timestamp},v1={$signature}",
    ], content: $payload);
}

/**
 * A checkout.session.completed event as Stripe sends it (API 2025+: shipping under collected_information).
 *
 * @param  array<string, mixed>  $session  Overrides merged into the session object.
 */
function checkoutCompletedEvent(DepositLink $link, array $session = []): array
{
    return [
        'id' => 'evt_test_'.Str::random(12),
        'object' => 'event',
        'api_version' => '2026-08-26.dahlia',
        'created' => now()->getTimestamp(),
        'type' => 'checkout.session.completed',
        'data' => ['object' => array_replace([
            'id' => 'cs_test_link'.$link->id,
            'object' => 'checkout.session',
            'mode' => 'payment',
            'status' => 'complete',
            'payment_status' => 'paid',
            'amount_total' => $link->deposit_cents,
            'currency' => 'eur',
            'created' => now()->subMinutes(5)->getTimestamp(),
            'expires_at' => now()->addMinutes(25)->getTimestamp(),
            'payment_intent' => 'pi_test_link'.$link->id,
            'metadata' => [
                'deposit_link_id' => (string) $link->id,
                'cake_request_id' => (string) $link->cake_request_id,
            ],
            'customer_details' => ['email' => 'payeur@example.test', 'name' => 'Léa Martin'],
            'collected_information' => ['shipping_details' => null],
        ], $session)],
    ];
}

/**
 * Every e-mail actually sent through the array mailer, rendered as text.
 *
 * @return Collection<int, array{to: list<string>, subject: string, body: string}>
 */
function sentMails(): Collection
{
    return app('mailer')->getSymfonyTransport()->messages()->map(function ($sent) {
        $email = $sent->getOriginalMessage();

        return [
            'to' => array_map(fn ($address) => $address->getAddress(), $email->getTo()),
            'subject' => (string) $email->getSubject(),
            'body' => html_entity_decode(strip_tags((string) $email->getHtmlBody()), ENT_QUOTES).' '.$email->getTextBody(),
        ];
    })->values();
}

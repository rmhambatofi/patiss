<?php

namespace App\Listeners;

use App\Support\MailPolicy;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Partout sauf en production : chaque e-mail envoyé (donc, en général, écrit dans le log par le
 * mailer « log ») est aussi transmis en POST, corps HTML, à l'URL MAIL_DEV_WEBHOOK_URL pour être lu
 * dans un navigateur. Le log Laravel reste la trace de référence ; un échec du webhook est
 * signalé mais n'empêche jamais l'envoi.
 */
class ForwardMailToDevWebhook
{
    public function handle(MessageSent $event): void
    {
        $url = config('mail.dev_webhook_url');

        if (! MailPolicy::forwardsToDevWebhook(app()->environment()) || blank($url)) {
            return;
        }

        $email = $event->message;
        $html = $email->getHtmlBody() ?? nl2br(e((string) $email->getTextBody()));

        try {
            Http::timeout(5)
                ->withHeaders([
                    'X-Mail-Subject' => (string) $email->getSubject(),
                    'X-Mail-To' => implode(', ', array_map(fn ($address) => $address->getAddress(), $email->getTo())),
                ])
                ->withBody($html, 'text/html')
                ->post($url);
        } catch (Throwable $e) {
            report($e);
        }
    }
}

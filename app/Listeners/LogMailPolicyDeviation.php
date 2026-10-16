<?php

namespace App\Listeners;

use App\Support\MailPolicy;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Log;

/**
 * Signale, à chaque envoi d'e-mail, que le mailer utilisé n'est pas celui demandé par MAIL_MAILER
 * (voir App\Support\MailPolicy et config/mail.php) :
 * - avertissement quand MAIL_MAILER=smtp est ignoré hors local/production et que l'e-mail est
 *   écrit dans le log ;
 * - erreur quand la production n'a pas de SMTP configuré : l'envoi va échouer.
 *
 * Seuls le sujet et les destinataires sont journalisés ici, jamais le contenu.
 */
class LogMailPolicyDeviation
{
    public function handle(MessageSending $event): void
    {
        $reason = config('mail.policy_reason');

        if (blank($reason)) {
            return;
        }

        $context = [
            'subject' => (string) $event->message->getSubject(),
            'to' => array_map(fn ($address) => $address->getAddress(), $event->message->getTo()),
            'app_env' => config('app.env'),
        ];

        match ($reason) {
            MailPolicy::REASON_SMTP_REROUTED_TO_LOG => Log::warning(
                'MAIL_MAILER=smtp ignoré : APP_ENV n’est ni « local » ni « production », l’e-mail est écrit dans le log au lieu d’être envoyé par SMTP.',
                $context,
            ),
            MailPolicy::REASON_SMTP_NOT_CONFIGURED => Log::error(
                'SMTP non configuré en production (MAIL_URL ou MAIL_HOST/MAIL_USERNAME/MAIL_PASSWORD vides) : l’e-mail ne peut pas être envoyé.',
                $context,
            ),
            default => null,
        };
    }
}

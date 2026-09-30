<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use SensitiveParameter;

/**
 * The atelier login code, sent at once rather than through the queue: a stopped
 * queue worker must not lock both atelier accounts out. Same content as
 * Filament's VerifyEmailAuthentication, which implements ShouldQueue.
 */
class AtelierLoginCode extends Notification
{
    public function __construct(
        #[SensitiveParameter]
        public string $code,
        public int $codeExpiryMinutes,
    ) {}

    /**
     * @return array<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $replace = ['code' => $this->code, 'minutes' => $this->codeExpiryMinutes];

        return (new MailMessage)
            ->subject(__('filament-panels::auth/multi-factor/email/notifications/verify-email-authentication.subject'))
            ->line(trans_choice('filament-panels::auth/multi-factor/email/notifications/verify-email-authentication.lines.0', $this->codeExpiryMinutes, $replace))
            ->line(trans_choice('filament-panels::auth/multi-factor/email/notifications/verify-email-authentication.lines.1', $this->codeExpiryMinutes, $replace));
    }
}

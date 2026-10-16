<?php

namespace App\Support;

/**
 * Décide quel mailer est réellement utilisé à partir des variables d'environnement, avant que la
 * configuration ne soit (éventuellement) mise en cache.
 *
 * Règles :
 * - `MAIL_MAILER=log` : les e-mails sont écrits dans le log (sauf en production, voir ci-dessous).
 * - `MAIL_MAILER=smtp` en `local` : le SMTP demandé est respecté (tests d'un vrai envoi).
 * - `MAIL_MAILER=smtp` sur tout autre environnement non production (`staging`, `testing`, APP_ENV
 *   vide…) : les e-mails restent dans le log et un avertissement est journalisé à chaque envoi.
 * - `APP_ENV=production` : le SMTP est toujours utilisé et le contenu des e-mails n'est jamais
 *   journalisé. Si le SMTP n'est pas configuré, une erreur est journalisée à chaque envoi.
 * - Tout autre mailer (`array`, `ses`, `failover`…) est appliqué tel quel.
 *
 * On lit `env('APP_ENV')` brut (et non `app()->environment()`) pour distinguer « non renseigné »
 * de `production`, valeur par défaut de `config/app.php`.
 */
final class MailPolicy
{
    public const PRODUCTION = 'production';

    public const LOCAL = 'local';

    /** `MAIL_MAILER=smtp` hors local/production : l'e-mail est écrit dans le log à la place. */
    public const REASON_SMTP_REROUTED_TO_LOG = 'smtp_rerouted_to_log';

    /** Production sans SMTP configuré : l'envoi échouera, une erreur est journalisée. */
    public const REASON_SMTP_NOT_CONFIGURED = 'smtp_not_configured_in_production';

    /**
     * @return array{mailer: string, reason: string|null}
     */
    public static function resolve(?string $requestedMailer, ?string $appEnv, bool $smtpConfigured): array
    {
        $requested = blank($requestedMailer) ? 'log' : trim((string) $requestedMailer);
        $env = blank($appEnv) ? null : trim((string) $appEnv);

        if ($env === self::PRODUCTION) {
            if (! in_array($requested, ['log', 'smtp'], true)) {
                return ['mailer' => $requested, 'reason' => null];
            }

            return [
                'mailer' => 'smtp',
                'reason' => $smtpConfigured ? null : self::REASON_SMTP_NOT_CONFIGURED,
            ];
        }

        if ($requested === 'smtp' && $env !== self::LOCAL) {
            return ['mailer' => 'log', 'reason' => self::REASON_SMTP_REROUTED_TO_LOG];
        }

        return ['mailer' => $requested, 'reason' => null];
    }

    /**
     * Un SMTP est considéré configuré avec une `MAIL_URL`, ou avec un hôte, un identifiant et un mot
     * de passe (boîte du domaine cPanel, voir docs/operations/emails-dns.md).
     */
    public static function smtpConfigured(?string $url, ?string $host, ?string $username, ?string $password): bool
    {
        if (filled($url)) {
            return true;
        }

        return filled($host) && filled($username) && filled($password);
    }

    /** Les e-mails sont-ils relayés au webhook de développement ? Partout sauf en production. */
    public static function forwardsToDevWebhook(?string $appEnv): bool
    {
        return trim((string) $appEnv) !== self::PRODUCTION;
    }
}

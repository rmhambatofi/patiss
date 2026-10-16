<?php

use App\Support\MailPolicy;

// Le mailer réellement utilisé dépend de MAIL_MAILER et de APP_ENV (voir App\Support\MailPolicy) :
// hors local et production, un MAIL_MAILER=smtp reste dans le log ; en production le SMTP est
// toujours utilisé. Le calcul est fait ici, et non au démarrage, pour survivre à `config:cache`
// (après quoi env() ne lit plus le fichier .env).
$policy = MailPolicy::resolve(
    requestedMailer: env('MAIL_MAILER'),
    appEnv: env('APP_ENV'),
    smtpConfigured: MailPolicy::smtpConfigured(env('MAIL_URL'), env('MAIL_HOST'), env('MAIL_USERNAME'), env('MAIL_PASSWORD')),
);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Mailer
    |--------------------------------------------------------------------------
    |
    | This option controls the default mailer that is used to send all email
    | messages unless another mailer is explicitly specified when sending
    | the message. All additional mailers can be configured within the
    | "mailers" array. Examples of each type of mailer are provided.
    |
    | Here it is the result of App\Support\MailPolicy, not MAIL_MAILER alone.
    | "policy_reason" explains a deviation from MAIL_MAILER so that
    | App\Listeners\LogMailPolicyDeviation can log a warning or an error at
    | sending time.
    |
    */

    'default' => $policy['mailer'],

    'policy_reason' => $policy['reason'],

    /*
    |--------------------------------------------------------------------------
    | Mailer Configurations
    |--------------------------------------------------------------------------
    |
    | Here you may configure all of the mailers used by your application plus
    | their respective settings. Several examples have been configured for
    | you and you are free to add your own as your application requires.
    |
    | Laravel supports a variety of mail "transport" drivers that can be used
    | when delivering an email. You may specify which one you're using for
    | your mailers below. You may also add additional mailers if needed.
    |
    | Supported: "smtp", "sendmail", "mailgun", "ses", "ses-v2",
    |            "postmark", "resend", "log", "array",
    |            "failover", "roundrobin"
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Dev Webhook
    |--------------------------------------------------------------------------
    |
    | In every environment except production, every sent e-mail is also POSTed
    | as HTML to this URL (see App\Listeners\ForwardMailToDevWebhook). Leave
    | empty to disable. The log mailer keeps writing the e-mail to the log.
    |
    */

    'dev_webhook_url' => env('MAIL_DEV_WEBHOOK_URL'),

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env('MAIL_SCHEME'),
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env('MAIL_EHLO_DOMAIN', parse_url((string) env('APP_URL', 'http://localhost'), PHP_URL_HOST)),
        ],

        'ses' => [
            'transport' => 'ses',
        ],

        'postmark' => [
            'transport' => 'postmark',
            // 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
            // 'client' => [
            //     'timeout' => 5,
            // ],
        ],

        'resend' => [
            'transport' => 'resend',
        ],

        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => env('MAIL_LOG_CHANNEL'),
        ],

        'array' => [
            'transport' => 'array',
        ],

        'failover' => [
            'transport' => 'failover',
            'mailers' => [
                'smtp',
                'log',
            ],
            'retry_after' => 60,
        ],

        'roundrobin' => [
            'transport' => 'roundrobin',
            'mailers' => [
                'ses',
                'postmark',
            ],
            'retry_after' => 60,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Global "From" Address
    |--------------------------------------------------------------------------
    |
    | You may wish for all emails sent by your application to be sent from
    | the same address. Here you may specify a name and address that is
    | used globally for all emails that are sent by your application.
    |
    */

    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'Laravel')),
    ],

];

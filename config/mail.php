<?php

$mailEnv = static function (string $key, mixed $default = null): mixed {
    $value = env($key, $default);

    return is_string($value) ? trim($value, " \t\n\r\0\x0B\"'") : $value;
};

$mailBool = static fn (string $key, mixed $default = false): bool => filter_var(
    $mailEnv($key, $default),
    FILTER_VALIDATE_BOOLEAN
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
    */

    'default' => $mailEnv('MAIL_MAILER', 'log'),

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

    'mailers' => [

        'smtp' => [
            'transport' => 'smtp',
            'scheme' => $mailEnv('MAIL_SCHEME'),
            'url' => $mailEnv('MAIL_URL'),
            'host' => $mailEnv('MAIL_HOST', '127.0.0.1'),
            'port' => $mailEnv('MAIL_PORT', 2525),
            'username' => $mailEnv('MAIL_USERNAME'),
            'password' => (static function () {
                $value = env('MAIL_PASSWORD');
                $password = is_string($value) ? trim($value, " \t\n\r\0\x0B\"'") : $value;
                $hostValue = env('MAIL_HOST', '');
                $host = is_string($hostValue) ? trim($hostValue, " \t\n\r\0\x0B\"'") : (string) $hostValue;

                if (is_string($password) && str_contains($host, 'gmail.com')) {
                    return preg_replace('/\s+/', '', $password);
                }

                return $password;
            })(),
            'timeout' => $mailEnv('MAIL_TIMEOUT', 6),
            'local_domain' => $mailEnv('MAIL_EHLO_DOMAIN', parse_url((string) $mailEnv('APP_URL', 'http://localhost'), PHP_URL_HOST)),
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
            'path' => $mailEnv('MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i'),
        ],

        'log' => [
            'transport' => 'log',
            'channel' => $mailEnv('MAIL_LOG_CHANNEL'),
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
        'address' => $mailEnv('MAIL_FROM_ADDRESS', 'hello@example.com'),
        'name' => $mailEnv('MAIL_FROM_NAME', $mailEnv('APP_NAME', 'UniRoad')),
    ],

    'contact_notification' => $mailEnv('CONTACT_NOTIFICATION_EMAIL'),

    'support_notification' => $mailEnv('SUPPORT_NOTIFICATION_EMAIL', $mailEnv('CONTACT_NOTIFICATION_EMAIL')),

    'support_mailer' => $mailEnv('SUPPORT_MAILER', 'resend'),

    'support_from' => [
        'address' => $mailEnv('SUPPORT_FROM_ADDRESS', 'onboarding@resend.dev'),
        'name' => $mailEnv('SUPPORT_FROM_NAME', $mailEnv('APP_NAME', 'UniRoad')),
    ],

    'contact_mailer' => $mailEnv('CONTACT_MAILER', $mailEnv('MAIL_MAILER', 'log')),

    'password_reset_mailer' => $mailEnv('PASSWORD_RESET_MAILER', $mailEnv('CONTACT_MAILER', $mailEnv('MAIL_MAILER', 'log'))),

    'email_verification_mailer' => $mailEnv('EMAIL_VERIFICATION_MAILER', $mailEnv('PASSWORD_RESET_MAILER', $mailEnv('MAIL_MAILER', 'log'))),

    'external_sending_enabled' => $mailBool('EMAIL_EXTERNAL_SENDING_ENABLED', false),

    'registration_mailer' => $mailEnv('REGISTRATION_MAILER', $mailEnv('EMAIL_VERIFICATION_MAILER', $mailEnv('CONTACT_MAILER', $mailEnv('MAIL_MAILER', 'log')))),

    'email_verification_enabled' => $mailBool('EMAIL_VERIFICATION_ENABLED', $mailEnv('EMAIL_EXTERNAL_SENDING_ENABLED', false)),

    'registration_emails_enabled' => $mailBool('REGISTRATION_EMAILS_ENABLED', false),

    'contact_confirmation_enabled' => $mailBool('CONTACT_CONFIRMATION_EMAIL_ENABLED', $mailEnv('EMAIL_EXTERNAL_SENDING_ENABLED', false)),

];

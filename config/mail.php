<?php

return [
    /**
     * 默认邮件发送器
     * 这个选项决定发送邮件时默认使用的发送器。如果发送时没有明确指定其他发送器，就会使用此配置。其他发送器可在 "mailers" 数组中配置，下方提供了各类型的示例。
     */
    'default' => env( 'MAIL_MAILER', 'log' ),
    /**
     * 邮件发送器配置
     * 这里可以配置应用使用的所有邮件发送器及其参数。下方提供了多个示例，你可以根据应用需求添加自己的配置。
     * Laravel 支持多种邮件传输驱动。可以为下方的发送器指定传输驱动，也可以按需添加更多发送器。
     * 支持的驱动："smtp"、"sendmail"、"mailgun"、"ses"、"ses-v2"、"postmark"、"resend"、"log"、"array"、"failover"、"roundrobin"。
     */
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'scheme' => env( 'MAIL_SCHEME' ),
            'url' => env( 'MAIL_URL' ),
            'host' => env( 'MAIL_HOST', '127.0.0.1' ),
            'port' => env( 'MAIL_PORT', 2525 ),
            'username' => env( 'MAIL_USERNAME' ),
            'password' => env( 'MAIL_PASSWORD' ),
            'timeout' => null,
            'local_domain' => env( 'MAIL_EHLO_DOMAIN', parse_url( (string) env( 'APP_URL', 'http://localhost' ), PHP_URL_HOST ) ),
        ],
        'ses' => [
            'transport' => 'ses',
        ],
        'postmark' => [
            'transport' => 'postmark',
            /**
             * 'message_stream_id' => env('POSTMARK_MESSAGE_STREAM_ID'),
             * 'client' => [
             *     'timeout' => 5,
             * ],
             */
        ],
        'resend' => [
            'transport' => 'resend',
        ],
        'sendmail' => [
            'transport' => 'sendmail',
            'path' => env( 'MAIL_SENDMAIL_PATH', '/usr/sbin/sendmail -bs -i' ),
        ],
        'log' => [
            'transport' => 'log',
            'channel' => env( 'MAIL_LOG_CHANNEL' ),
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
    /**
     * 全局发件人地址
     * 如果希望应用的所有邮件使用同一个发件人，可以在这里指定全局使用的发件人姓名和邮箱地址。
     */
    'from' => [
        'address' => env( 'MAIL_FROM_ADDRESS', 'hello@example.com' ),
        'name' => env( 'MAIL_FROM_NAME', env( 'APP_NAME', 'Laravel' ) ),
    ],
];
<?php

return [
    /**
     * 第三方服务
     * 此文件用于存储 Mailgun、Postmark、AWS 等第三方服务的凭据。将这些信息统一存放在此文件中，便于扩展包按约定查找各项服务的凭据。
     */
    'postmark' => [
        'key' => env( 'POSTMARK_API_KEY' ),
    ],
    'resend' => [
        'key' => env( 'RESEND_API_KEY' ),
    ],
    'ses' => [
        'key' => env( 'AWS_ACCESS_KEY_ID' ),
        'secret' => env( 'AWS_SECRET_ACCESS_KEY' ),
        'region' => env( 'AWS_DEFAULT_REGION', 'us-east-1' ),
    ],
    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env( 'SLACK_BOT_USER_OAUTH_TOKEN' ),
            'channel' => env( 'SLACK_BOT_USER_DEFAULT_CHANNEL' ),
        ],
    ],
];
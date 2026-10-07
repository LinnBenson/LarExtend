<?php

use App\Models\User;

return [
    /**
     * 默认身份验证配置
     * 这里定义应用默认的身份验证守卫（guard）和密码重置代理（broker）。你可以按需修改，默认值适用于大多数应用。
     */
    'defaults' => [
        'guard' => env( 'AUTH_GUARD', 'web' ),
        'passwords' => env( 'AUTH_PASSWORD_BROKER', 'users' ),
    ],
    /**
     * 身份验证守卫
     * 这里可以定义应用的所有身份验证守卫。默认配置使用会话存储和 Eloquent 用户提供器。
     * 每个守卫都有用户提供器，用于从数据库或其他存储系统中获取用户，通常使用 Eloquent。
     * 支持的驱动："session"。
     */
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ]
    ],
    /**
     * 用户提供器
     * 每个身份验证守卫都有用户提供器，用于从数据库或其他存储系统中获取用户，通常使用 Eloquent。
     * 如果应用有多个用户表或模型，可以配置多个提供器来对应这些模型或表，并将它们分配给其他身份验证守卫。
     * 支持的驱动："database"、"eloquent"。
     */
    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => env( 'AUTH_MODEL', User::class ),
        ],

        /**
         * 'users' => [
         *     'driver' => 'database',
         *     'table' => 'users',
         * ],
         */
    ],
    /**
     * 密码重置
     * 这些选项配置 Laravel 密码重置功能，包括存储令牌的数据表和获取用户的用户提供器。
     * expire 指定重置令牌的有效分钟数。较短的有效期可减少令牌被猜中的时间，你可以按需调整。
     * throttle 指定用户再次生成密码重置令牌前需要等待的秒数，防止用户在短时间内生成大量令牌。
     */
    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env( 'AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens' ),
            'expire' => 60,
            'throttle' => 60,
        ],
    ],
    /**
     * 密码确认超时时间
     * 这里定义密码确认的有效秒数。超时后，用户需要在确认页面重新输入密码，默认有效期为三小时。
     */
    'password_timeout' => env( 'AUTH_PASSWORD_TIMEOUT', 10800 ),
];
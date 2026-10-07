<?php

return [
    /**
     * 应用名称
     * 这个值是应用名称，框架在通知或其他需要显示应用名称的界面元素中会使用它。
     */
    'name' => env( 'APP_NAME', 'Laravel' ),
    /**
     * 应用环境
     * 这个值决定应用当前运行的环境，并影响应用中各种服务的配置方式。请在 ".env" 文件中设置。
     */
    'env' => env( 'APP_ENV', 'production' ),
    /**
     * 应用调试模式
     * 应用处于调试模式时，发生错误会显示带堆栈跟踪的详细错误信息。关闭后只会显示简单的通用错误页。
     */
    'debug' => (bool) env( 'APP_DEBUG', false ),
    /**
     * 应用地址
     * 控制台使用 Artisan 命令生成 URL 时会用到这个地址。应设置为应用的根地址，方便 Artisan 命令使用。
     */
    'url' => env( 'APP_URL', 'http://localhost' ),
    /**
     * 应用时区
     * 这里可以指定应用默认时区，PHP 日期和日期时间函数会使用它。默认时区是 "UTC"，适合大多数使用场景。
     */
    'timezone' => env( 'APP_TIMEZONE', 'UTC' ),
    /**
     * 应用语言配置
     * 应用语言决定 Laravel 翻译和本地化方法默认使用的语言。可以设置为你计划提供翻译字符串的任意语言。
     */
    'locale' => env( 'APP_LOCALE', 'en' ),
    'locales' => [
        'en' => 'English',
        'zh_CN' => '简体中文',
    ],
    'fallback_locale' => env( 'APP_FALLBACK_LOCALE', 'en' ),
    'faker_locale' => env( 'APP_FAKER_LOCALE', 'en_US' ),
    /**
     * 加密密钥
     * Laravel 加密服务会使用此密钥。应在部署应用前将其设置为随机的 32 字符字符串，以确保加密数据的安全。
     */
    'cipher' => 'AES-256-CBC',
    'key' => env( 'APP_KEY' ),
    'previous_keys' => [
        ...array_filter(
            explode( ',', (string) env( 'APP_PREVIOUS_KEYS', '' ) )
        ),
    ],
    /**
     * 维护模式驱动
     * 这些选项决定 Laravel 用于判断和管理维护模式状态的驱动。使用 "cache" 驱动可以在多台机器之间统一控制维护模式。
     * 支持的驱动："file"、"cache"。
     */
    'maintenance' => [
        'driver' => env( 'APP_MAINTENANCE_DRIVER', 'file' ),
        'store' => env( 'APP_MAINTENANCE_STORE', 'database' ),
    ],
];
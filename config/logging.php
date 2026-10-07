<?php

use Monolog\Handler\NullHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\SyslogUdpHandler;
use Monolog\Processor\PsrLogMessageProcessor;

return [
    /**
     * 默认日志通道
     * 这个选项定义写入日志时默认使用的通道。这里的值应对应下方 "channels" 中配置的某个通道。
     */
    'default' => env( 'LOG_CHANNEL', 'stack' ),
    /**
     * 弃用警告日志通道
     * 这个选项指定记录 PHP 或依赖库功能弃用警告时使用的日志通道，帮助应用为依赖的下一个主要版本做好准备。
     */
    'deprecations' => [
        'channel' => env( 'LOG_DEPRECATIONS_CHANNEL', 'null' ),
        'trace' => env( 'LOG_DEPRECATIONS_TRACE', false ),
    ],
    /**
     * 日志通道
     * 这里可以配置应用的日志通道。Laravel 使用 Monolog 日志库，其中提供多种功能强大的日志处理器和格式化器。
     * 可用的驱动："single"、"daily"、"slack"、"syslog"、"errorlog"、"monolog"、"custom"、"stack"。
     */
    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => explode( ',', (string) env( 'LOG_STACK', 'single' ) ),
            'ignore_exceptions' => false,
        ],
        'single' => [
            'driver' => 'single',
            'path' => storage_path( 'logs/laravel.log' ),
            'level' => env( 'LOG_LEVEL', 'debug' ),
            'replace_placeholders' => true,
        ],
        'daily' => [
            'driver' => 'daily',
            'path' => storage_path( 'logs/laravel.log' ),
            'level' => env( 'LOG_LEVEL', 'debug' ),
            'days' => env( 'LOG_DAILY_DAYS', 14 ),
            'replace_placeholders' => true,
        ],
        'slack' => [
            'driver' => 'slack',
            'url' => env( 'LOG_SLACK_WEBHOOK_URL' ),
            'username' => env( 'LOG_SLACK_USERNAME', env( 'APP_NAME', 'Laravel' ) ),
            'emoji' => env( 'LOG_SLACK_EMOJI', ':boom:' ),
            'level' => env( 'LOG_LEVEL', 'critical' ),
            'replace_placeholders' => true,
        ],
        'papertrail' => [
            'driver' => 'monolog',
            'level' => env( 'LOG_LEVEL', 'debug' ),
            'handler' => env( 'LOG_PAPERTRAIL_HANDLER', SyslogUdpHandler::class ),
            'handler_with' => [
                'host' => env( 'PAPERTRAIL_URL' ),
                'port' => env( 'PAPERTRAIL_PORT' ),
                'connectionString' => 'tls://'.env( 'PAPERTRAIL_URL' ).':'.env( 'PAPERTRAIL_PORT' ),
            ],
            'processors' => [PsrLogMessageProcessor::class],
        ],
        'stderr' => [
            'driver' => 'monolog',
            'level' => env( 'LOG_LEVEL', 'debug' ),
            'handler' => StreamHandler::class,
            'handler_with' => [
                'stream' => 'php://stderr',
            ],
            'formatter' => env( 'LOG_STDERR_FORMATTER' ),
            'processors' => [PsrLogMessageProcessor::class],
        ],
        'syslog' => [
            'driver' => 'syslog',
            'level' => env( 'LOG_LEVEL', 'debug' ),
            'facility' => env( 'LOG_SYSLOG_FACILITY', LOG_USER ),
            'replace_placeholders' => true,
        ],
        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env( 'LOG_LEVEL', 'debug' ),
            'replace_placeholders' => true,
        ],
        'null' => [
            'driver' => 'monolog',
            'handler' => NullHandler::class,
        ],
        'emergency' => [
            'path' => storage_path( 'logs/laravel.log' ),
        ],
    ],
];
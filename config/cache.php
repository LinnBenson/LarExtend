<?php

use Illuminate\Support\Str;

return [
    /**
     * 默认缓存存储
     * 这个选项决定框架默认使用的缓存存储。应用执行缓存操作时，如果没有明确指定其他存储，就会使用此配置。
     */
    'default' => env( 'CACHE_STORE', 'database' ),
    /**
     * 缓存存储
     * 这里可以定义应用的所有缓存存储及其驱动。同一个驱动可以配置多个存储，以对缓存内容进行分组。
     * 支持的驱动："array"、"database"、"file"、"memcached"、"redis"、"dynamodb"、"octane"、"failover"、"null"。
     */
    'stores' => [
        'array' => [
            'driver' => 'array',
            'serialize' => false,
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env( 'DB_CACHE_CONNECTION' ),
            'table' => env( 'DB_CACHE_TABLE', 'cache' ),
            'lock_connection' => env( 'DB_CACHE_LOCK_CONNECTION' ),
            'lock_table' => env( 'DB_CACHE_LOCK_TABLE' ),
        ],
        'file' => [
            'driver' => 'file',
            'path' => storage_path( 'framework/cache/data' ),
            'lock_path' => storage_path( 'framework/cache/data' ),
        ],
        'memcached' => [
            'driver' => 'memcached',
            'persistent_id' => env( 'MEMCACHED_PERSISTENT_ID' ),
            'sasl' => [
                env( 'MEMCACHED_USERNAME' ),
                env( 'MEMCACHED_PASSWORD' ),
            ],
            'options' => [
                /**
                 * Memcached::OPT_CONNECT_TIMEOUT => 2000,
                 */
            ],
            'servers' => [
                [
                    'host' => env( 'MEMCACHED_HOST', '127.0.0.1' ),
                    'port' => env( 'MEMCACHED_PORT', 11211 ),
                    'weight' => 100,
                ],
            ],
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env( 'REDIS_CACHE_CONNECTION', 'cache' ),
            'lock_connection' => env( 'REDIS_CACHE_LOCK_CONNECTION', 'default' ),
        ],
        'dynamodb' => [
            'driver' => 'dynamodb',
            'key' => env( 'AWS_ACCESS_KEY_ID' ),
            'secret' => env( 'AWS_SECRET_ACCESS_KEY' ),
            'region' => env( 'AWS_DEFAULT_REGION', 'us-east-1' ),
            'table' => env( 'DYNAMODB_CACHE_TABLE', 'cache' ),
            'endpoint' => env( 'DYNAMODB_ENDPOINT' ),
        ],
        'octane' => [
            'driver' => 'octane',
        ],
        'failover' => [
            'driver' => 'failover',
            'stores' => [
                'database',
                'array',
            ],
        ],
    ],
    /**
     * 缓存键前缀
     * 使用 APC、数据库、Memcached、Redis 或 DynamoDB 缓存存储时，其他应用可能共用同一缓存。可以为所有缓存键添加前缀，以避免冲突。
     */
    'prefix' => env( 'CACHE_PREFIX', Str::slug( (string) env( 'APP_PREFIX', 'laravel' ) ).'-cache-' ),
];
<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

return [
    /**
     * 默认数据库连接名称
     * 这里指定数据库操作默认使用的连接。执行查询或语句时，如果没有明确指定其他连接，就会使用此连接。
     */
    'default' => env( 'DB_CONNECTION', 'sqlite' ),
    /**
     * 数据库连接
     * 这里定义应用的所有数据库连接，并为 Laravel 支持的每种数据库提供示例配置。你可以按需添加或删除连接。
     */
    'connections' => [
        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env( 'DB_URL' ),
            'database' => env( 'DB_DATABASE', database_path( 'database.sqlite' ) ),
            'prefix' => env( 'DB_PREFIX', '' ),
            'foreign_key_constraints' => env( 'DB_FOREIGN_KEYS', true ),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],
        'mysql' => [
            'driver' => 'mysql',
            'url' => env( 'DB_URL' ),
            'host' => env( 'DB_HOST', '127.0.0.1' ),
            'port' => env( 'DB_PORT', '3306' ),
            'database' => env( 'DB_DATABASE', 'laravel' ),
            'username' => env( 'DB_USERNAME', 'root' ),
            'password' => env( 'DB_PASSWORD', '' ),
            'unix_socket' => env( 'DB_SOCKET', '' ),
            'charset' => env( 'DB_CHARSET', 'utf8mb4' ),
            'collation' => env( 'DB_COLLATION', 'utf8mb4_unicode_ci' ),
            'prefix' => env( 'DB_PREFIX', '' ),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded( 'pdo_mysql' ) ? array_filter( [
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env( 'MYSQL_ATTR_SSL_CA' ),
            ] ) : [],
        ],
        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env( 'DB_URL' ),
            'host' => env( 'DB_HOST', '127.0.0.1' ),
            'port' => env( 'DB_PORT', '3306' ),
            'database' => env( 'DB_DATABASE', 'laravel' ),
            'username' => env( 'DB_USERNAME', 'root' ),
            'password' => env( 'DB_PASSWORD', '' ),
            'unix_socket' => env( 'DB_SOCKET', '' ),
            'charset' => env( 'DB_CHARSET', 'utf8mb4' ),
            'collation' => env( 'DB_COLLATION', 'utf8mb4_unicode_ci' ),
            'prefix' => env( 'DB_PREFIX', '' ),
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded( 'pdo_mysql' ) ? array_filter( [
                (PHP_VERSION_ID >= 80500 ? Mysql::ATTR_SSL_CA : PDO::MYSQL_ATTR_SSL_CA) => env( 'MYSQL_ATTR_SSL_CA' ),
            ] ) : [],
        ],
        'pgsql' => [
            'driver' => 'pgsql',
            'url' => env( 'DB_URL' ),
            'host' => env( 'DB_HOST', '127.0.0.1' ),
            'port' => env( 'DB_PORT', '5432' ),
            'database' => env( 'DB_DATABASE', 'laravel' ),
            'username' => env( 'DB_USERNAME', 'root' ),
            'password' => env( 'DB_PASSWORD', '' ),
            'charset' => env( 'DB_CHARSET', 'utf8' ),
            'prefix' => env( 'DB_PREFIX', '' ),
            'prefix_indexes' => true,
            'search_path' => 'public',
            'sslmode' => env( 'DB_SSLMODE', 'prefer' ),
        ],
        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env( 'DB_URL' ),
            'host' => env( 'DB_HOST', 'localhost' ),
            'port' => env( 'DB_PORT', '1433' ),
            'database' => env( 'DB_DATABASE', 'laravel' ),
            'username' => env( 'DB_USERNAME', 'root' ),
            'password' => env( 'DB_PASSWORD', '' ),
            'charset' => env( 'DB_CHARSET', 'utf8' ),
            'prefix' => env( 'DB_PREFIX', '' ),
            'prefix_indexes' => true,
            /**
             * 'encrypt' => env('DB_ENCRYPT', 'yes'),
             * 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
             */
        ],
    ],
    /**
     * 迁移记录表
     * 这张表记录应用已经执行的所有迁移。框架据此判断磁盘上的哪些迁移尚未在数据库中执行。
     */
    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],
    /**
     * Redis 数据库
     * Redis 是开源、快速且功能丰富的键值存储系统，相比 Memcached 等常见键值存储提供更多命令。这里可以定义 Redis 连接配置。
     */
    'redis' => [
        'client' => env( 'REDIS_CLIENT', 'phpredis' ),
        'options' => [
            'cluster' => env( 'REDIS_CLUSTER', 'redis' ),
            'prefix' => env( 'REDIS_PREFIX', Str::slug( (string) env( 'APP_PREFIX', 'laravel' ) ).'-database-' ),
            'persistent' => env( 'REDIS_PERSISTENT', false ),
        ],
        'default' => [
            'url' => env( 'REDIS_URL' ),
            'host' => env( 'REDIS_HOST', '127.0.0.1' ),
            'username' => env( 'REDIS_USERNAME' ),
            'password' => env( 'REDIS_PASSWORD' ),
            'port' => env( 'REDIS_PORT', '6379' ),
            'database' => env( 'REDIS_DB', '0' ),
            'max_retries' => env( 'REDIS_MAX_RETRIES', 3 ),
            'backoff_algorithm' => env( 'REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter' ),
            'backoff_base' => env( 'REDIS_BACKOFF_BASE', 100 ),
            'backoff_cap' => env( 'REDIS_BACKOFF_CAP', 1000 ),
        ],
        'cache' => [
            'url' => env( 'REDIS_URL' ),
            'host' => env( 'REDIS_HOST', '127.0.0.1' ),
            'username' => env( 'REDIS_USERNAME' ),
            'password' => env( 'REDIS_PASSWORD' ),
            'port' => env( 'REDIS_PORT', '6379' ),
            'database' => env( 'REDIS_CACHE_DB', '1' ),
            'max_retries' => env( 'REDIS_MAX_RETRIES', 3 ),
            'backoff_algorithm' => env( 'REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter' ),
            'backoff_base' => env( 'REDIS_BACKOFF_BASE', 100 ),
            'backoff_cap' => env( 'REDIS_BACKOFF_CAP', 1000 ),
        ],
    ],
];
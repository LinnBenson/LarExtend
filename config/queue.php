<?php

return [
    /**
     * 默认队列连接名称
     * Laravel 队列通过统一的 API 支持多种后端，你可以使用相同的语法访问各个后端。下方定义默认队列连接。
     */
    'default' => env( 'QUEUE_CONNECTION', 'database' ),
    /**
     * 队列连接
     * 这里可以配置应用使用的每个队列后端的连接选项。下方为 Laravel 支持的每种后端提供了示例，你也可以添加更多连接。
     * 支持的驱动："sync"、"database"、"beanstalkd"、"sqs"、"redis"、"deferred"、"background"、"failover"、"null"。
     */
    'connections' => [
        'sync' => [
            'driver' => 'sync',
        ],
        'database' => [
            'driver' => 'database',
            'connection' => env( 'DB_QUEUE_CONNECTION' ),
            'table' => env( 'DB_QUEUE_TABLE', 'jobs' ),
            'queue' => env( 'DB_QUEUE', 'default' ),
            'retry_after' => (int) env( 'DB_QUEUE_RETRY_AFTER', 90 ),
            'after_commit' => false,
        ],
        'beanstalkd' => [
            'driver' => 'beanstalkd',
            'host' => env( 'BEANSTALKD_QUEUE_HOST', 'localhost' ),
            'queue' => env( 'BEANSTALKD_QUEUE', 'default' ),
            'retry_after' => (int) env( 'BEANSTALKD_QUEUE_RETRY_AFTER', 90 ),
            'block_for' => 0,
            'after_commit' => false,
        ],
        'sqs' => [
            'driver' => 'sqs',
            'key' => env( 'AWS_ACCESS_KEY_ID' ),
            'secret' => env( 'AWS_SECRET_ACCESS_KEY' ),
            'prefix' => env( 'SQS_PREFIX', 'https://sqs.us-east-1.amazonaws.com/your-account-id' ),
            'queue' => env( 'SQS_QUEUE', 'default' ),
            'suffix' => env( 'SQS_SUFFIX' ),
            'region' => env( 'AWS_DEFAULT_REGION', 'us-east-1' ),
            'after_commit' => false,
        ],
        'redis' => [
            'driver' => 'redis',
            'connection' => env( 'REDIS_QUEUE_CONNECTION', 'default' ),
            'queue' => env( 'REDIS_QUEUE', 'default' ),
            'retry_after' => (int) env( 'REDIS_QUEUE_RETRY_AFTER', 90 ),
            'block_for' => null,
            'after_commit' => false,
        ],
        'deferred' => [
            'driver' => 'deferred',
        ],
        'background' => [
            'driver' => 'background',
        ],
        'failover' => [
            'driver' => 'failover',
            'connections' => [
                'database',
                'deferred',
            ],
        ],
    ],
    /**
     * 任务批处理
     * 这些选项指定存储任务批处理信息的数据库连接和数据表。可以改为应用中定义的任意数据库连接和数据表。
     */
    'batching' => [
        'database' => env( 'DB_CONNECTION', 'sqlite' ),
        'table' => 'job_batches',
    ],
    /**
     * 失败的队列任务
     * 这些选项配置失败队列任务的记录方式，以控制失败任务如何存储以及存储在哪里。Laravel 支持将失败任务存储在文件或数据库中。
     * 支持的驱动："database-uuids"、"dynamodb"、"file"、"null"。
     */
    'failed' => [
        'driver' => env( 'QUEUE_FAILED_DRIVER', 'database-uuids' ),
        'database' => env( 'DB_CONNECTION', 'sqlite' ),
        'table' => 'failed_jobs',
    ],
];
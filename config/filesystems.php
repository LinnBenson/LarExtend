<?php

return [
    /**
     * 默认文件系统磁盘
     * 这里指定框架默认使用的文件系统磁盘。应用可以使用 "local" 本地磁盘或多种云端磁盘来存储文件。
     */
    'default' => env( 'FILESYSTEM_DISK', 'local' ),
    /**
     * 文件系统磁盘
     * 这里可以按需配置多个文件系统磁盘，同一个驱动也可以配置多个磁盘。下方提供了大多数受支持存储驱动的示例。
     * 支持的驱动："local"、"ftp"、"sftp"、"s3"。
     */
    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path( 'app/private' ),
            'serve' => true,
            'throw' => false,
            'report' => false,
        ],
        'public' => [
            'driver' => 'local',
            'root' => storage_path( 'app/public' ),
            'url' => rtrim( env( 'APP_URL', 'http://localhost' ), '/' ).'/storage',
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],
        's3' => [
            'driver' => 's3',
            'key' => env( 'AWS_ACCESS_KEY_ID' ),
            'secret' => env( 'AWS_SECRET_ACCESS_KEY' ),
            'region' => env( 'AWS_DEFAULT_REGION' ),
            'bucket' => env( 'AWS_BUCKET' ),
            'url' => env( 'AWS_URL' ),
            'endpoint' => env( 'AWS_ENDPOINT' ),
            'use_path_style_endpoint' => env( 'AWS_USE_PATH_STYLE_ENDPOINT', false ),
            'throw' => false,
            'report' => false,
        ],
    ],
    /**
     * 符号链接
     * 这里配置执行 Artisan 的 `storage:link` 命令时创建的符号链接。数组的键为链接路径，值为目标路径。
     */
    'links' => [
        public_path( 'storage' ) => storage_path( 'app/public' ),
    ],
];
<?php

return [
    'enabled' => [
        // 是否启用扩展中间件
        'middleware' => (bool) env( 'ENABLE_EXTENSION_MIDDLEWARE', false ),
    ],
    'exceptions' => [
        // API 异常处理工具
        'api' => 'echoRequest',
        // 验证异常处理工具
        'validation' => 'echoRequest'
    ],
    'plugin' => [
        'path' => [
            'work' => app_path( 'Plugins' ), // 插件工作目录
            'config' => config_path( 'plugins' ), // 用户插件配置目录
            'permissions' => config_path( 'plugins/Permissions.php' ), // 权限配置文件
            'pack' => storage_path( 'app/plugins' ), // 插件包存储目录
            'install' => storage_path( 'app/plugins/install' ), // 插件安装临时目录
            'log' => storage_path( 'logs/plugins' ), // 插件日志存储目录
        ],
        'verification' => [
            'package' => [
                'name' => [ 'required', 'string', 'min:2', 'max:50', 'regex:/\A[\p{L}\p{N} ._-]+\z/u' ],
                'description' => [ 'nullable', 'string', 'max:255' ],
                'version' => [ 'required', 'string', 'max:20', 'regex:/\A(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\z/' ],
                'author' => [ 'required', 'string', 'max:50', 'regex:/\A[a-zA-Z0-9 ._-]+\z/' ],
                'source' => [ 'nullable', 'string', 'max:255' ],
                'type' => [ 'required', 'string', 'in:expand,software,depend' ]
            ]
        ],
        'permissions' => [
            // 应用服务注册钩子
            'APP_SERVICE_REGISTER_HOOK',
            // 应用服务启动钩子
            'APP_SERVICE_BOOT_HOOK',
            // 管理员面板钩子
            'ADMINISTRATOR_PANEL_HOOK',
            // 管理员面板注册钩子
            'ADMINISTRATOR_PANEL_REGISTER_HOOK',
            // API 路由注册
            'API_ROUTE_REGISTRATION',
            // WEB 路由注册
            'WEB_ROUTE_REGISTRATION',
            // Console 路由注册
            'CONSOLE_ROUTE_REGISTRATION',
            // Channels 路由注册
            'CHANNELS_ROUTE_REGISTRATION',
            // 注册控制台命令
            'CONSOLE_COMMAND_REGISTRATION',
            // 插件定时任务注册
            'SCHEDULE_REGISTRATION',
            // 请求接管插件更新
            'REQUEST_TO_TAKE_OVER_UPDATES',
            // API 中间件钩子
            'API_MIDDLEWARE_HOOK',
            // WEB 中间件钩子
            'WEB_MIDDLEWARE_HOOK'
        ]
    ],
    'files' => [
        /**
         * 文件 MIME 类型映射
         * 这里配置文件扩展名与 MIME 类型的对应关系，用于在响应文件时设置正确的 Content-Type。
         */
        'mime_types' => [
            'css' => 'text/css; charset=utf-8',
            'js' => 'text/javascript; charset=utf-8',
            'mjs' => 'text/javascript; charset=utf-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            'ico' => 'image/vnd.microsoft.icon',
            'avif' => 'image/avif',
            'apng' => 'image/apng',
            'tif' => 'image/tiff',
            'tiff' => 'image/tiff',
            'otf' => 'font/otf',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'txt' => 'text/plain; charset=utf-8',
            'json' => 'application/json',
            'webmanifest' => 'application/manifest+json',
            'wasm' => 'application/wasm',
            'html' => 'text/html; charset=utf-8',
            'htm' => 'text/html; charset=utf-8',
            'xml' => 'application/xml; charset=utf-8',
            'csv' => 'text/csv; charset=utf-8',
            'md' => 'text/markdown; charset=utf-8',
            'pdf' => 'application/pdf',
            'mp3' => 'audio/mpeg',
            'wav' => 'audio/wav',
            'oga' => 'audio/ogg',
            'm4a' => 'audio/mp4',
            'weba' => 'audio/webm',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'ogv' => 'video/ogg'
        ],
        // 上传时按内容识别的类型可能与文件响应类型不同。
        'upload_mime_types' => [
            'txt' => ['text/plain', 'application/x-empty', 'application/octet-stream'],
            'md' => ['text/plain', 'text/markdown', 'text/x-markdown', 'text/html'],
            'csv' => ['text/plain', 'text/csv', 'application/csv', 'text/x-comma-separated-values', 'text/x-csv'],
            'yaml' => ['text/plain', 'application/yaml', 'application/x-yaml', 'text/x-yaml', 'text/yaml'],
            'yml' => ['text/plain', 'application/yaml', 'application/x-yaml', 'text/x-yaml', 'text/yaml'],
            'json' => ['text/plain', 'application/json', 'application/schema+json'],
            'css' => ['text/plain', 'text/css'],
            'js' => ['text/plain', 'text/javascript', 'application/javascript', 'application/x-javascript'],
            'mjs' => ['text/plain', 'text/javascript', 'application/javascript', 'application/x-javascript'],
            'webmanifest' => ['text/plain', 'application/json', 'application/manifest+json'],
            'apng' => ['image/png', 'image/apng', 'image/vnd.mozilla.apng'],
        ],
        /**
         * 上传文件类型配置
         * 这里配置不同类别的上传文件允许的扩展名。
         */
        'upload_types' => [
            // 常用图片
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'avif', 'bmp', 'heic', 'heif'],
            // 全部图片
            'image_extended' => [
                'jpg', 'jpeg', 'jfif', 'png', 'apng', 'gif', 'webp', 'avif', 'bmp', 'tif', 'tiff', 'ico', 'cur', 'heic', 'heif', 'jxl', 'raw'
            ],
            // 文档
            'document' => [
                'pdf', 'txt', 'csv', 'md', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'odt', 'ods', 'odp', 'rtf'
            ],
            // 音频
            'audio' => ['mp3', 'wav', 'ogg', 'oga', 'm4a', 'aac', 'flac', 'weba'],
            // 视频
            'video' => ['mp4', 'webm', 'ogv', 'mov', 'avi', 'mkv', 'm4v'],
            // 字体
            'font' => ['woff', 'woff2', 'ttf', 'otf'],
            // 压缩包
            'archive' => ['zip', 'rar', '7z', 'tar', 'gz', 'bz2', 'xz'],
            // 结构化数据
            'data' => ['json', 'yaml', 'yml'],
            // 仅供可信管理员或插件资源上传使用
            'web_resource' => [
                'css', 'js', 'mjs', 'html', 'htm', 'svg', 'webmanifest', 'wasm', 'ico', 'apng'
            ]
        ]
    ],
    'admin' => [
        'asset' => 'filament', // 静态资源路径
        'guard' => 'filament', // 管理后台认证守卫配置
        'color' => [
            'primary' => \Filament\Support\Colors\Color::Blue,
        ], // 管理后台主题颜色配置
        'navigation_groups' => [
            'lar::frame.groups.admin',
            'lar::frame.groups.user',
            'lar::frame.groups.order',
            'lar::frame.groups.content',
            'lar::frame.groups.plugin',
            'lar::frame.groups.other',
            'lar::frame.groups.setting',
            'lar::frame.groups.developer'
        ],
        'pages' => [

        ],
        'resources' => [
            \LarExtend\Filament\Resources\AdminControl\PluginList\PluginResource::class,
            \LarExtend\Filament\Resources\AdminControl\NotificationList\NotificationResource::class,
            \LarExtend\Filament\Resources\SettingCenter\SystemSettings\SystemSettingsResource::class,
            \LarExtend\Filament\Resources\SettingCenter\AdminSettings\AdminSettingsResource::class,
            \LarExtend\Filament\Resources\AdminControl\AdminList\AdminUserResource::class,
            \LarExtend\Filament\Resources\DeveloperCenter\Logs\LogResource::class,
            \LarExtend\Filament\Resources\DeveloperCenter\Routes\RouteResource::class,
            \LarExtend\Filament\Resources\DeveloperCenter\Icons\IconResource::class,
            \LarExtend\Filament\Resources\DeveloperCenter\Readme\ReadmeResource::class,
        ],
        'policies' => [
            \LarExtend\Models\Notification::class => \LarExtend\Filament\Resources\AdminControl\NotificationList\NotificationPolicy::class,
            \LarExtend\Models\Settings::class => \LarExtend\Filament\Resources\SettingCenter\SystemSettings\SettingsPolicy::class,
            \LarExtend\Models\AdminUser::class => \LarExtend\Filament\Resources\AdminControl\AdminList\AdminUserPolicy::class,
        ],
        'upload' => [
            'directory' => 'uploads',
            // 单个文件的大小上限，单位 KB
            'max_size' => 10240,
            'file_groups' => ['image_extended', 'document', 'audio', 'video', 'font', 'archive', 'data'],
            'disk' => [
                'driver' => 'local',
                'root' => storage_path( 'app/public/admin' ),
                'url' => '/storage/admin',
                'visibility' => 'public',
                'throw' => false,
                'report' => false,
            ]
        ]
    ]
];

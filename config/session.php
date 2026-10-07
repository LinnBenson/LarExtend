<?php

use Illuminate\Support\Str;

return [
    /**
     * 默认会话驱动
     * 这个选项决定处理请求时默认使用的会话驱动。Laravel 支持多种会话数据持久化存储方式，数据库存储是合适的默认选择。
     * 支持的驱动："file"、"cookie"、"database"、"memcached"、"redis"、"dynamodb"、"array"。
     */
    'driver' => env( 'SESSION_DRIVER', 'database' ),
    /**
     * 会话有效期
     * 这里指定会话在空闲多少分钟后过期。如果希望关闭浏览器时立即使会话过期，可以设置 expire_on_close 选项。
     */
    'lifetime' => (int) env( 'SESSION_LIFETIME', 120 ),

    'expire_on_close' => env( 'SESSION_EXPIRE_ON_CLOSE', false ),
    /**
     * 会话加密
     * 这个选项决定是否在存储前加密所有会话数据。Laravel 会自动完成加密，你仍然可以像平常一样使用会话。
     */
    'encrypt' => env( 'SESSION_ENCRYPT', false ),
    /**
     * 会话文件存储位置
     * 使用 "file" 会话驱动时，会话文件会存储在磁盘上。这里定义默认存储位置，你可以按需改为其他路径。
     */
    'files' => storage_path( 'framework/sessions' ),
    /**
     * 会话数据库连接
     * 使用 "database" 或 "redis" 会话驱动时，可以指定管理会话的连接。此连接应对应数据库配置中定义的连接。
     */
    'connection' => env( 'SESSION_CONNECTION' ),
    /**
     * 会话数据表
     * 使用 "database" 会话驱动时，可以指定存储会话的数据表。这里提供了默认值，你可以按需改为其他数据表。
     */
    'table' => env( 'SESSION_TABLE', 'sessions' ),
    /**
     * 会话缓存存储
     * 使用基于缓存的会话后端时，可以指定在请求之间存储会话数据的缓存存储。此名称必须对应已定义的缓存存储。
     * 适用的驱动："dynamodb"、"memcached"、"redis"。
     */
    'store' => env( 'SESSION_STORE' ),
    /**
     * 过期会话清理概率
     * 部分会话驱动需要主动清理存储中的过期会话。这里设置每次请求触发清理的概率，默认概率为 2/100。
     */
    'lottery' => [2, 100],
    /**
     * 会话 Cookie 名称
     * 这里可以修改框架创建的会话 Cookie 名称。通常无需修改，因为修改名称并不能显著提高安全性。
     */
    'cookie' => env(
        'SESSION_COOKIE',
        Str::slug( (string) env( 'APP_NAME', 'laravel' ) ).'-session'
    ),
    /**
     * 会话 Cookie 路径
     * 这个选项决定会话 Cookie 适用的路径。通常设置为应用的根路径，你可以在需要时修改。
     */
    'path' => env( 'SESSION_PATH', '/' ),
    /**
     * 会话 Cookie 域名
     * 这个值决定会话 Cookie 适用的域名和子域名。默认仅适用于当前域名，不包含子域名，通常无需修改。
     */
    'domain' => env( 'SESSION_DOMAIN' ),
    /**
     * 仅通过 HTTPS 发送 Cookie
     * 设置为 true 后，浏览器只会通过 HTTPS 连接向服务器发送会话 Cookie，防止 Cookie 通过不安全的连接传输。
     */
    'secure' => env( 'SESSION_SECURE_COOKIE' ),
    /**
     * 仅允许 HTTP 访问
     * 设置为 true 后，JavaScript 无法访问 Cookie 的值，Cookie 只能通过 HTTP 协议访问。通常不应关闭此选项。
     */
    'http_only' => env( 'SESSION_HTTP_ONLY', true ),
    /**
     * 同站 Cookie 策略
     * 这个选项决定 Cookie 在跨站请求中的行为，可用于降低 CSRF 攻击风险。默认设置为 "lax"，允许符合安全条件的跨站请求携带 Cookie。
     * 参考：https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Set-Cookie#samesitesamesite-value
     * 支持的值："lax"、"strict"、"none"、null。
     */
    'same_site' => env( 'SESSION_SAME_SITE', 'lax' ),
    /**
     * 分区 Cookie
     * 设置为 true 后，跨站场景中的 Cookie 会与顶层站点绑定。分区 Cookie 需标记为 "secure"，并将 SameSite 属性设置为 "none"，才能被浏览器接受。
     */
    'partitioned' => env( 'SESSION_PARTITIONED_COOKIE', false ),
];
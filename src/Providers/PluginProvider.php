<?php

namespace LarExtend\Providers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Route;
use LarExtend\Services\PluginLoadService;
use LarExtend\Filament\Concerns\AdminMiddleware;
use Illuminate\Support\Facades\DB;
use LarExtend\Services\PluginPermissionService;
use Illuminate\Support\Facades\Blade;

class PluginProvider {
    // 插件 ID
    public readonly string $id;
    // 插件路径
    public readonly string $path;
    // 插件包信息
    public readonly array $package;
    // 插件配置缓存
    private ?array $configs = null;

    /**
     * 获取插件声明权限的授权状态
     * @return array 返回插件的授权状态
     */
    public function getPermissions(): array {
        $permissions = $this->package['permissions'] ?? [];
        $permissions = is_array( $permissions ) ? $permissions : [];
        $allowList = PluginPermissionService::getCurrentPermissions();
        $result = [];
        foreach( $permissions as $permission ) {
            $allowListItem = $allowList[$permission] ?? [];
            $result[$permission] = ( is_array( $allowListItem ) && in_array( $this->id, $allowListItem, true ) );
        }
        return $result;
    }

    /**
     * 获取插件配置
     * @param string $key 配置项键名
     * @param mixed $default 默认值
     * @return mixed 查询内容
     */
    public function config( string $key = '', mixed $default = null ): mixed {
        if ( $this->configs === null ) {
            $loadConfig = function( string $file ): array {
                if ( !is_file( $file ) || !is_readable( $file ) ) { return []; }
                try {
                    $config = include $file;
                    return is_array( $config ) ? $config : [];
                }catch ( \Throwable $th ) {
                    Log::error( 'Failed to load plugin configuration.', [
                        'plugin' => $this->id,
                        'message' => $th->getMessage(),
                    ]);
                    return [];
                }
            };
            $configPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.config' ) );
            $this->configs = array_replace_recursive(
                $loadConfig( "{$this->path}config.php" ),
                $loadConfig( "{$configPath}{$this->id}.php" ),
            );
        }
        if ( $key === '' ) { return $this->configs; }
        return data_get( $this->configs, $key, $default );
    }

    /**
     * 写入插件日志
     * 按插件 ID 保存独立日志文件，日志通道名称使用插件 ID。
     * @param string $message 日志内容
     * @param array $context 日志上下文
     * @param string $level 日志等级
     * @return void
     */
    public function log( string $message, array $context = [], string $level = 'info' ): void {
        $logPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.log' ) );
        Log::build( [
            'driver' => 'single',
            'name' => $this->id,
            'path' => "{$logPath}{$this->id}.log",
            'level' => 'debug',
            'locking' => true,
        ] )->log( $level, $message, $context );
    }

    /**
     * 保存插件用户配置
     * 合并本次修改，仅持久化相对插件默认配置的差异。
     * @param array $config 本次修改的配置
     * @return bool 是否保存成功
     */
    public function saveConfig( array $config ): bool {
        $userConfigPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.config' ) ).$this->id.'.php';
        $lock = null;
        $temporary = null;
        try {
            $directory = dirname( $userConfigPath );
            if ( !is_dir( $directory ) && !@mkdir( $directory, 0755, true ) && !is_dir( $directory ) ) {
                throw new \RuntimeException( 'Unable to create the plugin configuration directory.' );
            }
            // 锁文件不随配置替换，保证整个读取、合并和写入过程串行执行。
            $lock = @fopen( "{$userConfigPath}.lock", 'c' );
            if ( $lock === false || !flock( $lock, LOCK_EX ) ) {
                throw new \RuntimeException( 'Unable to lock the plugin configuration file.' );
            }
            $loadConfig = function( string $file ): array {
                if ( !file_exists( $file ) ) { return []; }
                if ( !is_file( $file ) || !is_readable( $file ) ) {
                    throw new \RuntimeException( 'The plugin configuration file is not readable.' );
                }
                if ( function_exists( 'opcache_invalidate' ) ) { opcache_invalidate( $file, true ); }
                $values = require $file;
                return is_array( $values ) ? $values : [];
            };
            $selfConfig = $loadConfig( "{$this->path}config.php" );
            $userConfig = $loadConfig( $userConfigPath );
            $mergedConfig = array_replace_recursive( $selfConfig, $userConfig, $config );
            $diff = self::configDifferences( $mergedConfig, $selfConfig );
            $exportedConfig = "<?php

return ".var_export( $diff, true ).";
";
            $temporary = tempnam( $directory, '.plugin-config-' );
            if ( $temporary === false || file_put_contents( $temporary, $exportedConfig ) !== strlen( $exportedConfig ) ) {
                throw new \RuntimeException( 'Unable to write the plugin configuration file.' );
            }
            $mode = is_file( $userConfigPath ) ? fileperms( $userConfigPath ) & 0777 : 0644;
            if ( !chmod( $temporary, $mode ) || !rename( $temporary, $userConfigPath ) ) {
                throw new \RuntimeException( 'Unable to replace the plugin configuration file.' );
            }
            $temporary = null;
            if ( function_exists( 'opcache_invalidate' ) ) { opcache_invalidate( $userConfigPath, true ); }
            $this->configs = $mergedConfig;
            return true;
        }catch ( \Throwable $exception ) {
            Log::error( 'Failed to save plugin configuration.', [
                'plugin' => $this->id,
                'message' => $exception->getMessage(),
            ] );
            return false;
        }finally {
            try {
                if ( is_string( $temporary ) && is_file( $temporary ) && !unlink( $temporary ) ) {
                    // 删除插件配置临时文件失败。
                    Log::error( '[PluginProvider::saveConfig] Unable to remove temporary file.', ['path' => $temporary] );
                }
            }catch ( \Throwable $exception ) {
                // 清理插件配置临时文件时发生异常。
                Log::error( '[PluginProvider::saveConfig] Failed to clean temporary file.', ['path' => $temporary, 'message' => $exception->getMessage()] );
            }finally {
                if ( is_resource( $lock ) ) {
                    flock( $lock, LOCK_UN );
                    fclose( $lock );
                }
            }
        }
    }

    /**
     * 设置插件的视图路径
     * @param string $path 视图文件夹相对路径
     * @return void
     */
    public function setViews( string $path ): void {
        View::addNamespace( "p.{$this->id}", "{$this->path}{$path}" );
    }

    /**
     * 设置插件的组件路径
     * @param string $path 组件文件夹相对路径
     * @return void
     */
    public function setComponents( string $path ): void {
        Blade::anonymousComponentPath( "{$this->path}{$path}", "p.{$this->id}" );
    }

    /**
     * 设置插件的语言包路径
     * @param string $path 语言包文件夹相对路径
     * @return void
     */
    public function setLang( string $path ): void {
        Lang::addNamespace( "p.{$this->id}", "{$this->path}{$path}" );
    }

    /**
     * 设置插件的普通路由
     * @param callable $route 路由定义回调
     * @param array $middleware 路由中间件数组
     * @return void
     */
    public function setRoute( callable $route, array $middleware = [] ): void {
        Route::middleware( $middleware )
            ->prefix( "/plugin/{$this->id}" )
            ->name( "plugin.{$this->id}." )
            ->group( \Closure::fromCallable( $route ) );
    }

    /**
     * 设置插件的管理路由
     * @param callable $route 路由定义回调
     * @param int|string $minLevel 最低管理权限等级，默认为 'manage'
     * @return void
     */
    public function setAdminRoute( callable $route, int|string $minLevel = 'manage' ): void {
        Route::middleware( ['web', AdminMiddleware::class.":{$minLevel}"] )
            ->prefix( "/".setting( 'admin.path', 'admin' )."/{$this->id}" )
            ->name( "admin.{$this->id}." )
            ->group( \Closure::fromCallable( $route ) );
    }

    /**
     * 设置插件的 SQLite 数据库连接
     * @param string $name 连接名称
     * @param string $file 数据库文件相对路径
     * @param array $options 连接选项
     * @return bool
     * @throws \RuntimeException
     */
    public function setSQLite( string $name, string $file, array $options = [] ): bool {
        $connection = DB::getConnections()[$name] ?? null;
        if ( $connection !== null && $connection->transactionLevel() > 0 ) {
            // 如果当前连接存在且有活动事务，抛出异常，防止在事务中重新配置连接。
            throw new \RuntimeException( 'Cannot reconfigure a database connection with an active transaction.' );
        }
        $databasePath = "{$this->path}{$file}";
        $directory = dirname( $databasePath );
        if ( !is_dir( $directory ) ) {
            if ( !@mkdir( $directory, 0755, true ) && !is_dir( $directory ) ) {
                // 如果创建目录失败且目录仍不存在，抛出异常。
                throw new \RuntimeException( 'Unable to create the SQLite database directory.' );
            }
        }
        // WAL 模式需要在数据库所在目录创建辅助文件。
        if ( !is_writable( $directory ) ) {
            // 如果数据库目录不可写，抛出异常。
            throw new \RuntimeException( 'The SQLite database directory is not writable.' );
        }
        if ( file_exists( $databasePath ) && !is_file( $databasePath ) ) {
            // 如果数据库路径存在但不是普通文件，抛出异常。
            throw new \RuntimeException( 'The SQLite database path is not a regular file.' );
        }
        // c 模式：不存在时创建，已存在时不截断文件。
        $handle = @fopen( $databasePath, 'c' );
        if ( $handle === false ) {
            // 如果无法创建或打开数据库文件，抛出异常。
            throw new \RuntimeException( 'Unable to create or open the SQLite database file.' );
        }
        fclose( $handle );
        clearstatcache( true, $databasePath );
        if ( !is_file( $databasePath ) || !is_readable( $databasePath ) || !is_writable( $databasePath ) ) {
            // 如果数据库文件不可读写，抛出异常。
            throw new \RuntimeException( 'The SQLite database file must be readable and writable.' );
        }
        $config = array_merge([
            'driver' => 'sqlite',
            'url' => null,
            'database' => $databasePath,
            'prefix' => '',
            'foreign_key_constraints' => true,
            'busy_timeout' => 5000,
            'journal_mode' => 'WAL',
            'synchronous' => 'NORMAL',
        ], $options );
        // 清除已有连接，下次访问时按新配置建立连接。
        DB::purge( $name );
        config()->set( "database.connections.{$name}", $config );
        return true;
    }

    /**
     * 设置插件的静态资源路径
     * @param string $assetPath 资源文件夹相对路径
     * @return void
     */
    public function setAssets( string $assetPath ): void {
        Route::get( "/internal-plugin-{$this->id}/{path}", function( string $path )use ( $assetPath ){
            $assetDirectory = realpath( "{$this->path}{$assetPath}" );
            if ( $assetDirectory === false ) { abort( 404 ); }
            $file = realpath( "{$assetDirectory}/{$path}" );
            // 防止使用 ../ 读取插件目录外的文件
            if (
                $file === false ||
                !str_starts_with( $file, "{$assetDirectory}/" ) ||
                !is_file( $file ) ||
                !is_readable( $file )
            ) { abort( 404 ); }
            $extension = strtolower( pathinfo( $file, PATHINFO_EXTENSION ) );
            $contentTypes = config( 'lar.files.mime_types', [] );
            if ( !isset( $contentTypes[$extension] ) ) { abort( 404 ); }
            $contentType = $contentTypes[$extension];
            return response()->file( $file, [
                'Content-Type' => $contentType,
                'Cache-Control' => 'public, max-age=86400',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        })
        ->where( 'path', '.+' )
        ->name( "plugin.{$this->id}.assets" );
    }

    /**
     * 获取插件静态资源的 URL
     * @param string $path 资源相对路径
     * @param bool $version 是否附加版本号
     * @return string 资源的 URL
     */
    public function asset( string $path, bool $version = true ): string {
        $path = ltrim( $path, '/' );
        return route(
            "plugin.{$this->id}.assets",
            [ 'path' => $path ] + ( $version ? [ 'version' => $this->package['version'] ?? 'unknown' ] : [] ),
            false
        );
    }

    /**
     * 初始化插件对象
     * @param string $id 插件 ID
     * @param string $path 插件路径
     * @param array $package 插件包信息
     * @return void
     */
    public function initialize( string $id, string $path, array $package ): void {
        $this->id = $id;
        $this->path = $path;
        $this->package = $package;
    }

    /**
     * 提取配置差异
     * 严格区分值类型，保留新增空数组和数字键，以匹配递归覆盖的读取方式。
     * @param array $config 合并后的配置
     * @param array $defaults 默认配置
     * @return array 需要持久化的配置差异
     */
    private static function configDifferences( array $config, array $defaults ): array {
        $diff = [];
        foreach ( $config as $key => $value ) {
            $exists = array_key_exists( $key, $defaults );
            if ( $exists && $value === $defaults[$key] ) { continue; }
            if ( is_array( $value ) ) {
                $hasArrayDefault = $exists && is_array( $defaults[$key] );
                $nested = self::configDifferences( $value, $hasArrayDefault ? $defaults[$key] : [] );
                if ( $nested !== [] || !$hasArrayDefault ) { $diff[$key] = $nested; }
                continue;
            }
            if ( $value !== null && !is_scalar( $value ) ) {
                throw new \InvalidArgumentException( 'Saved configuration values must be arrays, scalars or null.' );
            }
            $diff[$key] = $value;
        }
        return $diff;
    }
}
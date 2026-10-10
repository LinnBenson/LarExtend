<?php

namespace App\Services;

use App\Providers\PluginServiceProvider;
use Composer\InstalledVersions;
use Closure;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

class PluginService {

    /**
     * 插件缓存信息
     * @var array 插件缓存信息，键为插件的哈希值，值为插件实例
     */
    public static array $cache = [];

    /**
     * 正在加载的插件，用于防止循环加载插件
     * @var array 正在加载的插件列表，键为插件的哈希值，值为 true
     */
    public static array $loading = [];

    /**
     * 获取插件实例
     * @param string $base 插件的基础路径
     * @return PluginServiceProvider|null
     */
    public static function getPluginInstance( string $base ): ?PluginServiceProvider {
        if ( config( 'plugins.enable' ) === false ) { return null; }
        // 路径检查
        if ( trim( $base ) === '' ) { return null; }
        $resolvedBase = realpath( $base );
        if ( $resolvedBase === false || !is_dir( $resolvedBase ) ) { return null; }
        $base = rtrim( $resolvedBase, '/\\' ).DIRECTORY_SEPARATOR;
        $hash = md5( $base );
        // 检查缓存
        if ( array_key_exists( $hash, self::$cache ) ) {
            return self::$cache[$hash];
        }
        // 开始加载插件
        if ( isset( self::$loading[$hash] ) ) {
            Log::error( 'Circular plugin loading detected.', [
                'base' => $base,
            ]);
            return null;
        }
        self::$loading[$hash] = true;
        try {
            self::$cache[$hash] = self::preload( $base );
            return self::$cache[$hash];
        }finally {
            unset( self::$loading[$hash] );
        }
    }

    /**
     * 插件预加载
     * @param string $base 插件的基础路径
     * @return PluginServiceProvider|null
     */
    public static function preload( string $base ): ?PluginServiceProvider {
        $errors = self::checkBaseErrors( $base );
        if ( count( $errors ) > 0 ) {
            // 插件基础路径验证失败
            Log::error( 'Plugin base path validation failed.', $errors );
            return null;
        }
        $base = rtrim( $base, '/\\' ).DIRECTORY_SEPARATOR;
        $pluginId = basename( $base );
        // 尝试实例化插件
        try {
            $pluginIndex = "{$base}index.php";
            $plugin = require $pluginIndex;
        }catch ( \Throwable $th ) {
            // 捕获插件入口文件加载时抛出的异常
            Log::error( 'Failed to load the plugin entry point.', [
                'base' => $base,
                'exception_type' => get_class( $th ),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]);
            return null;
        }
        if ( !( $plugin instanceof PluginServiceProvider ) ) {
            // 插件的入口文件返回的不是 PluginServiceProvider 实例，视为加载失败
            Log::error( 'Invalid plugin entry point return type.', [
                'base' => $base,
                'actual_type' => get_debug_type( $plugin ),
            ]);
            return null;
        }
        $plugin->id = $pluginId;
        $plugin->base = $base;
        // 读取插件包信息
        try {
            $packageJson = file_get_contents( "{$base}package.json" );
        }catch ( \Throwable ) { $packageJson = false; }
        if ( $packageJson === false ) {
            Log::error( 'Failed to read plugin package information.', [
                'base' => $base,
            ]);
            return null;
        }
        if ( !is_json( $packageJson ) ) {
            Log::error( 'Invalid plugin package JSON.', [
                'base' => $base,
            ]);
            return null;
        }
        $plugin->package = json_decode( $packageJson, true );
        // 如果插件定义了 boot 方法，则调用该方法进行初始化
        if ( is_public( $plugin, 'boot' ) ) {
            try {
                $status = $plugin->boot();
            }catch ( \Throwable $th ) {
                // 捕获 boot 方法抛出的异常
                Log::error( 'Failed to boot the plugin.', [
                    'base' => $base,
                    'exception_type' => get_class( $th ),
                    'file' => $th->getFile(),
                    'line' => $th->getLine(),
                ]);
                return null;
            }
            if ( $status === false ) {
                // 插件的 boot 方法返回 false，视为加载失败
                Log::error( 'Plugin boot returned false.', [
                    'base' => $base,
                ]);
                return null;
            }
        }
        // 预加载完成
        return $plugin;
    }

    /**
     * 插件基础错误检查
     * @param string $base 插件的基础路径
     * @return array 返回检测到的错误列表
     */
    public static function checkBaseErrors( string $base ): array {
        $errors = [];
        if ( trim( $base ) === '' ) { return [ 'Plugin base path cannot be empty.' ]; }
        $base = rtrim( $base, '/\\' ).DIRECTORY_SEPARATOR;
        $pluginId = basename( $base );
        // 检查插件 ID 合法性
        if ( preg_match( '/\A[a-zA-Z][a-zA-Z0-9_-]*\z/', $pluginId ) !== 1 ) {
            $errors[] = "[{$base}] Plugin ID must start with a letter and contain only letters, digits, underscores or hyphens.";
        }
        $pluginIdLower = strtolower( $pluginId );
        if ( in_array( $pluginIdLower, config( 'plugins.reserve', [] ), true ) ) {
            $errors[] = "[{$base}] Plugin ID '{$pluginId}' is reserved and cannot be used.";
        }
        // 检查插件基础路径是否存在
        if ( !is_dir( $base ) ) {  $errors[] = "[{$base}] The plugin base path does not exist."; return $errors; }
        // 检查必须存在的文件
        $mustFiles = [ 'index.php', 'package.json' ];
        foreach ( $mustFiles as $file ) {
            if ( !is_file( "{$base}{$file}" ) ) { $errors[] = "[{$base}] Missing required file: {$file}"; }
        }
        // 检查必须存在的目录
        $mustDirs = [];
        foreach ( $mustDirs as $dir ) {
            if ( !is_dir( "{$base}{$dir}" ) ) { $errors[] = "[{$base}] Missing required directory: {$dir}"; }
        }
        // 检查 package.json
        if ( !is_file( "{$base}package.json" ) ) { return $errors; }
        try {
            $packageJson = file_get_contents( "{$base}package.json" );
        }catch ( \Throwable ) { $packageJson = false; }
        if ( $packageJson === false ) {
            // 读取 package.json 失败
            $errors[] = "[{$base}] Failed to read package.json.";
            return $errors;
        }
        if ( !is_json( $packageJson ) ) {
            // package.json 不是有效的 JSON
            $errors[] = "[{$base}] package.json is not valid JSON.";
            return $errors;
        }
        $package = json_decode( $packageJson, true );
        // 检查 package.json 中的必要字段
        $requiredFields = [
            'name' => [ 'string' ],
            'version' => [ 'string' ],
            'author' => [ 'string' ],
            'description' => [ 'string' ]
        ];
        foreach ( $requiredFields as $field => $type ) {
            if ( !isset( $package[$field] ) ) {
                // 缺少必要字段
                $errors[] = "[{$base}] package.json is missing required field: {$field}";
            }else if ( !in_array( gettype( $package[$field] ), $type, true ) ) {
                // 字段类型不匹配
                $errors[] = "[{$base}] package.json field {$field} must be of type ".implode( '|', $type ).".";
            }
        }
        // 可选字段类型检查
        $optionalFields = [
            'source' => [ 'string', 'NULL' ],
            'rely_plugins' => [ 'array' ],
            'rely_composers' => [ 'array' ],
        ];
        foreach ( $optionalFields as $field => $type ) {
            if ( array_key_exists( $field, $package ) && !in_array( gettype( $package[$field] ), $type, true ) ) {
                // 字段类型不匹配
                $errors[] = "[{$base}] package.json field {$field} must be of type ".implode( '|', $type ).".";
            }
        }
        // 依赖检查
        if ( isset( $package['rely_plugins'] ) && is_array( $package['rely_plugins'] ) ) {
            foreach ( $package['rely_plugins'] as $dependencyId => $version ) {
                if ( !is_string( $dependencyId ) || preg_match( '/\A[a-zA-Z][a-zA-Z0-9_-]*\z/', $dependencyId ) !== 1 ) {
                    $errors[] = "[{$base}] Invalid plugin dependency ID.";
                    continue;
                }
                if ( $dependencyId === $pluginId ) {
                    $errors[] = "[{$base}] Plugin cannot depend on itself: {$dependencyId}";
                    continue;
                }
                $workPath = config( 'plugins.path.work' );
                $dependencyBase = rtrim( $workPath, '/\\' ).DIRECTORY_SEPARATOR.$dependencyId.DIRECTORY_SEPARATOR;
                try {
                    if ( !is_file( "{$dependencyBase}index.php" ) || !is_file( "{$dependencyBase}package.json" ) ) {
                        $errors[] = "[{$base}] Required plugin is missing: {$dependencyId}";
                        continue;
                    }
                    $dependencyJson = file_get_contents( "{$dependencyBase}package.json" );
                    $dependencyPackage = $dependencyJson === false ? null : json_decode( $dependencyJson, true );
                    $installedVersion = is_array( $dependencyPackage ) ? ( $dependencyPackage['version'] ?? null ) : null;
                    if ( !is_string( $installedVersion ) ) { $installedVersion = null; }
                    $error = self::checkDependencyVersion( $installedVersion, $version );
                    if ( $error !== null ) { $errors[] = "[{$base}] Plugin dependency {$dependencyId}: {$error}"; }
                }catch ( \Throwable ) {
                    $errors[] = "[{$base}] Failed to read plugin dependency: {$dependencyId}";
                }
            }
        }
        if ( isset( $package['rely_composers'] ) && is_array( $package['rely_composers'] ) ) {
            foreach ( $package['rely_composers'] as $composer => $version ) {
                if ( !is_string( $composer ) || preg_match( '/\A[a-z0-9_.-]+\/[a-z0-9_.-]+\z/', $composer ) !== 1 ) {
                    $errors[] = "[{$base}] Invalid Composer dependency name.";
                    continue;
                }
                try {
                    if ( !InstalledVersions::isInstalled( $composer ) ) {
                        $errors[] = "[{$base}] Required Composer package is not installed: {$composer}";
                        continue;
                    }
                    $error = self::checkDependencyVersion( InstalledVersions::getPrettyVersion( $composer ), $version );
                    if ( $error !== null ) { $errors[] = "[{$base}] Composer dependency {$composer}: {$error}"; }
                }catch ( \Throwable ) {
                    $errors[] = "[{$base}] Failed to inspect Composer dependency: {$composer}";
                }
            }
        }
        // 检查完成
        return $errors;
    }

    /**
     * 检查依赖版本。
     * 支持精确版本、单个比较条件及 *，不支持 Composer 复合版本约束。
     * @param string|null $installedVersion 已安装版本
     * @param mixed $constraint 配置中的版本要求，错误类型返回校验错误
     * @return string|null 错误说明，通过时返回 null
     */
    private static function checkDependencyVersion( ?string $installedVersion, mixed $constraint ): ?string {
        if ( !is_string( $constraint ) || trim( $constraint ) === '' ) {
            return 'Version constraint must be a non-empty string.';
        }
        $constraint = trim( $constraint );
        if ( $constraint === '*' ) { return null; }
        if ( preg_match( '/\A(>=|<=|!=|==|>|<|=)?\s*(v?\d+(?:\.\d+){0,3}(?:[-+][0-9A-Za-z.-]+)?)\z/', $constraint, $matches ) !== 1 ) {
            return 'Unsupported version constraint. Use *, an exact version or a single comparison such as >=1.0.0.';
        }
        if ( $installedVersion === null || $installedVersion === '' ) {
            return 'Installed version is unavailable.';
        }
        $operator = $matches[1] === '' ? '==' : $matches[1];
        $requiredVersion = ltrim( $matches[2], 'v' );
        if ( !version_compare( ltrim( $installedVersion, 'v' ), $requiredVersion, $operator ) ) {
            return "Installed version {$installedVersion} does not satisfy {$constraint}.";
        }
        return null;
    }

    /**
     * 清理插件权限记录
     * 删除未允许的权限项，并移除工作目录中没有对应目录的插件 ID，不检查插件内容。
     * @return bool 是否清理成功
     */
    public static function cleanUpPermissions(): bool {
        return self::updatePermissions( function ( array $permissions ): array {
            $allowedPermissions = config( 'plugins.permissions', [] );
            $workPath = config( 'plugins.path.work' );
            if ( !is_array( $allowedPermissions ) ) {
                throw new \RuntimeException( 'Allowed plugin permissions must be an array.' );
            }
            if ( !is_string( $workPath ) || trim( $workPath ) === '' ) {
                throw new \RuntimeException( 'Plugin work path is not configured.' );
            }
            // 只扫描工作目录的直接子目录
            clearstatcache();
            $existingPlugins = [];
            if ( is_dir( $workPath ) ) {
                foreach ( File::directories( $workPath ) as $directory ) {
                    $existingPlugins[basename( $directory )] = true;
                }
            }elseif ( file_exists( $workPath ) ) {
                throw new \RuntimeException( 'Plugin work path must be a directory.' );
            }
            foreach ( $permissions as $name => $pluginIds ) {
                if ( !in_array( $name, $allowedPermissions, true ) ) {
                    unset( $permissions[$name] );
                    continue;
                }
                if ( !is_array( $pluginIds ) ) {
                    throw new \RuntimeException( 'Permission entry must be an array.' );
                }
                $permissions[$name] = array_values( array_filter(
                    $pluginIds,
                    fn ( mixed $pluginId ): bool => is_string( $pluginId ) && isset( $existingPlugins[$pluginId] )
                ) );
            }
            return $permissions;
        }, 'Failed to clean up plugin permissions.' );
    }

    /**
     * 移除插件的权限记录
     * 从所有权限列表移除指定插件，保留其他插件及权限键；重复移除视为成功。
     * @param mixed $plugin 插件实例
     * @return bool 是否成功移除权限
     */
    public static function removePermissions( mixed $plugin ): bool {
        if ( !( $plugin instanceof PluginServiceProvider ) || !is_string( $plugin->id ) || trim( $plugin->id ) === '' ) { return false; }
        return self::updatePermissions( function ( array $permissions ) use ( $plugin ): array {
            foreach ( $permissions as $name => $pluginIds ) {
                if ( !is_array( $pluginIds ) ) {
                    throw new \RuntimeException( 'Permission entry must be an array.' );
                }
                if ( !in_array( $plugin->id, $pluginIds, true ) ) { continue; }
                $permissions[$name] = array_values( array_filter(
                    $pluginIds,
                    fn ( mixed $pluginId ): bool => $pluginId !== $plugin->id
                ) );
            }
            return $permissions;
        }, 'Failed to remove plugin permissions.' );
    }

    /**
     * 注册插件的权限
     * @param mixed $plugin 插件实例
     * @return bool 返回是否成功注册权限
     */
    public static function registrationPermissions( mixed $plugin ): bool {
        if ( !( $plugin instanceof PluginServiceProvider ) || !is_string( $plugin->id ) || trim( $plugin->id ) === '' ) { return false; }
        return self::updatePermissions( function ( array $permissions ) use ( $plugin ): ?array {
            $pluginUses = $plugin->getPermissions();
            if ( empty( $pluginUses ) ) { return null; }
            $allowedPermissions = config( 'plugins.permissions', [] );
            foreach ( $pluginUses as $name ) {
                if ( !in_array( $name, $allowedPermissions, true ) ) { continue; }
                if ( !array_key_exists( $name, $permissions ) ) {
                    $permissions[$name] = [];
                }else if ( !is_array( $permissions[$name] ) ) {
                    throw new \RuntimeException( 'Permission entry must be an array.' );
                }
                if ( !in_array( $plugin->id, $permissions[$name], true ) ) {
                    $permissions[$name][] = $plugin->id;
                }
            }
            return $permissions;
        }, 'Failed to register plugin permissions.' );
    }

    /**
     * 更新权限文件
     * 在同一文件锁内读取与修改，并通过同目录临时文件原子保存；未改变内容时跳过写入。
     * @param Closure $update 权限数组更新回调，返回 null 表示拒绝更新
     * @param string $failureMessage 操作失败日志标题
     * @return bool 是否更新成功
     */
    private static function updatePermissions( Closure $update, string $failureMessage ): bool {
        $lockHandle = null; $locked = false;
        $temporaryFile = null;
        try {
            $configPath = config( 'plugins.path.config' );
            if ( !is_string( $configPath ) || trim( $configPath ) === '' ) {
                throw new \RuntimeException( 'Plugin config path is not configured.' );
            }
            $configPath = rtrim( $configPath, '/\\' ).DIRECTORY_SEPARATOR;
            File::ensureDirectoryExists( $configPath, 0755, true );
            // 使用独立锁文件，所有权限写入入口必须使用同一把锁
            $permissionsFile = "{$configPath}permissions.php";
            $lockHandle = fopen( "{$permissionsFile}.lock", 'c' );
            if ( $lockHandle === false ) {
                throw new \RuntimeException( 'Failed to open permissions lock.' );
            }
            $locked = flock( $lockHandle, LOCK_EX );
            if ( !$locked ) {
                throw new \RuntimeException( 'Failed to acquire permissions lock.' );
            }
            // 获得锁后再检查文件，避免并发初始化覆盖
            clearstatcache( true, $permissionsFile );
            $permissions = is_file( $permissionsFile ) ? include $permissionsFile : [];
            if ( !is_array( $permissions ) ) {
                throw new \RuntimeException( 'Permissions file must return an array.' );
            }
            $updatedPermissions = $update( $permissions );
            if ( $updatedPermissions === null ) { return false; }
            if ( $updatedPermissions === $permissions ) { return true; }
            $permissions = $updatedPermissions;
            // 在同目录完整写入临时文件，再原子替换，避免读取到未写完的内容
            $temporaryFile = tempnam( $configPath, '.permissions-' );
            if ( $temporaryFile === false || realpath( dirname( $temporaryFile ) ) !== realpath( $configPath ) ) {
                throw new \RuntimeException( 'Failed to create permissions temporary file.' );
            }
            $content = "<?php\n\nreturn ".var_export( $permissions, true ).";\n";
            if ( File::put( $temporaryFile, $content ) !== strlen( $content ) ) {
                throw new \RuntimeException( 'Failed to write complete permissions file.' );
            }
            $mode = is_file( $permissionsFile ) ? fileperms( $permissionsFile ) : ( 0666 & ~umask() );
            if ( $mode === false || !chmod( $temporaryFile, $mode & 0777 ) ) {
                throw new \RuntimeException( 'Failed to set permissions file mode.' );
            }
            if ( !rename( $temporaryFile, $permissionsFile ) ) {
                throw new \RuntimeException( 'Failed to replace permissions file.' );
            }
            $temporaryFile = null;
            if ( function_exists( 'opcache_invalidate' ) ) { opcache_invalidate( $permissionsFile, true ); }
            return true;
        }catch ( \Throwable $th ) {
            Log::error( $failureMessage, [
                'message' => $th->getMessage(),
                'exception_type' => get_class( $th ),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ] );
            return false;
        }finally {
            if ( is_string( $temporaryFile ) && is_file( $temporaryFile ) ) {
                // 清理失败时的临时文件，不让清理异常影响文件锁释放
                if ( !@unlink( $temporaryFile ) ) {
                    Log::error( 'Failed to remove permissions temporary file.', ['file' => $temporaryFile] );
                }
            }
            if ( is_resource( $lockHandle ) ) {
                if ( $locked ) { flock( $lockHandle, LOCK_UN ); }
                fclose( $lockHandle );
            }
        }
    }

    /**
     * 执行插件钩子
     * @param string $name 钩子名称
     * @param array $data 传递给插件的数据
     * @param bool $collect 是否收集所有插件的返回值
     * @return mixed 返回最后一个插件的返回值，或者收集所有插件的返回值（如果 $collect 为 true），失败时返回初始数据或空数组
     */
    public static function HookPlugin( string $name, array $data = [], bool $collect = false ): mixed {
        $errorReturn = $collect ? [] : $data;
        try {
            $configPath = rtrim( config( 'plugins.path.config' ), '/\\' ).DIRECTORY_SEPARATOR;
            $permissionsFile = "{$configPath}permissions.php";
            if ( !is_file( $permissionsFile ) || !is_readable( $permissionsFile ) ) { return $errorReturn; }
            $permissions = include $permissionsFile;
            if ( !is_array( $permissions ) || !array_key_exists( $name, $permissions ) || !is_array( $permissions[$name] ) ) { return $errorReturn; }
            if ( !in_array( $name, config( 'plugins.permissions', [] ), true ) ) { return $errorReturn; }
            $plugins = $permissions[$name];
            $result = []; $lastData = $data;
            foreach ( $plugins as $pluginName ) {
                if ( !is_string( $pluginName ) ) { continue; }
                $plugin = plugin( $pluginName );
                if ( !( $plugin instanceof PluginServiceProvider ) ) { continue; }
                if ( is_public( $plugin, $name ) ) {
                    $lastData = $plugin->$name( ...$data );
                    if ( $collect ) {
                        $result[] = $lastData;
                    }
                }
            }
            return $collect ? $result : $lastData;
        } catch ( \Throwable $th ) {
            Log::error( 'Failed to execute plugin hook.', [
                'name' => $name,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ] );
            return $errorReturn;
        }
    }

}

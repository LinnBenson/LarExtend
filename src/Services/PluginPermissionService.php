<?php

namespace LarExtend\Services;

use Closure;
use Illuminate\Support\Facades\Log;

/**
 * 插件权限服务
 */
class PluginPermissionService {
    // 当前权限配置缓存
    private static ?array $currentPermissions = null;

    /**
     * 获取当前权限配置
     * @param bool $refresh 是否刷新配置
     * @return array 当前权限配置
     */
    public static function getCurrentPermissions( bool $refresh = false ): array {
        if ( !$refresh && self::$currentPermissions !== null ) {
            return self::$currentPermissions;
        }
        self::$currentPermissions = null;
        $permissions = [];
        $allow = config( 'lar.plugin.permissions', [] );
        $file = config( 'lar.plugin.path.permissions' );
        if ( !empty( $file ) && is_file( $file ) ) {
            if ( $refresh && function_exists( 'opcache_invalidate' ) ) { opcache_invalidate( $file, true ); }
            $permissions = require $file;
            $permissions = is_array( $permissions ) ? $permissions : [];
            foreach( $permissions as $key => $value ) {
                if ( !is_string( $key ) || !is_array( $value ) || !in_array( $key, $allow, true ) ) {
                    unset( $permissions[ $key ] );
                }
            }
        }
        self::$currentPermissions = $permissions;
        return $permissions;
    }

    /**
     * 添加插件权限
     * @param string $pluginId 插件 ID
     * @param array $permissions 插件权限
     * @return bool 添加结果
     */
    public static function add( string $pluginId, array $permissions ): bool {
        return self::changePermissions( $pluginId, $permissions, true );
    }

    /**
     * 移除插件权限
     * @param string $pluginId 插件 ID
     * @param array $permissions 插件权限
     * @return bool 移除结果
     */
    public static function remove( string $pluginId, array $permissions ): bool {
        return self::changePermissions( $pluginId, $permissions, false );
    }

    /**
     * 清理失效插件和权限
     * @return bool 清理结果
     */
    public static function clean(): bool {
        // 插件初始化可能修改授权，放在写锁之外执行，避免重复获取同一把锁。
        $invalid = [];
        try {
            foreach ( self::getCurrentPermissions( true ) as $plugins ) {
                foreach ( $plugins as $id ) {
                    if ( is_string( $id ) && plugin( $id ) === null ) { $invalid[$id] = true; }
                }
            }
        }catch ( \Throwable $exception ) {
            self::$currentPermissions = null;
            Log::error( 'Failed to inspect plugin permissions.', [
                'message' => $exception->getMessage(),
            ] ); // 记录检查插件权限失败日志
            return false;
        }
        return self::writePermissions( function( array $current ) use ( $invalid ): array {
            foreach ( $current as $permission => $plugins ) {
                $current[$permission] = array_values( array_unique( array_filter(
                    $plugins,
                    fn ( mixed $id ): bool => is_string( $id ) && !isset( $invalid[$id] ),
                ) ) );
                if ( !$current[$permission] ) { unset( $current[$permission] ); }
            }
            return $current;
        } );
    }

    /**
     * 执行指定权限的操作
     * @param string $permission 权限名称
     * @param array $data 传递给权限操作的数据
     * @param bool $collect 是否收集操作结果
     * @return mixed 操作结果
     */
    public static function execute( string $permission, array $data = [], bool $collect = false ): mixed {
        $returnError = $collect ? [] : $data;
        try {
            // 获取当前权限允许插件
            $allowedPlugins = self::getCurrentPermissions()[$permission] ?? [];
            // 遍历允许的插件并执行操作
            $result = []; $lastData = $data;
            foreach ( $allowedPlugins as $pluginId ) {
                try {
                    $plugin = plugin( $pluginId );
                    if ( !$plugin ) { continue; }
                    if ( is_public( $plugin, $permission ) ) {
                        $lastData = $plugin->$permission( ...$data );
                        if ( $collect ) {
                            $result[] = $lastData;
                        }
                    }
                }catch ( \Throwable $th ) {
                    Log::error( 'An error occurred regarding the plugin\'s execution permissions.', [
                        'plugin' => $pluginId,
                        'permission' => $permission,
                        'message' => $th->getMessage(),
                        'file' => $th->getFile(),
                        'line' => $th->getLine(),
                    ]); // 记录插件执行权限错误日志
                }
            }
            return $collect ? $result : $lastData;
        }catch ( \Throwable $th ) {
            Log::error( 'Permission execution failed.', [
                'permission' => $permission,
                'message' => $th->getMessage(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
            ]); // 记录权限执行失败日志
        }
        return $returnError;
    }

    /**
     * 调整指定插件的授权
     * 校验全部输入后再更新，重复添加和重复移除均不改变结果。
     * @param string $pluginId 插件 ID
     * @param array $permissions 权限名称列表
     * @param bool $grant 是否授予权限
     * @return bool 保存结果
     */
    private static function changePermissions( string $pluginId, array $permissions, bool $grant ): bool {
        if ( !PluginLoadService::verificationPluginId( $pluginId ) || !array_is_list( $permissions ) ) { return false; }
        $allow = config( 'lar.plugin.permissions', [] );
        foreach ( $permissions as $permission ) {
            if ( !is_string( $permission ) || !in_array( $permission, $allow, true ) ) { return false; }
        }
        return self::writePermissions( function( array $current ) use ( $pluginId, $permissions, $grant ): array {
            foreach ( $permissions as $permission ) {
                $plugins = $current[$permission] ?? [];
                if ( $grant ) {
                    if ( !in_array( $pluginId, $plugins, true ) ) { $plugins[] = $pluginId; }
                }else {
                    $plugins = array_values( array_filter( $plugins, fn ( mixed $id ): bool => $id !== $pluginId ) );
                }
                if ( $plugins ) {
                    $current[$permission] = $plugins;
                }else {
                    unset( $current[$permission] );
                }
            }
            return $current;
        } );
    }

    /**
     * 保存权限变更
     * 独立锁文件串行化读取与写入，临时文件原子替换避免读到半份配置。
     * @param Closure $update 根据最新配置生成新配置
     * @return bool 保存结果
     */
    private static function writePermissions( Closure $update ): bool {
        $file = config( 'lar.plugin.path.permissions' );
        if ( !$file ) { return false; }
        $lock = null;
        $temporary = null;
        try {
            $directory = dirname( $file );
            if ( !is_dir( $directory ) && !@mkdir( $directory, 0755, true ) && !is_dir( $directory ) ) {
                // 创建权限目录失败
                throw new \RuntimeException( 'Unable to create the permissions directory.' );
            }
            // 锁文件保持固定，不随配置替换或删除，确保并发操作使用同一把锁。
            $lock = @fopen( "{$file}.lock", 'c' );
            if ( $lock === false || !flock( $lock, LOCK_EX ) ) {
                // 获取锁失败
                throw new \RuntimeException( 'Unable to lock the permissions file.' );
            }
            if ( function_exists( 'opcache_invalidate' ) ) { opcache_invalidate( $file, true ); }
            $current = self::getCurrentPermissions( true );
            $updated = $update( $current );
            $content = "<?php\n\nreturn ".var_export( $updated, true ).";\n";
            $temporary = tempnam( $directory, '.permissions-' );
            if ( $temporary === false || file_put_contents( $temporary, $content ) !== strlen( $content ) ) {
                // 写入临时权限文件失败
                throw new \RuntimeException( 'Unable to write the permissions file.' );
            }
            $mode = is_file( $file ) ? fileperms( $file ) & 0777 : 0644;
            if ( !chmod( $temporary, $mode ) || !rename( $temporary, $file ) ) {
                // 替换权限文件失败
                throw new \RuntimeException( 'Unable to replace the permissions file.' );
            }
            $temporary = null;
            if ( function_exists( 'opcache_invalidate' ) ) { opcache_invalidate( $file, true ); }
            self::$currentPermissions = $updated;
            return true;
        }catch ( \Throwable $exception ) {
            self::$currentPermissions = null;
            Log::error( 'Failed to save plugin permissions.', [
                'path' => $file,
                'message' => $exception->getMessage(),
            ] ); // 记录保存插件权限失败日志
            return false;
        }finally {
            try {
                if ( is_string( $temporary ) && is_file( $temporary ) && !unlink( $temporary ) ) {
                    // 删除插件权限临时文件失败。
                    Log::error( '[PluginPermissionService::writePermissions] Unable to remove temporary file.', ['path' => $temporary] );
                }
            }catch ( \Throwable $exception ) {
                // 清理插件权限临时文件时发生异常。
                Log::error( '[PluginPermissionService::writePermissions] Failed to clean temporary file.', ['path' => $temporary, 'message' => $exception->getMessage()] );
            }finally {
                if ( is_resource( $lock ) ) {
                    flock( $lock, LOCK_UN );
                    fclose( $lock );
                }
            }
        }
    }

}
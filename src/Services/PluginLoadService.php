<?php

namespace LarExtend\Services;

use Composer\InstalledVersions;
use LarExtend\Providers\PluginProvider;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

/**
 * 插件加载服务
 */
class PluginLoadService {
    /**
     * 已加载的插件列表
     */
    private static array $plugins = [];

    /**
     * 初始化插件
     * @param string $pluginPath 插件路径
     * @param bool $refresh 是否刷新插件配置
     * @param bool $boot 是否调用插件的 boot 方法进行初始化
     * @return PluginProvider|null 插件信息或 null
     */
    public static function init( string $pluginPath, bool $refresh = false, bool $save = true, bool $boot = true ): ?PluginProvider {
        try {
            $pluginPath = self::normalizePath( $pluginPath );
            $pluginPathHash = hash( 'sha256', $pluginPath );
            // 返回错误并缓存
            $returnError = function() use ( $pluginPathHash ) {
                self::$plugins[$pluginPathHash] = null;
                return null;
            };
            // 检查插件是否已加载且不需要刷新
            if ( !$refresh && array_key_exists( $pluginPathHash, self::$plugins ) ) {
                return self::$plugins[$pluginPathHash];
            }
            // 检查插件路径是否存在且为目录
            if ( !is_dir( $pluginPath ) ) {
                Log::error( 'Invalid plugin path.', [
                    'path' => $pluginPath,
                ]); // 插件路径无效
                return $returnError();
            }
            // 尝试加载插件
            $plugin = self::load( $pluginPath, $boot );
            if ( $save ) {
                self::$plugins[$pluginPathHash] = $plugin;
            }
            return $plugin;
        }catch ( \Throwable $th ) {
            Log::error( 'Failed to initialize plugin.', [
                'path' => $pluginPath,
                'message' => $th->getMessage(),
            ]); // 记录插件初始化失败日志
            return null;
        }
    }

    /**
     * 清理插件缓存
     * @param string $pluginPath 插件路径
     */
    public static function clearCache( string $pluginPath ): void {
        $pluginPath = self::normalizePath( $pluginPath );
        $hash = hash( 'sha256', $pluginPath );
        unset( self::$plugins[$hash] );
    }

    // 正在加载的插件，用于防止循环加载插件
    public static array $loading = [];
    /**
     * 加载插件
     * @param string $pluginPath 插件路径
     * @param bool $boot 是否调用插件的 boot 方法进行初始化
     * @return PluginProvider|null 插件信息或 null
     */
    public static function load( string $pluginPath, bool $boot = true ): ?PluginProvider {
        $pluginPath = self::normalizePath( $pluginPath );
        $hash = hash( 'sha256', $pluginPath );
        if ( isset( self::$loading[$hash] ) ) {
            Log::error( 'Circular plugin loading detected.', [
                'path' => $pluginPath
            ]); // 检测到循环加载插件
            return null;
        }
        self::$loading[$hash] = true;
        try {
            // 获取插件 ID
            $pluginId = basename( $pluginPath );
            if ( !self::verificationPluginId( $pluginId ) ) {
                Log::error( 'Invalid plugin ID.', [
                    'path' => $pluginPath,
                    'id' => $pluginId,
                ]); // 插件 ID 无效
                return null;
            }
            // 检查包信息
            $package = self::verificationPackage( $pluginPath );
            if ( $package === false ) { return null; }
            // 加载主文件
            $pluginIndex = $pluginPath.'index.php';
            if ( !file_exists( $pluginIndex ) ) {
                Log::error( 'Missing plugin index file.', [
                    'path' => $pluginPath,
                    'id' => $pluginId,
                ]); // 缺少插件入口文件 index.php
                return null;
            }
            $plugin = require $pluginIndex;
            if ( !( $plugin instanceof PluginProvider ) ) {
                Log::error( 'Invalid plugin object.', [
                    'path' => $pluginPath,
                    'id' => $pluginId,
                ]); // 插件对象无效
                return null;
            }
            // 插件加载成功，开始初始化
            $plugin->initialize( $pluginId, $pluginPath, $package );
            // 如果插件定义了 boot 方法且 $boot 为 true，则调用该方法进行初始化
            if ( $boot && is_public( $plugin, 'boot' ) ) {
                try {
                    $status = $plugin->boot();
                }catch ( \Throwable $th ) {
                    // 捕获 boot 方法抛出的异常
                    Log::error( 'Failed to boot the plugin.', [
                        'path' => $pluginPath,
                        'id' => $pluginId,
                        'message' => $th->getMessage(),
                    ]);
                    return null;
                }
                if ( $status === false ) {
                    // 插件的 boot 方法返回 false，视为加载失败
                    Log::error( 'Plugin boot returned false.', [
                        'path' => $pluginPath,
                        'id' => $pluginId,
                    ]);
                    return null;
                }
            }
            return $plugin;
        }catch ( \Throwable $th ) {
            Log::error( 'Error loading plugin.', [
                'path' => $pluginPath,
                'id' => $pluginId,
                'message' => $th->getMessage(),
            ]); // 加载插件时发生错误
            return null;
        }finally {
            unset( self::$loading[$hash] );
        }
    }

    /**
     * 验证插件包信息
     * @param string $pluginPath 插件路径
     * @return array|false 验证通过返回插件数据，失败返回 false
     */
    public static function verificationPackage( string $pluginPath ): array|false {
        // 检查插件路径下是否存在 plugin.json 文件
        $pluginPath = self::normalizePath( $pluginPath );
        $pluginJsonPath = $pluginPath.'plugin.json';
        if ( !file_exists( $pluginJsonPath ) ) {
            Log::error( 'Missing plugin.json.', [
                'path' => $pluginPath,
            ]); // 缺少 plugin.json 文件
            return false;
        }
        // 检查是否为 JSON
        $pluginJson = file_get_contents( $pluginJsonPath );
        if ( !is_json( $pluginJson ) ) {
            Log::error( 'plugin.json must be a JSON file.', [
                'path' => $pluginPath,
            ]); // plugin.json 必须是 JSON 文件
            return false;
        }
        $pluginData = array_replace( [
            'description' => '',
            'source' => '',
        ], json_decode( $pluginJson, true ) ); // 原始 JSON 信息
        // 验证插件 JSON 内容是否符合配置的规则
        $validator = Validator::make( $pluginData, config( 'lar.plugin.verification.package' ) );
        if ( $validator->fails() ) {
            Log::error( 'plugin.json contains incorrect field information.', [
                'path' => $pluginPath,
                'fields' => array_keys( $validator->failed() )
            ]); // 插件 JSON 字段信息不正确
            return false;
        }
        $verificationData = $validator->validated(); // 验证通过的插件数据
        // 环境验证
        $require = self::verificationRequire( $pluginData['require'] ?? [], $pluginPath );
        if ( $require === false ) { return false; }
        return array_merge( $verificationData, [
            'require' => $require,
            'permissions' => self::verificationPermissions( isset( $pluginData['permissions'] ) && is_array( $pluginData['permissions'] ) ? $pluginData['permissions'] : [] )
        ]);
    }

    /**
     * 验证插件依赖是否存在及格式是否正确
     * @param mixed $require 插件依赖信息
     * @param string $pluginPath 插件路径
     * @return array|false 验证通过返回依赖信息，失败返回 false
     */
    public static function verificationRequire( mixed $require, string $pluginPath ): array|false {
        $pluginPath = self::normalizePath( $pluginPath );
        if ( !is_array( $require ) ) {
            Log::error( 'Invalid require format in plugin.json.', [
                'path' => $pluginPath,
            ]); // 依赖格式无效
            return false;
        }
        $requirePlugins = $require['plugins'] ?? []; // 依赖的插件列表
        $requireComposer = $require['composer'] ?? []; // 依赖的 Composer 包列表
        if ( !is_array( $requirePlugins ) || !is_array( $requireComposer ) ) {
            Log::error( 'Invalid require format in plugin.json.', [
                'path' => $pluginPath,
            ]); // 依赖格式无效
            return false;
        }
        // 检查依赖的插件是否存在
        try {
            foreach ( $requirePlugins as $id => $version ) {
                if ( !self::verificationRequireVersion( $pluginPath, 'plugins', $id, $version ) ) {
                    return false;
                }
            }
            // 检查依赖的 Composer 包是否存在
            foreach ( $requireComposer as $id => $version ) {
                if ( !self::verificationRequireVersion( $pluginPath, 'composer', $id, $version ) ) {
                    return false;
                }
            }
        } catch ( \Throwable $th ) {
            Log::error( $th->getMessage(), [
                'path' => $pluginPath,
            ]); // 依赖验证发生错误
            return false;
        }
        return [ 'plugins' => $requirePlugins, 'composer' => $requireComposer ];
    }

    /**
     * 验证插件 ID 是否有效
     * @param string $pluginId 插件 ID
     * @return bool 是否有效
     */
    public static function verificationPluginId( string $pluginId ): bool {
        $reserve = [
            'plugin', 'plugins', 'package', 'packages', 'permissions'
        ];
        if ( in_array( strtolower( $pluginId ), $reserve, true ) ) {
            return false; // 保留关键字不可用
        }
        return preg_match( '/\A[a-zA-Z][a-zA-Z0-9_-]+\z/', $pluginId ) === 1;
    }

    /**
     * 路径整理
     * @param string $path 插件路径
     * @return string 规范化后的插件路径
     */
    public static function normalizePath( string $path ): string {
        $resolved = realpath( $path );
        return rtrim( $resolved !== false ? $resolved : $path, DIRECTORY_SEPARATOR ).DIRECTORY_SEPARATOR;
    }

    // 避免递归验证依赖
    private static array $verificationRequireVersionHash = [];
    /**
     * 版本号验证
     * @param string $pluginPath 插件路径
     * @param string $type 类型（plugins 或 composer）
     * @param string $id 插件 ID 或 Composer 包 ID
     * @param string $version 版本号
     * @return bool 是否有效
     */
    private static function verificationRequireVersion( string $pluginPath, string $type, string $id, string $version ): bool {
        $hash = md5( $pluginPath.$type.$id.$version );
        // 报告错误
        $returnError = function( string $message, ?string $depend = null ) use ( $pluginPath, $type ) {
            Log::error( 'Dependency error occurred.', [
                'path' => $pluginPath,
                'type' => $type,
                'message' => $message,
                'depend' => $depend
            ]);
            return false;
        };
        if ( isset( self::$verificationRequireVersionHash[$hash] ) ) {
            return $returnError( "Circular dependency detected.", $id ); // 循环依赖检测
        }
        self::$verificationRequireVersionHash[$hash] = true;
        try {
            switch ( $type ) {
                case 'plugins':
                    if ( !self::verificationPluginId( $id ) ) {
                        return $returnError(  "Invalid plugin ID: {$id}", $id ); // 插件 ID 无效
                    }
                    if ( preg_match( '/\A(>=|<=|>|<|=)((0|[1-9][0-9]*)\.(0|[1-9][0-9]*)\.(0|[1-9][0-9]*))\z/', $version, $matches ) !== 1 ) {
                        return $returnError( "Invalid version constraint.", $id ); // 版本约束无效
                    }
                    $workPath = self::normalizePath( config( 'lar.plugin.path.work' ) );
                    if ( !is_dir( $workPath.$id ) ) {
                        return $returnError( "The required plugin does not exist.", $id ); // 必需的插件不存在
                    }
                    $depend = self::verificationPackage( self::normalizePath( $workPath.$id ) );
                    if ( $depend === false ) {
                        return $returnError( "There is an issue with a dependent plugin.", $id ); // 依赖的插件存在异常
                    }
                    $dependVersion = $depend['version']; // 获取依赖插件的版本号
                    $operator = $matches[1];
                    $requiredVersion = $matches[2];
                    if ( version_compare( $dependVersion, $requiredVersion, $operator ) === false ) {
                        return $returnError( "The dependent plugin version does not satisfy the required constraint: {$version}", $id ); // 依赖插件版本不满足要求
                    }
                    break;
                case 'composer':
                    if ( preg_match( '~\A[a-z0-9]([_.-]?[a-z0-9]+)*/[a-z0-9](([_.]|-{1,2})?[a-z0-9]+)*\z~', $id ) !== 1 ) {
                        return $returnError( "Invalid composer package ID: {$id}", $id ); // Composer 包 ID 无效
                    }
                    if ( preg_match( '/\A(>=|<=|>|<|=)((?:0|[1-9][0-9]*)(?:\.(?:0|[1-9][0-9]*)){2,3})\z/', $version, $matches ) !== 1 ) {
                        return $returnError( "Invalid version constraint.", $id ); // 版本约束无效
                    }
                    if ( !InstalledVersions::isInstalled( $id ) ) {
                        return $returnError( "The required Composer package is not installed.", $id ); // 必需的 Composer 包未安装
                    }
                    $dependVersion = InstalledVersions::getVersion( $id );
                    if ( $dependVersion === null || !preg_match( '/\A[0-9]+\./', $dependVersion ) ) {
                        return $returnError( "The Composer package does not have a comparable numeric version.", $id ); // Composer 包没有可比较的数字版本
                    }
                    $requiredVersion = substr_count( $matches[2], '.' ) === 2 ? "{$matches[2]}.0" : $matches[2];
                    if ( !version_compare( $dependVersion, $requiredVersion, $matches[1] ) ) {
                        return $returnError( "The Composer package version does not satisfy the required constraint: {$version}", $id ); // Composer 包版本不满足要求
                    }
                    break;

                default:
                    return $returnError( "Unsupported dependency type: {$type}", $id ); // 不支持的依赖类型
            }
            return true;
        }finally {
            unset( self::$verificationRequireVersionHash[$hash] );
        }
    }

    /**
     * 验证插件权限配置
     * @param array $permissions 插件声明的权限列表
     * @return array 验证通过的权限列表
     */
    private static function verificationPermissions( array $permissions ): array {
        if ( empty( $permissions ) || !array_is_list( $permissions ) ) { return []; }
        $result = [];
        $allow = config( 'lar.plugin.permissions', [] );
        foreach ( $permissions as $item ) {
            if ( !is_string( $item ) || !in_array( $item, $allow, true ) ) { continue; }
            $result[] = $item;
        }
        return $result;
    }
}
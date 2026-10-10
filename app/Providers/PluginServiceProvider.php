<?php

namespace App\Providers;

use Illuminate\Support\Facades\Log;

class PluginServiceProvider {

    // 插件 ID
    public ?string $id = null;

    // 插件基础路径
    public ?string $base = null;

    // 插件包信息
    public ?array $package = null;

    // 插件配置缓存
    private ?array $configs = null;

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
                        'file' => $file,
                        'exception_type' => get_class( $th ),
                        'line' => $th->getLine(),
                    ]);
                    return [];
                }
            };
            $configPath = rtrim( config( 'plugins.path.config' ), '/\\' ).DIRECTORY_SEPARATOR;
            $this->configs = array_replace_recursive(
                $loadConfig( "{$this->base}config.php" ),
                $loadConfig( "{$configPath}{$this->id}.php" ),
            );
        }
        if ( $key === '' ) { return $this->configs; }
        return data_get( $this->configs, $key, $default );
    }

}
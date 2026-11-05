<?php

use LarExtend\Services\PluginLoadService;

if ( !function_exists( 'setting' ) ) {
    /**
     * 读取系统配置
     * 根据键名读取 settings 表中的配置值，并按配置类型转换数据。
     * @param string $key 配置键名
     * @param mixed $default 配置不存在时的默认值
     * @return mixed 配置值或默认值
     */
    function setting( string $key, mixed $default = null ): mixed {
        $keys = explode( '.', $key );
        if ( count( $keys ) !== 2 || $keys[0] === '' || $keys[1] === '' ) {
            return value( $default );
        }
        $settings = request()->attributes->get( 'lar.settings' );
        if ( $settings === null ) {
            $model = new LarExtend\Models\Settings();
            if ( !$model->getConnection()->getSchemaBuilder()->hasTable( $model->getTable() ) ) {
                return value( $default );
            }
            $settings = $model->newQuery()
                ->get( ['category', 'type', 'key', 'value'] )
                ->mapWithKeys( function ( LarExtend\Models\Settings $config ): array {
                    $value = $config->value;
                    if ( $value !== null ) {
                        $value = match ( $config->type ) {
                            'boolean' => filter_var( $value, FILTER_VALIDATE_BOOLEAN ),
                            'number' => (int) $value,
                            'decimal' => (float) $value,
                            'json', 'images', 'files' => json_decode( $value, true, 512, JSON_THROW_ON_ERROR ),
                            default => $value,
                        };
                    }
                    return ["{$config->category}.{$config->key}" => $value];
                } )
                ->all();
            request()->attributes->set( 'lar.settings', $settings );
        }
        return array_key_exists( $key, $settings ) ? $settings[$key] : value( $default );
    }
}

if ( !function_exists( 'addConfig' ) ) {
    /**
     * 添加配置信息
     * @param string $key 配置键
     * @param array $value 配置值
     * @return void
     */
    function addConfig( string $key, array $value ): void {
        $config = config( $key );
        if ( is_array( $config ) && is_array( $value ) ) {
            $config = array_merge( $config, $value );
        } else {
            $config = $value;
        }
        config( [$key => $config] );
    }
}

if ( !function_exists( 'echoJson' ) ) {
    /**
     * 输出 JSON 响应
     * @param int $status 状态码
     * @param mixed $message 消息
     * @param array|null $data 响应数据
     * @param int $code HTTP 状态码
     * @return string JSON 字符串
     */
    function echoJson( int $status, mixed $message, ?array $data = null, int $code = 200 ): string {
        $statusMap = [
            0 => 'success',
            1 => 'info',
            2 => 'error',
            3 => 'warning',
        ];
        $statusText = $statusMap[$status] ?? 'unknown';
        if (
            is_array( $message ) &&
            array_is_list( $message ) &&
            isset( $message[0] ) &&
            is_string( $message[0] ) &&
            ( !isset( $message[1] ) || is_array( $message[1] ) ) &&
            count( $message ) <= 2 &&
            Lang::has( $message[0] )
        ) {
            $message = __( $message[0], $message[1] ?? [] );
        }else if ( is_array( $message ) && !array_is_list( $message ) && $data === null ) {
            $data = $message;
            $message = null;
        }
        $data = [
            'status' => $statusText,
            'code' => $code,
            'time' => time(),
            'message' => $message,
            'data' => $data ?? [],
        ];
        return json_encode( $data, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_INVALID_UTF8_SUBSTITUTE|JSON_THROW_ON_ERROR );
    }
}

if ( !function_exists( 'echoRequest' ) ) {
    /**
     * 输出请求响应
     * @param int $status 状态码
     * @param mixed $message 消息
     * @param array|null $data 响应数据
     * @param int $code HTTP 状态码
     * @param array $headers HTTP 头
     * @return \Illuminate\Http\JsonResponse
     */
    function echoRequest( int $status, mixed $message, ?array $data = null, int $code = 200, array $headers = [] ): \Illuminate\Http\JsonResponse {
        return \Illuminate\Http\JsonResponse::fromJsonString(
            echoJson( $status, $message, $data, $code ),
            $code,
            $headers
        );
    }
}

if ( !function_exists( 'plugin' ) ) {
    /**
     * 获取插件
     * @param string $pluginId 插件 ID
     * @param bool $refresh 是否刷新插件配置
     * @return \LarExtend\Providers\PluginProvider|null 插件信息或 null
     */
    function plugin( string $pluginId, bool $refresh = false ): ?\LarExtend\Providers\PluginProvider {
        if ( !PluginLoadService::verificationPluginId( $pluginId ) ) { return null; }
        $workPath = PluginLoadService::normalizePath( config( 'lar.plugin.path.work' ) );
        return PluginLoadService::init( $workPath.$pluginId, $refresh );
    }
}
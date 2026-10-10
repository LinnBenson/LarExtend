<?php

use App\Services\PluginService;
use App\Providers\PluginServiceProvider;

if ( !function_exists( 'echoJson' ) ) {
    /**
     * 输出 JSON 响应
     * 根据状态码和数据输出标准化的 JSON 响应。
     * @param int $status 状态码，布尔值表示成功或失败，整数表示自定义状态码
     * @param mixed $message 响应消息，通常为字符串或包含翻译键的数组
     * @param mixed $data 响应数据
     * @param int|null $code HTTP 状态码，为 null 时根据响应状态自动生成
     * @param array<string, string> $headers HTTP 响应头
     * @return \Illuminate\Http\JsonResponse JSON 响应对象或字符串
     */
    function echoJson( int $status, mixed $message, mixed $data = null, ?int $code = null, array $headers = [] ): \Illuminate\Http\JsonResponse {
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
        if ( $code === null ) { $code = 200; }
        $data = [
            'status' => $statusText,
            'code' => $code,
            'time' => time(),
            'message' => $message,
            'data' => is_array( $data ) ? $data : [],
        ];
        return response()->json( $data, $code, $headers, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES |JSON_INVALID_UTF8_SUBSTITUTE );
    }
}
if ( !function_exists( 'getDeviceType' ) ) {
    /**
     * 获取访问设备类型
     * @return string Linux、Windows、Mac、iPhone、iPad、Android 或 Other
     */
    function getDeviceType(): string {
        $ua = strtolower( request()->userAgent() ?? '' );
        if ( $ua === '' ) { return 'Other'; }
        if ( str_contains( $ua, 'ipad' ) ) { return 'iPad'; }
        if ( str_contains( $ua, 'ipod' ) ) { return 'iPod'; }
        if ( str_contains( $ua, 'iphone' ) ) { return 'iPhone'; }
        if ( str_contains( $ua, 'android' ) ) { return 'Android'; }
        if ( str_contains( $ua, 'windows' ) ) { return 'Windows'; }
        if ( str_contains( $ua, 'macintosh' ) || str_contains( $ua, 'mac os x' ) ) { return 'Mac'; }
        if ( str_contains( $ua, 'linux' ) ) { return 'Linux'; }
        return 'Other';
    }
}
if ( !function_exists( 'plugin' ) ) {
    /**
     * 获取插件实例
     * @param string $pluginId 插件唯一标识符
     * @return PluginServiceProvider|null 插件实例，插件不存在或加载失败返回 null
     */
    function plugin( string $pluginId ): ?PluginServiceProvider {
        if ( preg_match( '/\A[a-zA-Z][a-zA-Z0-9_-]*\z/', $pluginId ) !== 1 ) { return null; }
        $workPath = rtrim( config( 'plugins.path.work' ), '/\\' ).DIRECTORY_SEPARATOR;
        return PluginService::getPluginInstance( "{$workPath}{$pluginId}" );
    }
}
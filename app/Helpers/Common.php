<?php
if ( !function_exists( 'is_json' ) ) {
    /**
     * 判断字符串是否为 JSON
     * 判断传入字符串是否为 JSON 对象或 JSON 数组字符串。
     * @param mixed $value 待判断的字符串
     * @return bool 是否为合法 JSON 字符串
     */
    function is_json( mixed $value ): bool {
        $value = trim( $value );
        if ( $value === '' ) { return false; }
        if ( ! in_array( $value[0], ['{', '['], true ) ) { return false; }
        json_decode( $value );
        return json_last_error() === JSON_ERROR_NONE;
    }
}
if ( !function_exists( 'is_public' ) ) {
    /**
     * 判断对象方法是否公开
     * 判断传入方法名称是否为对象上存在的公开方法。
     * @param object $object 待判断的对象
     * @param string $method 待判断的方法名称
     * @return bool 是否为公开方法
     */
    function is_public( object $object, string $method ): bool {
        if ( $method === '' || ! method_exists( $object, $method ) ) { return false; }
        return ( new ReflectionMethod( $object, $method ) )->isPublic();
    }
}
if ( !function_exists( 'is_uuid' ) ) {
    /**
     * 判断字符串是否为 UUID
     * 判断传入字符串是否符合标准 UUID 格式。
     * @param mixed $value 待判断的字符串
     * @return bool 是否为 UUID 字符串
     */
    function is_uuid( mixed $value ): bool {
        return (bool) preg_match( '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value );
    }
}
if ( !function_exists( 'uuid' ) ) {
    /**
     * 生成 UUID
     * 生成一个标准 UUID 字符串。
     * @return string UUID 字符串
     */
    function uuid(): string {
        return (string) Illuminate\Support\Str::uuid();
    }
}
if ( !function_exists( 'randomString' ) ) {
    /**
     * 生成随机字符串
     * 根据长度和类型生成随机字符串。
     * @param int $length 字符串长度
     * @param int $type 字符串类型，0 仅数字，1 仅大小写字母，2 大小写字母加数字
     * @return string 随机字符串
     */
    function randomString( int $length, int $type = 2 ): string {
        if ( $length <= 0 ) { return ''; }
        $chars = match( $type ) {
            0 => '0123456789',
            1 => 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
            default => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
        };
        $result = '';
        $max = strlen( $chars ) - 1;
        for ( $i = 0; $i < $length; $i++ ) {
            $result .= $chars[random_int( 0, $max )];
        }
        return $result;
    }
}
if ( !function_exists( 'toDate' ) ) {
    /**
     * 格式化时间戳为日期字符串
     * 将传入的时间戳格式化为标准日期时间字符串，默认为当前时间戳。
     * @param int|null $timestamp 时间戳，为 null 时使用当前时间戳
     * @return string 格式化后的日期时间字符串
     */
    function toDate( ?int $timestamp = null ): string {
        $format = 'Y-m-d H:i:s';
        if ( $timestamp === null ) { $timestamp = time(); }
        return date( $format, $timestamp );
    }
}
if ( !function_exists( 'toString' ) ) {
    /**
     * 将任意值转换为字符串
     * 将传入的任意类型值转换为字符串表示形式。
     * @param mixed $value 待转换的值
     * @return string 转换后的字符串
     */
    function toString( mixed $value ): string {
        if ( is_string( $value ) ) { return $value; }
        if ( is_array( $value ) ) {
            $text = true;
            foreach( $value as $arrayKey => $arrayValue ) {
                if ( !is_numeric( $arrayKey ) || !is_string( $arrayValue ) ) { $text = false; break; }
            }
            return $text ? implode( "\n", $value ) : var_export( $value, true );
        }
        if ( is_bool( $value ) ) { return $value ? '[:true:]' : '[:false:]'; }
        if ( is_null( $value ) ) { return '[:null:]'; }
        if ( is_numeric( $value ) ) { return (string)$value; }
        if ( is_object( $value ) ) { return '[:object:'.get_class( $value ).':]'; }
        if ( is_callable( $value ) ) { return '[:function:]'; }
        return var_export( $value, true );
    }
}
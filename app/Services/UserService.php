<?php

namespace App\Services;

/**
 * 用户服务类
 * @package App\Services
 */
class UserService {

    /**
     * 生成随机邀请码
     * @return string 8 位大写十六进制邀请码
     */
    public static function generateInvite(): string {
        return strtoupper( bin2hex( random_bytes( 4 ) ) );
    }

    /**
     * 生成随机密码盐
     * @return string 32 位随机字符串
     */
    public static function generateHash(): string {
        return randomString( 32, 2 );
    }

}
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * 用户模型
 * @package App\Models
 */
class User extends Authenticatable {
    use Notifiable;

    /**
     * 表信息
     */
    protected $table = 'users';
    protected $primaryKey = 'uid';

    /**
     * 用户字段备注
     * @var array<string, string>
     */
    public const FIELD_COMMENTS = [
        'uid' => '用户 UID',
        'agent' => '上级代理',
        'username' => '用户名',
        'email' => '邮箱',
        'phone' => '手机号',
        'nickname' => '昵称',
        'password' => '登录密码',
        'avatar' => '头像',
        'level' => '级别',
        'status' => '状态：1启用，0禁用',
        'invite' => '邀请码',
        'hash' => '密码盐',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ];

    /**
     * 可以批量赋值的属性
     * 调用方需先验证输入，代理、权限、状态、邀请码和密码盐由服务端单独赋值。
     * @var list<string>
     */
    protected $fillable = [
        'username',
        'email',
        'phone',
        'nickname',
        'password',
        'avatar',
    ];

    /**
     * 序列化时需要隐藏的属性
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'hash'
    ];

    /**
     * 类型转换声明
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'uid' => 'integer',
            'agent' => 'integer',
            'status' => 'boolean',
            'level' => 'integer',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * 获取可公开信息
     * @return array<string, mixed> 用户的公开信息
     */
    public function getUserinfo(): array {
        return [
            'uid' => $this->uid,
            'username' => $this->username,
            'nickname' => $this->nickname,
            'avatar' => $this->avatar,
            'level' => $this->level,
        ];
    }

    /**
     * 获取用户表所有字段对应的备注
     * @return array<string, string> 字段备注列表
     */
    public static function fields(): array {
        return self::FIELD_COMMENTS;
    }

    /**
     * 根据字段名获取用户表字段对应的备注
     * @param string $field 字段名
     * @return string 字段备注
     */
    public static function field( string $field ): string {
        return self::FIELD_COMMENTS[$field] ?? '';
    }

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
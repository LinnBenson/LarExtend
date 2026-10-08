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

}
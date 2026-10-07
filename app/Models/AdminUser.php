<?php

namespace App\Models;

use Filament\Models\Contracts\HasAvatar;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

/**
 * 管理员用户模型
 * @package App\Models
 */
class AdminUser extends Authenticatable implements FilamentUser, HasAvatar {
    use Notifiable;

    /**
     * 管理员用户字段备注
     * @var array<string, string>
     */
    public const FIELD_COMMENTS = [
        'id' => '管理员用户ID',
        'name' => '用户名',
        'email' => '邮箱',
        'status' => '状态：1启用，0禁用',
        'level' => '级别',
        'password' => '密码',
        'avatar' => '头像',
        'remember_token' => '记住登录',
        'created_at' => '创建时间',
        'updated_at' => '更新时间',
    ];

    /**
     * 可以批量赋值的属性
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'status',
        'level',
        'password',
        'avatar',
    ];

    /**
     * 序列化时需要隐藏的属性
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * 获取需要类型转换的属性
     * @return array<string, string>
     */
    protected function casts(): array {
        return [
            'status' => 'boolean',
            'level' => 'integer',
            'password' => 'hashed',
        ];
    }

    /**
     * 获取字段备注列表
     * @return array<string, string> 字段备注列表
     */
    public static function fields(): array {
        return self::FIELD_COMMENTS;
    }

    /**
     * 获取字段备注
     * @param string $field 字段名
     * @return string 字段备注
     */
    public static function field( string $field ): string {
        return self::FIELD_COMMENTS[$field] ?? '';
    }

}
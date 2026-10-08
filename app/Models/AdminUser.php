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
     * 获取 Filament 头像地址。
     * 返回当前管理员上传的头像公开访问地址。
     * @return string|null 头像地址
     */
    public function getFilamentAvatarUrl(): ?string {
        if ( blank( $this->avatar ) ) { return null; }
        if ( ! Storage::disk( 'public' )->exists( $this->avatar ) ) { return null; }
        return Storage::disk( 'public' )->url( $this->avatar );
    }

    /**
     * 判断是否可以访问 Filament 面板。
     * 只允许启用状态的管理员用户访问后台面板。
     * @param Panel $panel Filament 面板
     * @return bool 是否允许访问
     */
    public function canAccessPanel( Panel $panel ): bool {
        $minimumLevel = config( 'admin_level.levels.Ordinary' );
        if ( !is_int( $minimumLevel ) || $minimumLevel < 1 ) { return false; }
        return $this->status === true && $this->level >= $minimumLevel;
    }

}
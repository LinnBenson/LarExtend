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
     * 用户权限映射
     * @var array<string, init>
     */
    public const LEVELS = [
        'ordinary' => 1,
        'service' => 1000,
        'agent' => 10000,
        'manage' => 90000,
        'administrator' => 99990
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
     * 序列化时附加的属性
     * @var list<string>
     */
    protected $appends = [ 'grade' ];

    /**
     * 获取用户级别名称
     * @return string 级别名称
     */
    public function getGradeAttribute(): string {
        return self::getLevel( $this->level ?? 0 );
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
        $minimumLevel = self::LEVELS['ordinary'];
        if ( !is_int( $minimumLevel ) || $minimumLevel < 1 ) { return false; }
        return $this->status === true && $this->level >= $minimumLevel;
    }

    /**
     * 判断是否为管理员
     * @param Panel $panel Filament 面板
     * @return bool 是否允许访问
     */
    public function canManagePanel( Panel $panel ): bool {
        $minimumLevel = self::LEVELS['manage'];
        if ( !is_int( $minimumLevel ) || $minimumLevel < 1 ) { return false; }
        return $this->status === true && $this->level >= $minimumLevel;
    }

    /**
     * 获取用户等级
     * 不传等级时返回全部配置，传入等级时按最接近的上限返回名称。
     * @param int|string|null $level 用户等级
     * @return array|string 等级列表或等级名称
     */
    public static function getLevel( int|string|null $level = null ): array|string {
        if ( $level === null ) { return self::LEVELS; }
        $level = is_string( $level ) ? trim( $level ) : $level;
        $level = filter_var( $level, FILTER_VALIDATE_INT );
        if ( $level === false || $level < 0 ) { return 'Unknown'; }
        foreach ( array_reverse( self::LEVELS, true ) as $name => $maximumLevel ) {
            if ( $level >= $maximumLevel ) { return $name; }
        }
        return 'Unknown';
    }

}
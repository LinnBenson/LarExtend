<?php

namespace App\Filament\Concerns;

use App\Models\AdminUser;
use Filament\Facades\Filament;

/**
 * 后台页面等级权限
 * 根据 admin_level.navigation 配置控制资源或页面访问和菜单显示。
 */
trait HasNavigationLevel {

    /**
     * 校验页面访问权限
     * @return bool 是否允许访问
     */
    public static function canAccess(): bool {
        return static::hasRequiredNavigationLevel() && parent::canAccess();
    }

    /**
     * 校验菜单显示权限
     * @return bool 是否显示菜单
     */
    public static function shouldRegisterNavigation(): bool {
        return static::hasRequiredNavigationLevel() && parent::shouldRegisterNavigation();
    }

    /**
     * 校验管理员状态和页面等级
     * 未配置或配置类型不正确时拒绝访问，等级必须大于配置值。
     * @return bool 是否达到要求
     */
    protected static function hasRequiredNavigationLevel(): bool {
        $user = Filament::auth()->user();
        if ( !$user instanceof AdminUser || $user->status !== true ) { return false; }
        $levels = config( 'admin_level.levels', [] );
        if ( !is_array( $levels ) ) { return false; }
        $minimumLevel = $levels[static::$navigationPermission] ?? null;
        if ( !is_int( $minimumLevel ) || $minimumLevel < 0 ) { return false; }
        return $user->level >= $minimumLevel;
    }

}

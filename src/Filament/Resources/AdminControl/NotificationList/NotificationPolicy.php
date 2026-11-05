<?php

namespace LarExtend\Filament\Resources\AdminControl\NotificationList;

use Illuminate\Database\Eloquent\Builder;
use LarExtend\Models\AdminUser;
use LarExtend\Models\Notification;

/**
 * 通知管理权限
 */
class NotificationPolicy {
    public function viewAny( AdminUser $user ): bool {
        return $user->status && $user->level >= AdminUser::LEVELS['manage'];
    }

    /**
     * 限制通知管理范围
     * 保留本人通知，排除发给其他现存同级或更高等级管理员的通知，兼容多态别名。
     * @param Builder $query 通知查询
     * @param AdminUser $user 当前管理员
     * @return Builder 过滤后的查询
     */
    public static function scopeVisible( Builder $query, AdminUser $user ): Builder {
        $notification = $query->getModel();
        $admin = new AdminUser();
        return $query->whereNotExists(
            AdminUser::query()->selectRaw( '1' )
                ->whereColumn( $admin->getQualifiedKeyName(), $notification->qualifyColumn( 'notifiable_id' ) )
                ->whereIn( $notification->qualifyColumn( 'notifiable_type' ), [AdminUser::class, $admin->getMorphClass()] )
                ->where( $admin->getQualifiedKeyName(), '!=', $user->getKey() )
                ->where( $admin->qualifyColumn( 'level' ), '>=', $user->level )
        );
    }

    public function view( AdminUser $user, Notification $record ): bool {
        return $this->viewAny( $user ) && self::scopeVisible( Notification::query(), $user )->whereKey( $record->getKey() )->exists();
    }

    public function delete( AdminUser $user, Notification $record ): bool { return $this->view( $user, $record ); }
    public function create( AdminUser $user ): bool { return false; }
    public function update( AdminUser $user, Notification $record ): bool { return false; }
}

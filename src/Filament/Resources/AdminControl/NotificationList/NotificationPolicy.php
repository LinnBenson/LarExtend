<?php

namespace LarExtend\Filament\Resources\AdminControl\NotificationList;

use LarExtend\Models\AdminUser;
use LarExtend\Models\Notification;

/**
 * 通知管理权限
 */
class NotificationPolicy {
    public function viewAny( AdminUser $user ): bool {
        return $user->status && $user->level >= AdminUser::LEVELS['manage'];
    }

    public function view( AdminUser $user, Notification $record ): bool { return $this->viewAny( $user ); }
    public function delete( AdminUser $user, Notification $record ): bool { return $this->viewAny( $user ); }
    public function create( AdminUser $user ): bool { return false; }
    public function update( AdminUser $user, Notification $record ): bool { return false; }
}

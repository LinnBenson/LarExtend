<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Facades\Filament;
use LarExtend\Models\AdminUser;

/**
 * 管理员授权策略
 */
class AdminUserPolicy {
    public function viewAny( AdminUser $user ): bool {
        return $user->canAccessPanel( Filament::getCurrentOrDefaultPanel() );
    }

    public function view( AdminUser $user, AdminUser $record ): bool {
        return $this->viewAny( $user ) && ( $user->is( $record ) || ( $user->exceedService() && $record->level < $user->level ) );
    }

    public function create( AdminUser $user ): bool {
        return $this->viewAny( $user ) && $user->exceedService();
    }

    public function update( AdminUser $user, AdminUser $record ): bool {
        return $this->view( $user, $record );
    }

    public function updateStatus( AdminUser $user, AdminUser $record ): bool {
        return !$user->is( $record ) && $this->update( $user, $record );
    }

    /**
     * 判断是否允许向目标管理员发送消息
     * @param AdminUser $user 当前管理员
     * @param AdminUser $record 接收管理员
     * @return bool 是否允许发送
     */
    public function sendMessage( AdminUser $user, AdminUser $record ): bool {
        return $this->viewAny( $user ) && !$user->is( $record )
            && $record->level < $user->level && $this->view( $user, $record )
            && setting( 'admin.send', false ) === true;
    }

    public function delete( AdminUser $user, AdminUser $record ): bool {
        return !$user->is( $record ) && $this->view( $user, $record );
    }
}

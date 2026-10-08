<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use App\Models\AdminUser;

/**
 * AdminUserPolicy
 * 管理员用户访问策略。
 * @package App\Filament\Resources\AdminControl\AdminUsers
 */
class AdminUserPolicy {

    /**
     * 查看管理员列表。
     * 仅允许启用且达到普通管理员等级的管理员查看列表。
     * @param AdminUser $user 当前管理员
     * @return bool 是否允许
     */
    public function viewAny( AdminUser $user ): bool {
        return $user->status === true && $user->level >= AdminUser::LEVELS['ordinary'];
    }

    /**
     * 查看管理员。
     * 允许查看自己，具有管理权限时可查看级别更低的管理员。
     * @param AdminUser $user 当前管理员
     * @param AdminUser $record 目标管理员
     * @return bool 是否允许
     */
    public function view( AdminUser $user, AdminUser $record ): bool {
        return $this->viewAny( $user ) &&
            ( $user->is( $record ) || ( $user->canManage() && $record->level < $user->level ) );
    }

    /**
     * 新增管理员。
     * 具有管理权限的管理员可以新增更低级管理员。
     * @param AdminUser $user 当前管理员
     * @return bool 是否允许
     */
    public function create( AdminUser $user ): bool {
        return $user->canManage();
    }

    /**
     * 编辑管理员。
     * 允许编辑自己，具有管理权限时可编辑级别更低的管理员。
     * @param AdminUser $user 当前管理员
     * @param AdminUser $record 目标管理员
     * @return bool 是否允许
     */
    public function update( AdminUser $user, AdminUser $record ): bool {
        return $this->view( $user, $record );
    }

    /**
     * 删除管理员。
     * 具有管理权限时可删除级别更低的管理员，并禁止删除自己。
     * @param AdminUser $user 当前管理员
     * @param AdminUser $record 目标管理员
     * @return bool 是否允许
     */
    public function delete( AdminUser $user, AdminUser $record ): bool {
        return $user->canManage() && !$user->is( $record ) && $record->level < $user->level;
    }

    /**
     * 批量删除管理员。
     * 管理员列表禁止执行批量删除。
     * @param AdminUser $user 当前管理员
     * @return bool 是否允许
     */
    public function deleteAny( AdminUser $user ): bool {
        return false;
    }

}

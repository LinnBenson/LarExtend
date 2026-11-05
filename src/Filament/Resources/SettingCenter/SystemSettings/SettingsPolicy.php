<?php

namespace LarExtend\Filament\Resources\SettingCenter\SystemSettings;

use LarExtend\Models\AdminUser;
use LarExtend\Models\Settings;

/**
 * 设置访问策略
 * 配置值编辑与配置项结构管理分别授权。
 */
class SettingsPolicy {
    public function viewAny( AdminUser $user ): bool {
        return $user->status && $user->level >= AdminUser::LEVELS['manage'];
    }

    public function updateValue( AdminUser $user, Settings $record ): bool {
        if ( $record->category === 'admin' ) { return $this->create( $user ); }
        return $this->viewAny( $user ) && in_array( $record->category, Settings::CATEGORIES, true );
    }

    public function create( AdminUser $user ): bool {
        return $user->status && $user->level >= AdminUser::LEVELS['administrator'];
    }

    public function update( AdminUser $user, Settings $record ): bool {
        return $this->create( $user ) && in_array( $record->category, Settings::CATEGORIES, true );
    }

    public function delete( AdminUser $user, Settings $record ): bool {
        return $this->update( $user, $record );
    }
}

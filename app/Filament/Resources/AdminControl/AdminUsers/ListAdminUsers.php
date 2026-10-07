<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use App\Filament\Resources\AdminControl\AdminUsers\AdminUserResource;
use Filament\Resources\Pages\ListRecords;

/**
 * 管理员列表页面
 * 使用 Filament 内置表格和资源访问校验。
 */
class ListAdminUsers extends ListRecords {
    protected static string $resource = AdminUserResource::class;

    /**
     * 校验列表访问权限
     * 首次加载和 Livewire 后续请求均检查，防止绕过导航直接访问。
     * @return void
     */
    public function boot(): void {
        abort_unless( AdminUserResource::canViewAny(), 403 );
    }
}

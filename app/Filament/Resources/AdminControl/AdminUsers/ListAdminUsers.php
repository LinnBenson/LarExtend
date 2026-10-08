<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;

/**
 * 管理员列表页面
 * 使用 Filament 内置表格和资源访问校验。
 */
class ListAdminUsers extends ListRecords {
    protected static string $resource = AdminUserResource::class;

    /**
     * 获取新增管理员操作。
     * @return array<CreateAction> 头部操作
     */
    protected function getHeaderActions(): array {
        return [CreateAction::make()->label( __( 'admin::AdminControl.AdminUsers.actions.create' ) )];
    }

    /**
     * 校验列表访问权限
     * 首次加载和 Livewire 后续请求均检查，防止绕过导航直接访问。
     * @return void
     */
    public function boot(): void {
        abort_unless( AdminUserResource::canViewAny(), 403 );
    }
}
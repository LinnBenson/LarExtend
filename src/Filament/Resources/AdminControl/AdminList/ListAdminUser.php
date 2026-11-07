<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

/**
 * 管理员列表页面
 */
class ListAdminUser extends ListRecords {
    protected static string $resource = AdminUserResource::class;
    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            AdminUserResource::getNavigationGroup(),
            AdminUserResource::getNavigationLabel(),
        ];
    }

    protected function getHeaderActions(): array {
        return [CreateAction::make()->icon( Heroicon::OutlinedUserPlus )->url( AdminUserResource::getUrl( 'create' ) )];
    }
}

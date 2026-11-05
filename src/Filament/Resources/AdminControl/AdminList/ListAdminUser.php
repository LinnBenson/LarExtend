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

    protected function getHeaderActions(): array {
        return [CreateAction::make()->icon( Heroicon::OutlinedUserPlus )->url( AdminUserResource::getUrl( 'create' ) )];
    }
}

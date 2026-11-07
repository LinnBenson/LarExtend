<?php

namespace LarExtend\Filament\Resources\SettingCenter\AdminSettings;

use Filament\Actions\Action;
use LarExtend\Filament\Resources\SettingCenter\SystemSettings\EditSystemSettings;

/**
 * 后台专用配置页面
 */
class EditAdminSettings extends EditSystemSettings {
    protected static string $resource = AdminSettingsResource::class;
    /**
     * 获取页面面包屑
     * @return array 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [
            AdminSettingsResource::getNavigationGroup(),
            AdminSettingsResource::getNavigationLabel(),
        ];
    }

    protected function categories(): array {
        return ['admin' => __( 'lar::frame.settings.categories.admin' )];
    }

    protected function getHeaderActions(): array {
        return [Action::make( 'manageSettings' )->label( __( 'lar::SettingCenter.AdminSettings.manage' ) )
            ->icon( 'heroicon-o-adjustments-horizontal' )->url( AdminSettingsResource::getUrl( 'manage' ) )];
    }
}

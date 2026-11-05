<?php

namespace LarExtend\Filament\Resources\SettingCenter\SystemSettings;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;
use LarExtend\Filament\Concerns\HasNavigationLevel;
use LarExtend\Models\Settings;

/**
 * 系统设置资源
 */
class SystemSettingsResource extends Resource {
    use HasNavigationLevel;

    // 关联的模型
    protected static ?string $model = Settings::class;
    // 导航访问等级键名
    protected static string $navigationPermission = 'manage';
    protected static ?string $slug = 'setting-center/system';
    protected static bool $isGloballySearchable = false;
    // 导航分组
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'lar::frame.groups.setting' ); }
    // 导航图标
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;
    // 导航标签
    public static function getNavigationLabel(): string { return __( 'lar::SettingCenter.SystemSettings.title' ); }
    // 模型标签
    public static function getModelLabel(): string { return __( 'lar::SettingCenter.SystemSettings.model' ); }
    // 复数模型标签
    public static function getPluralModelLabel(): string { return __( 'lar::SettingCenter.SystemSettings.title' ); }
    // 导航排序
    protected static ?int $navigationSort = 999;

    public static function getEloquentQuery(): Builder {
        return parent::getEloquentQuery()->whereIn( 'category', Settings::CATEGORIES );
    }

    public static function getPages(): array {
        return [
            'index' => EditSystemSettings::route( '/' ),

        ];
    }
}

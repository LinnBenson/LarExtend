<?php

namespace LarExtend\Filament\Resources\AdminControl\PluginList;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use LarExtend\Filament\Concerns\HasNavigationLevel;
use LarExtend\Filament\Concerns\AdminTool;

/**
 * 插件管理资源
 */
class PluginResource extends Resource {
    use HasNavigationLevel;

    // 导航访问等级键名
    protected static string $navigationPermission = 'manage';
    protected static ?string $slug = 'admin-control/plugins';
    protected static bool $isGloballySearchable = false;
    // 导航分组
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'lar::frame.groups.admin' ); }
    // 导航图标
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;
    // 导航标签
    public static function getNavigationLabel(): string { return __( 'lar::AdminControl.Plugin.title' ); }
    // 模型标签
    public static function getModelLabel(): string { return __( 'lar::AdminControl.Plugin.model' ); }
    // 复数模型标签
    public static function getPluralModelLabel(): string { return __( 'lar::AdminControl.Plugin.title' ); }
    // 导航排序
    protected static ?int $navigationSort = 999;

    public static function canViewAny(): bool {
        return static::hasRequiredNavigationLevel();
    }

    /**
     * 校验插件维护权限
     * @return bool 是否允许查看详情、修改配置和维护插件
     */
    public static function canMaintain(): bool {
        return static::canAccess() && AdminTool::check( 'administrator' );
    }

    public static function getPages(): array {
        return ['index' => ManagePlugins::route( '/' )];
    }
}

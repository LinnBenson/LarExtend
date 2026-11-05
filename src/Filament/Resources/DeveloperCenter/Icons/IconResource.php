<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Icons;

use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use LarExtend\Filament\Concerns\HasNavigationLevel;
use LarExtend\Models\AdminUser;

/**
 * Filament 图标资源
 */
class IconResource extends Resource {
    use HasNavigationLevel;

    // 导航访问等级键名
    protected static string $navigationPermission = 'administrator';
    protected static ?string $slug = 'developer-center/filament-icons';
    protected static bool $isGloballySearchable = false;
    // 导航分组
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'lar::frame.groups.developer' ); }
    // 导航图标
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;
    // 导航标签
    public static function getNavigationLabel(): string { return __( 'lar::DeveloperCenter.Icons.title' ); }
    // 模型标签
    public static function getModelLabel(): string { return __( 'lar::DeveloperCenter.Icons.title' ); }
    // 复数模型标签
    public static function getPluralModelLabel(): string { return __( 'lar::DeveloperCenter.Icons.title' ); }
    // 导航排序
    protected static ?int $navigationSort = 300;

    public static function canViewAny(): bool {
        $user = Filament::auth()->user();
        return $user instanceof AdminUser && $user->canAccessPanel( Filament::getCurrentOrDefaultPanel() )
            && $user->level >= AdminUser::LEVELS['administrator'];
    }

    public static function getPages(): array {
        return ['index' => ListIcons::route( '/' )];
    }
}

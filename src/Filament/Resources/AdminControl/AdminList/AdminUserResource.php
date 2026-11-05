<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use LarExtend\Filament\Concerns\AdminTool;
use LarExtend\Filament\Concerns\HasNavigationLevel;
use LarExtend\Models\AdminUser;

/**
 * 管理员列表资源
 */
class AdminUserResource extends Resource {
    use HasNavigationLevel;

    // 关联的模型
    protected static ?string $model = AdminUser::class;
    // 导航访问等级键名
    protected static string $navigationPermission = 'ordinary';
    protected static ?string $recordTitleAttribute = 'name';

    // 导航分组
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'lar::frame.groups.admin' ); }
    // 导航图标
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;
    // 导航标签
    public static function getNavigationLabel(): string { return __( 'lar::AdminControl.AdminUser.title' ); }
    // 模型标签
    public static function getModelLabel(): string { return __( 'lar::AdminControl.AdminUser.model' ); }
    // 复数模型标签
    public static function getPluralModelLabel(): string { return __( 'lar::AdminControl.AdminUser.title' ); }
    // 导航排序
    protected static ?int $navigationSort = 100;

    public static function form( Schema $schema ): Schema { return AdminUserForm::configure( $schema ); }
    public static function table( Table $table ): Table { return AdminUserTable::configure( $table ); }

    /**
     * 获取全局搜索字段
     * 支持通过管理员 UID、用户名和邮箱进行搜索。
     * @return array<int, string> 全局搜索字段
     */
    public static function getGloballySearchableAttributes(): array {
        return ['id', 'name', 'email'];
    }

    /**
     * 获取全局搜索结果标题
     * @param Model $record 管理员记录
     * @return string 包含等级、UID 和名称的标题
     */
    public static function getGlobalSearchResultTitle( Model $record ): string {
        $levelName = AdminTool::levelName( $record->level );
        return "{$levelName} · {$record->id} · {$record->name}";
    }

    /**
     * 限定列表和路由绑定的数据范围
     * @return Builder 管理员查询
     */
    public static function getEloquentQuery(): Builder {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();
        if ( !$user instanceof AdminUser || !( new AdminUserPolicy() )->viewAny( $user ) ) { return $query->whereRaw( '1 = 0' ); }
        if ( !$user->exceedService() ) { return $query->whereKey( $user->getKey() ); }
        return $query->where( function ( Builder $query ) use ( $user ): void {
            $query->whereKey( $user->getKey() )->orWhere( 'level', '<', $user->level );
        } );
    }

    public static function getPages(): array {
        return [
            'index' => ListAdminUser::route( '/' ),
            'create' => CreateAdminUser::route( '/create' ),
            'edit' => EditAdminUser::route( '/{record}/edit' ),
        ];
    }
}

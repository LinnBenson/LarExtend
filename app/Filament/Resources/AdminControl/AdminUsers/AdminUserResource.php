<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use App\Filament\Resources\AdminControl\AdminUsers\ListAdminUsers;
use App\Models\AdminUser;
use App\Filament\Concerns\HasNavigationLevel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;

/**
 * 管理员列表资源
 * 提供管理员只读查询，按后台等级配置限制访问。
 */
class AdminUserResource extends Resource {
    use HasNavigationLevel;

    // 权限键名
    protected static string $navigationPermission = 'Ordinary';

    // 关联的型类
    protected static ?string $model = AdminUser::class;

    /**
     * 判断列表访问权限
     * @return bool 是否允许访问
     */
    public static function canViewAny(): bool {
        return static::hasRequiredNavigationLevel() && parent::canViewAny();
    }

    /**
     * 配置管理员列表
     * 提供搜索、排序和状态筛选，不展示密码或登录令牌。
     * @param Table $table 表格实例
     * @return Table 配置后的表格
     */
    public static function table( Table $table ): Table {
        return $table
            ->columns( [
                TextColumn::make( 'id' )->label( __( 'admin::AdminControl.AdminUsers.fields.id' ) )->sortable(),
                TextColumn::make( 'name' )->label( __( 'admin::AdminControl.AdminUsers.fields.name' ) )->searchable()->sortable(),
                TextColumn::make( 'email' )->label( __( 'admin::AdminControl.AdminUsers.fields.email' ) )->searchable()->sortable(),
                TextColumn::make( 'status' )->label( __( 'admin::AdminControl.AdminUsers.fields.status' ) )->badge()
                    ->formatStateUsing( static fn ( bool $state ): string => __( $state ? 'admin::AdminControl.AdminUsers.enabled' : 'admin::AdminControl.AdminUsers.disabled' ) )
                    ->color( static fn ( bool $state ): string => $state ? 'success' : 'danger' )->sortable(),
                TextColumn::make( 'level' )->label( __( 'admin::AdminControl.AdminUsers.fields.level' ) )->sortable(),
                TextColumn::make( 'created_at' )->label( __( 'admin::AdminControl.AdminUsers.fields.created_at' ) )->dateTime( 'Y-m-d H:i:s' )->sortable(),
                TextColumn::make( 'updated_at' )->label( __( 'admin::AdminControl.AdminUsers.fields.updated_at' ) )->dateTime( 'Y-m-d H:i:s' )->sortable()
                    ->toggleable( isToggledHiddenByDefault: true ),
            ] )
            ->filters( [
                TernaryFilter::make( 'status' )->label( __( 'admin::AdminControl.AdminUsers.fields.status' ) )
                    ->placeholder( __( 'admin::AdminControl.AdminUsers.all_statuses' ) )
                    ->trueLabel( __( 'admin::AdminControl.AdminUsers.enabled' ) )->falseLabel( __( 'admin::AdminControl.AdminUsers.disabled' ) ),
            ] )
            ->defaultSort( 'id', 'desc' )
            ->paginationPageOptions( [10, 25, 50] )
            ->recordUrl( null )
            ->recordActions( [] )
            ->toolbarActions( [] )
            ->emptyStateHeading( __( 'admin::AdminControl.AdminUsers.empty' ) );
    }

    // 列表页面
    public static function getPages(): array { return ['index' => ListAdminUsers::route( '/' )]; }

    // 导航分组
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'admin::frame.groups.admin' ); }

    // 导航图标
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    // 导航标签
    public static function getNavigationLabel(): string { return __( 'admin::AdminControl.AdminUsers.title' ); }

    // 模型标签
    public static function getModelLabel(): string { return __( 'admin::AdminControl.AdminUsers.model' ); }

    // 复数模型标签
    public static function getPluralModelLabel(): string { return __( 'admin::AdminControl.AdminUsers.title' ); }

    // 导航排序
    protected static ?int $navigationSort = 10;

}
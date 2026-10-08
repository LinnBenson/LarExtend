<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use App\Filament\Resources\AdminControl\AdminUsers\ListAdminUsers;
use App\Models\AdminUser;
use App\Filament\Concerns\HasNavigationLevel;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;

/**
 * 管理员列表资源
 * 提供管理员只读查询，按后台等级配置限制访问。
 */
class AdminUserResource extends Resource {
    use HasNavigationLevel;

    // 关联的型类
    protected static ?string $model = AdminUser::class;

    /**
     * 权限键名
     * @return bool 是否允许访问
     */
    protected static string $navigationPermission = 'ordinary'; // 权限键名
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
        $t = function( $v1, $v2 = [] ) { return __( "admin::AdminControl.AdminUsers.{$v1}", $v2 ); };
        return $table
            ->columns( [
                TextColumn::make( 'id' )->label( $t( 'fields.id' ) )->sortable(),
                TextColumn::make( 'name' )->label( $t( 'fields.name' ) )->searchable()->sortable(),
                TextColumn::make( 'email' )->label( $t( 'fields.email' ) )->searchable()->sortable(),
                ToggleColumn::make( 'status' )->label( $t( 'fields.status' ) )
                    ->onColor( 'success' )->offColor( 'danger' )
                    ->onIcon( 'heroicon-m-check' )->offIcon( 'heroicon-m-x-mark' )
                    ->disabled(function ( AdminUser $record ): bool {
                        $adminUser = auth( 'admin' )->user();
                        return !$adminUser instanceof AdminUser || $adminUser->is( $record ) || Gate::forUser( $adminUser )->denies( 'update', $record );
                    } )
                    ->afterStateUpdated( function ( bool $state, AdminUser $record ): void {
                        $status = $state ? $t( 'enabled' ) : $t( 'disabled' );
                        Notification::make()
                            ->title( $t( 'editStatus.success' ) )
                            ->body( $t( 'editStatus.body', ['name' => $record->name, 'status' => $status] ) )
                            ->success()
                            ->send();
                    }),
                TextColumn::make( 'level' )->label( $t( 'fields.level' ) )->sortable(),
                TextColumn::make( 'created_at' )->label( $t( 'fields.created_at' ) )->dateTime( 'Y-m-d H:i:s' )->sortable(),
                TextColumn::make( 'updated_at' )->label( $t( 'fields.updated_at' ) )->dateTime( 'Y-m-d H:i:s' )->sortable()
                    ->toggleable( isToggledHiddenByDefault: true ),
            ] )
            ->filters( [
                TernaryFilter::make( 'status' )->label( $t( 'fields.status' ) )
                    ->placeholder( $t( 'all_statuses' ) )
                    ->trueLabel( $t( 'enabled' ) )->falseLabel( $t( 'disabled' ) ),
            ] )
            ->defaultSort( 'id', 'desc' )
            ->paginationPageOptions( [10, 25, 50] )
            ->recordUrl( null )
            ->recordActions( [] )
            ->toolbarActions( [] )
            ->emptyStateHeading( $t( 'empty' ) );
    }

    // 页面路由
    public static function getPages(): array {
        return [
            'index' => ListAdminUsers::route( '/' )
        ];
    }

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
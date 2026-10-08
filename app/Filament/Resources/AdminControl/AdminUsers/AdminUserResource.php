<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use App\Models\AdminUser;
use App\Filament\Concerns\HasNavigationLevel;
use App\Filament\Concerns\AdminTool;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use Filament\Notifications\Notification;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\ViewColumn;

/**
 * 管理员列表资源
 * 提供管理员查询与管理，按后台等级配置限制访问。
 */
class AdminUserResource extends Resource {
    use HasNavigationLevel;

    // 关联的型类
    protected static ?string $model = AdminUser::class;

    // 导航访问等级键名
    protected static string $navigationPermission = 'ordinary'; // 权限键名

    /**
     * 配置管理员列表
     * 提供搜索、排序和状态筛选，不展示密码或登录令牌。
     * @param Table $table 表格实例
     * @return Table 配置后的表格
     */
    public static function table( Table $table ): Table {
        $t = function( $v1, $v2 = [] ) { return __( "admin::AdminControl.AdminUsers.{$v1}", $v2 ); };
        // 按等级门槛生成闭开区间，最高等级不设上限。
        $levels = AdminUser::getLevel();
        $levels = is_array( $levels ) ? $levels : [];
        asort( $levels, SORT_NUMERIC );
        $levelNames = array_keys( $levels );
        $levelValues = array_values( $levels );
        $levelOptions = [];
        $levelRanges = [];
        foreach ( $levelNames as $index => $name ) {
            $minimum = $levelValues[$index];
            $maximum = isset( $levelValues[$index + 1] ) ? $levelValues[$index + 1] - 1 : null;
            $levelOptions[$name] = __( "admin::frame.levels.{$name}" );
            $levelRanges[$name] = [$minimum, $maximum];
        }
        return $table
            ->columns( [
                TextColumn::make( 'id' )->label( $t( 'fields.id' ) )->searchable()->sortable(),
                ViewColumn::make( 'name' )->view( 'Filament::AdminControl.AdminUsers.admin-user-name' )->label( $t( 'fields.name' ) )->searchable()->sortable(),
                TextColumn::make( 'email' )->label( $t( 'fields.email' ) )->searchable()->sortable(),
                ToggleColumn::make( 'status' )->label( $t( 'fields.status' ) )
                    ->onColor( 'success' )->offColor( 'danger' )
                    ->onIcon( 'heroicon-m-check' )->offIcon( 'heroicon-m-x-mark' )
                    ->disabled(function ( AdminUser $record ): bool {
                        $adminUser = auth( 'admin' )->user();
                        return !$adminUser instanceof AdminUser || $adminUser->is( $record ) || Gate::forUser( $adminUser )->denies( 'update', $record );
                    } )
                    ->updateStateUsing( function ( bool $state, AdminUser $record ): bool {
                        DB::transaction( function () use ( $state, $record ): void {
                            $record->status = $state;
                            $record->saveOrFail();
                        } );
                        return $state;
                    } )
                    ->afterStateUpdated( function ( bool $state, AdminUser $record ) use ( $t ): void {
                        $status = $state ? $t( 'enabled' ) : $t( 'disabled' );
                        Notification::make()
                            ->title( $t( 'editStatus.success' ) )
                            ->body( $t( 'editStatus.body', ['name' => $record->name, 'status' => $status] ) )
                            ->success()
                            ->send();
                    }),
                TextColumn::make( 'level' )->label( $t( 'fields.level' ) )
                    ->formatStateUsing( fn ( int $state ): string => AdminTool::levelName( $state )."[{$state}]" )
                    ->sortable(),
                TextColumn::make( 'created_at' )->label( $t( 'fields.created_at' ) )->dateTime( 'Y-m-d H:i:s' )->sortable(),
                TextColumn::make( 'updated_at' )->label( $t( 'fields.updated_at' ) )->dateTime( 'Y-m-d H:i:s' )->sortable()
                    ->toggleable( isToggledHiddenByDefault: true ),
            ] )
            ->filters( [
                SelectFilter::make( 'levelRange' )
                    ->label( $t( 'filters.level_range' ) )
                    ->placeholder( $t( 'filters.all_levels' ) )
                    ->options( $levelOptions )
                    ->native( false )
                    ->query( function ( Builder $query, array $data ) use ( $levelRanges ): Builder {
                        $value = $data['value'] ?? null;
                        if ( $value === null || $value === '' ) { return $query; }
                        if ( !is_string( $value ) || !isset( $levelRanges[$value] ) ) {
                            return $query->whereRaw( '1 = 0' );
                        }
                        [$minimum, $maximum] = $levelRanges[$value];
                        $query->where( 'level', '>=', $minimum );
                        if ( $maximum !== null ) { $query->where( 'level', '<=', $maximum ); }
                        return $query;
                    } ),
                TernaryFilter::make( 'status' )->label( $t( 'fields.status' ) )
                    ->placeholder( $t( 'all_statuses' ) )
                    ->trueLabel( $t( 'enabled' ) )->falseLabel( $t( 'disabled' ) ),
            ] )
            ->defaultSort( 'id', 'desc' )
            ->paginationPageOptions( [10, 25, 50] )
            ->recordActions( [
                ActionGroup::make( [
                    EditAction::make()->label( $t( 'actions.edit' ) ),
                    DeleteAction::make()->label( $t( 'actions.delete' ) )->databaseTransaction(),
                ] ),
            ] )
            ->recordActionsColumnLabel( $t( 'actions.title' ) )
            ->toolbarActions( [] )
            ->emptyStateHeading( $t( 'empty' ) );
    }

    /**
     * 配置管理员表单。
     * @param Schema $schema 表单结构
     * @return Schema 表单结构
     */
    public static function form( Schema $schema ): Schema {
        return AdminUserForm::configure( $schema );
    }

    /**
     * 获取可管理的管理员，无管理权限时仅返回自己。
     * @return Builder 管理员查询
     */
    public static function getEloquentQuery(): Builder {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();
        if ( !$user instanceof AdminUser ) { return $query->whereRaw( '1 = 0' ); }
        if ( !$user->canManage() ) { return $query->whereKey( $user->getKey() ); }
        return $query->where( function ( Builder $query ) use ( $user ): void {
            $query->whereKey( $user->getKey() )->orWhere( 'level', '<', $user->level );
        } );
    }

    // 页面路由
    public static function getPages(): array {
        return [
            'index' => ListAdminUsers::route( '/' ),
            'create' => CreateAdminUser::route( '/create' ),
            'edit' => EditAdminUser::route( '/{record}/edit' ),
        ];
    }

    /**
     * 获取全局搜索字段。
     * 支持通过管理员 UID、用户名和邮箱进行搜索。
     * @return array<int, string> 全局搜索字段
     */
    public static function getGloballySearchableAttributes(): array {
        return [ 'id', 'name', 'email' ];
    }
    public static function getGlobalSearchResultTitle( \Illuminate\Database\Eloquent\Model $record ): string {
        $levelName = AdminTool::levelName( $record->level );
        return "{$levelName} · {$record->id} · {$record->name}";
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

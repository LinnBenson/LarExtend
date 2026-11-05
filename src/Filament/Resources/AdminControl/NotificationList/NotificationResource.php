<?php

namespace LarExtend\Filament\Resources\AdminControl\NotificationList;

use BackedEnum;
use UnitEnum;
use Filament\Resources\Resource;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Builder;
use LarExtend\Models\AdminUser;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\HtmlString;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use LarExtend\Filament\Concerns\HasNavigationLevel;
use LarExtend\Models\Notification;

/**
 * 通知信息资源
 */
class NotificationResource extends Resource {
    use HasNavigationLevel;

    // 关联的模型
    protected static ?string $model = Notification::class;
    // 导航访问等级键名
    protected static string $navigationPermission = 'manage';
    protected static ?string $recordTitleAttribute = 'id';
    protected static bool $isGloballySearchable = false;

    // 导航分组
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'lar::frame.groups.admin' ); }
    // 导航图标
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBell;
    // 导航标签
    public static function getNavigationLabel(): string { return __( 'lar::AdminControl.Notification.title' ); }
    // 模型标签
    public static function getModelLabel(): string { return __( 'lar::AdminControl.Notification.model' ); }
    // 复数模型标签
    public static function getPluralModelLabel(): string { return __( 'lar::AdminControl.Notification.title' ); }
    // 导航排序
    protected static ?int $navigationSort = 110;

    /**
     * 获取当前管理员可管理的通知
     * @return Builder 通知查询
     */
    public static function getEloquentQuery(): Builder {
        $query = parent::getEloquentQuery();
        $user = Filament::auth()->user();
        if ( !$user instanceof AdminUser ) { return $query->whereRaw( '1 = 0' ); }
        return NotificationPolicy::scopeVisible( $query, $user );
    }

    public static function table( Table $table ): Table { return NotificationTable::configure( $table ); }

    /**
     * 通知详情
     * 以文本展示通知内容和原始数据，查看不改变接收者的已读状态。
     * @param Schema $schema 详情结构
     * @return Schema 详情结构
     */
    public static function infolist( Schema $schema ): Schema {
        return $schema->columns( 1 )->components([
            Section::make( __( 'lar::AdminControl.Notification.details' ) )
                ->icon( Heroicon::OutlinedInformationCircle )->compact()->columns( ['default' => 1, 'sm' => 2] )->schema([
                    TextEntry::make( 'notifiable_id' )->label( __( 'lar::AdminControl.Notification.recipientId' ) ),
                    TextEntry::make( 'readStatus' )->label( __( 'lar::AdminControl.Notification.readStatus' ) )
                        ->state( fn ( Notification $record ): string => __( $record->read_at ? 'lar::AdminControl.Notification.read' : 'lar::AdminControl.Notification.unread' ) )
                        ->badge()->color( fn ( Notification $record ): string => $record->read_at ? 'success' : 'warning' ),
                    TextEntry::make( 'created_at' )->label( __( 'lar::AdminControl.Notification.createdAt' ) )->dateTime( 'Y-m-d H:i:s' ),
                    TextEntry::make( 'read_at' )->label( __( 'lar::AdminControl.Notification.readAt' ) )->dateTime( 'Y-m-d H:i:s' )->placeholder( '—' ),
                ]),
            Section::make( __( 'lar::AdminControl.Notification.message' ) )
                ->icon( Heroicon::OutlinedEnvelope )->compact()->schema([
                    TextEntry::make( 'data.title' )->hiddenLabel()->weight( 'semibold' )->size( 'lg' ),
                    TextEntry::make( 'data.body' )->hiddenLabel()->placeholder( '—' )
                        ->formatStateUsing( fn ( string $state ): HtmlString => new HtmlString( '<div style="white-space: pre-line; overflow-wrap: anywhere;">'.e( $state ).'</div>' ) ),
                ]),
            Section::make( __( 'lar::AdminControl.Notification.data' ) )
                ->icon( Heroicon::OutlinedCodeBracket )->compact()->collapsible()->collapsed()->schema([
                    TextEntry::make( 'id' )->label( 'ID' )->copyable(),
                    TextEntry::make( 'notifiable_type' )->label( __( 'lar::AdminControl.Notification.recipientType' ) ),
                    TextEntry::make( 'type' )->label( __( 'lar::AdminControl.Notification.type' ) ),
                    TextEntry::make( 'updated_at' )->label( __( 'lar::AdminControl.Notification.updatedAt' ) )->dateTime( 'Y-m-d H:i:s' ),
                    TextEntry::make( 'payload' )->hiddenLabel()->copyable()
                        ->state( fn ( Notification $record ): string => json_encode( $record->data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE ) )
                        ->formatStateUsing( fn ( string $state ): HtmlString => new HtmlString( '<pre style="margin: 0; font-family: monospace; white-space: pre; max-height: 24rem; overflow: auto;">'.e( $state ).'</pre>' ) ),
                ]),
        ]);
    }

    public static function getPages(): array { return ['index' => ListNotifications::route( '/' )]; }
}

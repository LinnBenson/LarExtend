<?php

namespace LarExtend\Filament\Resources\AdminControl\NotificationList;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use LarExtend\Models\Notification;

/**
 * 通知信息表格
 */
class NotificationTable {
    public static function configure( Table $table ): Table {
        return $table->searchPlaceholder( __( 'lar::AdminControl.Notification.search' ) )->columns([
            TextColumn::make( 'id' )->label( 'ID' )->toggleable( isToggledHiddenByDefault: true ),
            TextColumn::make( 'data.title' )->label( __( 'lar::AdminControl.Notification.subject' ) )->searchable( ['data->title'] )->limit( 25 ),
            TextColumn::make( 'data.body' )->label( __( 'lar::AdminControl.Notification.body' ) )->searchable( ['data->body'] )->limit( 50 ),
            TextColumn::make( 'notifiable_type' )->label( __( 'lar::AdminControl.Notification.recipientType' ) )->formatStateUsing( fn ( string $state ): string => class_basename( $state ) )->wrap(),
            TextColumn::make( 'notifiable_id' )->label( __( 'lar::AdminControl.Notification.recipientId' ) )->searchable()->sortable(),
            TextColumn::make( 'read_at' )->label( __( 'lar::AdminControl.Notification.readStatus' ) )->badge()
                ->getStateUsing( fn ( Notification $record ): string => $record->read_at === null ? 'unread' : 'read' )
                ->formatStateUsing( fn ( string $state ): string => __( "lar::AdminControl.Notification.{$state}" ) )
                ->color( fn ( string $state ): string => $state === 'read' ? 'success' : 'warning' )->sortable(),
            TextColumn::make( 'created_at' )->label( __( 'lar::AdminControl.Notification.createdAt' ) )->dateTime( 'Y-m-d H:i:s' )->sortable(),
        ])->filters([
            SelectFilter::make( 'notifiable_type' )->label( __( 'lar::AdminControl.Notification.recipientType' ) )
                ->options( fn (): array => NotificationResource::getEloquentQuery()->distinct()->orderBy( 'notifiable_type' )->pluck( 'notifiable_type' )
                    ->mapWithKeys( fn ( string $type ): array => [$type => class_basename( $type )] )->all() ),
            TernaryFilter::make( 'read_at' )->label( __( 'lar::AdminControl.Notification.readStatus' ) )->nullable()
                ->trueLabel( __( 'lar::AdminControl.Notification.read' ) )->falseLabel( __( 'lar::AdminControl.Notification.unread' ) ),
        ])->defaultSort( 'created_at', 'desc' )->recordActions([
            ActionGroup::make([
                ViewAction::make()->extraModalWindowAttributes( ['class' => 'lar-notification-modal', 'autofocus' => true, 'tabindex' => -1] )->color( 'primary' )->authorize( 'view' )->modalHeading( fn ( Notification $record ): string => __( 'lar::AdminControl.Notification.view', ['title' => $record->data['title'] ?? ''] ) )->modalWidth( Width::ThreeExtraLarge )->stickyModalHeader()->stickyModalFooter(),
                DeleteAction::make()->extraModalWindowAttributes( ['class' => 'lar-notification-modal'] )->recordTitle( fn ( Notification $record ): string => $record->data['title'] ?? __( 'lar::AdminControl.Notification.model' ) )->authorize( 'delete' )->databaseTransaction(),
            ]),
        ]);
    }
}

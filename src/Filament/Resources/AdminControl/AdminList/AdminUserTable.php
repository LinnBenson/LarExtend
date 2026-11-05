<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Fieldset;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\HtmlString;
use LarExtend\Filament\Concerns\AdminTool;
use LarExtend\Models\AdminUser;

/**
 * 管理员列表及操作菜单
 */
class AdminUserTable {
    public static function configure( Table $table ): Table {
        return $table->searchPlaceholder( __( 'lar::AdminControl.AdminUser.search' ) )->columns([
            TextColumn::make( 'id' )->label( 'ID' )->sortable()->searchable(),
            TextColumn::make( 'name' )->label( __( 'lar::AdminControl.AdminUser.name' ) )->searchable()
                ->formatStateUsing( fn ( string $state, AdminUser $record ): HtmlString => new HtmlString(
                    view( 'Filament::AdminControl.AdminList.admin-user-name', [
                        'name' => $state,
                        'avatarUrl' => $record->getFilamentAvatarUrl(),
                    ] )->render()
                ) ),
            TextColumn::make( 'email' )->label( __( 'lar::AdminControl.AdminUser.email' ) )->searchable(),
            TextColumn::make( 'level' )->label( __( 'lar::AdminControl.AdminUser.level' ) )->sortable()
                ->formatStateUsing( fn ( int $state ): string => AdminTool::levelName( $state )." ({$state})" ),
            ToggleColumn::make( 'status' )->label( __( 'lar::AdminControl.AdminUser.status' ) )
                ->disabled( fn ( AdminUser $record ): bool => Gate::denies( 'updateStatus', $record ) )
                ->updateStateUsing( function ( AdminUser $record, bool $state ): bool {
                    $status = AdminUserWriter::updateStatus( $record, $state );
                    Notification::make()->success()->title( __( 'lar::AdminControl.AdminUser.statusUpdated' ) )->send();
                    return $status;
                } ),
            TextColumn::make( 'created_at' )->label( __( 'lar::AdminControl.AdminUser.createdAt' ) )->dateTime( 'Y-m-d H:i:s' )->sortable(),
            TextColumn::make( 'updated_at' )->label( __( 'lar::AdminControl.AdminUser.updatedAt' ) )->dateTime( 'Y-m-d H:i:s' )->sortable()->toggleable( isToggledHiddenByDefault: true ),
        ])->filters([
            SelectFilter::make( 'level' )->label( __( 'lar::AdminControl.AdminUser.type' ) )
                ->options( function (): array {
                    $options = [];
                    foreach ( AdminUser::LEVELS as $name => $minimum ) {
                        if ( $minimum <= Filament::auth()->user()->level ) {
                            $options[$name] = __( "lar::frame.levels.{$name}" );
                        }
                    }
                    return $options;
                } )
                ->query( function ( Builder $query, array $data ): Builder {
                    $name = $data['value'];
                    if ( !isset( AdminUser::LEVELS[$name] ) ) { return $query; }
                    $minimum = AdminUser::LEVELS[$name];
                    $query->where( 'level', '>=', $minimum );
                    foreach ( AdminUser::LEVELS as $boundary ) {
                        if ( $boundary > $minimum ) { return $query->where( 'level', '<', $boundary ); }
                    }
                    return $query;
                } ),
            TernaryFilter::make( 'status' )->label( __( 'lar::AdminControl.AdminUser.statusFilter' ) )
                ->trueLabel( __( 'lar::AdminControl.AdminUser.enabled' ) )
                ->falseLabel( __( 'lar::AdminControl.AdminUser.disabled' ) ),
            Filter::make( 'created_at' )->label( __( 'lar::AdminControl.AdminUser.createdAt' ) )->schema([
                Fieldset::make( __( 'lar::AdminControl.AdminUser.createdAt' ) )->columns( 2 )->schema([
                    DatePicker::make( 'from' )->label( __( 'lar::AdminControl.AdminUser.startDate' ) ),
                    DatePicker::make( 'until' )->label( __( 'lar::AdminControl.AdminUser.endDate' ) )->afterOrEqual( 'from' ),
                ]),
            ])->query( fn ( Builder $query, array $data ): Builder => $query
                ->when( $data['from'], fn ( Builder $query, string $date ): Builder => $query->whereDate( 'created_at', '>=', $date ) )
                ->when( $data['until'], fn ( Builder $query, string $date ): Builder => $query->whereDate( 'created_at', '<=', $date ) )
            )->indicateUsing( function ( array $data ): array {
                $indicators = [];
                if ( filled( $data['from'] ) ) {
                    $indicators[] = __( 'lar::AdminControl.AdminUser.createdFrom' ).': '.$data['from'];
                }
                if ( filled( $data['until'] ) ) {
                    $indicators[] = __( 'lar::AdminControl.AdminUser.createdUntil' ).': '.$data['until'];
                }
                return $indicators;
            } ),
        ])->filtersFormWidth( Width::Large )->defaultSort( 'id', 'desc' )->recordActions([
            ActionGroup::make([
                Action::make( 'sendMessage' )->label( __( 'lar::AdminControl.AdminUser.sendMessage' ) )
                    ->icon( 'heroicon-o-envelope' )->color( 'primary' )->authorize( 'sendMessage' )
                    ->modalHeading( __( 'lar::AdminControl.AdminUser.sendMessage' ) )
                    ->modalSubmitActionLabel( __( 'lar::AdminControl.AdminUser.send' ) )
                    ->modalSubmitAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedPaperAirplane ) )
                    ->modalWidth( Width::TwoExtraLarge )->stickyModalHeader()->stickyModalFooter()
                    ->schema([
                        TextInput::make( 'title' )->prefixIcon( Heroicon::OutlinedPencilSquare )->inlineLabel( false )->label( __( 'lar::AdminControl.AdminUser.messageTitle' ) )
                            ->default( fn (): string => 'From: '.Filament::auth()->user()->name )->required()->maxLength( 191 ),
                        Textarea::make( 'body' )->inlineLabel( false )->label( __( 'lar::AdminControl.AdminUser.messageBody' ) )->required()->maxLength( 10000 )->rows( 5 ),
                    ])
                    ->action( function ( AdminUser $record, array $data ): void {
                        AdminUserWriter::sendMessage( $record, $data );
                        Notification::make()->success()->title( __( 'lar::AdminControl.AdminUser.messageSent' ) )->send();
                    } ),
                EditAction::make()->url( fn ( AdminUser $record ): string => AdminUserResource::getUrl( 'edit', ['record' => $record] ) ),
                DeleteAction::make()->using( fn ( AdminUser $record ): bool => AdminUserWriter::delete( $record ) ),
            ]),
        ]);
    }
}

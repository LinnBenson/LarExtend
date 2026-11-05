<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Facades\Filament;
use LarExtend\Filament\Components\AvatarUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\View;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use LarExtend\Models\AdminUser;

/**
 * 管理员资料表单
 */
class AdminUserForm {
    public static function configure( Schema $schema ): Schema {
        $self = fn ( ?AdminUser $record ): bool => $record !== null && $record->is( Filament::auth()->user() );
        return $schema->columns( ['default' => 1, 'lg' => 2] )->components([
            Section::make( __( 'lar::AdminControl.AdminUser.accountInfo' ) )
                ->icon( Heroicon::OutlinedUserCircle )->columns( 1 )->columnSpan( 1 )->schema([
                    TextInput::make( 'name' )->label( __( 'lar::AdminControl.AdminUser.name' ) )->prefixIcon( Heroicon::OutlinedUser )->required()->maxLength( 255 )->unique( ignoreRecord: true )
                        ->disabled( fn (): bool => !Filament::auth()->user()->exceedService() )->validatedWhenNotDehydrated( false ),
                    TextInput::make( 'email' )->label( __( 'lar::AdminControl.AdminUser.email' ) )->prefixIcon( Heroicon::OutlinedEnvelope )->email()->required()->maxLength( 255 )->unique( ignoreRecord: true ),
                    Toggle::make( 'status' )->label( __( 'lar::AdminControl.AdminUser.statusFilter' ) )->inline( false )
                        ->onIcon( Heroicon::OutlinedCheck )->default( true )->disabled( $self )->validatedWhenNotDehydrated( false ),
                    TextInput::make( 'level' )->label( __( 'lar::AdminControl.AdminUser.level' ) )->prefixIcon( Heroicon::OutlinedShieldCheck )
                        ->numeric()->required()->rules( 'integer|min:1' )
                        ->default( fn (): int => Filament::auth()->user()->exceedService() ? Filament::auth()->user()->level - 1 : AdminUser::LEVELS['ordinary'] )
                        ->disabled( fn ( ?AdminUser $record ): bool => $self( $record ) || !Filament::auth()->user()->exceedService() )->validatedWhenNotDehydrated( false )
                        ->maxValue( fn (): int => Filament::auth()->user()->level - 1 )
                        ->belowContent( fn ( TextInput $component ): ?View => $component->isDisabled() ? null : View::make( 'Filament::AdminControl.AdminList.level-help' )
                            ->viewData( ['levels' => collect( AdminUser::LEVELS )->sort()->all(), 'maximum' => Filament::auth()->user()->level - 1] )
                            ->grow() ),
                    TextInput::make( 'password' )->label( __( 'lar::AdminControl.AdminUser.password' ) )->prefixIcon( Heroicon::OutlinedLockClosed )
                        ->password()->revealable()->afterStateHydrated( fn ( TextInput $component ) => $component->state( '' ) )
                        ->required( fn ( string $operation ): bool => $operation === 'create' )->minLength( 8 )->maxLength( 255 )
                        ->dehydrated( fn ( ?string $state ): bool => filled( $state ) ),
                ]),
            Section::make( __( 'lar::AdminControl.AdminUser.avatarSettings' ) )->icon( Heroicon::OutlinedPhoto )
                ->columnSpan( 1 )->schema([
                    AvatarUpload::make( 'avatar' )->label( __( 'lar::AdminControl.AdminUser.avatar' ) )->hiddenLabel()
                        ->alignCenter()->imageEditor()->disk( 'public' )->directory( 'admin-avatars' )->visibility( 'public' )
                        ->preventFilePathTampering()->maxSize( 2048 ),
                ]),
        ]);
    }
}

<?php

namespace App\Filament\Resources\AdminControl\AdminUsers;

use App\Models\AdminUser;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

/**
 * AdminUserForm
 * 管理员用户表单。
 * @package App\Filament\Resources\AdminControl\AdminUsers
 */
class AdminUserForm {
    /**
     * 配置表单。
     * 配置管理员用户新增和编辑表单字段。
     * @param Schema $schema 表单结构
     * @return Schema 表单结构
     */
    public static function configure( Schema $schema ): Schema {
        return $schema
            ->components( [
                Section::make( __( 'admin::AdminControl.AdminUsers.sections.basic' ) )
                    ->icon( Heroicon::OutlinedUserCircle )
                    ->columns( 1 )
                    ->schema( [
                        TextInput::make( 'name' )
                            ->label( __( 'admin::AdminControl.AdminUsers.fields.name' ) )
                            ->prefixIcon( Heroicon::OutlinedUser )
                            ->required()
                            ->maxLength( 255 )
                            ->unique( ignoreRecord: true )
                            ->disabled( function ( ?AdminUser $record ): bool {
                                $adminUser = Filament::auth()->user();
                                return $adminUser instanceof AdminUser &&
                                    $record?->getKey() === $adminUser->getKey() &&
                                    !$adminUser->canManage();
                            } ),
                        TextInput::make( 'email' )
                            ->label( __( 'admin::AdminControl.AdminUsers.fields.email' ) )
                            ->prefixIcon( Heroicon::OutlinedEnvelope )
                            ->required()
                            ->email()
                            ->maxLength( 255 )
                            ->unique( ignoreRecord: true ),
                        Toggle::make( 'status' )
                            ->label( __( 'admin::AdminControl.AdminUsers.fields.status' ) )
                            ->onIcon( Heroicon::OutlinedCheck )
                            ->offIcon( Heroicon::OutlinedXMark )
                            ->default( true )
                            ->required()
                            ->disabled( fn ( ?AdminUser $record ): bool => $record?->getKey() === Filament::auth()->id() )
                            ->inline( false ),
                        TextInput::make( 'level' )
                            ->label( __( 'admin::AdminControl.AdminUsers.fields.level' ) )
                            ->prefixIcon( Heroicon::OutlinedShieldCheck )
                            ->required()
                            ->numeric()
                            ->minValue( 0 )
                            ->maxValue( function ( ?AdminUser $record ): ?int {
                                if ( $record?->getKey() === Filament::auth()->id() ) { return null; }
                                return max( (int) Filament::auth()->user()?->level - 1, 0 );
                            } )
                            ->validationMessages( [
                                'max' => __( 'admin::AdminControl.AdminUsers.level_max' ),
                            ] )
                            ->disabled( fn ( ?AdminUser $record ): bool => $record?->getKey() === Filament::auth()->id() )
                            ->default( fn (): int => max( (int) Filament::auth()->user()?->level - 1, 0 ) ),
                        TextInput::make( 'password' )
                            ->label( __( 'admin::AdminControl.AdminUsers.fields.password' ) )
                            ->prefixIcon( Heroicon::OutlinedLockClosed )
                            ->password()
                            ->revealable()
                            ->required( fn ( string $operation ): bool => $operation === 'create' )
                            ->dehydrated( fn ( ?string $state ): bool => filled( $state ) )
                            ->maxLength( 255 ),
                    ] ),
                Section::make( __( 'admin::AdminControl.AdminUsers.sections.avatar' ) )
                    ->icon( Heroicon::OutlinedPhoto )
                    ->columns( 1 )
                    ->schema( [
                        FileUpload::make( 'avatar' )
                            ->preventFilePathTampering()
                            ->label( __( 'admin::AdminControl.AdminUsers.fields.avatar' ) )
                            ->hiddenLabel()
                            ->avatar()
                            ->image()
                            ->imageEditor()
                            ->disk( 'public' )
                            ->directory( 'avatars' )
                            ->visibility( 'public' )
                            ->maxSize( 2048 )
                            ->imagePreviewHeight( '160' )
                            ->acceptedFileTypes( ['image/jpeg', 'image/png', 'image/webp'] )
                            ->alignCenter(),
                    ] ),
            ] );
    }
}

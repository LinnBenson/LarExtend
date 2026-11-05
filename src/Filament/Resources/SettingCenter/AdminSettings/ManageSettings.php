<?php

namespace LarExtend\Filament\Resources\SettingCenter\AdminSettings;

use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use LarExtend\Filament\Resources\SettingCenter\SystemSettings\SettingsForm;
use LarExtend\Filament\Resources\SettingCenter\SystemSettings\SettingsFiles;
use LarExtend\Filament\Resources\SettingCenter\SystemSettings\SettingsWriter;
use LarExtend\Models\Settings;

/**
 * 公开配置项管理页面
 */
class ManageSettings extends ListRecords {
    protected static string $resource = AdminSettingsResource::class;

    protected function authorizeAccess(): void { abort_unless( AdminSettingsResource::canAccess(), 403 ); }
    public function getTitle(): string { return __( 'lar::SettingCenter.AdminSettings.manage' ); }
    public function form( Schema $schema ): Schema { return $schema->columns( ['default' => 1, 'md' => 2] )->inlineLabel( false )->components( SettingsForm::definition() ); }

    /**
     * 页面层级导航
     * @return array 面包屑及后台管理入口
     */
    public function getBreadcrumbs(): array {
        return [
            __( 'lar::frame.groups.setting' ),
            AdminSettingsResource::getUrl() => __( 'lar::SettingCenter.AdminSettings.title' ),
            $this->getTitle(),
        ];
    }

    protected function getHeaderActions(): array {
        return [
            Action::make( 'back' )->icon( 'heroicon-o-arrow-left' )->label( __( 'lar::SettingCenter.AdminSettings.back' ) )->url( AdminSettingsResource::getUrl() )->color( 'gray' ),
            CreateAction::make()->icon( 'heroicon-o-plus' )->label( __( 'lar::SettingCenter.AdminSettings.create' ) )->stickyModalHeader()->stickyModalFooter()
                ->using( fn ( array $data ): Settings => SettingsWriter::save( $data ) ),
        ];
    }

    public function table( Table $table ): Table {
        return $table->searchPlaceholder( __( 'lar::SettingCenter.AdminSettings.search' ) )->columns([
            TextColumn::make( 'category' )->label( __( 'lar::SettingCenter.AdminSettings.category' ) )->formatStateUsing( fn ( Settings $record ): string => $record->categoryLabel ),
            TextColumn::make( 'name' )->label( __( 'lar::SettingCenter.AdminSettings.name' ) )->formatStateUsing( fn ( string $state ): string => __( $state ) )->searchable(),
            TextColumn::make( 'key' )->label( __( 'lar::SettingCenter.AdminSettings.key' ) )->searchable(),
            TextColumn::make( 'type' )->label( __( 'lar::SettingCenter.AdminSettings.type' ) )->formatStateUsing( fn ( Settings $record ): string => $record->typeLabel ),
            TextColumn::make( 'index' )->label( __( 'lar::SettingCenter.AdminSettings.index' ) )->sortable(),
            TextColumn::make( 'updated_at' )->label( __( 'lar::SettingCenter.AdminSettings.updatedAt' ) )->dateTime( 'Y-m-d H:i:s' )->sortable(),
        ])->filters([
            SelectFilter::make( 'category' )->label( __( 'lar::SettingCenter.AdminSettings.category' ) )->options( SettingsForm::categories() ),
        ])->defaultSort( 'index' )->recordActions([ActionGroup::make([
            EditAction::make()->stickyModalHeader()->stickyModalFooter()
                ->mutateRecordDataUsing( function ( array $data ): array {
                    $data['value'] = match ( $data['type'] ) {
                        'boolean' => filter_var( $data['value'], FILTER_VALIDATE_BOOLEAN ),
                        'images', 'files' => SettingsFiles::addresses( $data['type'], $data['value'] ),
                        default => $data['value'],
                    };
                    return $data;
                } )->using( fn ( Settings $record, array $data ): Settings => SettingsWriter::save( $data, $record ) ),
            DeleteAction::make()->recordTitle( fn ( Settings $record ): string => __( 'lar::SettingCenter.AdminSettings.model' )." ({$record->key})" )
                ->using( fn ( Settings $record ): bool => SettingsWriter::delete( $record ) ),
        ])]);
    }
}

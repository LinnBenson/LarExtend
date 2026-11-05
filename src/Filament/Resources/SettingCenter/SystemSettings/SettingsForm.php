<?php

namespace LarExtend\Filament\Resources\SettingCenter\SystemSettings;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Illuminate\Validation\Rule;
use LarExtend\Models\Settings;
use LarExtend\Filament\Components\AssetUpload;

/**
 * 设置表单组件
 * 依据配置类型生成输入项，文件值使用可公开访问的相对地址。
 */
class SettingsForm {
    public static function categories(): array {
        return collect( Settings::CATEGORIES )->mapWithKeys( fn ( string $category ): array => [
            $category => __( "lar::frame.settings.categories.{$category}" ),
        ] )->all();
    }

    /**
     * 创建配置值字段
     * @param Settings $record 配置定义
     * @param string $name 表单字段路径
     * @return Field 输入组件
     */
    public static function valueField( Settings $record, string $name ): Field {
        $field = match ( $record->type ) {
            'image', 'images', 'file', 'files' => AssetUpload::make( $name )->assetType( $record->type )->directory( 'settings' ),
            'boolean' => Toggle::make( $name )->inline( false )->rules( 'nullable|boolean' ),
            'number' => TextInput::make( $name )->prefixIcon( Heroicon::OutlinedHashtag )->numeric()->rules( 'nullable|integer' ),
            'decimal' => TextInput::make( $name )->prefixIcon( Heroicon::OutlinedCalculator )->numeric()->step( 'any' ),
            'textarea' => Textarea::make( $name )->rows( 4 ),
            'json' => CodeEditor::make( $name )->language( Language::Json )->wrap()->rules( 'nullable|json' )
                ->formatStateUsing( fn ( ?string $state ): ?string => self::formatJson( $state ) ),
            'url' => TextInput::make( $name )->prefixIcon( Heroicon::OutlinedLink )->url()->rules( 'nullable|url:http,https' ),
            default => TextInput::make( $name )->prefixIcon( Heroicon::OutlinedPencil ),
        };
        if ( $field instanceof AssetUpload ) {
            $field->reorderable( in_array( $record->type, ['images', 'files'], true ) );
            // 描述开头的数量前缀同时用于前端限制和服务端校验。
            if ( $field->isMultiple() && preg_match( '/\A\[([0-9]+)\] /', $record->description ?? '', $matches ) ) {
                $limit = filter_var( ltrim( $matches[1], '0' ), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]] );
                $field->maxFiles( $limit === false ? 5 : $limit );
            }
            $field->preventFilePathTampering( allowFilePathUsing: fn ( string $file ): bool => $record->exists && in_array( $file, SettingsFiles::addresses( $record->getRawOriginal( 'type' ) ?? '', $record->getRawOriginal( 'value' ) ), true ) );
        }
        if ( $record->category === 'admin' && $record->key === 'path' ) {
            $field->rules( ['nullable', 'regex:/^[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*$/'] )->maxLength( 191 );
        }
        return $field->inlineLabel()->label( __( $record->name ?: $record->key ) )
            ->helperText( $record->description ? __( $record->description ) : null );
    }

    /**
     * 格式化 JSON 显示
     * 保留对象结构，空值和无效内容原样返回供编辑修正。
     * @param string|null $value 配置值
     * @return string|null 格式化后的配置值
     */
    public static function formatJson( ?string $value ): ?string {
        if ( $value === null || !json_validate( $value ) ) { return $value; }
        return json_encode( json_decode( $value ), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION );
    }

    /**
     * 配置项管理表单
     * @return array 表单组件
     */
    public static function definition(): array {
        return [
            Select::make( 'category' )->prefixIcon( Heroicon::OutlinedFolder )->label( __( 'lar::SettingCenter.AdminSettings.category' ) )->options( self::categories() )->required()->rules( [Rule::in( Settings::CATEGORIES )] ),
            Select::make( 'type' )->prefixIcon( Heroicon::OutlinedAdjustmentsHorizontal )->label( __( 'lar::SettingCenter.AdminSettings.type' ) )->options( collect( Settings::TYPES )->mapWithKeys(
                fn ( string $type ): array => [$type => __( "lar::frame.settings.types.{$type}" )]
            )->all() )->required()->live()->afterStateUpdated( fn ( Set $set ) => $set( 'value', null ) )->rules( [Rule::in( Settings::TYPES )] ),
            TextInput::make( 'key' )->prefixIcon( Heroicon::OutlinedKey )->label( __( 'lar::SettingCenter.AdminSettings.key' ) )->required()->maxLength( 191 )
                ->rules( ['regex:/^[a-zA-Z][a-zA-Z0-9]*$/'] )->unique( table: 'settings', column: 'key', ignoreRecord: true ),
            TextInput::make( 'index' )->prefixIcon( Heroicon::OutlinedBarsArrowDown )->label( __( 'lar::SettingCenter.AdminSettings.index' ) )->numeric()->rules( 'required|integer|min:0|max:4294967295' )->default( 0 ),
            TextInput::make( 'name' )->prefixIcon( Heroicon::OutlinedLanguage )->columnSpanFull()->label( __( 'lar::SettingCenter.AdminSettings.name' ) )->maxLength( 191 ),
            Textarea::make( 'description' )->columnSpanFull()->label( __( 'lar::SettingCenter.AdminSettings.description' ) )->rows( 2 )->live( onBlur: true ),
            Group::make()->columnSpanFull()->schema( function ( Get $get, ?Settings $record ): array {
                $record = $record ? clone $record : new Settings();
                $record->type = $get( 'type' ) ?? 'text';
                $record->description = $get( 'description' );
                $record->name = 'lar::SettingCenter.AdminSettings.value';
                return [self::valueField( $record, 'value' )->inlineLabel( false )];
            } ),
        ];
    }
}

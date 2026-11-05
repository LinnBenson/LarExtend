<?php

namespace LarExtend\Filament\Resources\SettingCenter\SystemSettings;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use LarExtend\Models\AdminUser;
use LarExtend\Models\Settings;
use LarExtend\Filament\Concerns\UploadedFiles;

/**
 * 设置写入服务
 * 在事务中重新读取权限和配置定义，防止请求篡改分类或配置类型。
 */
class SettingsWriter {
    /**
     * 保存分类配置值
     * @param string $category 配置类别
     * @param array $values 以记录 ID 为索引的值
     * @return void
     */
    public static function saveValues( string $category, array $values ): void {
        DB::transaction( function () use ( $category, $values ): void {
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $probe = new Settings();
            $probe->category = $category;
            Gate::forUser( $user )->authorize( 'updateValue', $probe );
            $records = Settings::query()->where( 'category', $category )->lockForUpdate()->get()->keyBy( 'id' );
            foreach ( $values as $id => $value ) {
                abort_unless( $records->has( $id ), 403 );
                $record = $records->get( $id );
                Gate::forUser( $user )->authorize( 'updateValue', $record );
                $record->value = self::validateValue( $record, $value, "data.{$id}" );
                SettingsFiles::releaseAfterCommit( $record );
                $record->save();
                UploadedFiles::retainAfterCommit( SettingsFiles::addresses( $record->type, $record->value ), $record->getConnection() );
            }
        } );
        request()->attributes->remove( 'lar.settings' );
    }

    /**
     * 新建或修改公开配置项
     * @param array $data 配置项表单
     * @param Settings|null $record 编辑目标
     * @return Settings 已保存的配置项
     */
    public static function save( array $data, ?Settings $record = null ): Settings {
        $result = DB::transaction( function () use ( $data, $record ): Settings {
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $target = $record ? Settings::query()->lockForUpdate()->findOrFail( $record->getKey() ) : new Settings();
            Gate::forUser( $user )->authorize( $record ? 'update' : 'create', $record ? $target : Settings::class );
            $validated = Validator::make( $data, [
                'category' => ['required', Rule::in( Settings::CATEGORIES )],
                'type' => ['required', Rule::in( Settings::TYPES )],
                'key' => ['required', 'string', 'max:191', 'regex:/^[a-zA-Z][a-zA-Z0-9]*$/', Rule::unique( 'settings', 'key' )->ignore( $target->getKey() )],
                'name' => 'nullable|string|max:191',
                'description' => 'nullable|string|max:65535',
                'index' => 'required|integer|min:0|max:4294967295',
            ] )->validate();
            $target->forceFill( $validated );
            $target->value = self::validateValue( $target, $data['value'] ?? null, 'value' );
            SettingsFiles::releaseAfterCommit( $target );
            $target->save();
            UploadedFiles::retainAfterCommit( SettingsFiles::addresses( $target->type, $target->value ), $target->getConnection() );
            return $target;
        } );
        request()->attributes->remove( 'lar.settings' );
        return $result;
    }

    /**
     * 删除公开配置项
     * @param Settings $record 配置项
     * @return bool 是否成功
     */
    public static function delete( Settings $record ): bool {
        $deleted = DB::transaction( function () use ( $record ): bool {
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $target = Settings::query()->lockForUpdate()->findOrFail( $record->getKey() );
            Gate::forUser( $user )->authorize( 'delete', $target );
            SettingsFiles::releaseAfterCommit( $target, deleting: true );
            return (bool) $target->delete();
        } );
        request()->attributes->remove( 'lar.settings' );
        return $deleted;
    }

    /**
     * 验证并序列化配置值
     * @param Settings $record 配置定义
     * @param string|int|float|bool|array|null $value 表单值
     * @param string $field 错误提示字段
     * @return string|null 数据库存储值
     */
    private static function validateValue( Settings $record, string|int|float|bool|array|null $value, string $field ): ?string {
        if ( in_array( $record->type, ['images', 'files'], true ) ) {
            $value ??= [];
            try {
                Validator::make( ['value' => $value], [
                    'value' => 'present|array|list',
                    'value.*' => 'required|string|max:2048|distinct:strict',
                ] )->validate();
            }catch ( \Illuminate\Validation\ValidationException $exception ) {
                throw \Illuminate\Validation\ValidationException::withMessages( [$field => $exception->validator->errors()->all()] );
            }
            return json_encode( $value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR );
        }
        if ( $value === '' && in_array( $record->type, ['number', 'decimal', 'json'], true ) ) { $value = null; }
        $rules = match ( $record->type ) {
            'boolean' => 'nullable|boolean',
            'number' => 'nullable|integer',
            'decimal' => 'nullable|numeric',
            'json' => 'nullable|json',
            'url' => 'nullable|url:http,https|max:2048',
            default => 'nullable|string',
        };
        if ( $record->category === 'admin' && $record->key === 'path' ) {
            $rules = ['nullable', 'string', 'max:191', 'regex:/^[a-zA-Z0-9_-]+(?:\/[a-zA-Z0-9_-]+)*$/'];
        }
        try {
            Validator::make( ['value' => $value], ['value' => $rules] )->validate();
        }catch ( \Illuminate\Validation\ValidationException $exception ) {
            throw \Illuminate\Validation\ValidationException::withMessages( [$field => $exception->errors()['value']] );
        }
        if ( $value === null ) { return null; }
        return $record->type === 'boolean' ? ( $value ? '1' : '0' ) : (string) $value;
    }
}

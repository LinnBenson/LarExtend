<?php

namespace LarExtend\Filament\Resources\SettingCenter\SystemSettings;

use Filament\Notifications\Notification;
use LarExtend\Models\Settings;
use LarExtend\Filament\Concerns\UploadedFiles;

/**
 * 设置附件清理
 * 配置提交成功后清理旧附件，仅处理上传磁盘 settings 目录中的文件。
 */
class SettingsFiles {
    /**
     * 安排旧附件删除
     * 从数据库原值获取地址，仅删除当前配置移除的附件。
     * @param Settings $record 待保存或删除的配置
     * @param bool $deleting 是否删除配置项
     * @return void
     */
    public static function releaseAfterCommit( Settings $record, bool $deleting = false ): void {
        $oldAddresses = self::addresses( $record->getRawOriginal( 'type' ) ?? '', $record->getRawOriginal( 'value' ) );
        $newAddresses = $deleting ? [] : self::addresses( $record->type, $record->value );
        foreach ( array_diff( $oldAddresses, $newAddresses ) as $address ) {
            UploadedFiles::releaseAfterCommit(
                $address, 'larAdmin', 'settings', $record->getConnection(),
                fn ( string $address ): bool => false,
                fn () => Notification::make()->warning()->title( __( 'lar::frame.upload.deleteFailed' ) )->send(),
            );
        }
    }

    /**
     * 读取配置附件地址
     * 将单文件值和多文件 JSON 列表统一为地址数组，忽略无效的历史值。
     * @param string $type 配置类型
     * @param string|null $value 数据库存储值
     * @return list<string> 附件地址
     */
    public static function addresses( string $type, ?string $value ): array {
        if ( $value === null || $value === '' ) { return []; }
        if ( in_array( $type, ['image', 'file'], true ) ) { return [$value]; }
        if ( !in_array( $type, ['images', 'files'], true ) ) { return []; }
        $addresses = json_decode( $value, true );
        if ( !is_array( $addresses ) || !array_is_list( $addresses ) ) { return []; }
        return array_values( array_unique( array_filter( $addresses, fn ( mixed $address ): bool => is_string( $address ) && $address !== '' ) ) );
    }
}

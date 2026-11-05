<?php

namespace LarExtend\Filament\Resources\AdminControl\AdminList;

use Filament\Facades\Filament;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use LarExtend\Models\AdminUser;
use LarExtend\Models\Settings;
use LarExtend\Filament\Concerns\UploadedFiles;
use Illuminate\Support\Facades\Storage;
use LarExtend\Services\SendNotification;

/**
 * 管理员写入服务
 * 在事务内锁定操作者和目标，依据最新等级重新授权。
 */
class AdminUserWriter {
    /**
     * 保存管理员资料
     * @param array $data 已验证的表单数据
     * @param AdminUser|null $record 编辑目标，新增时为空
     * @return AdminUser 保存后的管理员
     */
    public static function save( array $data, ?AdminUser $record = null ): AdminUser {
        return DB::transaction( function () use ( $data, $record ): AdminUser {
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $target = $record ? AdminUser::query()->lockForUpdate()->findOrFail( $record->getKey() ) : new AdminUser();
            Gate::forUser( $user )->authorize( $record ? 'update' : 'create', $record ? $target : AdminUser::class );
            if ( !$user->exceedService() ) { unset( $data['name'] ); }
            if ( $record && $user->is( $target ) ) {
                unset( $data['status'], $data['level'] );
            }else {
                $rules = [
                    'data.status' => 'required|boolean',
                ];
                if ( !$user->exceedService() ) {
                    unset( $data['level'] );
                }else {
                    $maximum = $user->level - 1;
                    $rules['data.level'] = "required|integer|min:1|max:{$maximum}";
                }
                Validator::make( ['data' => $data], $rules )->validate();
            }
            if ( empty( $data['password'] ) ) {
                unset( $data['password'] );
            }
            $oldAvatar = $target->avatar;
            $target->fill( $data )->save();
            if ( $record && $target->wasChanged( 'password' ) ) {
                SendNotification::make()
                    ->title( 'System Notification' )
                    ->body( "Your password has been modified by an administrator ({$user->id})." )
                    ->persistent()->sendToDatabase( $target );
            }
            UploadedFiles::retainAfterCommit( [$target->avatar], $target->getConnection() );
            if ( $oldAvatar && $oldAvatar !== $target->avatar ) { self::releaseAvatar( $target, $oldAvatar ); }
            return $target;
        } );
    }

    /**
     * 更新管理员状态
     * 单独保存状态，避免触发资料编辑中的等级变更规则。
     * @param AdminUser $record 操作目标
     * @param bool $status 启用状态
     * @return bool 保存后的状态
     */
    public static function updateStatus( AdminUser $record, bool $status ): bool {
        return DB::transaction( function () use ( $record, $status ): bool {
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $target = AdminUser::query()->lockForUpdate()->findOrFail( $record->getKey() );
            Gate::forUser( $user )->authorize( 'updateStatus', $target );
            $target->status = $status;
            $target->save();
            return $target->status;
        } );
    }

    /**
     * 发送管理员消息
     * 提交时重新读取消息开关及双方等级，授权与通知写入在同一事务中完成。
     * @param AdminUser $record 接收管理员
     * @param array $data 消息标题和内容
     * @return void
     */
    public static function sendMessage( AdminUser $record, array $data ): void {
        DB::transaction( function () use ( $record, $data ): void {
            $switch = Settings::query()->where( 'category', 'admin' )->where( 'key', 'send' )->sharedLock()->first();
            abort_unless( $switch && filter_var( $switch->value, FILTER_VALIDATE_BOOLEAN ), 403 );
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $target = AdminUser::query()->lockForUpdate()->findOrFail( $record->getKey() );
            request()->attributes->remove( 'lar.settings' );
            Gate::forUser( $user )->authorize( 'sendMessage', $target );
            $validated = Validator::make( $data, [
                'title' => 'required|string|max:191',
                'body' => 'required|string|max:10000',
            ], [], [
                'title' => __( 'lar::AdminControl.AdminUser.messageTitle' ),
                'body' => __( 'lar::AdminControl.AdminUser.messageBody' ),
            ] )->validate();
            SendNotification::make()->title( $validated['title'] )->body( $validated['body'] )
                ->persistent()->sendToDatabase( $target );
        } );
    }

    /**
     * 删除管理员
     * @param AdminUser $record 删除目标
     * @return bool 是否删除成功
     */
    public static function delete( AdminUser $record ): bool {
        return DB::transaction( function () use ( $record ): bool {
            $user = AdminUser::query()->lockForUpdate()->findOrFail( Filament::auth()->id() );
            $target = AdminUser::query()->lockForUpdate()->findOrFail( $record->getKey() );
            Gate::forUser( $user )->authorize( 'delete', $target );
            $avatar = $target->avatar;
            $deleted = (bool) $target->delete();
            if ( $deleted && $avatar ) { self::releaseAvatar( $target, $avatar ); }
            return $deleted;
        } );
    }

    /**
     * 提交后删除管理员原有头像
     * @param AdminUser $record 管理员记录
     * @param string $path 数据库中的旧头像路径
     * @return void
     */
    private static function releaseAvatar( AdminUser $record, string $path ): void {
        UploadedFiles::releaseAfterCommit( Storage::disk( 'public' )->url( $path ), 'public', 'admin-avatars', $record->getConnection(), fn ( string $address ): bool => false );
    }

}

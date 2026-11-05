<?php

namespace LarExtend\Services;

use Filament\Notifications\Events\DatabaseNotificationsSent;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

/**
 * 数据库通知发送服务
 * 复用 Filament 通知构建接口，同步入库并在事务提交后发送可选刷新事件。
 */
class SendNotification extends Notification {
    /**
     * 同步发送数据库通知
     * 接收者需使用 Notifiable；批量接收者使用同一连接，以保证写入的原子性。
     * @param Model|Authenticatable|Collection|array $users 接收者模型或集合
     * @param bool $isEventDispatched 是否在提交后广播刷新事件
     * @return static 当前通知
     */
    public function sendToDatabase( Model|Authenticatable|Collection|array $users, bool $isEventDispatched = false ): static {
        $users = $users instanceof Collection ? $users->all() : ( is_array( $users ) ? $users : [$users] );
        if ( !$users ) { return $this; }
        $connection = null;
        foreach ( $users as $user ) {
            if ( !$user instanceof Model ) { throw new InvalidArgumentException( 'Database notification recipients must be Eloquent models.' ); }
            $connection ??= $user->getConnection();
            if ( $user->getConnection() !== $connection ) { throw new InvalidArgumentException( 'Database notification recipients must use the same connection.' ); }
        }
        $connection->transaction( function () use ( $users, $isEventDispatched, $connection ): void {
            foreach ( $users as $user ) {
                $user->notifyNow( $this->toDatabase() );
            }
            if ( $isEventDispatched ) {
                $connection->afterCommit( function () use ( $users ): void {
                    foreach ( $users as $user ) { DatabaseNotificationsSent::dispatch( $user ); }
                } );
            }
        } );
        return $this;
    }

    /**
     * 生成数据库通知内容
     * 保留配置的显示时长，通知记录的 UUID 由 Laravel 通知渠道生成。
     * @return array 数据库通知载荷
     */
    public function getDatabaseMessage(): array {
        $data = parent::getDatabaseMessage();
        $data['duration'] = $this->getDuration();
        return $data;
    }
}

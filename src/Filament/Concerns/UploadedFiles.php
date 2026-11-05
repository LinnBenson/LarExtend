<?php

namespace LarExtend\Filament\Concerns;

use Closure;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

/**
 * 后台上传文件清理服务
 * 仅清理指定磁盘目录的直接子文件，在事务提交后检查业务引用。
 */
class UploadedFiles {
    /**
     * 登记本次请求新文件
     * 未被数据库事务确认的文件在请求结束时回收，包含部分写入失败的文件。
     * @param string $diskName 磁盘名称
     * @param string $path 新生成的磁盘相对路径
     * @param string $value 写入数据库的地址或路径
     * @return void
     */
    public static function track( string $diskName, string $path, string $value ): void {
        $request = request();
        if ( !$request->attributes->get( 'lar.pendingUploadsRegistered', false ) ) {
            $request->attributes->set( 'lar.pendingUploadsRegistered', true );
            app()->terminating( fn () => self::cleanPending( $request ) );
        }
        $pending = $request->attributes->get( 'lar.pendingUploads', [] );
        $pending[$diskName.':'.$path] = [$diskName, $path, $value];
        $request->attributes->set( 'lar.pendingUploads', $pending );
    }

    /**
     * 数据库提交后保留新文件
     * @param array $values 已写入记录的文件地址或路径
     * @param Connection $connection 业务连接
     * @return void
     */
    public static function retainAfterCommit( array $values, Connection $connection ): void {
        $request = request();
        $connection->afterCommit( function () use ( $request, $values ): void {
            $pending = $request->attributes->get( 'lar.pendingUploads', [] );
            foreach ( $pending as $key => $entry ) {
                if ( in_array( $entry[2], $values, true ) ) { unset( $pending[$key] ); }
            }
            $request->attributes->set( 'lar.pendingUploads', $pending );
        } );
    }

    /**
     * 清理请求中未保存的新文件
     * @param \Illuminate\Http\Request $request 上传请求
     * @return void
     */
    public static function cleanPending( \Illuminate\Http\Request $request ): void {
        foreach ( $request->attributes->get( 'lar.pendingUploads', [] ) as [$diskName, $path] ) {
            try {
                $disk = Storage::disk( $diskName );
                if ( !$disk->delete( $path ) || $disk->exists( $path ) ) { throw new RuntimeException( 'Pending upload cleanup failed.' ); }
            }catch ( Throwable $exception ) {
                Log::warning( '[UploadedFiles::cleanPending] Upload cleanup failed.', ['disk' => $diskName, 'exception' => $exception::class] );
            }
        }
        $request->attributes->set( 'lar.pendingUploads', [] );
    }

    /**
     * 安排旧文件清理
     * 地址应来自数据库原值；业务负责检查新值和旧值是否不同。
     * @param string $address 已保存的公开地址
     * @param string $diskName 存储磁盘名称
     * @param string $directory 允许清理的目录
     * @param Connection $connection 业务写入使用的数据库连接
     * @param Closure $isReferenced 提交后判断地址是否仍被引用
     * @param Closure|null $onFailure 删除失败时的通知回调
     * @return void
     */
    public static function releaseAfterCommit( string $address, string $diskName, string $directory, Connection $connection, Closure $isReferenced, ?Closure $onFailure = null ): void {
        $disk = Storage::disk( $diskName );
        $directory = trim( $directory, '/' );
        if ( !$directory || preg_match( '~(?:^|/)\\.\\.?(?:/|$)|[\\\\\\x00]~', $directory ) ) { throw new \InvalidArgumentException( 'Invalid upload directory.' ); }
        $prefix = rtrim( $disk->url( $directory ), '/' ).'/';
        if ( !str_starts_with( $address, $prefix ) ) { return; }
        $filename = substr( $address, strlen( $prefix ) );
        if ( !preg_match( '/^[a-zA-Z0-9][a-zA-Z0-9._-]*$/D', $filename ) ) { return; }
        $connection->afterCommit( function () use ( $disk, $directory, $address, $filename, $isReferenced, $onFailure ): void {
            try {
                if ( $isReferenced( $address ) ) { return; }
                if ( !$disk->delete( "{$directory}/{$filename}" ) || $disk->exists( "{$directory}/{$filename}" ) ) { throw new RuntimeException( 'Upload deletion failed.' ); }
            }catch ( Throwable $exception ) {
                Log::warning( 'Upload cleanup failed', ['exception' => $exception::class] );
                if ( $onFailure ) { $onFailure(); }
            }
        } );
    }
}

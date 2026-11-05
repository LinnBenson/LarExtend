<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Logs;

use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Finder\Finder;

/**
 * 日志文件管理
 * 所有文件操作均限定在日志目录内，读取内容限制为末尾 200 行和 1 MB。
 */
class LogFiles {
    public function __construct( private ?string $directory = null ) {}

    /**
     * 检查目录内日志是否可读取
     * @param string $path 相对日志路径
     * @return bool 是否存在可读日志
     */
    public function exists( string $path ): bool {
        try {
            return is_readable( $this->resolve( $path ) );
        }catch ( RuntimeException ) {
            return false;
        }
    }

    /**
     * 获取日志文件列表
     * @return array<int, array{path: string, name: string, size: string, modified: string}> 文件信息
     */
    public function all(): array {
        $root = realpath( $this->directory ?? storage_path( 'logs' ) );
        if ( $root === false ) { return []; }
        $files = [];
        foreach ( Finder::create()->files()->name( '*.log' )->in( $root )->sortByModifiedTime()->reverseSorting() as $file ) {
            if ( $file->isLink() || !str_starts_with( $file->getRealPath() ?: '', $root.DIRECTORY_SEPARATOR ) ) { continue; }
            $files[] = [
                'path' => $file->getRelativePathname(),
                'name' => $file->getRelativePathname(),
                'size' => number_format( $file->getSize() / 1024, 2 ).' KB',
                'modified' => date( 'Y-m-d H:i:s', $file->getMTime() ),
            ];
        }
        return $files;
    }

    /**
     * 读取日志末尾内容
     * 按块从末尾读取，避免大日志占满内存。
     * @param string $path 相对日志路径
     * @return string 日志内容
     */
    public function tail( string $path ): string {
        $handle = fopen( $this->resolve( $path ), 'rb' );
        if ( $handle === false ) { throw new RuntimeException( 'Unable to open log file.' ); }
        try {
            if ( !flock( $handle, LOCK_SH ) || fseek( $handle, 0, SEEK_END ) !== 0 ) {
                throw new RuntimeException( 'Unable to read log file.' );
            }
            $position = ftell( $handle );
            if ( $position === false ) { throw new RuntimeException( 'Unable to locate log file.' ); }
            $buffer = '';
            while ( $position > 0 && strlen( $buffer ) < 1048576 && substr_count( $buffer, "\n" ) <= 200 ) {
                $length = min( 8192, $position, 1048576 - strlen( $buffer ) );
                $position -= $length;
                if ( fseek( $handle, $position ) !== 0 ) { throw new RuntimeException( 'Unable to seek log file.' ); }
                $chunk = fread( $handle, $length );
                if ( $chunk === false ) { throw new RuntimeException( 'Unable to read log file.' ); }
                $buffer = $chunk.$buffer;
            }
            $lines = explode( "\n", rtrim( $buffer, "\r\n" ) );
            return mb_convert_encoding( implode( "\n", array_slice( $lines, -200 ) ), 'UTF-8', 'UTF-8' );
        }finally {
            fclose( $handle );
        }
    }

    /**
     * 删除指定日志
     * @param string $path 相对日志路径
     * @return void
     */
    public function delete( string $path ): void {
        if ( !File::delete( $this->resolve( $path ) ) ) { throw new RuntimeException( 'Unable to delete log file.' ); }
    }

    /**
     * 解析并校验日志路径
     * @param string $path 相对日志路径
     * @return string 目录内的真实文件路径
     */
    private function resolve( string $path ): string {
        $directory = $this->directory ?? storage_path( 'logs' );
        $root = realpath( $directory );
        $candidate = rtrim( $directory, '/\\' ).DIRECTORY_SEPARATOR.$path;
        $real = realpath( $candidate );
        if ( $root === false || $real === false || is_link( $candidate ) || !is_file( $real )
            || !str_starts_with( $real, $root.DIRECTORY_SEPARATOR ) || pathinfo( $real, PATHINFO_EXTENSION ) !== 'log' ) {
            throw new RuntimeException( 'Invalid log file.' );
        }
        return $real;
    }
}

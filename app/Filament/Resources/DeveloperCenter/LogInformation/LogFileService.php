<?php

namespace App\Filament\Resources\DeveloperCenter\LogInformation;

use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * 日志文件服务
 * 限定 storage/logs 文件访问范围，提供文件列表和有界尾部读取。
 */
class LogFileService {
    /**
     * 获取日志文件列表。
     * 递归返回 storage/logs 目录及其子目录中的普通日志文件。
     * @return array<int, array{name: string, path: string, size: string, modified_at: string}> 日志文件列表
     */
    public function getLogFiles(): array {
        $directory = storage_path( 'logs' );
        if ( ! File::isDirectory( $directory ) ) { return []; }
        $files = [];
        foreach ( File::allFiles( $directory ) as $file ) {
            $relativePath = str_replace( '\\', '/', $file->getRelativePathname() );
            $pathParts = explode( '/', $relativePath );
            $hasHiddenPart = collect( $pathParts )->contains(
                fn ( string $part ): bool => str_starts_with( $part, '.' )
            );
            if ( $hasHiddenPart || $this->resolveLogPath( $relativePath ) === null ) { continue; }
            $files[] = [
                'name' => $relativePath,
                'path' => $relativePath,
                'size' => $this->formatFileSize( $file->getSize() ),
                'modified_at' => date( 'Y.m.d H:i:s', $file->getMTime() ),
                'timestamp' => $file->getMTime(),
            ];
        }
        usort( $files, fn ( array $left, array $right ): int => $right['timestamp'] <=> $left['timestamp'] );
        return array_map( function ( array $file ): array {
            unset( $file['timestamp'] );
            return $file;
        }, $files );
    }

    /**
     * 解析日志文件路径。
     * 只允许访问 storage/logs 目录内的普通文件。
     * @param string $fileName 日志文件名
     * @return string|null 安全日志路径
     */
    public function resolveLogPath( string $fileName ): ?string {
        $fileName = str_replace( '\\', '/', trim( $fileName ) );
        if ( $fileName === '' || str_contains( $fileName, "\0" ) || str_starts_with( $fileName, '/' ) ) { return null; }
        $pathParts = explode( '/', $fileName );
        foreach ( $pathParts as $part ) {
            if ( $part === '' || str_starts_with( $part, '.' ) ) { return null; }
        }
        $directory = realpath( storage_path( 'logs' ) );
        if ( $directory === false ) { return null; }
        $candidate = $directory;
        foreach ( $pathParts as $part ) {
            $candidate .= "/{$part}";
            if ( is_link( $candidate ) ) { return null; }
        }
        $path = realpath( $candidate );
        if ( $path === false || ! is_file( $path ) ) { return null; }
        if ( ! str_starts_with( $path, "{$directory}/" ) ) { return null; }
        return $path;
    }

    /**
     * 读取日志文件最后 200 行。
     * 从文件末尾分块读取，并限制最大读取量避免异常长行占用过多内存。
     * @param string $path 日志文件路径
     * @return string 日志内容
     */
    public function readLastLines( string $path ): string {
        $maxLines = 200;
        $maxBytes = 2 * 1024 * 1024;
        $blockSize = 8192;
        $fileSize = filesize( $path );
        if ( $fileSize === false ) { throw new RuntimeException( '无法获取日志文件大小。' ); }
        $handle = fopen( $path, 'rb' );
        if ( $handle === false ) { throw new RuntimeException( '无法打开日志文件。' ); }
        $position = $fileSize;
        $content = '';
        try {
            while ( $position > 0 && substr_count( $content, "\n" ) <= $maxLines && strlen( $content ) < $maxBytes ) {
                $readBytes = min( $blockSize, $position );
                $position -= $readBytes;
                if ( fseek( $handle, $position ) !== 0 ) { throw new RuntimeException( '无法定位日志文件。' ); }
                $block = fread( $handle, $readBytes );
                if ( $block === false ) { throw new RuntimeException( '无法读取日志文件。' ); }
                $content = "{$block}{$content}";
            }
        }finally {
            fclose( $handle );
        }
        $content = $this->sanitizeUtf8( $content );
        $content = rtrim( $content, "\r\n" );
        if ( $content === '' ) { return __( 'admin::DeveloperCenter.LogInformation.empty_content' ); }
        $lines = preg_split( '/\R/', $content );
        if ( $lines === false ) { throw new RuntimeException( '无法解析日志内容。' ); }
        $content = implode( "\n", array_slice( $lines, -$maxLines ) );
        if ( $position > 0 && count( $lines ) < $maxLines ) {
            return __( 'admin::DeveloperCenter.LogInformation.truncated' )."\n\n{$content}";
        }
        return $content;
    }

    /**
     * 清理日志内容中的非法 UTF-8 字节。
     * 从文件尾部分块读取时可能截断多字节字符，必须在交给 Livewire 序列化前修复。
     * @param string $content 原始日志内容
     * @return string 可安全进行 JSON 序列化的 UTF-8 内容
     */
    private function sanitizeUtf8( string $content ): string {
        if ( mb_check_encoding( $content, 'UTF-8' ) ) { return $content; }
        if ( function_exists( 'mb_scrub' ) ) { return mb_scrub( $content, 'UTF-8' ); }
        $sanitized = iconv( 'UTF-8', 'UTF-8//IGNORE', $content );
        return $sanitized === false ? '' : $sanitized;
    }

    /**
     * 格式化文件大小。
     * 将字节数转换为易读格式。
     * @param int $bytes 文件字节数
     * @return string 文件大小
     */
    private function formatFileSize( int $bytes ): string {
        if ( $bytes < 1024 ) { return "{$bytes} B"; }
        if ( $bytes < 1024 * 1024 ) { return number_format( $bytes / 1024, 2 ) . ' KB'; }
        if ( $bytes < 1024 * 1024 * 1024 ) { return number_format( $bytes / 1024 / 1024, 2 ) . ' MB'; }
        return number_format( $bytes / 1024 / 1024 / 1024, 2 ) . ' GB';
    }

}

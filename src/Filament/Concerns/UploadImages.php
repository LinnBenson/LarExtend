<?php

namespace LarExtend\Filament\Concerns;

use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * 上传图片净化
 * 使用指定解码器检查尺寸，移除元数据及附加内容，GIF 可保留动画。
 */
class UploadImages {
    /**
     * 重新编码图片内容
     * @param TemporaryUploadedFile $file 临时上传图片
     * @param string $format 白名单中的 Imagick 解码器
     * @param int|null $targetSize 输出最长边，null 保留原尺寸
     * @param bool $preserveGif 是否保留 GIF 动画
     * @return string WebP 或 GIF 文件内容
     */
    public static function encode( TemporaryUploadedFile $file, string $format, ?int $targetSize = null, bool $preserveGif = false ): string {
        $image = null;
        $limits = [];
        try {
            // 限制解码缓存，避免大尺寸或异常图片耗尽服务器资源。
            foreach ( [\Imagick::RESOURCETYPE_MEMORY => 67108864, \Imagick::RESOURCETYPE_MAP => 134217728, \Imagick::RESOURCETYPE_DISK => 0, \Imagick::RESOURCETYPE_THREAD => 1] as $type => $limit ) {
                $limits[$type] = \Imagick::getResourceLimit( $type );
                \Imagick::setResourceLimit( $type, min( $limits[$type], $limit ) );
            }
            $preserveGif = $preserveGif && $format === 'GIF';
            $source = $format.':'.$file->getRealPath().( $preserveGif ? '' : '[0]' );
            $image = new \Imagick();
            // 在完整解码前检查帧与画布，解码后再次确认实际尺寸。
            foreach ( ['pingImage', 'readImage'] as $operation ) {
                $image->clear();
                $image->{$operation}( $source );
                if ( $image->getNumberImages() > 100 ) { throw new \RuntimeException( 'Image frame count exceeds limits.' ); }
                $pixels = 0;
                foreach ( $image as $frame ) {
                    $page = $frame->getImagePage();
                    $width = max( $frame->getImageWidth(), $page['width'], $page['x'] + $frame->getImageWidth() );
                    $height = max( $frame->getImageHeight(), $page['height'], $page['y'] + $frame->getImageHeight() );
                    $pixels += $width * $height;
                    if ( $width < 1 || $height < 1 || $width > 4096 || $height > 4096 || $pixels > 40000000 ) {
                        throw new \RuntimeException( 'Image dimensions or total pixels exceed limits.' );
                    }
                }
            }
            $image->setFirstIterator();
            if ( $preserveGif ) {
                // 保留帧偏移、播放时长、处置方式和循环次数，避免破坏增量帧动画。
                $iterations = $image->getImageIterations();
                foreach ( $image as $frame ) {
                    $frame->stripImage();
                    $frame->setImageFormat( 'GIF' );
                }
                $image->setFirstIterator();
                $image->setImageIterations( $iterations );
                $content = $image->getImagesBlob();
            }else {
                $image->autoOrient();
                if ( $targetSize !== null ) { $image->thumbnailImage( $targetSize, $targetSize, true, true ); }
                $image->setImagePage( 0, 0, 0, 0 );
                $image->stripImage();
                $image->setImageFormat( 'WEBP' );
                $image->setImageCompressionQuality( 85 );
                $content = $image->getImageBlob();
            }
            if ( $content === '' ) { throw new \RuntimeException( 'Image encoding failed.' ); }
            return $content;
        }finally {
            $image?->clear();
            foreach ( $limits as $type => $limit ) { \Imagick::setResourceLimit( $type, $limit ); }
        }
    }
}

<?php

namespace LarExtend\Filament\Components;

use Filament\Forms\Components\FileUpload;
use LarExtend\Filament\Concerns\UploadImages;
use LarExtend\Filament\Concerns\UploadedFiles;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * 头像上传组件
 * 校验原始格式和尺寸，仅保存重新编码后的静态 WebP 图片。
 */
class AvatarUpload extends FileUpload {
    private const FORMATS = [
        'jpg' => ['JPEG', ['image/jpeg']],
        'jpeg' => ['JPEG', ['image/jpeg']],
        'png' => ['PNG', ['image/png']],
        'webp' => ['WEBP', ['image/webp']],
    ];

    protected function setUp(): void {
        parent::setUp();
        $this->avatar()->acceptedFileTypes( array_values( array_unique( array_merge( ...array_column( self::FORMATS, 1 ) ) ) ) )
            ->rules( 'extensions:'.implode( ',', array_keys( self::FORMATS ) ) )
            ->preventFilePathTampering()->maxSize( 2048 );
    }

    /**
     * 校验并保存头像
     * 强制使用白名单解码器，解码前检查尺寸，只读取首帧并清除元数据。
     * @param TemporaryUploadedFile $file 临时上传文件
     * @return string 保存后的磁盘相对路径
     */
    public function saveUploadedFile( TemporaryUploadedFile $file ): string {
        try {
            $format = self::FORMATS[strtolower( $file->getClientOriginalExtension() )] ?? null;
            if ( !$format || $file->getSize() > 2097152 || !in_array( $file->getMimeType(), $format[1], true ) ) {
                throw new \RuntimeException( 'Invalid avatar format or size.' );
            }
            $content = UploadImages::encode( $file, $format[0], 500 );
            $path = trim( $this->getDirectory().'/'.Str::uuid().'.webp', '/' );
            UploadedFiles::track( $this->getDiskName(), $path, $path );
            if ( !$this->getDisk()->put( $path, $content, ['visibility' => $this->getVisibility()] ) ) {
                throw new \RuntimeException( 'Avatar storage failed.' );
            }
            return $path;
        }catch ( \Throwable $exception ) {
            // 记录处理失败类别，不记录上传内容和文件路径。
            Log::warning( '[AvatarUpload::saveUploadedFile] Avatar processing failed.', ['exception' => $exception::class] );
            throw ValidationException::withMessages( [$this->getStatePath() => __( 'lar::frame.upload.invalidAvatar' )] );
        }
    }
}

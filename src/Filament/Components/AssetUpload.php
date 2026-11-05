<?php

namespace LarExtend\Filament\Components;

use Filament\Forms\Components\FileUpload;
use LarExtend\Filament\Concerns\UploadImages;
use LarExtend\Filament\Concerns\UploadedFiles;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Js;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Symfony\Component\Mime\MimeTypes;

/**
 * 后台资源上传组件
 * 使用后台上传配置，保留已有公开地址，提供内联预览、移除和地址复制。
 */
class AssetUpload extends FileUpload {
    protected string $assetKind = 'file';
    protected bool $hasExplicitUploadTypes = false;
    protected array $uploadExtensions = [];
    protected function setUp(): void {
        parent::setUp();
        $this->disk( 'larAdmin' )->directory( config( 'lar.admin.upload.directory', 'uploads' ) )
            ->visibility( config( 'lar.admin.upload.disk.visibility', 'public' ) )
            ->fetchFileInformation( false )->preventFilePathTampering()
            ->openable()->previewable( fn (): bool => !$this->isMultiple() || $this->isImageAsset() )
            ->imagePreviewHeight( null )
            ->extraAttributes( fn (): array => $this->isMultiple() ? ['class' => 'lar-asset-upload-multiple'] : [], merge: true )
            ->panelLayout( fn (): ?string => $this->isMultiple() ? ( $this->isImageAsset() ? 'grid' : null ) : 'compact' )
            ->maxSize( config( 'lar.admin.upload.max_size', 10240 ) )
            ->maxFiles( 5 );
        $this->extraAlpineAttributes( fn (): array => [
            'x-on:filepond:addfile' => e( 'const item = $event.detail.file; if (item && !item.fileSize && Object.values(fileKeyIndex).some(file => file && file.url === item.source && file.size === null)) { const label = $el.querySelector(`[id="filepond--item-${item.id}"] .filepond--file-info-sub`); if (label) label.textContent = '.Js::from( __( 'lar::frame.upload.unknownSize' ) ).'; }' ),
        ], merge: true );
        $this->assetType( 'file' );
        $this->rules( fn (): array => ['extensions:'.implode( ',', $this->uploadExtensions )] );
    }

    /**
     * 设置资源展示类型
     * 未指定上传分组时，图片使用扩展图片组，文件使用配置的默认分组。
     * @param string $type image、images、file 或 files
     * @return static 当前组件
     */
    public function assetType( string $type ): static {
        if ( !in_array( $type, ['image', 'images', 'file', 'files'], true ) ) { throw new \InvalidArgumentException( 'Unsupported asset type.' ); }
        $this->multiple( in_array( $type, ['images', 'files'], true ) );
        $this->assetKind = in_array( $type, ['image', 'images'], true ) ? 'image' : 'file';
        if ( !$this->hasExplicitUploadTypes ) {
            $this->uploadTypes( $this->isImageAsset() ? ['image_extended'] : config( 'lar.admin.upload.file_groups', ['image_extended', 'document', 'audio', 'video', 'font', 'archive', 'data'] ) );
            $this->hasExplicitUploadTypes = false;
        }
        return $this;
    }

    /**
     * 指定上传白名单分组
     * 同时校验文件扩展名和实际 MIME 类型。
     * @param array $groups upload_types 中的分组名称
     * @return static 当前组件
     */
    public function uploadTypes( array $groups ): static {
        $configuredGroups = config( 'lar.files.upload_types', [] );
        $extensions = [];
        foreach ( $groups as $group ) {
            if ( !isset( $configuredGroups[$group] ) ) { throw new \InvalidArgumentException( "Unknown upload group: {$group}" ); }
            $extensions = array_merge( $extensions, $configuredGroups[$group] );
        }
        $extensions = array_values( array_unique( $extensions ) );
        if ( !$extensions ) { throw new \InvalidArgumentException( 'Upload groups must not be empty.' ); }
        $this->hasExplicitUploadTypes = true;
        $mimeTypes = [];
        foreach ( $extensions as $extension ) {
            $mimeTypes = array_merge( $mimeTypes, $this->getUploadMimeTypes( $extension ) );
        }
        $this->acceptedFileTypes( array_values( array_unique( $mimeTypes ) ) );
        $this->uploadExtensions = $extensions;
        return $this;
    }

    /**
     * 获取扩展名允许的上传 MIME
     * 显式映射优先于通用类型库，避免同名扩展被解释为无关格式。
     * @param string $extension 文件扩展名
     * @return array MIME 白名单
     */
    protected function getUploadMimeTypes( string $extension ): array {
        $override = config( "lar.files.upload_mime_types.{$extension}" );
        if ( $override !== null ) { return $override; }
        $types = MimeTypes::getDefault()->getMimeTypes( $extension );
        $configured = config( "lar.files.mime_types.{$extension}" );
        if ( $configured ) { $types[] = explode( ';', $configured )[0]; }
        return array_values( array_unique( $types ) );
    }

    public function isImageAsset(): bool { return $this->assetKind === 'image'; }

    /**
     * 保存上传文件并返回公开地址
     * @param TemporaryUploadedFile $file 临时上传文件
     * @return string|null 公开地址
     */
    public function saveUploadedFile( TemporaryUploadedFile $file ): ?string {
        $stream = null;
        try {
            $extension = strtolower( $file->getClientOriginalExtension() );
            $mime = $file->getMimeType();
            $types = $this->getUploadMimeTypes( $extension );
            if ( !in_array( $extension, $this->uploadExtensions, true ) || !in_array( $mime, $types, true ) || ( $this->getMaxSize() !== null && $file->getSize() > $this->getMaxSize() * 1024 ) ) {
                throw new \RuntimeException( 'Invalid upload type or size.' );
            }
            // Livewire 将空内容识别为二进制 MIME，仅对真正的空 TXT 放行。
            if ( $extension === 'txt' && in_array( $mime, ['application/x-empty', 'application/octet-stream'], true ) && $file->getSize() !== 0 ) {
                throw new \RuntimeException( 'Non-empty binary content is not plain text.' );
            }
            // 文本 MIME 不能证明结构化数据有效，保存前验证实际内容。
            if ( in_array( $extension, ['json', 'webmanifest'], true ) ) {
                $text = $file->get();
                if ( str_starts_with( $text, "\xEF\xBB\xBF" ) ) { $text = substr( $text, 3 ); }
                if ( !json_validate( $text ) ) { throw new \RuntimeException( 'Invalid JSON content.' ); }
            }
            // 白名单解码器限制图片解释方式，禁止将未知类型交由自动探测。
            $formats = ['jpg' => 'JPEG', 'jpeg' => 'JPEG', 'jfif' => 'JPEG', 'png' => 'PNG', 'apng' => 'PNG', 'gif' => 'GIF', 'webp' => 'WEBP', 'avif' => 'AVIF', 'bmp' => 'BMP', 'tif' => 'TIFF', 'tiff' => 'TIFF', 'ico' => 'ICO', 'cur' => 'ICO', 'heic' => 'HEIC', 'heif' => 'HEIF', 'jxl' => 'JXL'];
            if ( $this->isImageAsset() || str_starts_with( $mime, 'image/' ) ) {
                if ( !isset( $formats[$extension] ) ) { throw new \RuntimeException( 'Unsupported image decoder.' ); }
                $content = UploadImages::encode( $file, $formats[$extension], preserveGif: $extension === 'gif' );
                $extension = $extension === 'gif' ? 'gif' : 'webp';
            }else {
                $stream = $file->readStream();
                if ( !is_resource( $stream ) ) { throw new \RuntimeException( 'Unable to read upload.' ); }
                $content = $stream;
            }
            $path = trim( $this->getDirectory().'/'.Str::uuid().'.'.$extension, '/' );
            $url = $this->getDisk()->url( $path );
            UploadedFiles::track( $this->getDiskName(), $path, $url );
            if ( !$this->getDisk()->put( $path, $content, ['visibility' => $this->getVisibility()] ) ) { throw new \RuntimeException( 'Upload storage failed.' ); }
            return $url;
        }catch ( \Throwable $exception ) {
            Log::warning( '[AssetUpload::saveUploadedFile] Upload processing failed.', ['exception' => $exception::class] );
            throw ValidationException::withMessages( [$this->getStatePath() => __( 'lar::frame.upload.invalidAsset' )] );
        }finally {
            if ( is_resource( $stream ) ) { fclose( $stream ); }
        }
    }

    /**
     * 获取已有资源的预览信息
     * 兼容网站静态资源地址，不要求已有文件位于上传磁盘。
     * @param string $file 已保存的地址
     * @param string|array|null $storedFileNames 原始文件名
     * @return array|null 预览信息
     */
    public function getUploadedFile( string $file, string|array|null $storedFileNames ): ?array {
        if ( !preg_match( '~^(?:/(?!/)|https?://)~i', $file ) ) { return null; }
        $path = parse_url( $file, PHP_URL_PATH ) ?: '';
        $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );
        $mime = config( "lar.files.mime_types.{$extension}" );
        $size = null;
        $disk = $this->getDisk();
        $prefix = rtrim( $disk->url( '' ), '/' ).'/';
        // 仅从当前上传磁盘读取大小，外部地址不发起远程请求。
        if ( str_starts_with( $file, $prefix ) ) {
            $relativePath = rawurldecode( substr( $file, strlen( $prefix ) ) );
            if ( !preg_match( '~(?:^|/)\\.\\.?(?:/|$)|[\\\\\\x00?#]~', $relativePath ) ) {
                try {
                    if ( $disk->exists( $relativePath ) ) { $size = $disk->size( $relativePath ); }
                }catch ( \League\Flysystem\FilesystemException ) {
                    $size = null;
                }
            }
        }
        return [
            'name' => basename( $path ),
            'size' => $size,
            'type' => $mime ? explode( ';', $mime )[0] : ( MimeTypes::getDefault()->getMimeTypes( $extension )[0] ?? null ),
            'url' => $file,
        ];
    }

    /**
     * 渲染内联资源预览和操作按钮
     * @return string 组件 HTML
     */
    public function toEmbeddedHtml(): string {
        if ( $this->isMultiple() ) { return parent::toEmbeddedHtml(); }
        return $this->wrapEmbeddedHtml( view( 'Filament::components.asset-upload', ['field' => $this] )->render(), labelTag: 'div' );
    }
}

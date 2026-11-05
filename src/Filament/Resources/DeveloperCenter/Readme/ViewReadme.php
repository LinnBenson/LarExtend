<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Readme;

use Filament\Resources\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * 项目 README 浏览页面
 * 固定读取项目根目录文件，禁止通过请求指定其它文件。
 */
class ViewReadme extends Page {
    protected static string $resource = ReadmeResource::class;
    protected string $view = 'Filament::DeveloperCenter.Readme.readme';

    public function mount(): void {
        abort_unless( ReadmeResource::canAccess(), 403 );
    }

    public function getTitle(): string {
        return __( 'lar::DeveloperCenter.Readme.title' );
    }

    /**
     * 读取项目说明原文
     * @return array 文件元信息和 Markdown 原文
     */
    protected function getViewData(): array {
        abort_unless( ReadmeResource::canAccess(), 403 );
        $path = base_path( 'README.md' );
        $modified = null;
        $content = '';
        $error = false;
        try {
            $content = File::get( $path );
            $modified = Carbon::createFromTimestamp( File::lastModified( $path ), config( 'app.timezone' ) )->format( 'Y-m-d H:i:s' );
        }catch ( Throwable ) {
            $error = true;
        }
        return compact( 'path', 'modified', 'content', 'error' );
    }
}

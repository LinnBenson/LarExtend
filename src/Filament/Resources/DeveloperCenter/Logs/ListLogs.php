<?php

namespace LarExtend\Filament\Resources\DeveloperCenter\Logs;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\Locked;
use Throwable;

/**
 * 日志浏览页面
 */
class ListLogs extends Page {
    protected static string $resource = LogResource::class;
    protected string $view = 'Filament::DeveloperCenter.Logs.logs';

    #[Locked]
    public ?string $selectedPath = null;

    #[Locked]
    public int $previewVersion = 0;

    public function mount(): void {
        abort_unless( LogResource::canAccess(), 403 );
    }

    public function getTitle(): string {
        return __( 'lar::DeveloperCenter.Logs.title' );
    }

    /**
     * 选择要查看的日志
     * @param string $path 相对日志路径
     * @return void
     */
    public function selectLog( string $path ): void {
        abort_unless( LogResource::canAccess(), 403 );
        $this->selectedPath = $path;
        $this->previewVersion++;
    }

    /**
     * 获取日志列表和选中文件内容
     * @return array 页面数据
     */
    protected function getViewData(): array {
        abort_unless( LogResource::canAccess(), 403 );
        $files = [];
        $content = '';
        $error = false;
        try {
            $logs = new LogFiles();
            $files = $logs->all();
            if ( $this->selectedPath === null ) { $this->selectedPath = $files[0]['path'] ?? null; }
            if ( $this->selectedPath !== null ) { $content = $logs->tail( $this->selectedPath ); }
        }catch ( Throwable ) {
            $error = true;
        }
        return compact( 'files', 'content', 'error' );
    }

    /**
     * 删除日志确认操作
     * @return Action 删除操作
     */
    public function deleteLogAction(): Action {
        return Action::make( 'deleteLog' )->label( __( 'lar::DeveloperCenter.Logs.delete' ) )
            ->icon( Heroicon::OutlinedTrash )->iconButton()->color( 'danger' )
            ->requiresConfirmation()->modalHeading( __( 'lar::DeveloperCenter.Logs.deleteHeading' ) )
            ->modalDescription( __( 'lar::DeveloperCenter.Logs.deleteDescription' ) )
            ->stickyModalHeader()->stickyModalFooter()
            ->action( function ( array $arguments ): void {
                abort_unless( LogResource::canAccess(), 403 );
                try {
                    ( new LogFiles() )->delete( $arguments['path'] );
                    if ( $this->selectedPath === $arguments['path'] ) { $this->selectedPath = null; }
                    Notification::make()->success()->title( __( 'lar::DeveloperCenter.Logs.deleted' ) )->send();
                }catch ( Throwable ) {
                    Notification::make()->danger()->title( __( 'lar::DeveloperCenter.Logs.deleteFailed' ) )->send();
                }
            } );
    }
}

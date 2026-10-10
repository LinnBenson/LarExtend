<?php

namespace App\Filament\Resources\DeveloperCenter\LogInformation;

use App\Filament\Concerns\HasNavigationLevel;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\File;
use Livewire\Attributes\Locked;
use RuntimeException;
use Throwable;
use UnitEnum;

/**
 * LogInformation
 * 开发者中心日志信息页面。
 * @package App\Filament\Resources\DeveloperCenter\LogInformation
 */
class LogInformation extends Page {
    use HasNavigationLevel;

    protected static string $navigationPermission = 'administrator';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $slug = 'developer-center/logs';

    protected static ?int $navigationSort = 10;

    protected string $view = 'Filament::DeveloperCenter.LogInformation.log-information';

    #[Locked]
    public ?string $selectedLog = null;

    #[Locked]
    public ?string $logContent = null;

    /**
     * 初始化日志页面。
     * 默认打开最近修改的日志文件。
     * @return void
     */
    public function mount(): void {
        $files = $this->getLogFiles();
        if ( $files === [] ) { return; }
        $this->viewLog( $files[0]['path'] );
    }

    /**
     * 获取日志文件列表。
     * 目录读取失败时显示通知，避免页面直接报错。
     * @return array<int, array{name: string, path: string, size: string, modified_at: string}> 日志文件列表
     */
    public function getLogFiles(): array {
        abort_unless( static::canAccess(), 403 );
        try {
            return app( LogFileService::class )->getLogFiles();
        }catch ( Throwable ) {
            $this->notifyFailure( __( 'admin::DeveloperCenter.LogInformation.list_failed' ) );
            return [];
        }
    }

    /**
     * 校验页面权限。
     * 首次访问和后续 Livewire 请求均校验管理员状态与等级。
     * @return void
     */
    public function boot(): void {
        abort_unless( static::canAccess(), 403 );
    }

    /**
     * 刷新日志内容。
     * 重新读取当前日志；尚未选择文件时打开最新日志。
     * @return void
     */
    public function refreshLogs(): void {
        abort_unless( static::canAccess(), 403 );
        if ( $this->selectedLog !== null ) {
            $this->viewLog( $this->selectedLog );
            return;
        }
        $this->mount();
    }

    /**
     * 页面头部操作。
     * @return array<Action> 操作列表
     */
    protected function getHeaderActions(): array {
        return [Action::make( 'refreshLogs' )
            ->label( __( 'admin::DeveloperCenter.LogInformation.refresh' ) )
            ->icon( Heroicon::OutlinedArrowPath )
            ->action( fn () => $this->refreshLogs() )];
    }

    /**
     * 查看日志文件。
     * 读取指定日志文件最后 200 行，最多读取 2MB 内容。
     * @param string $fileName 日志文件名
     * @return void
     */
    public function viewLog( string $fileName ): void {
        abort_unless( static::canAccess(), 403 );
        $path = app( LogFileService::class )->resolveLogPath( $fileName );
        if ( $path === null ) {
            $this->selectedLog = null;
            $this->logContent = null;
            $this->notifyFailure( __( 'admin::DeveloperCenter.LogInformation.invalid_file' ) );
            return;
        }
        try {
            $this->selectedLog = str_replace( '\\', '/', $fileName );
            $this->logContent = app( LogFileService::class )->readLastLines( $path );
            $this->dispatch( 'log-content-updated' );
        }catch ( Throwable ) {
            $this->selectedLog = null;
            $this->logContent = null;
            $this->notifyFailure( __( 'admin::DeveloperCenter.LogInformation.read_failed' ) );
        }
    }

    /**
     * 删除日志文件。
     * 删除 storage/logs 目录内指定的日志文件。
     * @param string $fileName 日志文件名
     * @return void
     */
    public function deleteLog( string $fileName ): void {
        abort_unless( static::canAccess(), 403 );
        $path = app( LogFileService::class )->resolveLogPath( $fileName );
        if ( $path === null ) {
            $this->notifyFailure( __( 'admin::DeveloperCenter.LogInformation.invalid_file' ) );
            return;
        }
        try {
            if ( ! File::delete( $path ) ) { throw new RuntimeException( '日志文件删除失败。' ); }
            if ( $this->selectedLog === str_replace( '\\', '/', $fileName ) ) {
                $this->selectedLog = null;
                $this->logContent = null;
                $files = $this->getLogFiles();
                if ( $files !== [] ) { $this->viewLog( $files[0]['path'] ); }
            }
            Notification::make()
                ->title( __( 'admin::DeveloperCenter.LogInformation.deleted' ) )
                ->success()
                ->send();
        }catch ( Throwable ) {
            $this->notifyFailure( __( 'admin::DeveloperCenter.LogInformation.delete_failed' ) );
        }
    }

    /**
     * 删除日志操作。
     * 使用 Filament 确认弹窗并在确认后删除指定日志文件。
     * @return Action 删除日志操作
     */
    public function deleteLogAction(): Action {
        return Action::make( 'deleteLog' )
            ->requiresConfirmation()
            ->modalIcon( Heroicon::OutlinedTrash )
            ->modalHeading( fn ( array $arguments ): string => __( 'admin::DeveloperCenter.LogInformation.confirm_heading', ['file' => (string) ( $arguments['fileName'] ?? '' )] ) )
            ->modalDescription( __( 'admin::DeveloperCenter.LogInformation.confirm_description' ) )
            ->modalSubmitActionLabel( __( 'admin::DeveloperCenter.LogInformation.confirm_delete' ) )
            ->modalCancelActionLabel( __( 'admin::DeveloperCenter.LogInformation.cancel' ) )
            ->color( 'danger' )
            ->action( function ( array $arguments ): void {
                $this->deleteLog( (string) ( $arguments['fileName'] ?? '' ) );
            } );
    }

    /**
     * 获取页面面包屑。
     * 返回开发者中心日志页面层级。
     * @return array<string> 面包屑列表
     */
    public function getBreadcrumbs(): array {
        return [__( 'admin::frame.groups.developer' ), __( 'admin::DeveloperCenter.LogInformation.title' )];
    }

    /**
     * 发送失败通知。
     * 显示日志文件操作失败信息。
     * @param string $message 失败信息
     * @return void
     */
    private function notifyFailure( string $message ): void {
        Notification::make()
            ->title( $message )
            ->danger()
            ->send();
    }

    /**
     * 页面信息
     */
    public static function getNavigationLabel(): string { return __( 'admin::DeveloperCenter.LogInformation.title' ); }
    public function getTitle(): string { return __( 'admin::DeveloperCenter.LogInformation.title' ); }
    public static function getNavigationGroup(): string|UnitEnum|null { return __( 'admin::frame.groups.developer' ); }
}

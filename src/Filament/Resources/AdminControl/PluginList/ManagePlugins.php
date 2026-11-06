<?php

namespace LarExtend\Filament\Resources\AdminControl\PluginList;

use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Alignment;
use Filament\Support\Icons\Heroicon;
use LarExtend\Filament\Resources\DeveloperCenter\Logs\LogFiles;
use LarExtend\Services\PluginPackService;
use LarExtend\Services\PluginLoadService;
use LarExtend\Services\PluginPermissionService;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * 插件管理页面
 * 通过现有安装服务处理链接与临时上传的插件包。
 */
class ManagePlugins extends Page {
    protected static string $resource = PluginResource::class;
    protected string $view = 'Filament::AdminControl.PluginList.plugins';
    public string $search = '';
    #[\Livewire\Attributes\Locked]
    public ?string $permissionPluginId = null;
    #[\Livewire\Attributes\Locked]
    public array $permissionStates = [];

    /**
     * 修改插件配置
     * 读取当前合并配置，并通过插件配置接口保存差异。
     * @return Action 配置编辑弹窗操作
     */
    public function editPluginConfigAction(): Action {
        return Action::make( 'editPluginConfig' )
            ->label( __( 'lar::AdminControl.Plugin.editConfig' ) )
            ->icon( Heroicon::OutlinedAdjustmentsHorizontal )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->modalHeading( fn ( array $arguments ): string => __( 'lar::AdminControl.Plugin.configHeading', ['name' => $this->getMaintenancePlugin( $arguments )['package']['name'] ?? ''] ) )
            ->modalWidth( \Filament\Support\Enums\Width::FourExtraLarge )
            ->stickyModalHeader()->stickyModalFooter()
            ->modalFooterActionsAlignment( Alignment::End )
            ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.saveConfig' ) )
            ->modalSubmitAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedCheck ) )
            ->modalCancelAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedXMark ) )
            ->schema( [
                CodeEditor::make( 'configJson' )
                    ->label( __( 'lar::AdminControl.Plugin.configJson' ) )
                    ->language( Language::Json )->wrap()
                    ->required()
                    ->rules( 'string|json' )
                    ->rules( [fn (): \Closure => function ( string $attribute, $value, \Closure $fail ): void {
                        if ( is_string( $value ) && json_validate( $value ) && !is_array( json_decode( $value, true ) ) ) {
                            $fail( __( 'lar::AdminControl.Plugin.configInvalid' ) );
                        }
                    }] ),
            ] )
            ->fillForm( function ( array $arguments, Action $action ): array {
                $entry = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $entry['package'] ), 404 );
                try {
                    $config = plugin( $entry['id'], true )?->config();
                    if ( is_array( $config ) ) {
                        return ['configJson' => json_encode( $config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRESERVE_ZERO_FRACTION | JSON_THROW_ON_ERROR )];
                    }
                }catch ( \Throwable ) {
                }
                Notification::make()->title( __( 'lar::AdminControl.Plugin.configReadFailed' ) )->danger()->send();
                $action->cancel();
                return [];
            } )
            ->action( function ( array $arguments, array $data, Action $action ): void {
                $entry = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $entry['package'] ), 404 );
                $saved = false;
                try {
                    $config = json_decode( $data['configJson'], true, 512, JSON_THROW_ON_ERROR );
                    $saved = is_array( $config ) && plugin( $entry['id'], true )?->saveConfig( $config ) === true;
                }catch ( \Throwable ) {
                }
                if ( !$saved ) {
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.configSaveFailed' ) )->danger()->send();
                    $action->halt();
                    return;
                }
                Notification::make()->title( __( 'lar::AdminControl.Plugin.configSaved' ) )->success()->send();
            } );
    }

    /**
     * 查看插件日志
     * 根据已校验插件的 ID 读取日志末尾内容。
     * @return Action 日志弹窗操作
     */
    public function pluginLogsAction(): Action {
        return Action::make( 'pluginLogs' )
            ->label( __( 'lar::AdminControl.Plugin.logs' ) )
            ->icon( Heroicon::OutlinedDocumentText )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->modalHeading( fn ( array $arguments ): string => $this->getMaintenancePlugin( $arguments )['package']['name'] ?? __( 'lar::AdminControl.Plugin.logs' ) )
            ->modalWidth( \Filament\Support\Enums\Width::FourExtraLarge )
            ->stickyModalHeader()->stickyModalFooter()
            ->modalSubmitAction( false )
            ->modalFooterActionsAlignment( Alignment::End )
            ->modalCancelActionLabel( __( 'lar::AdminControl.Plugin.close' ) )
            ->modalCancelAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedXMark ) )
            ->modalContent( function ( array $arguments ): \Illuminate\Contracts\View\View {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ), 404 );
                $content = '';
                $error = false;
                $filename = "{$plugin['id']}.log";
                try {
                    $logs = new LogFiles( PluginLoadService::normalizePath( config( 'lar.plugin.path.log' ) ) );
                    $content = $logs->tail( $filename );
                }catch ( \Throwable ) {
                    $error = true;
                }
                return view( 'Filament::AdminControl.PluginList.plugin-logs', compact( 'filename', 'content', 'error' ) );
            } );
    }

    public function mount(): void {
        abort_unless( PluginResource::canAccess(), 403 );
    }

    public function getTitle(): string {
        return __( 'lar::AdminControl.Plugin.title' );
    }

    protected function getViewData(): array {
        abort_unless( PluginResource::canAccess(), 403 );
        $data = app( PluginCatalog::class )->all();
        $search = trim( $this->search );
        if ( $search === '' ) { return $data; }
        foreach ( $data['groups'] as $type => $plugins ) {
            $data['groups'][$type] = array_filter( $plugins, function ( array $plugin ) use ( $search ): bool {
                $package = $plugin['package'] ?? [];
                $text = implode( ' ', [$plugin['id'], $package['name'] ?? '', $package['description'] ?? '', $package['author'] ?? ''] );
                return mb_stripos( $text, $search ) !== false;
            } );
        }
        return $data;
    }

    protected function getHeaderActions(): array {
        return [
            Action::make( 'cleanPermissions' )
                ->label( __( 'lar::AdminControl.Plugin.cleanPermissions' ) )
                ->icon( Heroicon::OutlinedTrash )->color( 'gray' )
                ->authorize( fn (): bool => PluginResource::canAccess() )
                ->requiresConfirmation()
                ->modalIcon( Heroicon::OutlinedTrash )
                ->modalIconColor( 'danger' )
                ->modalHeading( __( 'lar::AdminControl.Plugin.cleanPermissions' ) )
                ->modalDescription( __( 'lar::AdminControl.Plugin.cleanPermissionsDescription' ) )
                ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.confirmClean' ) )
                ->modalSubmitAction( fn ( Action $action ): Action => $action->color( 'danger' ) )
                ->action( function ( Action $action ): void {
                    abort_unless( PluginResource::canAccess(), 403 );
                    if ( !PluginPermissionService::clean() ) {
                        Notification::make()->title( __( 'lar::AdminControl.Plugin.cleanFailed' ) )->danger()->send();
                        $action->halt();
                        return;
                    }
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.cleaned' ) )->success()->send();
                } ),
            Action::make( 'installPlugin' )
                ->label( __( 'lar::AdminControl.Plugin.install' ) )
                ->icon( Heroicon::OutlinedArrowDownTray )
                ->authorize( fn (): bool => PluginResource::canMaintain() )
                ->modalHeading( __( 'lar::AdminControl.Plugin.install' ) )
                ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.install' ) )
                ->modalSubmitAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedArrowDownTray ) )
                ->modalCancelAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedXMark ) )
                ->extraModalWindowAttributes( ['class' => 'plugin-install-modal'] )
                ->modalFooterActionsAlignment( Alignment::End )
                ->stickyModalHeader()->stickyModalFooter()
                ->schema( [
                    ToggleButtons::make( 'method' )->label( __( 'lar::AdminControl.Plugin.method' ) )->hiddenLabel()
                        ->options( [
                            'link' => __( 'lar::AdminControl.Plugin.linkInstall' ),
                            'upload' => __( 'lar::AdminControl.Plugin.uploadInstall' ),
                        ] )->icons( [
                            'link' => Heroicon::OutlinedLink,
                            'upload' => Heroicon::OutlinedArrowUpTray,
                        ] )->default( 'link' )->inline()->required()->live()
                        ->extraAttributes( ['class' => 'plugin-install-method'] )->rules( 'required|in:link,upload' ),
                    TextInput::make( 'link' )->label( __( 'lar::AdminControl.Plugin.link' ) )
                        ->prefixIcon( Heroicon::OutlinedLink )->placeholder( 'https://github.com/author/plugin' )
                        ->helperText( __( 'lar::AdminControl.Plugin.linkHelp' ) )
                        ->visible( fn ( Get $get ): bool => $get( 'method' ) === 'link' )
                        ->required()->rules( 'url:http,https|max:2048' ),
                    FileUpload::make( 'archive' )->label( __( 'lar::AdminControl.Plugin.archive' ) )
                        ->helperText( __( 'lar::AdminControl.Plugin.archiveHelp' ) )
                        ->visible( fn ( Get $get ): bool => $get( 'method' ) === 'upload' )
                        ->acceptedFileTypes( ['application/zip', 'application/x-zip-compressed'] )
                        ->maxSize( config( 'lar.admin.upload.max_size', 10240 ) )
                        ->storeFiles( false )->required(),
                ] )
                ->action( function ( array $data, Action $action ): void {
                    abort_unless( PluginResource::canMaintain(), 403 );
                    if ( $data['method'] === 'link' ) {
                        $installed = PluginPackService::installToLink( $data['link'] );
                    }else {
                        $archive = $data['archive'];
                        abort_unless( $archive instanceof TemporaryUploadedFile, 422 );
                        $installed = PluginPackService::install( $archive->getRealPath() );
                    }
                    if ( !$installed ) {
                        Notification::make()->title( __( 'lar::AdminControl.Plugin.installFailed' ) )
                            ->body( __( 'lar::AdminControl.Plugin.installFailedHelp' ) )->danger()->send();
                        $action->halt();
                        return;
                    }
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.installed' ) )->success()->send();
                } ),
        ];
    }

    /**
     * 插件管理视图弹窗
     * 每次渲染重新从工作目录验证插件，仅执行其管理视图。
     * @return Action 管理视图操作
     */
    public function pluginAdminAction(): Action {
        return Action::make( 'pluginAdmin' )
            ->label( __( 'lar::AdminControl.Plugin.admin' ) )
            ->icon( Heroicon::OutlinedWindow )
            ->modalAutofocus( false )
            ->authorize( fn (): bool => PluginResource::canAccess() )
            ->modalHeading( fn ( array $arguments ): string => $this->getAdminPlugin( $arguments )['package']['name'] )
            ->modalWidth( \Filament\Support\Enums\Width::FourExtraLarge )
            ->stickyModalHeader()->stickyModalFooter()
            ->modalSubmitAction( false )
            ->modalFooterActionsAlignment( Alignment::End )
            ->modalCancelActionLabel( __( 'lar::AdminControl.Plugin.close' ) )
            ->modalCancelAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedXMark ) )
            ->modalContent( function ( array $arguments ): \Illuminate\Contracts\View\View {
                $plugin = $this->getAdminPlugin( $arguments );
                $path = config( 'lar.plugin.path.work' ) . "/{$plugin['id']}/admin.blade.php";
                return view()->file( $path, ['plugin' => $plugin] );
            } );
    }

    /**
     * 卸载插件确认操作
     * 确认后复核工作目录中的目标并调用统一卸载服务。
     * @return Action 卸载操作
     */
    public function uninstallPluginAction(): Action {
        return Action::make( 'uninstallPlugin' )
            ->label( __( 'lar::AdminControl.Plugin.uninstall' ) )
            ->color( 'danger' )->icon( Heroicon::OutlinedTrash )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->requiresConfirmation()
            ->modalIcon( Heroicon::OutlinedTrash )->modalIconColor( 'danger' )
            ->modalHeading( function ( array $arguments ): string {
                $plugin = $this->getMaintenancePlugin( $arguments );
                return __( 'lar::AdminControl.Plugin.uninstallHeading', ['name' => $plugin['package']['name'] ?? $plugin['id']] );
            } )
            ->modalDescription( __( 'lar::AdminControl.Plugin.uninstallDescription' ) )
            ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.uninstall' ) )
            ->action( function ( array $arguments, Action $action ): void {
                $plugin = $this->getMaintenancePlugin( $arguments );
                $path = config( 'lar.plugin.path.work' ) . "/{$plugin['id']}";
                if ( is_link( $path ) || !PluginPackService::uninstall( $plugin['id'] ) ) {
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.uninstallFailed' ) )->danger()->send();
                    $action->halt();
                    return;
                }
                Notification::make()->title( __( 'lar::AdminControl.Plugin.uninstalled' ) )->success()->send();
            } );
    }

    /**
     * 从插件来源更新
     * 确认后重新读取包来源，并复用链接安装服务处理更新。
     * @return Action 更新操作
     */
    public function updatePluginAction(): Action {
        return Action::make( 'updatePlugin' )
            ->label( __( 'lar::AdminControl.Plugin.update' ) )
            ->icon( Heroicon::OutlinedArrowDownTray )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->requiresConfirmation()
            ->modalHeading( function ( array $arguments ): string {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ) && trim( $plugin['package']['source'] ) !== '', 404 );
                return __( 'lar::AdminControl.Plugin.updateHeading', ['name' => $plugin['package']['name']] );
            } )
            ->modalDescription( __( 'lar::AdminControl.Plugin.updateDescription' ) )
            ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.update' ) )
            ->modalSubmitAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedArrowDownTray ) )
            ->modalCancelAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedXMark ) )
            ->action( function ( array $arguments, Action $action ): void {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ) && trim( $plugin['package']['source'] ) !== '', 404 );
                // 插件接管安装
                $source = PluginPermissionService::execute( 'REQUEST_TO_TAKE_OVER_UPDATES', [
                    'id' => $plugin['id'],
                    'package' => $plugin['package'],
                    'link' => trim( $plugin['package']['source'] ),
                    'method' => 'GET',
                    'headers' => [],
                    'data' => []
                ]);
                if (
                    !is_array( $source ) ||
                    !is_string( $source['link'] ?? null ) || trim( $source['link'] ) === '' ||
                    !is_string( $source['method'] ?? null ) || trim( $source['method'] ) === '' ||
                    !is_array( $source['headers'] ?? null ) || !is_array( $source['data'] ?? null )
                ) {
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.updateFailed' ) )
                        ->body( is_string( $source ) ? e( $source ) : __( 'lar::AdminControl.Plugin.updateSourceInvalid' ) )->danger()->send();
                    $action->halt();
                    return;
                }
                if ( !PluginPackService::installToLink( $source['link'], $source['method'], $source['headers'], $source['data'] ) ) {
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.updateFailed' ) )
                        ->body( __( 'lar::AdminControl.Plugin.installFailedHelp' ) )->danger()->send();
                    $action->halt();
                    return;
                }
                Notification::make()->title( __( 'lar::AdminControl.Plugin.updated' ) )->success()->send();
            } );
    }

    /**
     * 备份插件文件
     * 确认后复核维护权限并调用插件打包服务。
     * @return Action 备份确认操作
     */
    public function backupPluginAction(): Action {
        return Action::make( 'backupPlugin' )
            ->label( __( 'lar::AdminControl.Plugin.backup' ) )
            ->icon( Heroicon::OutlinedArchiveBox )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->requiresConfirmation()
            ->modalIcon( Heroicon::OutlinedArchiveBox )
            ->modalHeading( function ( array $arguments ): string {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ), 404 );
                return __( 'lar::AdminControl.Plugin.backupHeading', ['name' => $plugin['package']['name']] );
            } )
            ->modalDescription( __( 'lar::AdminControl.Plugin.backupDescription' ) )
            ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.backup' ) )
            ->action( function ( array $arguments, Action $action ): void {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ), 404 );
                if ( !PluginPackService::packPlugin( $plugin['id'] ) ) {
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.backupFailed' ) )->danger()->send();
                    $action->halt();
                    return;
                }
                Notification::make()->title( __( 'lar::AdminControl.Plugin.backupSucceeded' ) )
                    ->body( __( 'lar::AdminControl.Plugin.backupSaved', ['file' => "{$plugin['id']}.zip"] ) )->success()->send();
            } );
    }

    /**
     * 执行插件重置
     * 确认后校验目标插件，并根据 reset 返回值通知执行结果。
     * @return Action 重置操作
     */
    public function resetPluginAction(): Action {
        return Action::make( 'resetPlugin' )
            ->label( __( 'lar::AdminControl.Plugin.reset' ) )
            ->icon( Heroicon::OutlinedArrowPath )->color( 'warning' )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->requiresConfirmation()
            ->modalHeading( function ( array $arguments ): string {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ), 404 );
                return __( 'lar::AdminControl.Plugin.resetHeading', ['name' => $plugin['package']['name']] );
            } )
            ->modalDescription( __( 'lar::AdminControl.Plugin.resetDescription' ) )
            ->modalSubmitActionLabel( __( 'lar::AdminControl.Plugin.reset' ) )
            ->action( function ( array $arguments ): void {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ), 404 );
                $result = false;
                try {
                    $instance = PluginLoadService::init( config( 'lar.plugin.path.work' ) . "/{$plugin['id']}", boot: false );
                    if ( $instance && ( !method_exists( $instance, 'reset' ) || !( new \ReflectionMethod( $instance, 'reset' ) )->isPublic() ) ) {
                        Notification::make()->title( __( 'lar::AdminControl.Plugin.resetNotRequired' ) )->info()->send();
                        return;
                    }
                    if ( $instance ) {
                        $result = $instance->reset();
                    }
                }catch ( \Throwable $exception ) {
                    report( $exception );
                }
                if ( $result === true ) {
                    Notification::make()->title( __( 'lar::AdminControl.Plugin.resetSucceeded' ) )->success()->send();
                    return;
                }
                Notification::make()->title( __( 'lar::AdminControl.Plugin.resetFailed' ) )
                    ->body( is_string( $result ) ? e( $result ) : null )->danger()->send();
            } );
    }

    /**
     * 查看插件详情
     * 展示包元数据，权限页按需读取插件权限状态。
     * @return Action 详情弹窗操作
     */
    public function viewPluginAction(): Action {
        return Action::make( 'viewPlugin' )
            ->mountUsing( function (): void {
                $this->permissionPluginId = null;
                $this->permissionStates = [];
            } )
            ->label( __( 'lar::AdminControl.Plugin.view' ) )->icon( Heroicon::OutlinedEye )
            ->authorize( fn (): bool => PluginResource::canMaintain() )
            ->modalHeading( __( 'lar::AdminControl.Plugin.details' ) )
            ->modalWidth( \Filament\Support\Enums\Width::FourExtraLarge )
            ->stickyModalHeader()->stickyModalFooter()
            ->modalSubmitAction( false )
            ->modalFooterActionsAlignment( Alignment::End )
            ->modalCancelActionLabel( __( 'lar::AdminControl.Plugin.close' ) )
            ->modalCancelAction( fn ( Action $action ): Action => $action->icon( Heroicon::OutlinedXMark ) )
            ->modalContent( function ( array $arguments ): \Illuminate\Contracts\View\View {
                $plugin = $this->getMaintenancePlugin( $arguments );
                abort_unless( isset( $plugin['package'] ), 404 );
                $readmePath = config( 'lar.plugin.path.work' ) . '/' . $plugin['id'] . '/README.md';
                $readme = null;
                if ( is_file( $readmePath ) && !is_link( $readmePath ) ) {
                    $readme = @file_get_contents( $readmePath );
                }
                return view( 'Filament::AdminControl.PluginList.plugin-details', [
                    'plugin' => $plugin,
                    'readme' => $readme,
                    'permissionStates' => $this->permissionStates,
                    'permissionsLoaded' => $this->permissionPluginId === $plugin['id'],
                ] );
            } );
    }

    /**
     * 读取插件权限状态
     * @param string $id 插件 ID
     * @return void
     */
    public function loadPluginPermissions( string $id ): void {
        $plugin = $this->getMaintenancePlugin( ['id' => $id] );
        abort_unless( isset( $plugin['package'] ), 404 );
        $this->permissionPluginId = null;
        $this->permissionStates = [];
        $instance = PluginLoadService::init( config( 'lar.plugin.path.work' ) . "/{$id}", boot: false );
        if ( !$instance ) {
            Notification::make()->title( __( 'lar::AdminControl.Plugin.permissionsFailed' ) )->danger()->send();
            return;
        }
        PluginPermissionService::getCurrentPermissions( true );
        $this->permissionStates = $instance->getPermissions();
        $this->permissionPluginId = $id;
    }

    /**
     * 设置单项插件权限
     * @param string $id 插件 ID
     * @param string $permission 权限标识
     * @param bool $enabled 目标启用状态
     * @return void
     */
    public function setPluginPermission( string $id, string $permission, bool $enabled ): void {
        $plugin = $this->getMaintenancePlugin( ['id' => $id] );
        abort_unless( isset( $plugin['package'] ) && in_array( $permission, $plugin['package']['permissions'], true )
            && in_array( $permission, config( 'lar.plugin.permissions', [] ), true ), 403 );
        $success = $enabled ? PluginPermissionService::add( $id, [$permission] ) : PluginPermissionService::remove( $id, [$permission] );
        if ( !$success ) {
            Notification::make()->title( __( 'lar::AdminControl.Plugin.permissionFailed' ) )->danger()->send();
            return;
        }
        $this->loadPluginPermissions( $id );
        Notification::make()->title( __( 'lar::AdminControl.Plugin.permissionUpdated' ) )->success()->send();
    }

    /**
     * 批量设置插件权限
     * 启用声明权限，关闭时清除系统权限列表内的全部授权。
     * @param string $id 插件 ID
     * @param bool $enabled 目标启用状态
     * @return void
     */
    public function setAllPluginPermissions( string $id, bool $enabled ): void {
        $plugin = $this->getMaintenancePlugin( ['id' => $id] );
        abort_unless( isset( $plugin['package'] ), 404 );
        $permissions = $enabled ? $plugin['package']['permissions'] : config( 'lar.plugin.permissions', [] );
        $success = $enabled ? PluginPermissionService::add( $id, $permissions ) : PluginPermissionService::remove( $id, $permissions );
        if ( !$success ) {
            Notification::make()->title( __( 'lar::AdminControl.Plugin.permissionFailed' ) )->danger()->send();
            return;
        }
        $this->loadPluginPermissions( $id );
        Notification::make()->title( __( 'lar::AdminControl.Plugin.permissionUpdated' ) )->success()->send();
    }

    protected function getAdminPlugin( array $arguments ): array {
        $plugin = $this->getCatalogPlugin( $arguments );
        abort_unless( $plugin['hasAdmin'] ?? false, 404 );
        return $plugin;
    }

    /**
     * 获取允许维护的插件
     * 在读取配置、权限和执行插件生命周期前复核维护权限。
     * @param array $arguments 操作参数
     * @return array 插件目录信息
     */
    protected function getMaintenancePlugin( array $arguments ): array {
        abort_unless( PluginResource::canMaintain(), 403 );
        return $this->getCatalogPlugin( $arguments );
    }

    protected function getCatalogPlugin( array $arguments ): array {
        abort_unless( PluginResource::canAccess(), 403 );
        $id = $arguments['id'] ?? null;
        abort_unless( is_string( $id ), 404 );
        $groups = app( PluginCatalog::class )->all()['groups'];
        $plugin = $groups['software'][$id] ?? $groups['expand'][$id] ?? $groups['depend'][$id] ?? $groups['invalid'][$id] ?? null;
        abort_unless( $plugin, 404 );
        return $plugin;
    }
}

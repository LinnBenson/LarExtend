<?php

namespace LarExtend\Providers;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets\AccountWidget;
use Filament\Support\Colors\Color;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Http\Kernel;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use LarExtend\Filament\Concerns\EnsureAdminSession;
use LarExtend\Filament\Pages\Login;
use LarExtend\Filament\Pages\Dashboard;

class AdminPanelProvider extends PanelProvider {
    /**
     * 配置后台面板。
     * 配置管理员后台面板路径、登录守卫、资源发现和中间件。
     * @param Panel $panel Filament 面板
     * @return Panel Filament 面板
     */
    public function panel( Panel $panel ): Panel {
        $panel->default()
            ->id( 'administrator' )
            ->brandName( 'Admin Dashboard' )
            ->path( setting( 'admin.path', 'admin' ) )
            ->login( Login::class )
            ->authGuard( config( 'lar.admin.guard', 'filament' ) )
            ->databaseNotifications()
            ->databaseNotificationsPolling( '15s' )
            ->colors(config( 'lar.admin.color', [
                'primary' => Color::Blue,
            ]))
            ->discoverResources( in: app_path( 'Filament/Resources'), for: 'App\Filament\Resources' )
            ->resources(array_merge([

            ], config( 'lar.admin.resources', [] ) ))
            ->discoverPages( in: app_path( 'Filament/Pages'), for: 'App\Filament\Pages' )
            ->navigationGroups(array_map(
                static fn ( string $group ): string => __( $group ),
                config( 'lar.admin.navigation_groups', [] )
            ))
            ->pages(array_merge([
                Dashboard::class
            ], config( 'lar.admin.pages', [] ) ))
            ->discoverWidgets( in: app_path( 'Filament/Widgets'), for: 'App\Filament\Widgets' )
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->middleware( [EnsureAdminSession::class], isPersistent: true )
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<link rel="stylesheet" href="'.asset( config( 'lar.admin.asset', 'filament' ).'/css/global.css' ).'">'.
                    '<script src="'.asset( config( 'lar.admin.asset', 'filament' ).'/js/global.js' ).'" defer data-navigate-once></script>'
                )
            );
        // 管理员面板钩子
        \LarExtend\Services\PluginPermissionService::execute( 'ADMINISTRATOR_PANEL_HOOK', [ $panel ] );
        return $panel;
    }

    /**
     * 注册面板
     * 调用父类完成 Filament 面板注册。
     * @return void
     */
    public function register(): void {
        parent::register();
        $this->app->booting( function (): void {
            // 管理员面板注册钩子
            \LarExtend\Services\PluginPermissionService::execute( 'ADMINISTRATOR_PANEL_REGISTER_HOOK' );
        });

    }

    /**
     * 加载后台资源
     * 注册后台视图和语言包命名空间。
     * @return void
     */
    public function boot(): void {
        $this->callAfterResolving( HttpKernel::class, function ( Kernel $kernel ): void {
            $kernel->addToMiddlewarePriorityBefore( AuthenticatesRequests::class, EnsureAdminSession::class );
        } );
        $this->loadViewsFrom( __DIR__.'/../Filament/Views', 'Filament' );
    }
}

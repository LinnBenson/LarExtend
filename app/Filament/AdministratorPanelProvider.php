<?php

namespace App\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\HtmlString;
use App\Filament\Resources\Dashboard\Login\Login;

/**
 * Filament 后台面板服务提供器。
 * @package App\Filament
 */
class AdministratorPanelProvider extends PanelProvider {

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
            ->path( config( 'filament.path', 'admin' ) )
            ->authGuard( 'admin' )
            ->login( Login::class )
            ->databaseNotifications()
            ->databaseNotificationsPolling( '0s' )
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path( 'Filament/Resources'), for: 'App\Filament\Resources' )
            ->discoverPages(in: app_path( 'Filament/Pages'), for: 'App\Filament\Pages' )
            ->pages([
                Dashboard::class,
            ])
            ->navigationGroups( array_map(
                static fn ( string $group ): string => __( $group ),
                config( 'filament.navigation_groups', [] )
            ))
            ->discoverWidgets(in: app_path( 'Filament/Widgets' ), for: 'App\Filament\Widgets' )
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
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString( '<link rel="stylesheet" href="'.asset( config( 'filament.assets_path' ).'/css/global.css' ).'">' )
            );
        return $panel;
    }

    /**
     * 注册后台资源
     * @return void
     */
    public function register(): void {
        $this->loadViewsFrom( app_path( 'Filament/Views' ), 'Filament' );
        $this->loadTranslationsFrom( app_path( 'Filament/Lang' ), 'admin' );
        $this->mergeConfigFrom( app_path( 'Filament/Config/admin_level.php' ), 'admin_level' );
        parent::register();
    }

}

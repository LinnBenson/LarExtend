<?php

namespace LarExtend\Providers;

use Illuminate\Support\ServiceProvider;
use LarExtend\Commands\InstallCommand;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Debug\ExceptionHandler;
use LarExtend\Exceptions\ValidationExceptionHandler;

/**
 * 基础支持提供者
 */
class SupportProvider extends ServiceProvider {
    /**
     * 接入应用服务启动流程
     * @return void
     */
    public function boot(): void {
        // 注册模型与策略映射
        foreach ( config( 'lar.admin.policies', [] ) as $model => $policy ) {
            Gate::policy( $model, $policy );
        }
        $this->registerCommands(); // 注册控制台命令
        config([ 'filament.assets_path' => config( 'lar.admin.asset', 'filament' ) ]); // 设置 Filament 静态资源路径
        /**
         * 注册路由表
         */
        if ( ! $this->app->routesAreCached() ) {
            Route::middleware( ['web'] )->group( __DIR__.'/../Routes/web.php' );
            Route::middleware( ['api'] )->prefix( 'api' )->group( __DIR__.'/../Routes/api.php' );
        }
        // 频道授权定义需要在启用路由缓存后继续加载
        $this->app->booted( function (): void {
            Broadcast::routes( ['middleware' => ['web']] );
            require __DIR__.'/../Routes/channels.php';
        } );
        // 应用服务启动钩子
        \LarExtend\Services\PluginPermissionService::execute( 'APP_SERVICE_BOOT_HOOK' );
    }

    /**
     * 注册应用程序服务
     * @return void
     */
    public function register(): void {
        $this->loadTranslationsFrom( __DIR__.'/../../resources/lang', 'lar' ); // 加载 LarExtend 语言包
        $this->replaceConfigRecursivelyFrom( __DIR__.'/../config.php', 'lar' ); // 递归覆盖 LarExtend 默认配置
        config( ['filesystems.disks.larAdmin' => config( 'lar.admin.upload.disk' )] );
        $this->registerAdminGuard(); // 注册后台默认的路由守卫
        // 注册验证异常处理器
        $this->callAfterResolving( ExceptionHandler::class, function ( ExceptionHandler $handler ): void {
            $handler->renderable( new ValidationExceptionHandler() );
        });
        $this->app->booting( function (): void {
            // 应用服务注册钩子
            \LarExtend\Services\PluginPermissionService::execute( 'APP_SERVICE_REGISTER_HOOK' );
        });
        $this->callAfterResolving( Schedule::class, function ( Schedule $schedule ): void {
            // 插件定时任务注册
            \LarExtend\Services\PluginPermissionService::execute( 'SCHEDULE_REGISTRATION', [$schedule] );
        });
    }

    /**
     * 注册控制台命令
     * @return void
     */
    private function registerCommands(): void {
        if ( $this->app->runningInConsole() ) {
            $this->callAfterResolving( ConsoleKernelContract::class, function ( ConsoleKernel $kernel ): void {
                $kernel->addCommandRoutePaths( [__DIR__.'/../Routes/console.php'] );
            } );
            $this->commands( [
                InstallCommand::class,
            ]);
            $this->publishes( [
                __DIR__.'/../../resources/filament' => public_path( config( 'lar.admin.asset', 'filament' ) ),
            ], 'lar-extend-assets' );
            // 注册控制台命令钩子
            \LarExtend\Services\PluginPermissionService::execute( 'CONSOLE_COMMAND_REGISTRATION' );
        }
    }

    /**
     * 注册后台默认的路由守卫
     * @return void
     */
    private function registerAdminGuard(): void {
        config([
            'auth.guards.filament' => config( 'auth.guards.admin', [
                'driver' => 'session',
                'provider' => 'admin_users',
            ])
        ]);
        config([
            'auth.providers.admin_users' => config( 'auth.providers.admin_users', [
                'driver' => 'eloquent',
                'model' => \LarExtend\Models\AdminUser::class,
            ])
        ]);
    }
}

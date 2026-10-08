<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\AdminUser;
use App\Filament\Resources\AdminControl\AdminUsers\AdminUserPolicy;

class AppServiceProvider extends ServiceProvider {

    /**
     * 注册应用服务
     */
    public function register(): void {

    }

    /**
     * 启动应用服务
     */
    public function boot(): void {
        Gate::policy( AdminUser::class, AdminUserPolicy::class );
    }

}

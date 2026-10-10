<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;

// 主页面路由
Route::get( '/', [IndexController::class, 'index'] )->name( 'index' );
// 前端调试工具
if ( config( 'app.debug' ) ) {
    Route::any( '/debug', [IndexController::class, 'debug'] )->name( 'index.debug' );
}

// WEB 路由注册
\App\Services\PluginService::HookPlugin( 'WEB_ROUTE_REGISTRATION' );